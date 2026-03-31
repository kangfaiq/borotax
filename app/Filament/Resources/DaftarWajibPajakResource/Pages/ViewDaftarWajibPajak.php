<?php

namespace App\Filament\Resources\DaftarWajibPajakResource\Pages;

use App\Filament\Resources\DaftarWajibPajakResource;
use Filament\Resources\Pages\ViewRecord;

class ViewDaftarWajibPajak extends ViewRecord
{
    protected static string $resource = DaftarWajibPajakResource::class;

    /**
     * Load kontak dari relasi User ke form saat view.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record->user;

        if ($user) {
            $data['no_whatsapp'] = $user->no_whatsapp;
            $data['no_telp'] = $user->no_telp;
            $data['email'] = $user->email;
        }

        return $data;
    }
}
