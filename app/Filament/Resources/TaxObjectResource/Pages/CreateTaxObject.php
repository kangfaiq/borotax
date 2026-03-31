<?php

namespace App\Filament\Resources\TaxObjectResource\Pages;

use App\Domain\Tax\Models\TaxObject;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Shared\Models\ActivityLog;
use App\Filament\Resources\TaxObjectResource;
use App\Filament\Resources\TaxObjectResource\Concerns\HandlesFotoUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxObject extends CreateRecord
{
    use HandlesFotoUpload;

    protected static string $resource = TaxObjectResource::class;

    public int $daftarObjekPage = 1;

    public string $daftarObjekSearch = '';

    public function updatedDaftarObjekSearch(): void
    {
        $this->daftarObjekPage = 1;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-fill NIK from WP via NPWPD
        $wp = WajibPajak::where('npwpd', $data['npwpd'])->first();
        if ($wp) {
            $data['nik'] = $wp->nik;
            $data['nik_hash'] = $wp->nik_hash;
        }

        // Auto-generate NOPD (next sequential per NPWPD)
        $maxNopd = TaxObject::where('npwpd', $data['npwpd'])->max('nopd') ?? 0;
        $data['nopd'] = $maxNopd + 1;

        $data['tanggal_daftar'] = now()->toDateString();
        $data['is_active'] = true;

        return $data;
    }

    protected function afterCreate(): void
    {
        ActivityLog::log(
            action: 'DAFTAR_OBJEK_PAJAK',
            targetTable: 'tax_objects',
            targetId: $this->record->id,
            description: "Mendaftarkan Objek Pajak: {$this->record->nama_objek_pajak}, NPWPD: {$this->record->npwpd}, NOPD: {$this->record->nopd}"
        );

        Notification::make()
            ->title('Objek Pajak Berhasil Didaftarkan')
            ->body("NPWPD: {$this->record->npwpd} | NOPD: {$this->record->nopd}")
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
