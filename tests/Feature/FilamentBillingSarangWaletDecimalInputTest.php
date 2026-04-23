<?php

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\HargaPatokanSarangWalet;
use App\Domain\Tax\Models\Tax;
use App\Filament\Pages\BuatBillingSarangWalet;
use Carbon\Carbon;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-03-30 10:00:00');

    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

it('accepts comma decimal input on filament sarang walet billing', function () {
    $petugas = createFilamentSarangWaletDecimalAdminFixture('petugas');
    $wajibPajak = $this->createApprovedWajibPajakFixture();
    $jenisPajak = JenisPajak::where('kode', '41109')->firstOrFail();
    $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->orderBy('urutan')->firstOrFail();
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41109', [
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'nama_objek_pajak' => 'Rumah Walet Filament',
        'alamat_objek' => 'Jl. Walet Petugas No. 5',
        'kecamatan' => 'Bojonegoro',
        'kelurahan' => 'Kadipaten',
        'tarif_persen' => 10,
        'is_active' => true,
        'is_opd' => false,
        'is_insidentil' => false,
    ]);
    $hargaPatokan = HargaPatokanSarangWalet::create([
        'nama_jenis' => 'Sarang Walet Sudut',
        'harga_patokan' => 6000000,
        'satuan' => 'kg',
        'dasar_hukum' => 'Peraturan Harga Patokan Sarang Walet',
        'berlaku_mulai' => now()->startOfYear(),
        'berlaku_sampai' => null,
        'is_active' => true,
    ]);

    $this->actingAs($petugas);

    Livewire::test(BuatBillingSarangWalet::class)
        ->set('selectedTaxObjectId', (string) $taxObject->id)
        ->set('selectedTaxObjectData', [
            'id' => (string) $taxObject->id,
            'nama' => $taxObject->nama_objek_pajak,
            'alamat' => $taxObject->alamat_objek,
            'npwpd' => $taxObject->npwpd,
            'nopd' => $taxObject->nopd,
            'nik_hash' => $taxObject->nik_hash,
            'sub_jenis' => $subJenisPajak->nama,
            'jenis_pajak_nama' => 'Sarang Walet',
            'tarif_persen' => 10.0,
            'jenis_pajak_id' => $taxObject->jenis_pajak_id,
            'sub_jenis_pajak_id' => $taxObject->sub_jenis_pajak_id,
            'next_tahun' => 2026,
            'next_label' => 'Tahun 2026',
            'is_new' => false,
        ])
        ->set('wajibPajakData', [
            'id' => $wajibPajak->id,
            'user_id' => $wajibPajak->user_id,
            'nama_lengkap' => $wajibPajak->nama_lengkap,
            'npwpd' => $wajibPajak->npwpd,
            'tipe' => $wajibPajak->tipe_wajib_pajak,
        ])
        ->set('masaPajakTahun', 2026)
        ->set('selectedJenisSarangId', (string) $hargaPatokan->id)
        ->set('volumeKg', '2,50')
        ->call('terbitkanBilling');

    $tax = Tax::with('sarangWaletDetail')->firstOrFail();

    expect((float) $tax->sarangWaletDetail->volume_kg)->toBe(2.5)
        ->and((float) $tax->sarangWaletDetail->subtotal_dpp)->toBe(15000000.0);
});

function createFilamentSarangWaletDecimalAdminFixture(string $role): User
{
    return User::create([
        'name' => Str::headline($role) . ' User',
        'nama_lengkap' => Str::headline($role) . ' User',
        'email' => sprintf('sarang-walet-%s-%s@example.test', $role, Str::random(8)),
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}