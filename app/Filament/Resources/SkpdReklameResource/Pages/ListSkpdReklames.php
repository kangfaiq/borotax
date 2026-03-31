<?php

namespace App\Filament\Resources\SkpdReklameResource\Pages;

use App\Filament\Resources\SkpdReklameResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSkpdReklames extends ListRecords
{
    protected static string $resource = SkpdReklameResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
