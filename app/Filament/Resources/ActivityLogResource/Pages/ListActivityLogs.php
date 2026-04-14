<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('autoExpireHistory')
                ->label('Histori Auto-Expire')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->url(ActivityLogResource::getAutoExpireHistoryUrl()),
        ];
    }
}
