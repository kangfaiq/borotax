<?php

namespace App\Filament\Resources\WajibPajakResource\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\WajibPajakResource\Widgets\RiwayatPerubahanWajibPajak;
use App\Filament\Resources\WajibPajakResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWajibPajak extends ViewRecord
{
    protected static string $resource = WajibPajakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn(): bool => WajibPajakResource::canEditApprovedRecord($this->record)),
        ];
    }

    /**
     * Footer widgets: Riwayat Perubahan
     */
    protected function getFooterWidgets(): array
    {
        return [
            RiwayatPerubahanWajibPajak::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }
}
