<?php

use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Filament\Pages\BuatSkpdAirTanah;
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

it('creates draft air tanah skpd from comma decimal direct usage input', function () {
    $petugas = createBuatSkpdAirTanahDecimalAdminFixture('petugas');
    [$jenisPajak, $subJenisPajak] = createBuatSkpdAirTanahDecimalTaxReferences();

    $waterObject = WaterObject::create([
        'nik' => '3522011234567890',
        'nama_objek_pajak' => 'Sumber Air Tanpa Meter Decimal',
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak?->id,
        'jenis_sumber' => 'sumurBor',
        'npwpd' => 'P100000000610',
        'nopd' => 2610,
        'alamat_objek' => 'Jl. Air Decimal No. 10',
        'kelurahan' => 'Kadipaten',
        'kecamatan' => 'Bojonegoro',
        'last_meter_reading' => null,
        'tanggal_daftar' => now()->toDateString(),
        'is_active' => true,
        'is_opd' => false,
        'tarif_persen' => 20,
        'kelompok_pemakaian' => '1',
        'kriteria_sda' => '1',
        'uses_meter' => false,
    ]);

    $this->actingAs($petugas);

    Livewire::test(BuatSkpdAirTanah::class)
        ->set('selectedWaterObjectId', (string) $waterObject->id)
        ->set('selectedWaterObjectData', [
            'id' => (string) $waterObject->id,
            'nama' => $waterObject->nama_objek_pajak,
            'alamat' => $waterObject->alamat_objek,
            'npwpd' => $waterObject->npwpd,
            'nopd' => $waterObject->nopd,
            'nik_hash' => $waterObject->nik_hash,
            'jenis_pajak_id' => $jenisPajak->id,
            'last_meter_reading' => null,
            'uses_meter' => false,
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
        ])
        ->set('wajibPajakData', [
            'nik' => '3522011234567890',
            'nama_lengkap' => 'WP Air Decimal',
            'alamat' => 'Jl. Diponegoro No. 9',
            'npwpd' => $waterObject->npwpd,
        ])
        ->set('usesMeter', false)
        ->set('periodeBulan', '2030-01')
        ->set('directUsage', '30,50')
        ->set('tarifTiers', [
            ['min_vol' => 0, 'max_vol' => 100, 'npa' => 1000],
        ])
        ->set('tarifPersen', 20)
        ->call('buatSkpd')
        ->assertHasNoErrors();

    $draft = SkpdAirTanah::query()->firstOrFail();

    expect((float) $draft->usage)->toBe(30.5)
        ->and((float) $draft->meter_reading_before)->toBe(0.0)
        ->and((float) $draft->meter_reading_after)->toBe(0.0);
});

it('creates draft air tanah skpd from dot decimal meter input', function () {
    $petugas = createBuatSkpdAirTanahDecimalAdminFixture('petugas');
    [$jenisPajak, $subJenisPajak] = createBuatSkpdAirTanahDecimalTaxReferences();

    $waterObject = WaterObject::create([
        'nik' => '3522011234567891',
        'nama_objek_pajak' => 'Sumber Air Meter Decimal',
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak?->id,
        'jenis_sumber' => 'sumurBor',
        'npwpd' => 'P100000000611',
        'nopd' => 2611,
        'alamat_objek' => 'Jl. Air Decimal No. 11',
        'kelurahan' => 'Kadipaten',
        'kecamatan' => 'Bojonegoro',
        'last_meter_reading' => 100.25,
        'tanggal_daftar' => now()->toDateString(),
        'is_active' => true,
        'is_opd' => false,
        'tarif_persen' => 20,
        'kelompok_pemakaian' => '1',
        'kriteria_sda' => '1',
        'uses_meter' => true,
    ]);

    $this->actingAs($petugas);

    Livewire::test(BuatSkpdAirTanah::class)
        ->set('selectedWaterObjectId', (string) $waterObject->id)
        ->set('selectedWaterObjectData', [
            'id' => (string) $waterObject->id,
            'nama' => $waterObject->nama_objek_pajak,
            'alamat' => $waterObject->alamat_objek,
            'npwpd' => $waterObject->npwpd,
            'nopd' => $waterObject->nopd,
            'nik_hash' => $waterObject->nik_hash,
            'jenis_pajak_id' => $jenisPajak->id,
            'last_meter_reading' => 100.25,
            'uses_meter' => true,
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
        ])
        ->set('wajibPajakData', [
            'nik' => '3522011234567891',
            'nama_lengkap' => 'WP Air Meter Decimal',
            'alamat' => 'Jl. Diponegoro No. 10',
            'npwpd' => $waterObject->npwpd,
        ])
        ->set('usesMeter', true)
        ->set('periodeBulan', '2030-01')
        ->set('meterReadingBefore', 100.25)
        ->set('meterReadingAfter', '130.75')
        ->set('tarifTiers', [
            ['min_vol' => 0, 'max_vol' => 100, 'npa' => 1000],
        ])
        ->set('tarifPersen', 20)
        ->call('buatSkpd')
        ->assertHasNoErrors();

    $draft = SkpdAirTanah::query()->firstOrFail();

    expect((float) $draft->meter_reading_before)->toBe(100.25)
        ->and((float) $draft->meter_reading_after)->toBe(130.75)
        ->and((float) $draft->usage)->toBe(30.5);
});

function createBuatSkpdAirTanahDecimalAdminFixture(string $role): User
{
    return User::create([
        'name' => Str::headline($role) . ' User',
        'nama_lengkap' => Str::headline($role) . ' User',
        'email' => sprintf('air-tanah-decimal-%s-%s@example.test', $role, Str::random(8)),
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}

function createBuatSkpdAirTanahDecimalTaxReferences(): array
{
    $jenisPajak = JenisPajak::where('kode', '41108')->firstOrFail();
    $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->first();

    return [$jenisPajak, $subJenisPajak];
}