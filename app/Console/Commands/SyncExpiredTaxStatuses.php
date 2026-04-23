<?php

namespace App\Console\Commands;

use App\Filament\Resources\ActivityLogResource;
use App\Enums\TaxStatus;
use App\Domain\Shared\Models\ActivityLog;
use App\Domain\Shared\Services\NotificationService;
use App\Domain\Tax\Models\Tax;
use Illuminate\Console\Command;

class SyncExpiredTaxStatuses extends Command
{
    private const MAX_NOTIFICATION_BILLING_CODES = 5;

    private const MAX_NOTIFICATION_JENIS_PAJAK = 3;

    private const MAX_NOTIFICATION_SOURCE_STATUSES = 3;

    protected $signature = 'tax:sync-expired-statuses';

    protected $description = 'Sinkronisasi billing overdue menjadi status lewat jatuh tempo';

    public function handle(): int
    {
        $result = Tax::syncExpiredStatusesWithDetails();
        $updated = $result['count'];

        if ($updated > 0) {
            $billingCodes = collect($result['billing_codes']);
            $body = $this->buildNotificationBody($result);

            ActivityLog::log(
                action: ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES,
                actorId: null,
                actorType: 'system',
                targetTable: 'taxes',
                targetId: $billingCodes->first(),
                description: $body,
                oldValues: ['count' => 0],
                newValues: [
                    'count' => $updated,
                    'billing_codes' => $billingCodes->all(),
                    'jenis_pajak_breakdown' => $result['jenis_pajak_breakdown'],
                    'source_status_breakdown' => $result['source_status_breakdown'],
                ],
                summaryCount: $updated,
                sourceStatuses: $this->formatSourceStatusesForStorage(collect($result['source_status_breakdown'])),
            );

            $historyUrl = ActivityLogResource::getAutoExpireHistoryUrl();

            foreach ($result['jenis_pajak_breakdown'] as $item) {
                $count = (int) $item['count'];
                $label = $item['label'];

                NotificationService::notifyRole(
                    ['admin', 'verifikator', 'petugas'],
                    "Sinkronisasi Billing Lewat Jatuh Tempo - {$label}",
                    $body,
                    actionLabel: 'Lihat Histori Auto-Expire',
                    actionUrl: $historyUrl,
                );
            }
        }

        $this->info("Selesai. {$updated} billing overdue disinkronkan menjadi lewat jatuh tempo.");

        return self::SUCCESS;
    }

    private function buildNotificationBody(array $result): string
    {
        $updated = (int) $result['count'];
        $billingList = $this->formatBillingBatchSummary(collect($result['billing_codes']));
        $jenisPajakSummary = $this->formatJenisPajakSummary(collect($result['jenis_pajak_breakdown']));
        $sourceStatusSummary = $this->formatSourceStatusSummary(collect($result['source_status_breakdown']));

        if ($updated === 1) {
            return "1 billing otomatis ditandai lewat jatuh tempo. Billing batch: {$billingList}. Jenis pajak: {$jenisPajakSummary}. Status asal: {$sourceStatusSummary}.";
        }

        return "{$updated} billing otomatis ditandai lewat jatuh tempo. Billing batch: {$billingList}. Ringkasan per jenis pajak: {$jenisPajakSummary}. Status asal: {$sourceStatusSummary}.";
    }

    private function formatBillingBatchSummary($billingCodes): string
    {
        $visibleCodes = $billingCodes->take(self::MAX_NOTIFICATION_BILLING_CODES);
        $remainingCount = $billingCodes->count() - $visibleCodes->count();

        return $visibleCodes->implode(', ')
            . ($remainingCount > 0 ? " (+{$remainingCount} lainnya)" : '');
    }

    private function formatJenisPajakSummary($jenisPajakBreakdown): string
    {
        $visibleBreakdown = $jenisPajakBreakdown->take(self::MAX_NOTIFICATION_JENIS_PAJAK)
            ->map(fn (array $item): string => "{$item['label']}: {$item['count']} billing");

        $remainingCount = $jenisPajakBreakdown->count() - $visibleBreakdown->count();

        return $visibleBreakdown->implode('; ')
            . ($remainingCount > 0 ? " (+{$remainingCount} jenis lain)" : '');
    }

    private function formatSourceStatusSummary($sourceStatusBreakdown): string
    {
        $visibleBreakdown = $sourceStatusBreakdown->take(self::MAX_NOTIFICATION_SOURCE_STATUSES)
            ->map(fn (array $item): string => "{$item['label']}: {$item['count']} billing");

        $remainingCount = $sourceStatusBreakdown->count() - $visibleBreakdown->count();

        return $visibleBreakdown->implode('; ')
            . ($remainingCount > 0 ? " (+{$remainingCount} status lain)" : '');
    }

    private function formatSourceStatusesForStorage($sourceStatusBreakdown): string
    {
        $keys = $sourceStatusBreakdown
            ->map(fn (array $item): ?string => match ($item['label']) {
                TaxStatus::Pending->getLabel() => TaxStatus::Pending->value,
                TaxStatus::Verified->getLabel() => TaxStatus::Verified->value,
                TaxStatus::PartiallyPaid->getLabel() => TaxStatus::PartiallyPaid->value,
                default => null,
            })
            ->filter()
            ->values();

        return $keys->isEmpty() ? '' : ',' . $keys->implode(',') . ',';
    }
}