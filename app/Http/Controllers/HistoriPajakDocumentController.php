<?php

namespace App\Http\Controllers;

use App\Domain\HistoriPajak\Exceptions\WajibPajakTidakDitemukanException;
use App\Domain\HistoriPajak\Services\HistoriPajakService;
use App\Exports\HistoriPajakExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HistoriPajakDocumentController extends Controller
{
    public function exportExcel(Request $request, HistoriPajakService $service): SymfonyResponse
    {
        [$npwpd, $tahun] = $this->validateInput($request);
        $rows = $this->resolveRows($service, $npwpd, $tahun);
        $ringkasan = $service->ringkasan($rows);

        return Excel::download(
            new HistoriPajakExport($rows, $ringkasan, $npwpd, $tahun),
            'Histori-Pajak-' . $npwpd . '-' . $tahun . '.xlsx'
        );
    }

    public function showPdf(Request $request, HistoriPajakService $service): SymfonyResponse
    {
        [$npwpd, $tahun] = $this->validateInput($request);
        $rows = $this->resolveRows($service, $npwpd, $tahun);
        $ringkasan = $service->ringkasan($rows);

        $pdf = Pdf::loadView('pdf.histori-pajak', [
            'rows' => $rows,
            'ringkasan' => $ringkasan,
            'npwpd' => $npwpd,
            'tahun' => $tahun,
            'tanggalCetak' => now(),
        ]);
        $pdf->setPaper([0, 0, 935.43, 609.45], 'portrait');

        return $pdf->stream('Histori-Pajak-' . $npwpd . '-' . $tahun . '.pdf');
    }

    /**
     * @return array{0:string,1:int}
     */
    private function validateInput(Request $request): array
    {
        $validated = $request->validate([
            'npwpd' => ['required', 'string', 'regex:/^P[12]\d{11}$/'],
            'tahun' => ['required', 'integer', 'min:2019', 'max:' . now()->year],
        ], [
            'npwpd.regex' => 'Format NPWPD harus diawali P1 atau P2 diikuti 11 digit angka (total 13 karakter).',
        ]);

        return [strtoupper(trim($validated['npwpd'])), (int) $validated['tahun']];
    }

    private function resolveRows(HistoriPajakService $service, string $npwpd, int $tahun)
    {
        try {
            return $service->cari($npwpd, $tahun);
        } catch (WajibPajakTidakDitemukanException $e) {
            abort(404, 'NPWPD tidak ditemukan.');
        }
    }
}