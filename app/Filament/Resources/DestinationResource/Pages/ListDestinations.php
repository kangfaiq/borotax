<?php

namespace App\Filament\Resources\DestinationResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\DestinationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDestinations extends ListRecords
{
    protected static string $resource = DestinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
