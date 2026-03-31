<?php

namespace App\Filament\Widgets;

use App\Domain\Master\Models\JenisPajak;
use App\Domain\Tax\Models\TaxObject;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ObjekBelumLengkap extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $airTanah = JenisPajak::where('kode', '41108')->first();

        if (!$airTanah) {
            return [];
        }

        $totalObjekAirTanah = TaxObject::where('jenis_pajak_id', $airTanah->id)
            ->where('status', 'aktif')
            ->count();

        $belumLengkap = TaxObject::where('jenis_pajak_id', $airTanah->id)
            ->where('status', 'aktif')
            ->where(function ($q) {
                $q->whereNull('kelompok_pemakaian')
                    ->orWhere('kelompok_pemakaian', '')
                    ->orWhereNull('kriteria_sda')
                    ->orWhere('kriteria_sda', '');
            })
            ->count();

        $sudahLengkap = $totalObjekAirTanah - $belumLengkap;
        $persenLengkap = $totalObjekAirTanah > 0
            ? round(($sudahLengkap / $totalObjekAirTanah) * 100, 1)
            : 0;

        return [
            Stat::make('Objek Air Tanah - Belum Lengkap', $belumLengkap)
                ->description("Belum ada kelompok pemakaian / kriteria SDA")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($belumLengkap > 0 ? 'warning' : 'success'),

            Stat::make('Kelengkapan Data Air Tanah', "{$persenLengkap}%")
                ->description("{$sudahLengkap} dari {$totalObjekAirTanah} objek sudah lengkap")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($persenLengkap >= 100 ? 'success' : ($persenLengkap >= 50 ? 'info' : 'warning')),
        ];
    }
}
