<?php

namespace App\Filament\Resources\DaftarWajibPajakResource\Pages;

use App\Domain\Auth\Models\User;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Shared\Models\ActivityLog;
use App\Filament\Resources\DaftarWajibPajakResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDaftarWajibPajak extends CreateRecord
{
    protected static string $resource = DaftarWajibPajakResource::class;

    private bool $isNewUser = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ambil data kontak dari form (disimpan ke User, bukan WajibPajak)
        $contactEmail = $data['email'];
        $contactWhatsapp = $data['no_whatsapp'] ?? null;
        $contactTelp = $data['no_telp'] ?? null;

        // Buat atau ambil user berdasarkan email
        $user = User::where('email', $contactEmail)->first();

        if (!$user) {
            $user = User::create([
                'name' => $data['nama_lengkap'],
                'email' => $contactEmail,
                'password' => '@Password123',
                'nik' => $data['nik'],
                'nama_lengkap' => $data['nama_lengkap'],
                'alamat' => $data['alamat'],
                'no_whatsapp' => $contactWhatsapp,
                'no_telp' => $contactTelp,
                'province_code' => $data['province_code'] ?? null,
                'regency_code' => $data['regency_code'] ?? null,
                'district_code' => $data['district_code'] ?? null,
                'village_code' => $data['village_code'] ?? null,
                'role' => 'wajibPajak',
                'status' => 'verified',
                'verified_at' => now(),
                'must_change_password' => true,
            ]);
            $this->isNewUser = true;
        } else {
            // Update kontak di User yang sudah ada
            $user->update([
                'no_whatsapp' => $contactWhatsapp,
                'no_telp' => $contactTelp,
            ]);
        }

        // Hapus field kontak dari data WajibPajak (sudah disimpan di User)
        unset($data['email'], $data['no_whatsapp'], $data['no_telp']);

        $data['user_id'] = $user->id;
        $data['status'] = 'disetujui';
        $data['npwpd'] = WajibPajak::generateNpwpd($data['tipe_wajib_pajak'] ?? 'perorangan');
        $data['tanggal_daftar'] = now();
        $data['tanggal_verifikasi'] = now();
        $data['petugas_id'] = auth()->id();
        $data['petugas_nama'] = auth()->user()->nama_lengkap ?? auth()->user()->name;

        // Auto-set province & regency untuk Bojonegoro
        if (($data['asal_wilayah'] ?? 'bojonegoro') === 'bojonegoro') {
            $data['province_code'] = '35';
            $data['regency_code'] = '35.22';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record->user;

        ActivityLog::log(
            action: 'DAFTAR_WAJIB_PAJAK',
            targetTable: 'wajib_pajak',
            targetId: $this->record->id,
            description: "Mendaftarkan WP: {$this->record->nama_lengkap}, NPWPD: {$this->record->npwpd}"
        );

        $body = "NPWPD: {$this->record->npwpd}";
        if ($this->isNewUser) {
            $body .= "\nAkun user dibuat (email: {$user->email})\nPassword default: @Password123";
        }

        Notification::make()
            ->title('Wajib Pajak Berhasil Didaftarkan')
            ->body($body)
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

