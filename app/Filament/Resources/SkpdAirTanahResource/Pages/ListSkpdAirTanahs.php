<?php

namespace App\Filament\Resources\SkpdAirTanahResource\Pages;

use App\Filament\Resources\SkpdAirTanahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSkpdAirTanahs extends ListRecords
{
    protected static string $resource = SkpdAirTanahResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
