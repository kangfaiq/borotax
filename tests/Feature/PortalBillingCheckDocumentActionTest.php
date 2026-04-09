<?php

namespace Tests\Feature;

use App\Enums\TaxStatus;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalBillingCheckDocumentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_billing_with_sptpd_and_stpd_shows_paid_document_actions(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $tax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Paid,
            'billing_code' => '352210100000260301',
            'sptpd_number' => '352210100000260301',
            'stpd_number' => '352210100000260301',
            'paid_at' => now(),
        ]);

        $this->actingAs($wajibPajak->user);

        $this->get(route('portal.billing', ['code' => $tax->billing_code]))
            ->assertOk()
            ->assertSee('Cetak SPTPD')
            ->assertSee('Unduh SPTPD')
            ->assertSee('Cetak STPD')
            ->assertSee('Unduh STPD')
            ->assertDontSee('Cetak Billing')
            ->assertDontSee('Unduh Billing');
    }

    public function test_paid_billing_without_sptpd_keeps_billing_actions_and_shows_fallback_notice(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $tax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Paid,
            'billing_code' => '352210100000260302',
            'paid_at' => now(),
            'sptpd_number' => null,
            'stpd_number' => null,
        ]);

        $this->actingAs($wajibPajak->user);

        $this->get(route('portal.billing', ['code' => $tax->billing_code]))
            ->assertOk()
            ->assertSee('Cetak Billing')
            ->assertSee('Unduh Billing')
            ->assertSee('SPTPD belum terbit karena dokumen triwulan terkait belum lengkap')
            ->assertDontSee('Cetak SPTPD')
            ->assertDontSee('Unduh SPTPD');
    }

    public function test_paid_historical_billing_with_newer_pembetulan_keeps_resolution_actions(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $original = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Paid,
            'billing_code' => '352210100000260303',
            'sptpd_number' => '352210100000260303',
            'paid_at' => now(),
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2030,
        ]);

        $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000260304',
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2030,
            'pembetulan_ke' => 1,
            'parent_tax_id' => $original->id,
        ]);

        $this->actingAs($wajibPajak->user);

        $this->get(route('portal.billing', ['code' => $original->billing_code]))
            ->assertOk()
            ->assertSee('Lihat Resolusi Dokumen')
            ->assertSee('Unduh Billing Historis')
            ->assertDontSee('Cetak SPTPD')
            ->assertDontSee('Unduh SPTPD');
    }
}