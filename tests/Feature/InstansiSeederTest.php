<?php

use App\Domain\Master\Models\Instansi;
use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Enums\InstansiKategori;
use Database\Seeders\InstansiSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedBojonegoroRegionFixtures();
});

it('seeds instansi records from the embedded satker payload', function () {
    $this->seed(InstansiSeeder::class);

    expect(Instansi::count())->toBe(3344);

    $firstInstansi = Instansi::query()
        ->where('kode', 'SATKER-00001')
        ->firstOrFail();

    expect($firstInstansi->nama)->toBe('DINAS PENDIDIKAN')
        ->and($firstInstansi->alamat)->toBe('JL. PATIMURA NO. 09')
        ->and($firstInstansi->kategori)->toBe(InstansiKategori::Opd)
        ->and($firstInstansi->asal_wilayah)->toBe('bojonegoro')
        ->and($firstInstansi->province_code)->toBe('35')
        ->and($firstInstansi->regency_code)->toBe('35.22')
        ->and($firstInstansi->district_code)->toBe('35.22.15')
        ->and($firstInstansi->village_code)->toBe('35.22.15.1006')
        ->and($firstInstansi->keterangan)->toBeNull()
        ->and($firstInstansi->district?->name)->toBe('Bojonegoro')
        ->and($firstInstansi->village?->name)->toBe('Sumbang')
        ->and($firstInstansi->is_active)->toBeTrue();

    $lastInstansi = Instansi::query()
        ->where('kode', 'SATKER-03351')
        ->firstOrFail();

    expect($lastInstansi->nama)->toBe('BADAN KOORDINASI WILAYAH PEMERINTAHAN DAN PEMBANGUNAN JAWA TIMUR II (BAKORWIL II) BOJONEGORO')
        ->and($lastInstansi->alamat)->toBe('JL PAHLAWAN NO 5 KEPATIHAN KEC BOJONEGORO')
        ->and($lastInstansi->kategori)->toBe(InstansiKategori::Opd)
        ->and($lastInstansi->asal_wilayah)->toBe('bojonegoro')
        ->and($lastInstansi->province_code)->toBe('35')
        ->and($lastInstansi->regency_code)->toBe('35.22')
        ->and($lastInstansi->district_code)->toBe('35.22.15')
        ->and($lastInstansi->village_code)->toBe('35.22.15.1005')
        ->and($lastInstansi->keterangan)->toBeNull();

    $pemdes = Instansi::query()
        ->where('kode', 'SATKER-00071')
        ->firstOrFail();

    expect($pemdes->nama)->toBe('DESA SIDOBANDUNG KEC. BALEN')
        ->and($pemdes->kategori)->toBe(InstansiKategori::Pemdes);

    $lembaga = Instansi::query()
        ->where('kode', 'SATKER-00507')
        ->firstOrFail();

    expect($lembaga->nama)->toBe('TK DHARMA WANITA NGADILUHUR II')
        ->and($lembaga->kategori)->toBe(InstansiKategori::Lembaga);

    $opdBoundary = Instansi::query()
        ->where('kode', 'SATKER-03173')
        ->firstOrFail();

    expect($opdBoundary->nama)->toBe('PUSKESMAS MARGOMULYO')
        ->and($opdBoundary->kategori)->toBe(InstansiKategori::Opd);

    $lembagaBoundary = Instansi::query()
        ->where('kode', 'SATKER-03213')
        ->firstOrFail();

    expect($lembagaBoundary->kategori)->toBe(InstansiKategori::Lembaga);
});

it('keeps instansi seeding idempotent', function () {
    $this->seed(InstansiSeeder::class);
    $this->seed(InstansiSeeder::class);

    expect(Instansi::count())->toBe(3344);
});

function seedBojonegoroRegionFixtures(): void
{
    static $districtRows = null;
    static $villageRows = null;

    Province::query()->updateOrCreate(
        ['code' => '35'],
        ['name' => 'Jawa Timur']
    );

    Regency::query()->updateOrCreate(
        ['code' => '35.22'],
        [
            'province_code' => '35',
            'name' => 'Kabupaten Bojonegoro',
        ]
    );

    if ($districtRows === null) {
        $districtRows = collect(json_decode(file_get_contents(database_path('data/districts.json')), true, flags: JSON_THROW_ON_ERROR))
            ->filter(fn (array $district): bool => $district['regency_code'] === '35.22')
            ->map(fn (array $district): array => [
                'regency_code' => $district['regency_code'],
                'code' => $district['code'],
                'name' => $district['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all();

        $districtCodes = array_column($districtRows, 'code');

        $villageRows = collect(json_decode(file_get_contents(database_path('data/villages.json')), true, flags: JSON_THROW_ON_ERROR))
            ->filter(fn (array $village): bool => in_array($village['district_code'], $districtCodes, true))
            ->map(fn (array $village): array => [
                'district_code' => $village['district_code'],
                'code' => $village['code'],
                'name' => $village['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all();
    }

    DB::table('districts')->upsert($districtRows, ['code'], ['regency_code', 'name', 'updated_at']);
    DB::table('villages')->upsert($villageRows, ['code'], ['district_code', 'name', 'updated_at']);
}