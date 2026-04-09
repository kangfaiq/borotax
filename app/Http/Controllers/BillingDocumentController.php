<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Domain\Tax\Models\Tax;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Enums\TaxStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class BillingDocumentController extends Controller
{
    /**
     * Cek status pembayaran: jika sudah lunas → redirect ke SPTPD,
     * jika belum → tampilkan billing document.
     */
    public function checkStatus(string $taxId): RedirectResponse|View
    {
        $tax = Tax::with(['jenisPajak', 'taxObject', 'parent'])
            ->findOrFail($taxId);

        $this->authorizeTaxAccess($tax);

        $latestPembetulan = $this->findLatestPembetulan($tax);

        if ($latestPembetulan) {
            return $this->renderResolutionView($tax, $latestPembetulan, 'qr');
        }

        if ($tax->status === TaxStatus::Paid && $tax->sptpd_number) {
            return redirect()->route('portal.sptpd.show', $tax->id);
        }

        return redirect()->route('portal.billing.document.show', $tax->id);
    }

    // ── Billing SA ────────────────────────────────────────────────────────

    public function show(string $taxId): SymfonyResponse|View
    {
        $tax = Tax::with(['jenisPajak', 'taxObject', 'parent'])
            ->findOrFail($taxId);

        $this->authorizeTaxAccess($tax);

        $latestPembetulan = $this->findLatestPembetulan($tax);

        if ($latestPembetulan && !request()->boolean('historical')) {
            return $this->renderResolutionView($tax, $latestPembetulan, 'document');
        }

        return $this->generateBillingPdf($taxId, 'stream');
    }

    public function download(string $taxId): SymfonyResponse
    {
        return $this->generateBillingPdf($taxId, 'download');
    }

    // ── SPTPD ─────────────────────────────────────────────────────────────

    public function showSptpd(string $taxId): SymfonyResponse
    {
        return $this->generateSptpdPdf($taxId, 'stream');
    }

    public function downloadSptpd(string $taxId): SymfonyResponse
    {
        return $this->generateSptpdPdf($taxId, 'download');
    }

    // ── STPD ──────────────────────────────────────────────────────────────

    public function showStpd(string $taxId): SymfonyResponse
    {
        return $this->generateStpdPdf($taxId, 'stream');
    }

    public function downloadStpd(string $taxId): SymfonyResponse
    {
        return $this->generateStpdPdf($taxId, 'download');
    }

    // ── Private: PDF Generators ───────────────────────────────────────────

    private function generateBillingPdf(string $taxId, string $mode): SymfonyResponse
    {
        $tax = $this->loadTaxWithRelations($taxId);
        $data = $this->buildBaseViewData($tax);

        return $this->renderPdf('documents.billing-sa', $data, 'Billing', $tax->billing_code, $mode);
    }

    private function generateSptpdPdf(string $taxId, string $mode): SymfonyResponse
    {
        $tax = $this->loadTaxWithRelations($taxId);

        if (!$tax->sptpd_number) {
            abort(404, 'SPTPD belum terbit (Billing belum lunas/terverifikasi).');
        }

        $data = $this->buildBaseViewData($tax);

        return $this->renderPdf('documents.sptpd', $data, 'SPTPD', $tax->sptpd_number, $mode);
    }

    private function generateStpdPdf(string $taxId, string $mode): SymfonyResponse
    {
        $tax = $this->loadTaxWithRelations($taxId);
        $approvedManual = $tax->stpdManuals()
            ->where('status', 'disetujui')
            ->latest('tanggal_verifikasi')
            ->first();

        if (!$tax->stpd_number) {
            abort(404, 'STPD tidak tersedia untuk billing ini (Tidak ada sanksi atau belum lunas/terverifikasi).');
        }

        $sanksiBelumDibayar = $approvedManual?->isTipeSanksi()
            ? $tax->getSanksiBelumDibayar()
            : $tax->getSanksiBelumDibayar();

        $data = [
            ...$this->buildBaseViewData($tax),
            'stpdDocumentNumber'  => $approvedManual?->nomor_stpd ?? $tax->stpd_number,
            'stpdPaymentCode'     => $approvedManual?->isTipeSanksi() ? $tax->getPreferredPaymentCode() : $tax->billing_code,
            'sanksi'              => (float) $tax->sanksi,
            'pimpinan'            => Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first(),
            'sanksiBelumDibayar'  => $sanksiBelumDibayar,
            'isSanksiBelumLunas'  => $approvedManual?->isTipeSanksi() ?? ($sanksiBelumDibayar > 0),
            'stpdManual'          => $approvedManual,
        ];

        return $this->renderPdf('documents.stpd', $data, 'STPD', $tax->stpd_number, $mode);
    }

    // ── Private: Shared Helpers ───────────────────────────────────────────

    private function loadTaxWithRelations(string $taxId): Tax
    {
        $tax = Tax::with(['jenisPajak', 'subJenisPajak', 'taxObject', 'user', 'parent'])
            ->findOrFail($taxId);

        $this->authorizeTaxAccess($tax);

        if ($tax->isMblb()) {
            $tax->load('mblbDetails.hargaPatokanMblb');
        }

        if ($tax->isSarangWalet()) {
            $tax->load('sarangWaletDetail.hargaPatokanSarangWalet');
        }

        return $tax;
    }

    private function authorizeTaxAccess(Tax $tax): void
    {
        $user = auth()->user();

        abort_if(
            !$user || (!$user->hasRole(['admin', 'verifikator', 'petugas']) && $tax->user_id !== $user->id),
            404
        );
    }

    private function buildBaseViewData(Tax $tax): array
    {
        $wajibPajak = WajibPajak::where('user_id', $tax->user_id)->first();

        if (!$wajibPajak) {
            abort(404, 'Data Wajib Pajak tidak ditemukan');
        }

        $pembetulanData = $this->getPembetulanData($tax);
        $latestPembetulan = $this->findLatestPembetulan($tax);

        return [
            'tax'               => $tax,
            'taxObject'         => $tax->taxObject,
            'wajibPajak'        => $wajibPajak,
            'isPdf'             => true,
            'pembetulanKe'      => $pembetulanData['pembetulanKe'],
            'kreditPajak'       => $pembetulanData['kreditPajak'],
            'parentPaid'        => $pembetulanData['parentPaid'],
            'isMblb'            => $tax->isMblb(),
            'mblbDetails'       => $tax->isMblb() ? $tax->mblbDetails : collect(),
            'isSarangWalet'     => $tax->isSarangWalet(),
            'sarangWaletDetail' => $tax->isSarangWalet() ? $tax->sarangWaletDetail : null,
            'hasNewerPembetulan' => $latestPembetulan !== null,
            'latestPembetulan'   => $latestPembetulan,
            'historicalBillingNote' => $latestPembetulan
                ? $this->buildHistoricalBillingNote($tax, $latestPembetulan)
                : null,
        ];
    }

    private function getPembetulanData(Tax $tax): array
    {
        $pembetulanKe = (int) $tax->pembetulan_ke;
        $kreditPajak = 0;
        $parentPaid = false;

        if ($pembetulanKe > 0 && $tax->parent) {
            $kreditPajak = (float) $tax->parent->amount;
            $parentPaid = in_array($tax->parent->status, [TaxStatus::Paid, TaxStatus::Verified, TaxStatus::Cancelled]);
        }

        return compact('pembetulanKe', 'kreditPajak', 'parentPaid');
    }

    private function findLatestPembetulan(Tax $tax): ?Tax
    {
        $current = $tax;
        $latest = null;

        while (true) {
            $next = $current->children()
                ->with(['jenisPajak', 'taxObject', 'parent'])
                ->orderByDesc('revision_attempt_no')
                ->orderByDesc('created_at')
                ->first();

            if (! $next) {
                break;
            }

            $latest = $next;
            $current = $next;
        }

        return $latest;
    }

    private function resolvePrimaryDocument(Tax $tax, bool $isLatestPembetulan = false): array
    {
        if ($tax->status === TaxStatus::Paid && $tax->sptpd_number) {
            return [
                'url' => route('portal.sptpd.show', $tax->id),
                'label' => $isLatestPembetulan ? 'Lihat SPTPD Pembetulan Terbaru' : 'Lihat SPTPD',
            ];
        }

        return [
            'url' => route('portal.billing.document.show', $tax->id),
            'label' => $isLatestPembetulan ? 'Lihat Billing Pembetulan Terbaru' : 'Lihat Billing',
        ];
    }

    private function buildResolutionMessage(Tax $scannedTax, Tax $latestTax): string
    {
        if ($scannedTax->status === TaxStatus::Cancelled) {
            return "Billing yang dipindai sudah dibatalkan dan diganti. Dokumen terbaru yang berlaku saat ini adalah {$latestTax->billing_code}.";
        }

        if ($scannedTax->status === TaxStatus::Paid) {
            return "Billing yang dipindai sudah pernah dilunasi, tetapi setelah itu diterbitkan billing pembetulan. Untuk kebutuhan aktif, gunakan dokumen terbaru {$latestTax->billing_code}.";
        }

        return "Billing yang dipindai sudah memiliki pembetulan yang lebih baru. Gunakan dokumen terbaru {$latestTax->billing_code} untuk melihat kewajiban yang sedang berlaku.";
    }

    private function buildScannedDocumentNote(Tax $tax): string
    {
        return match ($tax->status) {
            TaxStatus::Cancelled => 'Dokumen ini bersifat historis karena billing asal sudah dibatalkan saat pembetulan dibuat.',
            TaxStatus::Paid => 'Dokumen ini tetap tersimpan sebagai arsip historis pembayaran sebelum pembetulan diterbitkan.',
            default => 'Dokumen ini adalah arsip billing yang dipindai melalui QR pada cetakan lama.',
        };
    }

    private function buildHistoricalBillingNote(Tax $tax, Tax $latestTax): string
    {
        if ($tax->status === TaxStatus::Cancelled) {
            return "Billing ini sudah dibatalkan dan diganti melalui pembetulan. Billing terbaru yang berlaku: {$latestTax->billing_code}.";
        }

        if ($tax->status === TaxStatus::Paid) {
            return "Billing ini sudah menjadi arsip historis karena setelah pelunasan diterbitkan pembetulan. Gunakan billing terbaru {$latestTax->billing_code} untuk melihat kewajiban terkini.";
        }

        return "Billing ini sudah memiliki pembetulan yang lebih baru. Gunakan billing terbaru {$latestTax->billing_code} untuk status kewajiban yang berlaku.";
    }

    private function renderResolutionView(Tax $tax, Tax $latestPembetulan, string $source): View
    {
        $isQrSource = $source === 'qr';

        return view('portal.billing.status-resolution', [
            'scannedTax' => $tax,
            'latestTax' => $latestPembetulan,
            'latestDocument' => $this->resolvePrimaryDocument($latestPembetulan, true),
            'resolutionMessage' => $this->buildResolutionMessage($tax, $latestPembetulan),
            'scannedDocumentNote' => $this->buildScannedDocumentNote($tax),
            'useStandaloneLayout' => $this->shouldUseStandaloneResolutionLayout(),
            'contextTitle' => $isQrSource
                ? 'Billing yang dipindai sudah memiliki pembetulan yang lebih baru'
                : 'Billing lama yang Anda buka sudah memiliki pembetulan yang lebih baru',
            'contextKicker' => $isQrSource ? 'Resolusi Pembetulan' : 'Banner Pembetulan',
            'showScannedDocumentAction' => true,
            'scannedDocumentUrl' => route('portal.billing.document.show', ['taxId' => $tax->id, 'historical' => 1]),
        ]);
    }

    private function shouldUseStandaloneResolutionLayout(): bool
    {
        return auth()->user()?->hasRole(['admin', 'verifikator', 'petugas']) ?? false;
    }

    private function renderPdf(string $view, array $data, string $prefix, string $number, string $mode): SymfonyResponse
    {
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper([0, 0, 609.449, 935.433], 'portrait');

        $filename = $prefix . '_' . str_replace([' ', '/'], '_', $number) . '.pdf';

        return $mode === 'download'
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
