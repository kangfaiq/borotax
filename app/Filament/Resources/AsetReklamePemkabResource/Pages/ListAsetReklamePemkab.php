<?php

namespace App\Filament\Resources\AsetReklamePemkabResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\AsetReklamePemkabResource;
use Filament\Resources\Pages\ListRecords;

class ListAsetReklamePemkab extends ListRecords
{
    protected static string $resource = AsetReklamePemkabResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
