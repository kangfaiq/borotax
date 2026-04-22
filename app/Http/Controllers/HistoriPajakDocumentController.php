<?php

namespace App\Http\Controllers;

use App\Domain\HistoriPajak\Exceptions\WajibPajakTidakDitemukanException;
use App\Domain\HistoriPajak\Services\HistoriPajakService;
use App\Exports\HistoriPajakExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HistoriPajakDocumentController extends Controller
{
    public function exportExcel(Request $request, HistoriPajakService $service): SymfonyResponse
    {
        [$npwpd, $tahun] = $this->validateInput($request);
        $rows = $this->resolveRows($service, $npwpd, $tahun);
        $ringkasan = $service->ringkasan($rows);

        try {
            $content = Excel::raw(
                new HistoriPajakExport($rows, $ringkasan, $npwpd, $tahun),
                ExcelWriter::XLSX
            );

            return response($content, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename=Histori-Pajak-' . $npwpd . '-' . $tahun . '.xlsx',
                'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal mengekspor histori pajak ke Excel.', [
                'npwpd' => $npwpd,
                'tahun' => $tahun,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Gagal membuat file Excel. Silakan coba lagi.');
        }
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