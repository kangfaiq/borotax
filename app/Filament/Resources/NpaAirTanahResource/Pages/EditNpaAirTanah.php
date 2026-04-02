<?php

namespace App\Filament\Resources\NpaAirTanahResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use App\Filament\Resources\NpaAirTanahResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNpaAirTanah extends EditRecord
{
    protected static string $resource = NpaAirTanahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
