<?php

namespace App\Http\Controllers;

use App\Domain\Master\Models\Pimpinan;
use App\Domain\Retribusi\Models\SkrdSewaRetribusi;
use Barryvdh\DomPDF\Facade\Pdf;

class SkrdSewaDocumentController extends Controller
{
    public function download(string $skrdId)
    {
        $skrd = SkrdSewaRetribusi::with(['jenisPajak', 'subJenisPajak'])
            ->findOrFail($skrdId);

        $this->authorizeAccess($skrd);

        return $this->generatePdf($skrd, 'download');
    }

    public function show(string $skrdId)
    {
        $skrd = SkrdSewaRetribusi::with(['jenisPajak', 'subJenisPajak'])
            ->findOrFail($skrdId);

        $this->authorizeAccess($skrd);

        return $this->generatePdf($skrd, 'stream');
    }

    private function generatePdf(SkrdSewaRetribusi $skrd, string $mode = 'stream')
    {
        $pimpinan = $skrd->pimpinan_id
            ? Pimpinan::find($skrd->pimpinan_id)
            : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

        $pdf = Pdf::loadView('documents.skrd-sewa-tanah', [
            'skrd' => $skrd,
            'pimpinan' => $pimpinan,
            'isPdf' => true,
        ]);

        $pdf->setPaper([0, 0, 609.449, 935.433], 'portrait');

        $filename = 'SKRD_SewaTanah_' . str_replace([' ', '/'], '_', $skrd->nomor_skrd) . '.pdf';

        return $mode === 'download'
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    private function authorizeAccess(SkrdSewaRetribusi $skrd): void
    {
        $user = auth()->user();

        if ($user?->hasRole(['admin', 'verifikator', 'petugas'])) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses untuk melihat dokumen ini.');
    }
}
