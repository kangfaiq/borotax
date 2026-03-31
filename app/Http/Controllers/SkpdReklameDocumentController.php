<?php

namespace App\Http\Controllers;

use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Master\Models\Pimpinan;
use Barryvdh\DomPDF\Facade\Pdf;

class SkpdReklameDocumentController extends Controller
{
    /**
     * Download SKPD Reklame as PDF.
     */
    public function download(string $skpdId)
    {
        $skpd = SkpdReklame::with(['reklameObject', 'jenisPajak', 'subJenisPajak', 'asetReklamePemkab', 'permohonanSewa'])
            ->findOrFail($skpdId);

        $this->authorizeSkpdAccess($skpd);

        return $this->generatePdf($skpd, 'download');
    }

    /**
     * Show (stream) SKPD Reklame as PDF in browser.
     */
    public function show(string $skpdId)
    {
        $skpd = SkpdReklame::with(['reklameObject', 'jenisPajak', 'subJenisPajak', 'asetReklamePemkab', 'permohonanSewa'])
            ->findOrFail($skpdId);

        $this->authorizeSkpdAccess($skpd);

        return $this->generatePdf($skpd, 'stream');
    }

    /**
     * Public: tampilkan PDF SKPD sewa reklame (signed URL, tanpa login).
     */
    public function showPublic(string $skpdId)
    {
        $skpd = SkpdReklame::with(['jenisPajak', 'subJenisPajak', 'asetReklamePemkab', 'permohonanSewa'])
            ->where('status', 'disetujui')
            ->whereNotNull('permohonan_sewa_id')
            ->findOrFail($skpdId);

        return $this->generatePdf($skpd, 'stream');
    }

    /**
     * Public: unduh PDF SKPD sewa reklame (signed URL, tanpa login).
     */
    public function downloadPublic(string $skpdId)
    {
        $skpd = SkpdReklame::with(['jenisPajak', 'subJenisPajak', 'asetReklamePemkab', 'permohonanSewa'])
            ->where('status', 'disetujui')
            ->whereNotNull('permohonan_sewa_id')
            ->findOrFail($skpdId);

        return $this->generatePdf($skpd, 'download');
    }

    /**
     * Generate PDF from SKPD record.
     */
    private function generatePdf(SkpdReklame $skpd, string $mode = 'stream')
    {
        $pimpinan = $skpd->pimpinan_id
            ? Pimpinan::find($skpd->pimpinan_id)
            : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

        $pdf = Pdf::loadView('documents.skpd-reklame', [
            'skpd' => $skpd,
            'pimpinan' => $pimpinan,
            'isPdf' => true,
        ]);

        $pdf->setPaper([0, 0, 609.449, 935.433], 'portrait');

        $filename = 'SKPD_Reklame_' . str_replace([' ', '/'], '_', $skpd->nomor_skpd) . '.pdf';

        return $mode === 'download'
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    private function authorizeSkpdAccess(SkpdReklame $skpd): void
    {
        $user = auth()->user();

        if ($user?->hasRole(['admin', 'verifikator', 'petugas'])) {
            return;
        }

        $nikHash = $user?->nik ? ReklameObject::generateHash($user->nik) : null;
        $matchesOwnerByObject = $nikHash
            && $skpd->reklameObject
            && $skpd->reklameObject->nik_hash === $nikHash;
        $matchesOwnerByNpwpd = $user?->wajibPajak?->npwpd
            && $skpd->npwpd
            && $user->wajibPajak->npwpd === $skpd->npwpd;

        abort_unless($matchesOwnerByObject || $matchesOwnerByNpwpd, 404);
    }
}
