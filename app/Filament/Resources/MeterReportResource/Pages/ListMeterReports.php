<?php

namespace App\Filament\Resources\MeterReportResource\Pages;

use App\Filament\Resources\MeterReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMeterReports extends ListRecords
{
    protected static string $resource = MeterReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
