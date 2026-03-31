<?php

namespace App\Http\Controllers;

use App\Domain\Master\Models\Pimpinan;
use App\Domain\Tax\Models\TaxAssessmentLetter;
use App\Domain\WajibPajak\Models\WajibPajak;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class TaxAssessmentLetterDocumentController extends Controller
{
    public function show(string $letterId): Response
    {
        return $this->generatePdf($letterId, 'stream');
    }

    public function download(string $letterId): Response
    {
        return $this->generatePdf($letterId, 'download');
    }

    private function generatePdf(string $letterId, string $mode): Response
    {
        $letter = TaxAssessmentLetter::with([
            'sourceTax.jenisPajak',
            'sourceTax.subJenisPajak',
            'sourceTax.taxObject',
            'sourceTax.user',
            'generatedTax',
            'parentLetter.generatedTax',
            'user',
            'pimpinan',
            'compensations.targetTax',
        ])->findOrFail($letterId);

        $this->authorizeAccess($letter);

        if (!$letter->isApproved()) {
            abort(404, 'Surat ketetapan belum disetujui.');
        }

        $tax = $letter->sourceTax;
        $wajibPajak = $tax ? WajibPajak::where('user_id', $tax->user_id)->first() : null;
        $pimpinan = $letter->pimpinan_id
            ? Pimpinan::find($letter->pimpinan_id)
            : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

        $pdf = Pdf::loadView('documents.tax-assessment-letter', [
            'letter' => $letter,
            'tax' => $tax,
            'taxObject' => $tax?->taxObject,
            'wajibPajak' => $wajibPajak,
            'generatedTax' => $letter->generatedTax,
            'pimpinan' => $pimpinan,
        ]);

        $pdf->setPaper([0, 0, 609.449, 935.433], 'portrait');

        $filename = strtoupper($letter->letter_type->value) . '_' . str_replace([' ', '/'], '_', $letter->document_number) . '.pdf';

        return $mode === 'download' ? $pdf->download($filename) : $pdf->stream($filename);
    }

    private function authorizeAccess(TaxAssessmentLetter $letter): void
    {
        $user = auth()->user();

        abort_if(
            !$user || (!$user->hasRole(['admin', 'verifikator', 'petugas']) && $letter->user_id !== $user->id),
            404
        );
    }
}