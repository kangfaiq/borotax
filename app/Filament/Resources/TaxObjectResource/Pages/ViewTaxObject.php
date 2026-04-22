<?php

namespace App\Filament\Resources\TaxObjectResource\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\TaxObjectResource\Widgets\RiwayatPerubahanTaxObject;
use App\Filament\Resources\TaxObjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTaxObject extends ViewRecord
{
    protected static string $resource = TaxObjectResource::class;

    public int $daftarObjekPage = 1;

    public string $daftarObjekSearch = '';

    public function updatedDaftarObjekSearch(): void
    {
        $this->daftarObjekPage = 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    /**
     * Footer widgets: Riwayat Perubahan
     */
    protected function getFooterWidgets(): array
    {
        return [
            RiwayatPerubahanTaxObject::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }
}
