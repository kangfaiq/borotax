<?php

namespace App\Filament\Pages;

use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Tax\Models\Tax;
use App\Enums\TaxStatus;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class LaporanPendapatan extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static string | \UnitEnum | null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Pendapatan';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.laporan-pendapatan';

    public ?int $tahun = null;

    public function mount(): void
    {
        $this->tahun = request()->query('tahun') ? (int) request()->query('tahun') : null;
    }

    public function getTitle(): string|Htmlable
    {
        if ($this->tahun) {
            return "Laporan Pendapatan — Tahun {$this->tahun}";
        }
        return 'Laporan Pendapatan';
    }

    public function getBreadcrumbs(): array
    {
        $crumbs = [
            static::getUrl() => 'Laporan Pendapatan',
        ];

        if ($this->tahun) {
            $crumbs['#'] = "Tahun {$this->tahun}";
        }

        return $crumbs;
    }

    public function getViewData(): array
    {
        Tax::syncExpiredStatuses();

        $currentYear = (int) date('Y');
        $years = range($currentYear, 2019);

        // If no year selected, compute per-year stats
        if (!$this->tahun) {
            $yearStats = [];

            foreach ($years as $y) {
                // Semua data dari tabel Tax (termasuk tagihan dari SKPD Reklame & Air Tanah)
                $taxBase = Tax::whereYear('created_at', $y);
                $taxTransaksi = (clone $taxBase)->count();
                $taxPendapatan = (clone $taxBase)
                    ->where('status', TaxStatus::Paid)
                    ->get()
                    ->sum(fn ($t) => (float) $t->amount + (float) ($t->opsen ?? 0));
                $taxPending = (clone $taxBase)->whereIn('status', [TaxStatus::Pending, TaxStatus::Verified, TaxStatus::Expired])->count();

                // SKPD yang belum terbit (belum ada record Tax)
                $skpdReklamePending = SkpdReklame::whereYear('created_at', $y)
                    ->whereIn('status', ['draft', 'menungguVerifikasi'])
                    ->count();
                $skpdAirPending = SkpdAirTanah::whereYear('created_at', $y)
                    ->whereIn('status', ['draft', 'menungguVerifikasi'])
                    ->count();

                $yearStats[$y] = [
                    'total_transaksi'  => $taxTransaksi,
                    'total_pendapatan' => $taxPendapatan,
                    'pending'          => $taxPending + $skpdReklamePending + $skpdAirPending,
                ];
            }

            return [
                'tahun'     => null,
                'years'     => $years,
                'yearStats' => $yearStats,
            ];
        }

        // Year selected — show jenis pajak cards filtered by year
        $jenisPajaks = JenisPajak::active()->ordered()->get();

        $stats = [];
        foreach ($jenisPajaks as $jp) {
            // Semua data dari tabel Tax (termasuk tagihan dari SKPD Reklame & Air Tanah)
            $taxBase = Tax::where('jenis_pajak_id', $jp->id)
                ->whereYear('created_at', $this->tahun);

            $taxTransaksi = (clone $taxBase)->count();
            $taxPendapatan = (clone $taxBase)
                ->where('status', TaxStatus::Paid)
                ->get()
                ->sum(fn ($t) => (float) $t->amount + (float) ($t->opsen ?? 0));
            $taxBulanIni = (clone $taxBase)
                ->where('status', TaxStatus::Paid)
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', $this->tahun)
                ->get()
                ->sum(fn ($t) => (float) $t->amount + (float) ($t->opsen ?? 0));
            $taxPending = (clone $taxBase)->whereIn('status', [TaxStatus::Pending, TaxStatus::Verified, TaxStatus::Expired])->count();

            // SKPD yang belum terbit (belum ada record Tax)
            $skpdReklamePending = SkpdReklame::where('jenis_pajak_id', $jp->id)
                ->whereYear('created_at', $this->tahun)
                ->whereIn('status', ['draft', 'menungguVerifikasi'])
                ->count();
            $skpdAirPending = SkpdAirTanah::where('jenis_pajak_id', $jp->id)
                ->whereYear('created_at', $this->tahun)
                ->whereIn('status', ['draft', 'menungguVerifikasi'])
                ->count();

            $stats[$jp->id] = [
                'total_transaksi'      => $taxTransaksi,
                'total_pendapatan'     => $taxPendapatan,
                'pendapatan_bulan_ini' => $taxBulanIni,
                'pending'              => $taxPending + $skpdReklamePending + $skpdAirPending,
            ];
        }

        return [
            'tahun'       => $this->tahun,
            'jenisPajaks' => $jenisPajaks,
            'stats'       => $stats,
        ];
    }
}
