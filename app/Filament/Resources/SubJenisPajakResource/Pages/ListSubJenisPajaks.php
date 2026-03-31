<?php

namespace App\Filament\Resources\SubJenisPajakResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\SubJenisPajakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubJenisPajaks extends ListRecords
{
    protected static string $resource = SubJenisPajakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
