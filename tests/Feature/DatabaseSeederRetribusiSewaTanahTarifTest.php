<?php

use App\Domain\Retribusi\Models\TarifSewaTanah;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\RetribusiSewaTanahTarifSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('includes retribusi sewa tanah tarif seeder in database seeder', function () {
    $databaseSeederSource = file_get_contents((new ReflectionClass(DatabaseSeeder::class))->getFileName());

    expect($databaseSeederSource)->toContain('RetribusiSewaTanahTarifSeeder::class');
});

it('seeds active tarif for sewa tanah kain sub jenis', function () {
    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
        RetribusiSewaTanahTarifSeeder::class,
    ]);

    $this->assertDatabaseHas('tarif_sewa_tanah', [
        'tarif_nominal' => 20000,
        'satuan_waktu' => 'perBulan',
        'berlaku_mulai' => '2026-01-01',
        'is_active' => true,
    ]);

    expect(
        TarifSewaTanah::query()
            ->whereHas('subJenisPajak', fn ($query) => $query->where('kode', 'SEWA_TANAH_KAIN'))
            ->where('is_active', true)
            ->count()
    )->toBe(1);
});

it('maps sewa tanah tarif groups to the expected reklame umbrella categories', function () {
    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
        RetribusiSewaTanahTarifSeeder::class,
    ]);

    expect(
        TarifSewaTanah::query()
            ->whereHas('subJenisPajak', fn ($query) => $query->whereIn('kode', ['SEWA_TANAH_PERMANEN', 'SEWA_TANAH_RUMIJA']))
            ->where('tarif_nominal', 80000)
            ->where('satuan_waktu', 'perTahun')
            ->count()
    )->toBe(2);

    expect(
        TarifSewaTanah::query()
            ->whereHas('subJenisPajak', fn ($query) => $query->where('kode', 'SEWA_TANAH_KAIN'))
            ->where('tarif_nominal', 20000)
            ->where('satuan_waktu', 'perBulan')
            ->count()
    )->toBe(1);
});