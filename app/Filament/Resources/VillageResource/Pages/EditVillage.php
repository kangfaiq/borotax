<?php

namespace App\Filament\Resources\VillageResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\VillageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVillage extends EditRecord
{
    protected static string $resource = VillageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
