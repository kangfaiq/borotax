<?php

namespace App\Filament\Widgets;

use App\Domain\Tax\Models\Tax;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class TrendPendapatanChart extends ChartWidget
{
    protected ?string $heading = 'Trend Pendapatan (6 Bulan Terakhir)';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        // Ambil data 6 bulan terakhir
        $start = now()->subMonths(5)->startOfMonth();
        $end = now()->endOfMonth();

        $taxes = Tax::paid()
            ->whereBetween('paid_at', [$start, $end])
            ->get();

        // Siapkan array bulan
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('M Y');
        }

        // Grouping
        $data = $taxes->groupBy(function ($tax) {
            return Carbon::parse($tax->paid_at)->format('M Y');
        });

        // Mapping ke format datasets
        $values = collect($months)->map(function ($month) use ($data) {
            if (isset($data[$month])) {
                return $data[$month]->sum(fn($t) => (float) $t->amount);
            }
            return 0;
        });

        return [
            'datasets' => [
                [
                    'label' => 'Total Pendapatan',
                    'data' => $values->all(),
                    'fill' => true,
                    'borderColor' => '#3b82f6',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
