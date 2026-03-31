<?php

namespace App\Filament\Resources\KelompokLokasiJalanResource\Pages;

use App\Filament\Resources\KelompokLokasiJalanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKelompokLokasiJalans extends ListRecords
{
    protected static string $resource = KelompokLokasiJalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}