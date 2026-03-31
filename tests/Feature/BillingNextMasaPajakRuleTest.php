<?php

namespace Tests\Feature;

use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Services\BillingService;
use Carbon\Carbon;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingNextMasaPajakRuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-03-30 10:00:00');

        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_mblb_wapu_prefills_current_period_even_if_previous_billing_exists(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $subJenisWapu = SubJenisPajak::where('kode', 'MBLB_WAPU')->firstOrFail();

        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41106', [
            'sub_jenis_pajak_id' => $subJenisWapu->id,
            'nopd' => 6101,
        ]);

        $this->createTaxFixture($taxObject, null, [
            'masa_pajak_bulan' => 1,
            'masa_pajak_tahun' => 2026,
        ]);

        $nextPeriod = app(BillingService::class)->getNextMasaPajak($taxObject->id);

        $this->assertSame(3, $nextPeriod['bulan']);
        $this->assertSame(2026, $nextPeriod['tahun']);
        $this->assertSame(Carbon::create(2026, 3, 1)->translatedFormat('F Y'), $nextPeriod['label']);
        $this->assertFalse($nextPeriod['isNew']);
        $this->assertTrue($nextPeriod['isMultiBilling']);
    }

    public function test_opd_prefills_current_period_even_if_previous_billing_exists(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $subJenisKatering = SubJenisPajak::where('kode', 'PBJT_KATERING')->firstOrFail();

        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102', [
            'sub_jenis_pajak_id' => $subJenisKatering->id,
            'nopd' => 6201,
            'is_opd' => true,
        ]);

        $this->createTaxFixture($taxObject, null, [
            'masa_pajak_bulan' => 2,
            'masa_pajak_tahun' => 2026,
        ]);

        $nextPeriod = app(BillingService::class)->getNextMasaPajak($taxObject->id);

        $this->assertSame(3, $nextPeriod['bulan']);
        $this->assertSame(2026, $nextPeriod['tahun']);
        $this->assertFalse($nextPeriod['isNew']);
        $this->assertTrue($nextPeriod['isMultiBilling']);
    }

    public function test_insidentil_prefills_current_period_even_if_previous_billing_exists(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $subJenisHiburan = SubJenisPajak::where('kode', 'PBJT_KESENIAN')->firstOrFail();

        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41103', [
            'sub_jenis_pajak_id' => $subJenisHiburan->id,
            'nopd' => 6301,
            'is_insidentil' => true,
        ]);

        $this->createTaxFixture($taxObject, null, [
            'masa_pajak_bulan' => 1,
            'masa_pajak_tahun' => 2026,
        ]);

        $nextPeriod = app(BillingService::class)->getNextMasaPajak($taxObject->id);

        $this->assertSame(3, $nextPeriod['bulan']);
        $this->assertSame(2026, $nextPeriod['tahun']);
        $this->assertFalse($nextPeriod['isNew']);
        $this->assertTrue($nextPeriod['isMultiBilling']);
    }

    public function test_regular_object_prefills_next_month_from_last_billing_based_on_shared_nopd(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $subJenisWp = SubJenisPajak::where('kode', 'MBLB_WP')->firstOrFail();

        $olderObject = $this->createTaxObjectFixture($wajibPajak, '41106', [
            'sub_jenis_pajak_id' => $subJenisWp->id,
            'nopd' => 6401,
        ]);

        $replacementObject = $this->createTaxObjectFixture($wajibPajak, '41106', [
            'sub_jenis_pajak_id' => $subJenisWp->id,
            'nopd' => 6401,
        ]);

        $this->createTaxFixture($olderObject, null, [
            'masa_pajak_bulan' => 2,
            'masa_pajak_tahun' => 2026,
        ]);

        $nextPeriod = app(BillingService::class)->getNextMasaPajak($replacementObject->id);

        $this->assertSame(3, $nextPeriod['bulan']);
        $this->assertSame(2026, $nextPeriod['tahun']);
        $this->assertSame(Carbon::create(2026, 3, 1)->translatedFormat('F Y'), $nextPeriod['label']);
        $this->assertFalse($nextPeriod['isNew']);
        $this->assertFalse($nextPeriod['isMultiBilling']);
    }

    public function test_duplicate_detection_uses_shared_nopd_for_regular_objects(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $subJenisWp = SubJenisPajak::where('kode', 'MBLB_WP')->firstOrFail();

        $olderObject = $this->createTaxObjectFixture($wajibPajak, '41106', [
            'sub_jenis_pajak_id' => $subJenisWp->id,
            'nopd' => 6501,
        ]);

        $replacementObject = $this->createTaxObjectFixture($wajibPajak, '41106', [
            'sub_jenis_pajak_id' => $subJenisWp->id,
            'nopd' => 6501,
        ]);

        $this->createTaxFixture($olderObject, null, [
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2026,
        ]);

        $this->assertTrue(app(BillingService::class)->billingExistsForPeriod($replacementObject->id, 3, 2026));
    }

    public function test_multi_billing_sequence_uses_shared_nopd_for_same_period(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $subJenisWapu = SubJenisPajak::where('kode', 'MBLB_WAPU')->firstOrFail();

        $olderObject = $this->createTaxObjectFixture($wajibPajak, '41106', [
            'sub_jenis_pajak_id' => $subJenisWapu->id,
            'nopd' => 6601,
        ]);

        $replacementObject = $this->createTaxObjectFixture($wajibPajak, '41106', [
            'sub_jenis_pajak_id' => $subJenisWapu->id,
            'nopd' => 6601,
        ]);

        $this->createTaxFixture($olderObject, null, [
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2026,
            'billing_sequence' => 1,
        ]);

        $this->createTaxFixture($replacementObject, null, [
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2026,
            'billing_sequence' => 2,
        ]);

        $this->assertSame(3, app(BillingService::class)->getNextBillingSequence($replacementObject->id, 3, 2026));
    }
}