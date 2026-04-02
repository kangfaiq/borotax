<?php

namespace App\Filament\Resources\HargaPatokanSarangWaletResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use App\Filament\Resources\HargaPatokanSarangWaletResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHargaPatokanSarangWalet extends EditRecord
{
    protected static string $resource = HargaPatokanSarangWaletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
