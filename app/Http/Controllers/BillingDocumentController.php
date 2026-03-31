<?php

namespace App\Http\Controllers;

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
    public function checkStatus(string $taxId): RedirectResponse
    {
        $tax = Tax::findOrFail($taxId);

        if ($tax->status === TaxStatus::Paid && $tax->sptpd_number) {
            return redirect()->route('portal.sptpd.show', $tax->id);
        }

        return redirect()->route('portal.billing.document.show', $tax->id);
    }

    // ── Billing SA ────────────────────────────────────────────────────────

    public function show(string $taxId): SymfonyResponse
    {
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
