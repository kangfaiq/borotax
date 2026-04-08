<?php

namespace App\Filament\Resources\ObjekRetribusiSewaTanahResource\Pages;

use App\Filament\Resources\ObjekRetribusiSewaTanahResource;
use Filament\Resources\Pages\CreateRecord;

class CreateObjekRetribusiSewaTanah extends CreateRecord
{
    protected static string $resource = ObjekRetribusiSewaTanahResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ObjekRetribusiSewaTanahResource::syncOwnerData($data);
    }
}
