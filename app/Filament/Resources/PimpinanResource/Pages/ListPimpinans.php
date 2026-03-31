<?php

namespace App\Filament\Resources\PimpinanResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PimpinanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPimpinans extends ListRecords
{
    protected static string $resource = PimpinanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
