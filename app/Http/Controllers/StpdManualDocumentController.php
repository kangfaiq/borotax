<?php

namespace App\Http\Controllers;

use App\Domain\Master\Models\Pimpinan;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\WajibPajak\Models\WajibPajak;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class StpdManualDocumentController extends Controller
{
    public function show(string $stpdId): Response
    {
        return $this->generatePdf($stpdId, 'stream');
    }

    public function download(string $stpdId): Response
    {
        return $this->generatePdf($stpdId, 'download');
    }

    private function generatePdf(string $stpdId, string $mode): Response
    {
        $stpd = StpdManual::with('tax')->findOrFail($stpdId);

        $this->authorizeStpdAccess($stpd);

        if ($stpd->status !== 'disetujui') {
            abort(404, 'STPD belum disetujui.');
        }

        $data = $this->buildViewData($stpd);

        $pdf = Pdf::loadView('documents.stpd', $data);
        $pdf->setPaper([0, 0, 609.449, 935.433], 'portrait');

        $filename = 'STPD_' . str_replace([' ', '/'], '_', $stpd->nomor_stpd) . '.pdf';

        return $mode === 'download' ? $pdf->download($filename) : $pdf->stream($filename);
    }

    private function buildViewData(StpdManual $stpd): array
    {
        $tax = $stpd->tax;
        $tax->load(['jenisPajak', 'subJenisPajak', 'taxObject', 'user', 'parent']);

        if ($tax->isMblb()) {
            $tax->load('mblbDetails.hargaPatokanMblb');
        }

        if ($tax->isSarangWalet()) {
            $tax->load('sarangWaletDetail.hargaPatokanSarangWalet');
        }

        $wajibPajak = WajibPajak::where('user_id', $tax->user_id)->first();

        $pembetulanKe = (int) $tax->pembetulan_ke;
        $kreditPajak = ($pembetulanKe > 0 && $tax->parent) ? (float) $tax->parent->amount : 0;

        $pimpinan = $stpd->pimpinan_id
            ? Pimpinan::find($stpd->pimpinan_id)
            : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

        $sanksi = (float) $stpd->sanksi_dihitung;

        return [
            'tax'                => $tax,
            'taxObject'          => $tax->taxObject,
            'wajibPajak'         => $wajibPajak,
            'isPdf'              => true,
            'stpdDocumentNumber' => $stpd->nomor_stpd ?? $tax->stpd_number,
            'stpdPaymentCode'    => $stpd->isTipeSanksi() ? $tax->getPreferredPaymentCode() : $tax->billing_code,
            'sanksi'             => $sanksi,
            'pembetulanKe'       => $pembetulanKe,
            'kreditPajak'        => $kreditPajak,
            'pimpinan'           => $pimpinan,
            'isMblb'             => $tax->isMblb(),
            'mblbDetails'        => $tax->isMblb() ? $tax->mblbDetails : collect(),
            'isSarangWalet'      => $tax->isSarangWalet(),
            'sarangWaletDetail'  => $tax->isSarangWalet() ? $tax->sarangWaletDetail : null,
            'sanksiBelumDibayar' => $sanksi,
            'isSanksiBelumLunas' => $stpd->tipe === 'sanksi_saja',
            'stpdManual'         => $stpd,
        ];
    }

    private function authorizeStpdAccess(StpdManual $stpd): void
    {
        $user = auth()->user();

        abort_if(
            !$user || (!$user->hasRole(['admin', 'verifikator', 'petugas']) && $stpd->tax?->user_id !== $user->id),
            404
        );
    }
}
