<?php

namespace Tests\Feature;

use App\Domain\Tax\Models\TaxPayment;
use App\Enums\TaxStatus;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalDashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_dashboard_uses_logged_in_taxpayer_actual_summary_data(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'dashboard-owner@example.test',
            'total_kupon_undian' => 4,
        ]);
        $otherWajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'dashboard-other@example.test',
            'total_kupon_undian' => 9,
        ]);

        $firstObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $secondObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $otherObject = $this->createTaxObjectFixture($otherWajibPajak, '41102');

        $pendingTax = $this->createTaxFixture($firstObject, $wajibPajak->user, [
            'amount' => 1_000_000,
            'sanksi' => 50_000,
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000269001',
            'masa_pajak_bulan' => 1,
            'masa_pajak_tahun' => 2031,
        ]);

        $partiallyPaidTax = $this->createTaxFixture($secondObject, $wajibPajak->user, [
            'amount' => 500_000,
            'sanksi' => 50_000,
            'status' => TaxStatus::PartiallyPaid,
            'billing_code' => '352210100000269002',
            'masa_pajak_bulan' => 2,
            'masa_pajak_tahun' => 2031,
        ]);

        TaxPayment::create([
            'tax_id' => $partiallyPaidTax->id,
            'amount_paid' => 200_000,
            'principal_paid' => 200_000,
            'penalty_paid' => 0,
            'payment_channel' => 'QRIS',
            'paid_at' => now()->subDay(),
        ]);

        $paidTax = $this->createTaxFixture($firstObject, $wajibPajak->user, [
            'amount' => 750_000,
            'sanksi' => 50_000,
            'status' => TaxStatus::Paid,
            'billing_code' => '352210100000269003',
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2031,
        ]);

        $verifiedTax = $this->createTaxFixture($secondObject, $wajibPajak->user, [
            'amount' => 300_000,
            'sanksi' => 0,
            'status' => TaxStatus::Verified,
            'billing_code' => '352210100000269004',
            'masa_pajak_bulan' => 4,
            'masa_pajak_tahun' => 2031,
        ]);

        TaxPayment::create([
            'tax_id' => $verifiedTax->id,
            'amount_paid' => 300_000,
            'principal_paid' => 300_000,
            'penalty_paid' => 0,
            'payment_channel' => 'VA',
            'paid_at' => now()->subHours(12),
        ]);

        $this->createTaxFixture($otherObject, $otherWajibPajak->user, [
            'amount' => 9_999_999,
            'sanksi' => 0,
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000269999',
            'masa_pajak_bulan' => 5,
            'masa_pajak_tahun' => 2031,
        ]);

        $this->actingAs($wajibPajak->user);

        $response = $this->get(route('portal.dashboard'));

        $response->assertOk()
            ->assertSee('Rp 1.400.000')
            ->assertSee('Rp 1.100.000')
            ->assertSee('>2<', false)
            ->assertSee('>4<', false)
            ->assertSee($pendingTax->billing_code)
            ->assertSee($paidTax->billing_code)
            ->assertDontSee('352210100000269999');
    }
}