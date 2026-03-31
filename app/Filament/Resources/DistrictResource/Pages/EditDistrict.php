<?php

namespace App\Filament\Resources\DistrictResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\DistrictResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDistrict extends EditRecord
{
    protected static string $resource = DistrictResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
