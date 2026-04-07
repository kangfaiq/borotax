<?php

namespace App\Filament\Resources\ReklameNilaiStrategisResource\Pages;

use App\Filament\Resources\ReklameNilaiStrategisResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReklameNilaiStrategis extends ListRecords
{
    protected static string $resource = ReklameNilaiStrategisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}