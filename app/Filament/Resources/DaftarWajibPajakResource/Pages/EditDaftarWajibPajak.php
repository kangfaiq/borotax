<?php

namespace App\Filament\Resources\DaftarWajibPajakResource\Pages;

use Filament\Actions\ViewAction;
use App\Filament\Resources\DaftarWajibPajakResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDaftarWajibPajak extends EditRecord
{
    protected static string $resource = DaftarWajibPajakResource::class;

    /**
     * Load kontak dari relasi User ke form saat edit.
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

    /**
     * Simpan kontak ke User, strip dari data WajibPajak.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = $this->record->user;

        if ($user) {
            $updateData = [];
            if (isset($data['no_whatsapp'])) {
                $updateData['no_whatsapp'] = $data['no_whatsapp'];
            }
            if (isset($data['no_telp'])) {
                $updateData['no_telp'] = $data['no_telp'];
            }
            if (isset($data['email'])) {
                $updateData['email'] = $data['email'];
            }
            if (!empty($updateData)) {
                $user->update($updateData);
            }
        }

        // Hapus field kontak dari data WajibPajak
        unset($data['email'], $data['no_whatsapp'], $data['no_telp']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
