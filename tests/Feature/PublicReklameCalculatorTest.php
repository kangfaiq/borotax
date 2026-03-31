<?php

namespace Tests\Feature;

use Database\Seeders\ReklameNilaiStrategisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicReklameCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_reklame_calculator_uses_harga_patokan_reklame_and_location_master_data(): void
    {
        $this->seedReklameTaxReferences([
            ReklameNilaiStrategisSeeder::class,
        ]);

        $response = $this->get(route('publik.kalkulator-reklame'));

        $response->assertOk()
            ->assertViewIs('portal.publik.kalkulator-reklame')
            ->assertViewHas('tarifData', function (array $tarifData): bool {
                return collect($tarifData)->contains(function (array $entry): bool {
                    return $entry['nama'] === 'Neon Box'
                        && $entry['satuan'] === 'perTahun'
                        && $entry['is_insidentil'] === false
                        && ($entry['tarifPerKelompok']['A'] ?? null) === 276250;
                });
            })
            ->assertViewHas('lokasiData', function (array $lokasiData): bool {
                return isset($lokasiData['A'])
                    && in_array('Jalan Panglima Sudirman', $lokasiData['A']['streets'], true)
                    && ($lokasiData['A']['label'] ?? null) === 'Kelompok A';
            })
            ->assertViewHas('nsRates', function (array $nsRates): bool {
                return ($nsRates['A']['big']['tahun'] ?? null) === 10000000
                    && ($nsRates['B']['med']['bulan'] ?? null) === 250000;
            })
            ->assertViewHas('reklameTetapForNs', function (array $reklameTetapForNs): bool {
                return in_array('Neon Box', $reklameTetapForNs, true)
                    && in_array('Billboard / Papan Nama / Tinplat', $reklameTetapForNs, true);
            })
            ->assertSee('const TARIF_DATA =', false)
            ->assertSee('Neon Box', false)
            ->assertSee('Jalan Panglima Sudirman', false);
    }
}