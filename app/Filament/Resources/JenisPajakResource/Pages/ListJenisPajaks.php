<?php

namespace App\Filament\Resources\JenisPajakResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\JenisPajakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJenisPajaks extends ListRecords
{
    protected static string $resource = JenisPajakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
