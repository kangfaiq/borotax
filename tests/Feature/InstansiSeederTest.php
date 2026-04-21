<?php

use App\Domain\Master\Models\Instansi;
use App\Enums\InstansiKategori;
use Database\Seeders\InstansiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds instansi records from the embedded satker payload', function () {
    $this->seed(InstansiSeeder::class);

    expect(Instansi::count())->toBe(3344);

    $firstInstansi = Instansi::query()
        ->where('kode', 'SATKER-00001')
        ->firstOrFail();

    expect($firstInstansi->nama)->toBe('DINAS PENDIDIKAN')
        ->and($firstInstansi->alamat)->toBe('JL. PATIMURA NO. 09')
        ->and($firstInstansi->kategori)->toBe(InstansiKategori::Instansi)
        ->and($firstInstansi->keterangan)->toBe('Desa/Kelurahan: SUMBANG; Kecamatan: KEC. BOJONEGORO')
        ->and($firstInstansi->is_active)->toBeTrue();

    $lastInstansi = Instansi::query()
        ->where('kode', 'SATKER-03351')
        ->firstOrFail();

    expect($lastInstansi->nama)->toBe('BADAN KOORDINASI WILAYAH PEMERINTAHAN DAN PEMBANGUNAN JAWA TIMUR II (BAKORWIL II) BOJONEGORO')
        ->and($lastInstansi->alamat)->toBe('JL PAHLAWAN NO 5 KEPATIHAN KEC BOJONEGORO')
        ->and($lastInstansi->kategori)->toBe(InstansiKategori::Instansi)
        ->and($lastInstansi->keterangan)->toBe('Desa/Kelurahan: KEPATIHAN; Kecamatan: BOJONEGORO');
});

it('keeps instansi seeding idempotent', function () {
    $this->seed(InstansiSeeder::class);
    $this->seed(InstansiSeeder::class);

    expect(Instansi::count())->toBe(3344);
});