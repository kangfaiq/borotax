<?php

namespace App\Exports;

use App\Domain\HistoriPajak\Dto\DokumenPajakRow;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class HistoriPajakExport implements WithMultipleSheets
{
    /**
     * @param  Collection<int, DokumenPajakRow>  $rows
     * @param  array<string, mixed>  $ringkasan
     */
    public function __construct(
        private Collection $rows,
        private array $ringkasan,
        private string $npwpd,
        private int $tahun,
    ) {}

    public function sheets(): array
    {
        return [
            new HistoriPajakRingkasanSheet($this->ringkasan, $this->npwpd, $this->tahun),
            new HistoriPajakDetailSheet($this->rows),
        ];
    }
}

class HistoriPajakRingkasanSheet implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;

    /**
     * @param  array<string, mixed>  $ringkasan
     */
    public function __construct(
        private array $ringkasan,
        private string $npwpd,
        private int $tahun,
    ) {}

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function headings(): array
    {
        return ['Keterangan', 'Nilai'];
    }

    public function collection(): Collection
    {
        return collect([
            [$this->sanitizeCellValue('NPWPD'), $this->sanitizeCellValue($this->npwpd)],
            [$this->sanitizeCellValue('Tahun Pajak'), $this->sanitizeCellValue((string) $this->tahun)],
            [$this->sanitizeCellValue('Total Dokumen'), $this->sanitizeCellValue((string) ($this->ringkasan['total_dokumen'] ?? 0))],
            [$this->sanitizeCellValue('Total Tagihan (Rp)'), $this->sanitizeCellValue(number_format((float) ($this->ringkasan['total_tagihan'] ?? 0), 0, ',', '.'))],
            [$this->sanitizeCellValue('Total Terbayar (Rp)'), $this->sanitizeCellValue(number_format((float) ($this->ringkasan['total_terbayar'] ?? 0), 0, ',', '.'))],
            [$this->sanitizeCellValue('Total Tunggakan (Rp)'), $this->sanitizeCellValue(number_format((float) ($this->ringkasan['total_tunggakan'] ?? 0), 0, ',', '.'))],
        ]);
    }

    private function sanitizeCellValue(?string $value): string
    {
        $value ??= '';
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';

        return mb_substr($value, 0, 32767);
    }
}

class HistoriPajakDetailSheet implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;

    /**
     * @param  Collection<int, DokumenPajakRow>  $rows
     */
    public function __construct(private Collection $rows) {}

    public function title(): string
    {
        return 'Detail Dokumen';
    }

    public function headings(): array
    {
        return [
            'Jenis Dokumen', 'Jenis Pajak', 'NOPD', 'Objek Pajak', 'Nomor', 'Masa',
            'Tanggal Terbit', 'Jatuh Tempo', 'Tanggal Bayar',
            'Tagihan (Rp)', 'Terbayar (Rp)', 'Sisa (Rp)', 'Status',
        ];
    }

    public function collection(): Collection
    {
        return $this->rows->map(fn (DokumenPajakRow $r) => [
            $this->sanitizeCellValue($r->jenisDokumen->label()),
            $this->sanitizeCellValue($r->jenisPajak),
            $this->sanitizeCellValue($r->nopd ?? '-'),
            $this->sanitizeCellValue($r->namaObjekPajak ?? '-'),
            $this->sanitizeCellValue($r->nomor),
            $this->sanitizeCellValue($r->masa),
            $this->sanitizeCellValue($r->tanggalTerbit?->format('d-m-Y') ?? '-'),
            $this->sanitizeCellValue($r->jatuhTempo?->format('d-m-Y') ?? '-'),
            $this->sanitizeCellValue($r->tanggalBayar?->format('d-m-Y H:i') ?? '-'),
            $this->sanitizeCellValue(number_format($r->jumlahTagihan, 0, ',', '.')),
            $this->sanitizeCellValue(number_format($r->jumlahTerbayar, 0, ',', '.')),
            $this->sanitizeCellValue(number_format($r->jumlahSisa(), 0, ',', '.')),
            $this->sanitizeCellValue($r->effectiveStatusLabel()),
        ])->values();
    }

    private function sanitizeCellValue(?string $value): string
    {
        $value ??= '';
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';

        return mb_substr($value, 0, 32767);
    }
}
