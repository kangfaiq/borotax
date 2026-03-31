<?php

namespace App\Filament\Resources\NpaAirTanahResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\NpaAirTanahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNpaAirTanahs extends ListRecords
{
    protected static string $resource = NpaAirTanahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
