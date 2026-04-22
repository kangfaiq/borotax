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
            ['NPWPD', $this->npwpd],
            ['Tahun Pajak', (string) $this->tahun],
            ['Total Dokumen', (string) ($this->ringkasan['total_dokumen'] ?? 0)],
            ['Total Tagihan (Rp)', number_format((float) ($this->ringkasan['total_tagihan'] ?? 0), 0, ',', '.')],
            ['Total Terbayar (Rp)', number_format((float) ($this->ringkasan['total_terbayar'] ?? 0), 0, ',', '.')],
            ['Total Tunggakan (Rp)', number_format((float) ($this->ringkasan['total_tunggakan'] ?? 0), 0, ',', '.')],
        ]);
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
            $r->jenisDokumen->label(),
            $r->jenisPajak,
            $r->nopd ?? '-',
            $r->namaObjekPajak ?? '-',
            $r->nomor,
            $r->masa,
            $r->tanggalTerbit?->format('d-m-Y') ?? '-',
            $r->jatuhTempo?->format('d-m-Y') ?? '-',
            $r->tanggalBayar?->format('d-m-Y H:i') ?? '-',
            number_format($r->jumlahTagihan, 0, ',', '.'),
            number_format($r->jumlahTerbayar, 0, ',', '.'),
            number_format($r->jumlahSisa(), 0, ',', '.'),
            $r->effectiveStatusLabel(),
        ])->values();
    }
}
