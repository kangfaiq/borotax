<?php

namespace App\Filament\Resources\PortalMblbSubmissionResource\Pages;

use App\Filament\Resources\PortalMblbSubmissionResource;
use Filament\Resources\Pages\ListRecords;

class ListPortalMblbSubmissions extends ListRecords
{
    protected static string $resource = PortalMblbSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}