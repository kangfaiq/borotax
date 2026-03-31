<?php

namespace App\Filament\Resources\GebyarSubmissionResource\Pages;

use App\Filament\Resources\GebyarSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGebyarSubmissions extends ListRecords
{
    protected static string $resource = GebyarSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
