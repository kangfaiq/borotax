<?php

namespace App\Filament\Resources\SkrdSewaRetribusiResource\Pages;

use App\Filament\Resources\SkrdSewaRetribusiResource;
use Filament\Resources\Pages\ListRecords;

class ListSkrdSewaRetribusi extends ListRecords
{
    protected static string $resource = SkrdSewaRetribusiResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
