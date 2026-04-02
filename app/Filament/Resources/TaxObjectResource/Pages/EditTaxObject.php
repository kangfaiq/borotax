<?php

namespace App\Filament\Resources\TaxObjectResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\TaxObjectResource;
use App\Filament\Resources\TaxObjectResource\Concerns\HandlesFotoUpload;
use App\Domain\Shared\Models\ActivityLog;
use App\Domain\Shared\Models\DataChangeRequest;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTaxObject extends EditRecord
{
    use HandlesFotoUpload;

    protected static string $resource = TaxObjectResource::class;

    public int $daftarObjekPage = 1;

    public string $daftarObjekSearch = '';

    public function updatedDaftarObjekSearch(): void
    {
        $this->daftarObjekPage = 1;
    }

    /**
     * Field yang termasuk data identitas objek (perlu approval jika berubah)
     */
    private array $identityFields = [
        'nama_objek_pajak',
        'alamat_objek',
        'kelurahan',
        'kecamatan',
        'panjang',
        'lebar',
        'luas_m2',
        'jumlah_muka',
        'harga_patokan_reklame_id',
        'lokasi_jalan_id',
        'kelompok_lokasi',
        'latitude',
        'longitude',
        'tarif_persen',
        'kelompok_pemakaian',
        'kriteria_sda',
    ];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }

    /**
     * Intercept save: buat DataChangeRequest jika ada perubahan field identitas
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Simpan old values sebelum update untuk semua field (termasuk encrypted fields)
        $oldValues = [];
        foreach (array_keys($data) as $field) {
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

        // Detect perubahan non-identitas (langsung apply)
        $nonIdentityChanges = [];
        foreach ($data as $field => $value) {
            if (in_array($field, $this->identityFields)) continue;
            $oldVal = $record->getAttribute($field);
            if ((string) ($oldVal ?? '') !== (string) ($value ?? '')) {
                $nonIdentityChanges[$field] = $value;
            }
        }

        // Jika ada perubahan identitas → buat DataChangeRequest
        if (!empty($identityChanges)) {
            // Cek apakah sudah ada pending request
            if (DataChangeRequest::hasPendingFor($record)) {
                Notification::make()
                    ->title('Sudah Ada Permintaan Pending')
                    ->body('Masih ada permintaan perubahan data yang belum direview untuk objek pajak ini.')
                    ->warning()
                    ->send();

                $this->halt();
                return $record;
            }

            DataChangeRequest::createRequest(
                entity: $record,
                fieldChanges: $identityChanges,
                alasanPerubahan: 'Perubahan data objek pajak melalui form edit backoffice.',
            );

            // Apply non-identity changes langsung
            if (!empty($nonIdentityChanges)) {
                $record->update($nonIdentityChanges);

                ActivityLog::log(
                    action: 'UPDATE_TAX_OBJECT',
                    targetTable: 'tax_objects',
                    targetId: $record->id,
                    description: 'Update field non-identitas objek pajak (langsung).',
                    oldValues: array_intersect_key($oldValues, $nonIdentityChanges),
                    newValues: $nonIdentityChanges,
                );
            }

            Notification::make()
                ->title('Permintaan Perubahan Diajukan')
                ->body('Perubahan data identitas objek pajak memerlukan persetujuan. Permintaan telah dikirim ke verifikator.')
                ->info()
                ->send();

            return $record;
        }

        // Tidak ada perubahan identitas → update langsung
        $record->update($data);

        // Log perubahan
        if (!empty($nonIdentityChanges)) {
            $logOld = [];
            $logNew = [];
            foreach ($nonIdentityChanges as $field => $newVal) {
                $logOld[$field] = $oldValues[$field] ?? null;
                $logNew[$field] = $newVal;
            }

            ActivityLog::log(
                action: 'UPDATE_TAX_OBJECT',
                targetTable: 'tax_objects',
                targetId: $record->id,
                description: 'Update objek pajak.',
                oldValues: $logOld,
                newValues: $logNew,
            );
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
