<?php

namespace App\Domain\HistoriPajak\Services;

use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\HistoriPajak\Dto\DokumenPajakRow;
use App\Domain\HistoriPajak\Exceptions\WajibPajakTidakDitemukanException;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Retribusi\Models\SkrdSewaRetribusi;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxAssessmentLetter;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\JenisDokumenPajak;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HistoriPajakService
{
    /**
     * Map status mentah -> label tampil.
     */
    private const STATUS_LABELS = [
        // TaxStatus
        'draft' => 'Draft',
        'pending' => 'Menunggu Pembayaran',
        'verified' => 'Terverifikasi',
        'paid' => 'Lunas',
        'expired' => 'Kedaluwarsa',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        'partially_paid' => 'Sebagian',
        // SKPD / STPD / Letter status
        'menungguVerifikasi' => 'Menunggu Verifikasi',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
    ];

    /**
     * Cari semua dokumen pajak milik NPWPD pada tahun tertentu.
     *
     * @return Collection<int, DokumenPajakRow>
     *
     * @throws WajibPajakTidakDitemukanException
     */
    public function cari(string $npwpd, int $tahun): Collection
    {
        if (! WajibPajak::query()->where('npwpd', $npwpd)->exists()) {
            throw new WajibPajakTidakDitemukanException($npwpd);
        }

        $rows = collect()
            ->merge($this->loadTax($npwpd, $tahun))
            ->merge($this->loadStpdManual($npwpd, $tahun))
            ->merge($this->loadAssessmentLetter($npwpd, $tahun))
            ->merge($this->loadSkpdReklame($npwpd, $tahun))
            ->merge($this->loadSkpdAirTanah($npwpd, $tahun))
            ->merge($this->loadSkrdSewa($npwpd, $tahun));

        return $rows
            ->sortBy([
                fn (DokumenPajakRow $a, DokumenPajakRow $b) => strcasecmp($a->jenisPajak, $b->jenisPajak),
                fn (DokumenPajakRow $a, DokumenPajakRow $b) => strcasecmp((string) $a->nopd, (string) $b->nopd),
                fn (DokumenPajakRow $a, DokumenPajakRow $b) => ($b->tanggalTerbit?->getTimestamp() ?? 0) <=> ($a->tanggalTerbit?->getTimestamp() ?? 0),
            ])
            ->values();
    }

    /**
     * Hitung ringkasan total dari kumpulan dokumen.
     *
     * @param  Collection<int, DokumenPajakRow>  $rows
     * @return array{total_dokumen:int,total_tagihan:float,total_terbayar:float,total_tunggakan:float}
     */
    public function ringkasan(Collection $rows): array
    {
        $totalTagihan = (float) $rows->sum(fn (DokumenPajakRow $row) => $row->jumlahTagihan);
        $totalTerbayar = (float) $rows->sum(fn (DokumenPajakRow $row) => $row->jumlahTerbayar);

        return [
            'total_dokumen' => $rows->count(),
            'total_tagihan' => $totalTagihan,
            'total_terbayar' => $totalTerbayar,
            'total_tunggakan' => max(0.0, $totalTagihan - $totalTerbayar),
        ];
    }

    /**
     * @return Collection<int, DokumenPajakRow>
     */
    private function loadTax(string $npwpd, int $tahun): Collection
    {
        return Tax::query()
            ->whereHas('taxObject', fn ($q) => $q->where('npwpd', $npwpd))
            ->where('masa_pajak_tahun', $tahun)
            ->with(['taxObject:id,npwpd,nopd,nama_objek_pajak', 'jenisPajak:id,nama', 'payments'])
            ->get()
            ->map(function (Tax $tax) {
                return new DokumenPajakRow(
                    jenisDokumen: JenisDokumenPajak::BILLING,
                    jenisPajak: $tax->jenisPajak?->nama ?? '-',
                    nopd: $tax->taxObject?->nopd ? (string) $tax->taxObject->nopd : null,
                    namaObjekPajak: $tax->taxObject?->nama_objek_pajak,
                    nomor: (string) ($tax->billing_code ?? $tax->skpd_number ?? '-'),
                    masa: $this->labelMasa($tax->masa_pajak_bulan, $tax->masa_pajak_tahun),
                    tanggalTerbit: $tax->created_at,
                    jatuhTempo: $tax->payment_expired_at,
                    jumlahTagihan: (float) $tax->amount + (float) $tax->sanksi,
                    jumlahTerbayar: $tax->getTotalPaid(),
                    tanggalBayar: $tax->payments
                        ->sortByDesc(fn ($payment) => $payment->paid_at?->getTimestamp() ?? 0)
                        ->first()?->paid_at ?? $tax->paid_at,
                    status: (string) ($tax->status?->value ?? $tax->status ?? '-'),
                    statusLabel: $this->statusLabel((string) ($tax->status?->value ?? $tax->status ?? '-')),
                );
            });
    }

    /**
     * @return Collection<int, DokumenPajakRow>
     */
    private function loadStpdManual(string $npwpd, int $tahun): Collection
    {
        return StpdManual::query()
            ->whereHas('tax', function ($q) use ($npwpd, $tahun) {
                $q->where('masa_pajak_tahun', $tahun)
                    ->whereHas('taxObject', fn ($qq) => $qq->where('npwpd', $npwpd));
            })
            ->with(['tax.taxObject:id,npwpd,nopd,nama_objek_pajak', 'tax.jenisPajak:id,nama'])
            ->get()
            ->map(function (StpdManual $stpd) {
                $tax = $stpd->tax;
                $tagihan = (float) ($stpd->pokok_belum_dibayar ?? 0) + (float) $stpd->sanksi_dihitung;

                return new DokumenPajakRow(
                    jenisDokumen: JenisDokumenPajak::STPD_MANUAL,
                    jenisPajak: $tax?->jenisPajak?->nama ?? '-',
                    nopd: $tax?->taxObject?->nopd ? (string) $tax->taxObject->nopd : null,
                    namaObjekPajak: $tax?->taxObject?->nama_objek_pajak,
                    nomor: (string) ($stpd->nomor_stpd ?? '-'),
                    masa: $tax ? $this->labelMasa($tax->masa_pajak_bulan, $tax->masa_pajak_tahun) : '-',
                    tanggalTerbit: $stpd->tanggal_buat ?? $stpd->created_at,
                    jatuhTempo: $stpd->proyeksi_tanggal_bayar,
                    jumlahTagihan: $tagihan,
                    jumlahTerbayar: 0.0,
                    tanggalBayar: null,
                    status: (string) $stpd->status,
                    statusLabel: $this->statusLabel((string) $stpd->status),
                );
            });
    }

    /**
     * @return Collection<int, DokumenPajakRow>
     */
    private function loadAssessmentLetter(string $npwpd, int $tahun): Collection
    {
        return TaxAssessmentLetter::query()
            ->whereHas('sourceTax', function ($q) use ($npwpd, $tahun) {
                $q->where('masa_pajak_tahun', $tahun)
                    ->whereHas('taxObject', fn ($qq) => $qq->where('npwpd', $npwpd));
            })
            ->with(['sourceTax.taxObject:id,npwpd,nopd,nama_objek_pajak', 'sourceTax.jenisPajak:id,nama'])
            ->get()
            ->map(function (TaxAssessmentLetter $letter) {
                $tax = $letter->sourceTax;

                return new DokumenPajakRow(
                    jenisDokumen: JenisDokumenPajak::SURAT_KETETAPAN,
                    jenisPajak: $tax?->jenisPajak?->nama ?? '-',
                    nopd: $tax?->taxObject?->nopd ? (string) $tax->taxObject->nopd : null,
                    namaObjekPajak: $tax?->taxObject?->nama_objek_pajak,
                    nomor: (string) ($letter->document_number ?? '-'),
                    masa: $tax ? $this->labelMasa($tax->masa_pajak_bulan, $tax->masa_pajak_tahun) : '-',
                    tanggalTerbit: $letter->issue_date ?? $letter->created_at,
                    jatuhTempo: $letter->due_date,
                    jumlahTagihan: (float) ($letter->total_assessment ?? 0),
                    jumlahTerbayar: 0.0,
                    tanggalBayar: null,
                    status: (string) ($letter->status?->value ?? $letter->status ?? '-'),
                    statusLabel: $this->statusLabel((string) ($letter->status?->value ?? $letter->status ?? '-')),
                );
            });
    }

    /**
     * @return Collection<int, DokumenPajakRow>
     */
    private function loadSkpdReklame(string $npwpd, int $tahun): Collection
    {
        return SkpdReklame::query()
            ->where('npwpd', $npwpd)
            ->whereYear('tanggal_buat', $tahun)
            ->with(['reklameObject:id,npwpd,nopd,nama_objek_pajak', 'jenisPajak:id,nama'])
            ->get()
            ->map(function (SkpdReklame $skpd) {
                return new DokumenPajakRow(
                    jenisDokumen: JenisDokumenPajak::SKPD_REKLAME,
                    jenisPajak: $skpd->jenisPajak?->nama ?? '-',
                    nopd: $skpd->reklameObject?->nopd ? (string) $skpd->reklameObject->nopd : null,
                    namaObjekPajak: $skpd->reklameObject?->nama_objek_pajak ?? $skpd->nama_reklame,
                    nomor: (string) ($skpd->nomor_skpd ?? '-'),
                    masa: $skpd->masa_berlaku_mulai
                        ? $skpd->masa_berlaku_mulai->translatedFormat('M Y')
                        : (string) $tahun,
                    tanggalTerbit: $skpd->tanggal_buat ?? $skpd->created_at,
                    jatuhTempo: $skpd->jatuh_tempo,
                    jumlahTagihan: (float) ($skpd->jumlah_pajak ?? 0),
                    jumlahTerbayar: 0.0,
                    tanggalBayar: null,
                    status: (string) $skpd->status,
                    statusLabel: $this->statusLabel((string) $skpd->status),
                );
            });
    }

    /**
     * @return Collection<int, DokumenPajakRow>
     */
    private function loadSkpdAirTanah(string $npwpd, int $tahun): Collection
    {
        return SkpdAirTanah::query()
            ->whereHas('waterObject', fn ($q) => $q->where('npwpd', $npwpd))
            ->where(function ($q) use ($tahun) {
                $q->whereRaw('YEAR(tanggal_buat) = ?', [$tahun])
                    ->orWhereRaw("LEFT(periode_bulan, 4) = ?", [(string) $tahun]);
            })
            ->with(['waterObject:id,npwpd,nopd,nama_objek_pajak', 'jenisPajak:id,nama'])
            ->get()
            ->map(function (SkpdAirTanah $skpd) {
                return new DokumenPajakRow(
                    jenisDokumen: JenisDokumenPajak::SKPD_AIR_TANAH,
                    jenisPajak: $skpd->jenisPajak?->nama ?? '-',
                    nopd: $skpd->nopd ?: ($skpd->waterObject?->nopd ? (string) $skpd->waterObject->nopd : null),
                    namaObjekPajak: $skpd->nama_objek ?: $skpd->waterObject?->nama_objek_pajak,
                    nomor: (string) ($skpd->nomor_skpd ?? '-'),
                    masa: $this->labelMasaPeriode($skpd->periode_bulan),
                    tanggalTerbit: $skpd->tanggal_buat ?? $skpd->created_at,
                    jatuhTempo: $skpd->jatuh_tempo,
                    jumlahTagihan: (float) ($skpd->jumlah_pajak ?? 0),
                    jumlahTerbayar: 0.0,
                    tanggalBayar: null,
                    status: (string) $skpd->status,
                    statusLabel: $this->statusLabel((string) $skpd->status),
                );
            });
    }

    /**
     * @return Collection<int, DokumenPajakRow>
     */
    private function loadSkrdSewa(string $npwpd, int $tahun): Collection
    {
        return SkrdSewaRetribusi::query()
            ->where('npwpd', $npwpd)
            ->whereYear('tanggal_buat', $tahun)
            ->with(['objekRetribusi:id,npwpd,nopd,nama_objek', 'jenisPajak:id,nama'])
            ->get()
            ->map(function (SkrdSewaRetribusi $skrd) {
                return new DokumenPajakRow(
                    jenisDokumen: JenisDokumenPajak::SKRD_SEWA_TANAH,
                    jenisPajak: $skrd->jenisPajak?->nama ?? '-',
                    nopd: $skrd->objekRetribusi?->nopd ? (string) $skrd->objekRetribusi->nopd : null,
                    namaObjekPajak: $skrd->nama_objek ?? $skrd->objekRetribusi?->nama_objek,
                    nomor: (string) ($skrd->nomor_skrd ?? '-'),
                    masa: $skrd->masa_berlaku_mulai
                        ? $skrd->masa_berlaku_mulai->translatedFormat('M Y')
                        : (string) $tahun,
                    tanggalTerbit: $skrd->tanggal_buat ?? $skrd->created_at,
                    jatuhTempo: $skrd->jatuh_tempo,
                    jumlahTagihan: (float) ($skrd->jumlah_retribusi ?? 0),
                    jumlahTerbayar: 0.0,
                    tanggalBayar: null,
                    status: (string) $skrd->status,
                    statusLabel: $this->statusLabel((string) $skrd->status),
                );
            });
    }

    private function labelMasa(?int $bulan, ?int $tahun): string
    {
        if (! $tahun) {
            return '-';
        }

        if (! $bulan) {
            return (string) $tahun;
        }

        return Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('M Y');
    }

    private function labelMasaPeriode(?string $periode): string
    {
        if (! $periode) {
            return '-';
        }

        // Format diperkirakan "YYYY-MM" atau "YYYY-MM-DD"
        try {
            return Carbon::parse($periode)->translatedFormat('M Y');
        } catch (\Throwable) {
            return $periode;
        }
    }

    private function statusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}
