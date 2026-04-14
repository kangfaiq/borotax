<?php

namespace App\Filament\Widgets;

use App\Domain\Shared\Models\ActivityLog;
use App\Filament\Resources\ActivityLogResource;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LargestAutoExpireBatchWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $largestBatch = ActivityLog::query()
            ->where('action', ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES)
            ->where('created_at', '>=', now()->subDays(7))
            ->whereNotNull('summary_count')
            ->orderByDesc('summary_count')
            ->orderByDesc('created_at')
            ->first();

        if (! $largestBatch) {
            return [
                Stat::make('Batch Auto-Expire Terbesar (7 Hari)', '0 billing')
                    ->description('Belum ada batch auto-expire dalam 7 hari terakhir.')
                    ->descriptionIcon(Heroicon::OutlinedClock)
                    ->color('gray')
                    ->url(ActivityLogResource::getAutoExpireHistoryUrl()),
            ];
        }

        $description = collect([
            'Tanggal: ' . $largestBatch->created_at?->format('d/m/Y H:i'),
            'Status asal: ' . ($largestBatch->auto_expire_source_status_summary ?? '-'),
            'Batch: ' . ($largestBatch->auto_expire_billing_summary ?? '-'),
        ])->implode(' | ');

        return [
            Stat::make('Batch Auto-Expire Terbesar (7 Hari)', number_format((int) $largestBatch->summary_count) . ' billing')
                ->description($description)
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color('warning')
                ->url(ActivityLogResource::getAutoExpireHistoryUrl()),
        ];
    }
}
