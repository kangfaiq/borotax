<?php

namespace App\Filament\Resources\KelompokLokasiJalanResource\Pages;

use App\Filament\Resources\KelompokLokasiJalanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKelompokLokasiJalan extends EditRecord
{
    protected static string $resource = KelompokLokasiJalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}