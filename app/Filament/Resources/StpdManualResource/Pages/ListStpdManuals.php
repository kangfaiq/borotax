<?php

namespace App\Filament\Resources\StpdManualResource\Pages;

use App\Filament\Resources\StpdManualResource;
use Filament\Resources\Pages\ListRecords;

class ListStpdManuals extends ListRecords
{
    protected static string $resource = StpdManualResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
