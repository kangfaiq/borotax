<?php

namespace App\Http\Controllers;

use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\Master\Models\Pimpinan;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class SkpdAirTanahDocumentController extends Controller
{
    public function download(string $skpdId): Response
    {
        return $this->generatePdf($skpdId, 'download');
    }

    public function show(string $skpdId): Response
    {
        return $this->generatePdf($skpdId, 'stream');
    }

    private function generatePdf(string $skpdId, string $mode): Response
    {
        $skpd = SkpdAirTanah::with(['waterObject', 'jenisPajak', 'subJenisPajak'])
            ->findOrFail($skpdId);

        $this->authorizeSkpdAccess($skpd);

        $pimpinan = $skpd->pimpinan_id
            ? Pimpinan::find($skpd->pimpinan_id)
            : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

        $pdf = Pdf::loadView('documents.skpd-air-tanah', [
            'skpd' => $skpd,
            'pimpinan' => $pimpinan,
            'isPdf' => true,
        ]);

        $pdf->setPaper([0, 0, 609.449, 935.433], 'portrait');

        $filename = 'SKPD_Air_Tanah_' . str_replace([' ', '/'], '_', $skpd->nomor_skpd) . '.pdf';

        return $mode === 'download' ? $pdf->download($filename) : $pdf->stream($filename);
    }

    private function authorizeSkpdAccess(SkpdAirTanah $skpd): void
    {
        $user = auth()->user();

        if ($user?->hasRole(['admin', 'verifikator', 'petugas'])) {
            return;
        }

        $nikHash = $user?->nik ? WaterObject::generateHash($user->nik) : null;
        $matchesOwner = $nikHash
            && $skpd->waterObject
            && $skpd->waterObject->nik_hash === $nikHash;

        abort_unless($matchesOwner, 404);
    }
}
