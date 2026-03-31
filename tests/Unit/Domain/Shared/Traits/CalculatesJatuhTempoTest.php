<?php

namespace Tests\Unit\Domain\Shared\Traits;

use PHPUnit\Framework\TestCase;
use App\Domain\Shared\Traits\CalculatesJatuhTempo;
use Carbon\Carbon;

class CalculatesJatuhTempoTest extends TestCase
{
    /**
     * Dummy class to use the trait for testing.
     */
    private $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new class {
            use CalculatesJatuhTempo;
        };
    }

    public function test_hitung_sanksi_split_calculation()
    {
        // Skenario: Masa Pajak November 2023.
        // Jatuh tempo: 31 Desember 2023.
        // Dibayar pada: 15 Maret 2024.
        // Denda keterlambatan:
        // - Bulan 1 (Januari 2024) -> telat masuk Jan 2024: Tarif 1%
        // - Bulan 2 (Februari 2024) -> telat masuk Feb 2024: Tarif 1%
        // - Bulan 3 (Maret 2024) -> telat masuk Mar 2024: Tarif 1%
        // Total bulan: 3 bulan terlambat (karena dibayar tgl 15 Mar > tgl jatuh tempo tapi beda bulan)
        // Note: Sebenarnya jatuh tempo 31 Des, telat di Jan dihitung sbg 1 bln terlambat bulan "Januari". Tarif yg diambil 1%.

        $masaPajak = Carbon::create(2023, 11, 1);
        $jatuhTempo = Carbon::create(2023, 12, 31);
        $tanggalBayar = Carbon::create(2024, 3, 15);
        $pokokPajak = 100000;

        $result = $this->calculator::hitungSanksi($pokokPajak, $masaPajak, $jatuhTempo, $tanggalBayar);

        $this->assertEquals(3, $result['bulan_terlambat'], 'Harus 3 bulan terlambat');
        
        // Perhitungan denda:
        // Bulan 1 keterlambatan = jatuhTempo->addMonth() = Januari 2024 -> 1% (0.01) * 100000 = 1000
        // Bulan 2 keterlambatan = Februari 2024 -> 1% (0.01) * 100000 = 1000
        // Bulan 3 keterlambatan = Maret 2024 -> 1% (0.01) * 100000 = 1000
        // Total = 3000
        $this->assertEquals(3000, $result['denda'], 'Denda harus 3000');
    }

    public function test_hitung_sanksi_split_calculation_2()
    {
        // Skenario: Masa Pajak Oktober 2023.
        // Jatuh tempo: 30 November 2023.
        // Dibayar pada: 15 Februari 2024.
        
        $masaPajak = Carbon::create(2023, 10, 1);
        $jatuhTempo = Carbon::create(2023, 11, 30);
        $tanggalBayar = Carbon::create(2024, 2, 15);
        $pokokPajak = 100000;

        $result = $this->calculator::hitungSanksi($pokokPajak, $masaPajak, $jatuhTempo, $tanggalBayar);

        $this->assertEquals(3, $result['bulan_terlambat'], 'Harus 3 bulan terlambat');
        
        // Perhitungan denda:
        // Bulan 1 keterlambatan = jatuhTempo->addMonth() = Desember 2023 -> 2% (0.02) * 100000 = 2000
        // Bulan 2 keterlambatan = Januari 2024 -> 1% (0.01) * 100000 = 1000
        // Bulan 3 keterlambatan = Februari 2024 -> 1% (0.01) * 100000 = 1000
        // Total = 4000
        $this->assertEquals(4000, $result['denda'], 'Denda gabungan harus 4000');
    }
}
