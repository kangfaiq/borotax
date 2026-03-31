<?php

namespace App\Filament\Resources\PermohonanSewaReklameResource\Pages;

use App\Filament\Resources\PermohonanSewaReklameResource;
use Filament\Resources\Pages\ListRecords;

class ListPermohonanSewaReklame extends ListRecords
{
    protected static string $resource = PermohonanSewaReklameResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
