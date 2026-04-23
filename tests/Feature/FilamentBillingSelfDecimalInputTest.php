<?php

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\HargaSatuanListrik;
use App\Domain\Tax\Models\Tax;
use App\Filament\Pages\BuatBillingSelfAssessment;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
    ]);
});

it('accepts comma decimal input on filament self-assessment ppj non pln billing', function () {
    $petugas = createFilamentDecimalAdminUserFixture('petugas');
    $wajibPajak = $this->createApprovedWajibPajakFixture();
    $subJenisPajak = SubJenisPajak::where('kode', 'PPJ_DIHASILKAN_SENDIRI')->firstOrFail();
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41105', [
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'tarif_persen' => 1.5,
    ]);
    $hargaSatuan = HargaSatuanListrik::create([
        'nama_wilayah' => 'Kabupaten Bojonegoro',
        'harga_per_kwh' => 1500,
        'dasar_hukum' => 'Peraturan Harga Satuan Listrik',
        'berlaku_mulai' => now()->startOfYear(),
        'berlaku_sampai' => null,
        'is_active' => true,
    ]);

    $this->actingAs($petugas);

    Livewire::test(BuatBillingSelfAssessment::class)
        ->set('selectedTaxObjectId', $taxObject->id)
        ->set('selectedTaxObjectData', [
            'id' => $taxObject->id,
            'nama' => $taxObject->nama_objek_pajak,
            'alamat' => $taxObject->alamat_objek,
            'npwpd' => $taxObject->npwpd,
            'nopd' => $taxObject->nopd,
            'nik_hash' => $taxObject->nik_hash,
            'sub_jenis' => $subJenisPajak->nama,
            'jenis_pajak_nama' => 'PPJ',
            'tarif_persen' => 1.5,
            'jenis_pajak_id' => $taxObject->jenis_pajak_id,
            'sub_jenis_pajak_id' => $taxObject->sub_jenis_pajak_id,
            'next_bulan' => 4,
            'next_tahun' => 2026,
            'next_label' => 'April 2026',
            'is_new' => false,
            'is_opd' => false,
            'is_insidentil' => false,
            'is_multi_billing' => false,
            'sub_jenis_kode' => 'PPJ_DIHASILKAN_SENDIRI',
        ])
        ->set('wajibPajakData', [
            'id' => $wajibPajak->id,
            'user_id' => $wajibPajak->user_id,
            'nama_lengkap' => $wajibPajak->nama_lengkap,
            'npwpd' => $wajibPajak->npwpd,
            'tipe' => $wajibPajak->tipe_wajib_pajak,
        ])
        ->set('masaPajakBulan', 4)
        ->set('masaPajakTahun', 2026)
        ->set('ppjHargaSatuanOptions', [[
            'id' => $hargaSatuan->id,
            'label' => 'Kabupaten Bojonegoro — Rp 1.500/kWh',
            'harga' => 1500.0,
        ]])
        ->set('ppjKapasitasKva', '100,50')
        ->set('ppjTingkatPenggunaanPersen', '80,25')
        ->set('ppjJangkaWaktuJam', '10,75')
        ->set('ppjHargaSatuanListrikId', $hargaSatuan->id)
        ->set('ppjHargaSatuan', 1500.0)
        ->call('terbitkanBilling');

    $tax = Tax::with('ppjDetail')->firstOrFail();

    expect((float) $tax->ppjDetail->kapasitas_kva)->toBe(100.5)
        ->and((float) $tax->ppjDetail->tingkat_penggunaan_persen)->toBe(80.25)
        ->and((float) $tax->ppjDetail->jangka_waktu_jam)->toBe(10.75)
        ->and((float) $tax->ppjDetail->harga_satuan)->toBe(1500.0);
});

function createFilamentDecimalAdminUserFixture(string $role): User
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
    ]);
}