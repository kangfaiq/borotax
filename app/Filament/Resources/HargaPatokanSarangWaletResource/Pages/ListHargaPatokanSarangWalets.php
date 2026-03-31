<?php

namespace App\Filament\Resources\HargaPatokanSarangWaletResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\HargaPatokanSarangWaletResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHargaPatokanSarangWalets extends ListRecords
{
    protected static string $resource = HargaPatokanSarangWaletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
