<?php

namespace Tests\Feature;

use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxPayment;
use App\Enums\TaxStatus;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BillingQrStatusResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_status_redirects_to_sptpd_when_paid_billing_has_no_pembetulan(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $tax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Paid,
            'billing_code' => '352210100000260001',
            'sptpd_number' => '352210100000260001',
        ]);
        $this->createPrincipalPayment($tax);

        $this->actingAs($wajibPajak->user);

        $this->get(route('portal.billing.check-status', $tax->id))
            ->assertRedirect(route('portal.sptpd.show', $tax->id));
    }

    public function test_qr_status_shows_resolution_page_when_original_billing_has_newer_pembetulan(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $original = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Paid,
            'billing_code' => '352210100000260010',
            'sptpd_number' => '352210100000260010',
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2030,
        ]);

        $pembetulan = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000260011',
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2030,
            'pembetulan_ke' => 1,
            'parent_tax_id' => $original->id,
        ]);

        $this->actingAs($wajibPajak->user);

        $this->get(route('portal.billing.check-status', $original->id))
            ->assertOk()
            ->assertSee('Billing yang dipindai sudah memiliki pembetulan yang lebih baru')
            ->assertSee('<aside class="sidebar" id="sidebar">', false)
            ->assertSee('<header class="topbar">', false)
            ->assertSee($original->billing_code)
            ->assertSee($pembetulan->billing_code)
            ->assertSee('Lihat Billing Pembetulan Terbaru')
            ->assertSee(route('portal.billing.document.show', $pembetulan->id), false);
    }

    public function test_qr_status_resolution_uses_standalone_layout_for_backoffice_roles(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $original = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Paid,
            'billing_code' => '352210100000260012',
            'sptpd_number' => '352210100000260012',
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2030,
        ]);

        $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000260013',
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2030,
            'pembetulan_ke' => 1,
            'parent_tax_id' => $original->id,
        ]);

        foreach (['admin', 'petugas', 'verifikator'] as $role) {
            $this->actingAs($this->createPortalUserFixture([
                'role' => $role,
                'navigation_mode' => 'sidebar',
            ]));

            $this->get(route('portal.billing.check-status', $original->id))
                ->assertOk()
                ->assertSee('billing-status-shell', false)
                ->assertDontSee('<aside class="sidebar" id="sidebar">', false)
                ->assertDontSee('<header class="topbar">', false);

            auth()->logout();
        }
    }

    public function test_qr_status_resolution_uses_latest_sptpd_when_latest_pembetulan_already_paid(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $original = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Paid,
            'billing_code' => '352210100000260020',
            'sptpd_number' => '352210100000260020',
            'masa_pajak_bulan' => 4,
            'masa_pajak_tahun' => 2030,
        ]);

        $firstPembetulan = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Paid,
            'billing_code' => '352210100000260021',
            'sptpd_number' => '352210100000260021',
            'masa_pajak_bulan' => 4,
            'masa_pajak_tahun' => 2030,
            'pembetulan_ke' => 1,
            'parent_tax_id' => $original->id,
        ]);

        $latestPembetulan = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Paid,
            'billing_code' => '352210100000260022',
            'sptpd_number' => '352210100000260022',
            'masa_pajak_bulan' => 4,
            'masa_pajak_tahun' => 2030,
            'pembetulan_ke' => 2,
            'parent_tax_id' => $firstPembetulan->id,
        ]);
        $this->createPrincipalPayment($latestPembetulan);

        $this->actingAs($wajibPajak->user);

        $this->get(route('portal.billing.check-status', $original->id))
            ->assertOk()
            ->assertSee($latestPembetulan->billing_code)
            ->assertSee('Lihat SPTPD Pembetulan Terbaru')
            ->assertSee(route('portal.sptpd.show', $latestPembetulan->id), false);
    }

    public function test_direct_billing_document_open_shows_resolution_banner_for_original_with_newer_pembetulan(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $original = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Paid,
            'billing_code' => '352210100000260030',
            'sptpd_number' => '352210100000260030',
            'masa_pajak_bulan' => 5,
            'masa_pajak_tahun' => 2030,
        ]);

        $pembetulan = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000260031',
            'masa_pajak_bulan' => 5,
            'masa_pajak_tahun' => 2030,
            'pembetulan_ke' => 1,
            'parent_tax_id' => $original->id,
        ]);

        $this->actingAs($wajibPajak->user);

        $this->get(route('portal.billing.document.show', $original->id))
            ->assertOk()
            ->assertSee('Billing lama yang Anda buka sudah memiliki pembetulan yang lebih baru')
            ->assertSee('<aside class="sidebar" id="sidebar">', false)
            ->assertSee($original->billing_code)
            ->assertSee($pembetulan->billing_code)
            ->assertSee(route('portal.billing.document.show', ['taxId' => $original->id, 'historical' => 1]), false)
            ->assertSee('Lihat Billing yang Dipindai');
    }

    public function test_historical_billing_document_can_still_be_opened_as_pdf_after_resolution_banner(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $original = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Paid,
            'billing_code' => '352210100000260040',
            'sptpd_number' => '352210100000260040',
            'masa_pajak_bulan' => 6,
            'masa_pajak_tahun' => 2030,
        ]);

        $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000260041',
            'masa_pajak_bulan' => 6,
            'masa_pajak_tahun' => 2030,
            'pembetulan_ke' => 1,
            'parent_tax_id' => $original->id,
        ]);

        $this->actingAs($wajibPajak->user);

        $response = $this->get(route('portal.billing.document.show', ['taxId' => $original->id, 'historical' => 1]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type', ''));
    }

    private function createPrincipalPayment(Tax $tax): TaxPayment
    {
        return TaxPayment::create([
            'tax_id' => $tax->id,
            'external_ref' => 'QR-STATUS-' . Str::random(6),
            'amount_paid' => (float) $tax->amount,
            'principal_paid' => (float) $tax->amount,
            'penalty_paid' => 0,
            'payment_channel' => 'MANUAL',
            'paid_at' => now()->subMinute(),
            'description' => 'Fixture pembayaran pokok untuk uji QR status billing',
            'raw_response' => ['note' => 'Generated in BillingQrStatusResolutionTest'],
        ]);
    }
}