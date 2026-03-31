<?php

namespace App\Filament\Resources\TaxObjectResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\TaxObjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxObjects extends ListRecords
{
    protected static string $resource = TaxObjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Daftar Objek Baru'),
        ];
    }
}
