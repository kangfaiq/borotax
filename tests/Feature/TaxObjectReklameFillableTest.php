<?php

namespace Tests\Feature;

use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\ReklameSubJenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxObjectReklameFillableTest extends TestCase
{
    use RefreshDatabase;

    public function test_tax_object_persists_bentuk_and_dimension_columns_via_mass_assignment(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
            ReklameSubJenisPajakSeeder::class,
        ]);

        $taxObject = $this->createTaxObjectFixture(
            $this->createApprovedWajibPajakFixture(),
            '41104',
            [
                'bentuk' => 'persegi',
                'panjang' => 4,
                'lebar' => 3,
                'jumlah_muka' => 1,
            ],
        );

        $taxObject->update([
            'bentuk' => 'trapesium',
            'sisi_atas' => 2,
            'sisi_bawah' => 4,
            'tinggi' => 3,
            'diameter' => 5,
            'diameter2' => 6,
            'alas' => 7,
        ]);

        $taxObject->refresh();

        $this->assertSame('trapesium', $taxObject->bentuk);
        $this->assertSame('2.00', (string) $taxObject->sisi_atas);
        $this->assertSame('4.00', (string) $taxObject->sisi_bawah);
        $this->assertSame('3.00', (string) $taxObject->tinggi);
        $this->assertSame('5.00', (string) $taxObject->diameter);
        $this->assertSame('6.00', (string) $taxObject->diameter2);
        $this->assertSame('7.00', (string) $taxObject->alas);
    }
}
