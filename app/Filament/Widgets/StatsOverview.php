<?php

namespace App\Filament\Widgets;

use Exception;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\Tax\Models\Tax;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Reklame\Models\ReklameRequest;
use App\Domain\AirTanah\Models\MeterReport;
use App\Enums\TaxStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Total WP Terdaftar
        $totalWP = WajibPajak::where('status', 'disetujui')->count();
        $wpBulanIni = WajibPajak::where('status', 'disetujui')
            ->whereMonth('tanggal_verifikasi', now()->month)
            ->whereYear('tanggal_verifikasi', now()->year)
            ->count();
        $wpBulanLalu = WajibPajak::where('status', 'disetujui')
            ->whereMonth('tanggal_verifikasi', now()->subMonth()->month)
            ->whereYear('tanggal_verifikasi', now()->subMonth()->year)
            ->count();

        $wpTrend = $wpBulanLalu > 0
            ? round((($wpBulanIni - $wpBulanLalu) / $wpBulanLalu) * 100, 1)
            : 0;

        // Total Pendapatan Bulan Ini (hanya yang sudah terbayar)
        $totalPendapatan = Tax::where('status', TaxStatus::Paid)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->get()
            ->sum(function ($tax) {
                try {
                    return (float) $tax->amount;
                } catch (Exception $e) {
                    return 0;
                }
            });

        // Pengajuan Menunggu Verifikasi
        $wpMenunggu = WajibPajak::where('status', 'menungguVerifikasi')->count();
        $reklameMenunggu = ReklameRequest::whereIn('status', ['diajukan', 'menungguVerifikasi'])->count();
        $meterMenunggu = MeterReport::where('status', 'submitted')->count();
        $totalMenunggu = $wpMenunggu + $reklameMenunggu + $meterMenunggu;

        // SKPD Terbit Bulan Ini
        $skpdReklameTerbit = SkpdReklame::where('status', 'disetujui')
            ->whereMonth('tanggal_verifikasi', now()->month)
            ->whereYear('tanggal_verifikasi', now()->year)
            ->count();
        $skpdAirTanahTerbit = SkpdAirTanah::where('status', 'disetujui')
            ->whereMonth('tanggal_verifikasi', now()->month)
            ->whereYear('tanggal_verifikasi', now()->year)
            ->count();
        $totalSkpdTerbit = $skpdReklameTerbit + $skpdAirTanahTerbit;

        return [
            Stat::make('Total Wajib Pajak', number_format($totalWP))
                ->description($wpTrend >= 0 ? "+{$wpTrend}% dari bulan lalu" : "{$wpTrend}% dari bulan lalu")
                ->descriptionIcon($wpTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($wpTrend >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($totalPendapatan, 0, ',', '.'))
                ->description('Total dari pembayaran')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Menunggu Verifikasi', $totalMenunggu)
                ->description("WP: {$wpMenunggu} | Reklame: {$reklameMenunggu} | Air: {$meterMenunggu}")
                ->descriptionIcon('heroicon-m-clock')
                ->color($totalMenunggu > 0 ? 'warning' : 'success'),

            Stat::make('SKPD Terbit', $totalSkpdTerbit)
                ->description("Reklame: {$skpdReklameTerbit} | Air Tanah: {$skpdAirTanahTerbit}")
                ->descriptionIcon('heroicon-m-document-check')
                ->color('info'),
        ];
    }
}
