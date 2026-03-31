<?php

namespace App\Filament\Resources\HargaPatokanReklameResource\Pages;

use App\Filament\Resources\HargaPatokanReklameResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHargaPatokanReklame extends EditRecord
{
    protected static string $resource = HargaPatokanReklameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}