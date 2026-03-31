<?php

namespace App\Filament\Resources\PembetulanRequestResource\Pages;

use App\Filament\Resources\PembetulanRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListPembetulanRequests extends ListRecords
{
    protected static string $resource = PembetulanRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
