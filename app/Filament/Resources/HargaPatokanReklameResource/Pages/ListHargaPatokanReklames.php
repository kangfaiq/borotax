<?php

namespace App\Filament\Resources\HargaPatokanReklameResource\Pages;

use App\Filament\Resources\HargaPatokanReklameResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHargaPatokanReklames extends ListRecords
{
    protected static string $resource = HargaPatokanReklameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}