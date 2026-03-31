<?php

namespace Tests\Feature;

use App\Domain\Reklame\Models\KelompokLokasiJalan;
use App\Domain\Reklame\Models\ReklameNilaiStrategis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReklameEffectiveDateVersioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_kelompok_lokasi_options_follow_reference_date_and_keep_selected_history(): void
    {
        $lokasi2026 = KelompokLokasiJalan::create([
            'kelompok' => 'A',
            'nama_jalan' => 'Jalan Uji Efektif',
            'deskripsi' => 'Versi 2026',
            'is_active' => true,
            'berlaku_mulai' => '2026-01-01',
            'berlaku_sampai' => '2026-12-31',
        ]);

        $lokasi2027 = KelompokLokasiJalan::create([
            'kelompok' => 'B',
            'nama_jalan' => 'Jalan Uji Efektif',
            'deskripsi' => 'Versi 2027',
            'is_active' => true,
            'berlaku_mulai' => '2027-01-01',
            'berlaku_sampai' => null,
        ]);

        $options2026 = KelompokLokasiJalan::getActiveOptions('2026-06-01');
        $options2027 = KelompokLokasiJalan::getActiveOptions('2027-06-01');
        $options2027WithSelectedHistory = KelompokLokasiJalan::getActiveOptions('2027-06-01', $lokasi2026->id);

        $this->assertArrayHasKey($lokasi2026->id, $options2026);
        $this->assertArrayNotHasKey($lokasi2027->id, $options2026);
        $this->assertStringContainsString('Kelompok A', $options2026[$lokasi2026->id]);

        $this->assertArrayHasKey($lokasi2027->id, $options2027);
        $this->assertArrayNotHasKey($lokasi2026->id, $options2027);
        $this->assertStringContainsString('Kelompok B', $options2027[$lokasi2027->id]);

        $this->assertArrayHasKey($lokasi2026->id, $options2027WithSelectedHistory);
        $this->assertArrayHasKey($lokasi2027->id, $options2027WithSelectedHistory);
    }

    public function test_nilai_strategis_uses_effective_tariff_for_reference_date(): void
    {
        ReklameNilaiStrategis::create([
            'kelas_kelompok' => 'A',
            'luas_min' => 10,
            'luas_max' => 24.99,
            'tarif_per_tahun' => 5000000,
            'tarif_per_bulan' => 500000,
            'is_active' => true,
            'berlaku_mulai' => '2026-01-01',
            'berlaku_sampai' => '2026-12-31',
        ]);

        ReklameNilaiStrategis::create([
            'kelas_kelompok' => 'A',
            'luas_min' => 10,
            'luas_max' => 24.99,
            'tarif_per_tahun' => 7000000,
            'tarif_per_bulan' => 700000,
            'is_active' => true,
            'berlaku_mulai' => '2027-01-01',
            'berlaku_sampai' => null,
        ]);

        $nilai2026 = ReklameNilaiStrategis::hitungNilaiStrategis('A1', 12, 'perTahun', 1, 1, '2026-06-01');
        $nilai2027 = ReklameNilaiStrategis::hitungNilaiStrategis('A1', 12, 'perTahun', 1, 1, '2027-06-01');

        $this->assertEquals(5000000.0, $nilai2026);
        $this->assertEquals(7000000.0, $nilai2027);
    }
}