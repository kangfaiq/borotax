<?php

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\HargaPatokanMblb;
use App\Domain\Tax\Models\Tax;
use App\Filament\Pages\BuatBillingMblb;
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

it('accepts flexible decimal input on filament mblb billing', function (string $volumeInput, float $expectedVolume) {
    $petugas = createFilamentMblbDecimalAdminFixture('petugas');
    $wajibPajak = $this->createApprovedWajibPajakFixture();
    $subJenisPajak = SubJenisPajak::where('kode', 'MBLB_WP')->firstOrFail();
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41106', [
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'nama_objek_pajak' => 'Tambang Decimal MBLB',
        'tarif_persen' => 20,
        'is_active' => true,
        'is_opd' => false,
        'is_insidentil' => false,
    ]);
    $mineral = HargaPatokanMblb::create([
        'nama_mineral' => 'Batu Split',
        'harga_patokan' => 125000,
        'satuan' => 'm3',
        'dasar_hukum' => 'Peraturan Harga Patokan MBLB',
        'berlaku_mulai' => now()->startOfYear(),
        'berlaku_sampai' => null,
        'is_active' => true,
    ]);

    $this->actingAs($petugas);

    Livewire::test(BuatBillingMblb::class)
        ->set('selectedTaxObjectId', (string) $taxObject->id)
        ->set('selectedTaxObjectData', [
            'id' => (string) $taxObject->id,
            'nama' => $taxObject->nama_objek_pajak,
            'alamat' => $taxObject->alamat_objek,
            'npwpd' => $taxObject->npwpd,
            'nopd' => $taxObject->nopd,
            'nik_hash' => $taxObject->nik_hash,
            'sub_jenis' => $subJenisPajak->nama,
            'jenis_pajak_nama' => 'MBLB',
            'tarif_persen' => 20.0,
            'jenis_pajak_id' => $taxObject->jenis_pajak_id,
            'sub_jenis_pajak_id' => $taxObject->sub_jenis_pajak_id,
            'next_bulan' => 4,
            'next_tahun' => 2026,
            'next_label' => 'April 2026',
            'is_new' => false,
            'is_opd' => false,
            'is_insidentil' => false,
            'is_multi_billing' => false,
            'sub_jenis_kode' => 'MBLB_WP',
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
        ->set('mineralItems', [[
            'id' => $mineral->id,
            'nama_mineral' => $mineral->nama_mineral,
            'nama_alternatif' => [],
            'harga_patokan' => (float) $mineral->harga_patokan,
            'satuan' => $mineral->satuan,
            'volume' => $volumeInput,
        ]])
        ->call('terbitkanBilling')
        ->assertSet('billingResult.nama_objek', $taxObject->nama_objek_pajak);

    $tax = Tax::with('mblbDetails')->firstOrFail();

    expect($tax->mblbDetails)->toHaveCount(1)
        ->and((float) $tax->mblbDetails->first()->volume)->toBe($expectedVolume)
        ->and((float) $tax->omzet)->toBe(406250.0)
        ->and((float) $tax->amount)->toBe(81250.0);
})->with([
    'comma decimal' => ['3,25', 3.25],
    'dot decimal' => ['3.25', 3.25],
]);

function createFilamentMblbDecimalAdminFixture(string $role): User
{
    return User::create([
        'name' => Str::headline($role) . ' User',
        'nama_lengkap' => Str::headline($role) . ' User',
        'email' => sprintf('mblb-decimal-%s-%s@example.test', $role, Str::random(8)),
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}