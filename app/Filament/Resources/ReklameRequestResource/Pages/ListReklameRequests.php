<?php

namespace App\Filament\Resources\ReklameRequestResource\Pages;

use App\Filament\Resources\ReklameRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReklameRequests extends ListRecords
{
    protected static string $resource = ReklameRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
