<?php

namespace App\Filament\Widgets;

use Exception;
use App\Domain\Tax\Models\Tax;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Support\Htmlable;

class PendapatanPerJenisChart extends ChartWidget
{
    protected ?string $heading = 'Pendapatan per Jenis Pajak (Bulan Ini)';
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        // Ambil data pajak yang lunas bulan ini
        $taxes = Tax::with('jenisPajak')
            ->paid()
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->get();

        // Grouping dan Summing (Manual Decryption)
        $data = $taxes->groupBy('jenisPajak.nama')
            ->map(function ($group) {
                return $group->sum(function ($tax) {
                    try {
                        // Cek apakah amount terenkripsi / perlu didekripsi
                        // Karena trait HasEncryptedAttributes otomatis decrypt saat akses property,
                        // kita cukup akses $tax->amount.
                        // Tapi pastikan itu angka valid.
                        return (float) $tax->amount;
                    } catch (Exception $e) {
                        return 0;
                    }
                });
            });

        return [
            'datasets' => [
                [
                    'label' => 'Total Pendapatan (Rp)',
                    'data' => $data->values()->all(),
                    'backgroundColor' => [
                        '#3b82f6',
                        '#ef4444',
                        '#f59e0b',
                        '#10b981',
                        '#6366f1',
                        '#8b5cf6'
                    ],
                ],
            ],
            'labels' => $data->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
