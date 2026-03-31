<?php

namespace App\Filament\Resources\TaxObjectResource\Widgets;

use App\Domain\Shared\Models\ActivityLog;
use App\Domain\Shared\Models\DataChangeRequest;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class RiwayatPerubahanTaxObject extends Widget
{
    protected string $view = 'filament.widgets.riwayat-perubahan-widget';

    protected int|string|array $columnSpan = 'full';

    public ?Model $record = null;

    protected function getViewData(): array
    {
        if (!$this->record) {
            return ['activityLogs' => collect(), 'changeRequests' => collect()];
        }

        $activityLogs = ActivityLog::forTarget('tax_objects', $this->record->id)
            ->with('actor')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $changeRequests = DataChangeRequest::forEntity('tax_objects', $this->record->id)
            ->with(['requester', 'reviewer'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return [
            'activityLogs' => $activityLogs,
            'changeRequests' => $changeRequests,
        ];
    }
}
