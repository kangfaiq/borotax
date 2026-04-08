<?php

namespace App\Filament\Resources\ObjekRetribusiSewaTanahResource\Pages;

use App\Filament\Resources\ObjekRetribusiSewaTanahResource;
use Filament\Resources\Pages\EditRecord;

class EditObjekRetribusiSewaTanah extends EditRecord
{
    protected static string $resource = ObjekRetribusiSewaTanahResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ObjekRetribusiSewaTanahResource::syncOwnerData($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
            \Filament\Actions\RestoreAction::make(),
        ];
    }
}
