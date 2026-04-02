<?php

namespace App\Filament\Resources\HargaSatuanListrikResource\Pages;

use App\Filament\Resources\HargaSatuanListrikResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

class EditHargaSatuanListrik extends EditRecord
{
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
    protected static string $resource = HargaSatuanListrikResource::class;
}
