<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\TaxStatus;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class StpdManualDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_stpd_manual_document_is_limited_to_owner_or_backoffice_roles(): void
    {
        $stpd = $this->createStpdManualFixture('disetujui');
        $owner = $stpd->tax()->firstOrFail()->user()->firstOrFail();

        $this->actingAs($this->createPortalUser('3522019999999999', 'Portal User Lain'));
        $this->get(route('stpd-manual.show', $stpd->id))->assertNotFound();

        $this->actingAs($owner);
        $this->get(route('stpd-manual.show', $stpd->id))->assertOk();

        $this->actingAs($this->createAdminPanelUser('admin'));
        $this->get(route('stpd-manual.show', $stpd->id))->assertOk();
    }

    public function test_draft_stpd_manual_document_returns_not_found_even_for_backoffice(): void
    {
        $stpd = $this->createStpdManualFixture('draft');

        $this->actingAs($this->createAdminPanelUser('admin'));
        $this->get(route('stpd-manual.show', $stpd->id))->assertNotFound();
    }

    private function createStpdManualFixture(string $status): StpdManual
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $this->seedPimpinanReferences();

        $owner = $this->createPortalUser('3522011234567890', 'Portal STPD User');
        $jenisPajak = JenisPajak::where('kode', '41102')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();
        $npwpd = 'P100000000777';

        WajibPajak::create([
            'user_id' => $owner->id,
            'nik' => '3522011234567890',
            'nama_lengkap' => 'Portal STPD User',
            'alamat' => 'Jl. Imam Bonjol No. 1',
            'tipe_wajib_pajak' => 'perorangan',
            'status' => 'disetujui',
            'tanggal_daftar' => now(),
            'tanggal_verifikasi' => now(),
            'npwpd' => $npwpd,
            'nopd' => 777,
        ]);

        $taxObject = TaxObject::create([
            'nik' => '3522011234567890',
            'nama_objek_pajak' => 'Restoran Uji STPD',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => $npwpd,
            'nopd' => 777,
            'alamat_objek' => 'Jl. Imam Bonjol No. 1',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tarif_persen' => 10,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'is_insidentil' => false,
        ]);

        $nomorStpd = $status === 'disetujui' ? 'STPD/2030/01/000001' : null;

        $tax = Tax::create([
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'tax_object_id' => $taxObject->id,
            'user_id' => $owner->id,
            'amount' => 1000000,
            'omzet' => 10000000,
            'sanksi' => 100000,
            'tarif_persentase' => 10,
            'status' => TaxStatus::Paid,
            'billing_code' => '352210200030000777',
            'payment_expired_at' => now()->addDays(30),
            'masa_pajak_bulan' => 1,
            'masa_pajak_tahun' => 2030,
            'pembetulan_ke' => 0,
            'billing_sequence' => 0,
            'paid_at' => now(),
            'stpd_number' => $nomorStpd,
            'stpd_payment_code' => $status === 'disetujui' ? Tax::generateManualStpdPaymentCode('352210200030000777') : null,
        ]);

        return StpdManual::create([
            'tax_id' => $tax->id,
            'tipe' => 'sanksi_saja',
            'nomor_stpd' => $nomorStpd,
            'status' => $status,
            'proyeksi_tanggal_bayar' => now()->addDays(10),
            'bulan_terlambat' => 1,
            'sanksi_dihitung' => 100000,
            'pokok_belum_dibayar' => 0,
            'catatan_petugas' => 'Uji akses dokumen STPD manual',
            'petugas_id' => $this->createAdminPanelUser('petugas')->id,
            'petugas_nama' => 'Petugas User',
            'tanggal_buat' => now()->subDay(),
            'verifikator_id' => $status === 'disetujui' ? $this->createAdminPanelUser('verifikator', Pimpinan::firstOrFail()->id)->id : null,
            'verifikator_nama' => $status === 'disetujui' ? 'Verifikator User' : null,
            'tanggal_verifikasi' => $status === 'disetujui' ? now()->subHours(12) : null,
            'pimpinan_id' => Pimpinan::firstOrFail()->id,
        ]);
    }

    private function createPortalUser(string $nik, string $name): User
    {
        return User::create([
            'name' => $name,
            'nama_lengkap' => $name,
            'email' => sprintf('%s-%s@example.test', str()->slug($name), Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => $nik,
            'alamat' => 'Alamat uji',
            'role' => 'user',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);
    }

    private function createAdminPanelUser(string $role, ?string $pimpinanId = null): User
    {
        return User::create([
            'name' => Str::headline($role) . ' User',
            'nama_lengkap' => Str::headline($role) . ' User',
            'email' => sprintf('%s-%s@example.test', $role, Str::random(8)),
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
            'pimpinan_id' => $pimpinanId,
        ]);
    }
}