<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Domain\Shared\Models\ActivityLog;
use App\Filament\Resources\ActivityLogResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAutoExpireActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected static ?string $title = 'Histori Auto-Expire';

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->where('action', ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES)
            ->reorder()
            ->orderByDesc('summary_count')
            ->orderByDesc('created_at');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('allActivityLogs')
                ->label('Semua Activity Log')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(ActivityLogResource::getUrl('index')),
        ];
    }
}