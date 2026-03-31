<?php

namespace App\Filament\Resources\DaftarWajibPajakResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\DaftarWajibPajakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDaftarWajibPajaks extends ListRecords
{
    protected static string $resource = DaftarWajibPajakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Daftar Wajib Pajak Baru'),
        ];
    }
}
