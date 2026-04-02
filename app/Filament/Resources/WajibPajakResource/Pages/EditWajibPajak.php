<?php

namespace App\Filament\Resources\WajibPajakResource\Pages;

use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\WajibPajakResource;
use App\Domain\Shared\Models\ActivityLog;
use App\Domain\Shared\Models\DataChangeRequest;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

class EditWajibPajak extends EditRecord
{
    protected static string $resource = WajibPajakResource::class;

    /**
     * Field identitas WP yang perlu approval jika berubah
     */
    private array $identityFields = [
        'nik',
        'nama_lengkap',
        'alamat',
        'tipe_wajib_pajak',
        'nama_perusahaan',
        'nib',
        'npwp_pusat',
        'asal_wilayah',
        'province_code',
        'regency_code',
        'district_code',
        'village_code',
    ];

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada delete untuk WP
        ];
    }

    /**
     * Intercept save: buat DataChangeRequest untuk perubahan field identitas
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Simpan old values sebelum update (termasuk encrypted fields)
        $oldValues = [];
        foreach ($this->identityFields as $field) {
            $oldValues[$field] = $record->getAttribute($field);
        }

        // Detect perubahan pada field identitas
        $identityChanges = [];
        foreach ($this->identityFields as $field) {
            if (!array_key_exists($field, $data)) continue;
            $oldVal = $oldValues[$field];
            $newVal = $data[$field];
            if ((string) ($oldVal ?? '') !== (string) ($newVal ?? '')) {
                $identityChanges[$field] = $newVal;
            }
        }

        if (empty($identityChanges)) {
            Notification::make()
                ->title('Tidak Ada Perubahan')
                ->body('Tidak ada field yang berubah.')
                ->info()
                ->send();

            return $record;
        }

        // Cek apakah sudah ada pending request
        if (DataChangeRequest::hasPendingFor($record)) {
            Notification::make()
                ->title('Sudah Ada Permintaan Pending')
                ->body('Masih ada permintaan perubahan data yang belum direview untuk wajib pajak ini.')
                ->warning()
                ->send();

            $this->halt();
            return $record;
        }

        DataChangeRequest::createRequest(
            entity: $record,
            fieldChanges: $identityChanges,
            alasanPerubahan: 'Perubahan data wajib pajak melalui form edit backoffice.',
        );

        Notification::make()
            ->title('Permintaan Perubahan Diajukan')
            ->body('Perubahan data wajib pajak memerlukan persetujuan. Permintaan telah dikirim ke verifikator.')
            ->info()
            ->send();

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
