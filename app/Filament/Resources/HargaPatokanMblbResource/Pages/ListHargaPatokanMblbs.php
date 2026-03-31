<?php

namespace App\Filament\Resources\HargaPatokanMblbResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\HargaPatokanMblbResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHargaPatokanMblbs extends ListRecords
{
    protected static string $resource = HargaPatokanMblbResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
