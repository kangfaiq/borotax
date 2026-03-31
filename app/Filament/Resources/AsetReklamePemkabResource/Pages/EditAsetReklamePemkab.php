<?php

namespace App\Filament\Resources\AsetReklamePemkabResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\AsetReklamePemkabResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsetReklamePemkab extends EditRecord
{
    protected static string $resource = AsetReklamePemkabResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['panjang']) && !empty($data['lebar'])) {
            $data['luas_m2'] = (float) $data['panjang'] * (float) $data['lebar'];
        }

        return $data;
    }
}
