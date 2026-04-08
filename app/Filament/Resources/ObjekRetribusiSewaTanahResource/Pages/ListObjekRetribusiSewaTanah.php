<?php

namespace App\Filament\Resources\ObjekRetribusiSewaTanahResource\Pages;

use App\Filament\Resources\ObjekRetribusiSewaTanahResource;
use Filament\Resources\Pages\ListRecords;

class ListObjekRetribusiSewaTanah extends ListRecords
{
    protected static string $resource = ObjekRetribusiSewaTanahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
