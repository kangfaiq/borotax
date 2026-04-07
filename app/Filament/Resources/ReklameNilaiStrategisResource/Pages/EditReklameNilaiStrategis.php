<?php

namespace App\Filament\Resources\ReklameNilaiStrategisResource\Pages;

use App\Filament\Resources\ReklameNilaiStrategisResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditReklameNilaiStrategis extends EditRecord
{
    protected static string $resource = ReklameNilaiStrategisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}