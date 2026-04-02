<?php

namespace App\Filament\Resources\HargaPatokanMblbResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use App\Filament\Resources\HargaPatokanMblbResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHargaPatokanMblb extends EditRecord
{
    protected static string $resource = HargaPatokanMblbResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
