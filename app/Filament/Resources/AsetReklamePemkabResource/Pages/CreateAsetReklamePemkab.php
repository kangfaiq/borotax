<?php

namespace App\Filament\Resources\AsetReklamePemkabResource\Pages;

use App\Filament\Resources\AsetReklamePemkabResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAsetReklamePemkab extends CreateRecord
{
    protected static string $resource = AsetReklamePemkabResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-calculate luas if not set
        if (empty($data['luas_m2']) && !empty($data['panjang']) && !empty($data['lebar'])) {
            $data['luas_m2'] = (float) $data['panjang'] * (float) $data['lebar'];
        }

        return $data;
    }
}
