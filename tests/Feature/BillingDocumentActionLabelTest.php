<?php

namespace Tests\Feature;

use App\Enums\TaxStatus;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingDocumentActionLabelTest extends TestCase
{
    use RefreshDatabase;

    public function test_tax_model_returns_dynamic_billing_document_labels(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');

        $activeBilling = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000260101',
            'masa_pajak_bulan' => 1,
            'masa_pajak_tahun' => 2030,
        ]);

        $historicalBilling = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Paid,
            'billing_code' => '352210100000260102',
            'sptpd_number' => '352210100000260102',
            'masa_pajak_bulan' => 2,
            'masa_pajak_tahun' => 2030,
        ]);

        $revisionBilling = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000260103',
            'masa_pajak_bulan' => 2,
            'masa_pajak_tahun' => 2030,
            'pembetulan_ke' => 1,
            'parent_tax_id' => $historicalBilling->id,
        ]);

        $this->assertSame('Cetak Billing', $activeBilling->getBillingDocumentActionLabel());
        $this->assertSame('Cetak billing', $activeBilling->getBillingDocumentActionTitle());
        $this->assertSame('Unduh Billing', $activeBilling->getBillingDownloadActionLabel());
        $this->assertSame('Unduh billing', $activeBilling->getBillingDownloadActionTitle());

        $historicalBilling->load('children');

        $this->assertSame('Lihat Resolusi Dokumen', $historicalBilling->getBillingDocumentActionLabel());
        $this->assertSame('Lihat resolusi dokumen billing', $historicalBilling->getBillingDocumentActionTitle());
        $this->assertSame('Unduh Billing Historis', $historicalBilling->getBillingDownloadActionLabel());
        $this->assertSame('Unduh billing historis', $historicalBilling->getBillingDownloadActionTitle());

        $this->assertSame('Cetak Billing Pembetulan', $revisionBilling->getBillingDocumentActionLabel());
        $this->assertSame('Cetak billing pembetulan', $revisionBilling->getBillingDocumentActionTitle());
        $this->assertSame('Unduh Billing Pembetulan', $revisionBilling->getBillingDownloadActionLabel());
        $this->assertSame('Unduh billing pembetulan', $revisionBilling->getBillingDownloadActionTitle());
    }

    public function test_portal_views_render_resolution_labels_for_original_billing_with_newer_pembetulan(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');

        $original = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000260110',
        ]);

        $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000260111',
            'pembetulan_ke' => 1,
            'parent_tax_id' => $original->id,
        ]);

        $this->actingAs($wajibPajak->user);

        $historyResponse = $this->get(route('portal.history'));
        $historyResponse->assertOk();
        $this->assertStringContainsString('title="Lihat resolusi dokumen billing"', $historyResponse->getContent());
        $this->assertStringContainsString('title="Unduh billing historis"', $historyResponse->getContent());

        $dashboardResponse = $this->get(route('portal.dashboard'));
        $dashboardResponse->assertOk();
        $this->assertStringContainsString('title="Lihat resolusi dokumen billing"', $dashboardResponse->getContent());
        $this->assertStringContainsString('title="Unduh billing historis"', $dashboardResponse->getContent());

        $this->get(route('portal.billing', ['code' => $original->billing_code]))
            ->assertOk()
            ->assertSee('Lihat Resolusi Dokumen')
            ->assertSee('Unduh Billing Historis');

        $this->get(route('portal.self-assessment.success', $original->id))
            ->assertOk()
            ->assertSee('Lihat Resolusi Dokumen')
            ->assertSee('Unduh Billing Historis');
    }
}