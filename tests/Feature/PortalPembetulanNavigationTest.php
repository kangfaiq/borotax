<?php

namespace Tests\Feature;

use App\Domain\Tax\Models\PembetulanRequest;
use App\Enums\TaxStatus;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalPembetulanNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);
    }

    public function test_portal_sidebar_includes_pembetulan_navigation_entry(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture();

        $response = $this->actingAs($wajibPajak->user)
            ->get(route('portal.dashboard'));

        $response->assertOk()
            ->assertSee('Ajukan Pembetulan')
            ->assertSee(route('portal.pembetulan.index'), false);
    }

    public function test_portal_pembetulan_index_lists_only_latest_eligible_billings_for_logged_in_user(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'pembetulan-owner@example.test',
        ]);
        $otherWajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'pembetulan-other@example.test',
        ]);

        $primaryObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $secondaryObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $otherObject = $this->createTaxObjectFixture($otherWajibPajak, '41102');

        $historicalBilling = $this->createTaxFixture($primaryObject, $wajibPajak->user, [
            'billing_code' => '352210100000261001',
            'masa_pajak_bulan' => 1,
            'masa_pajak_tahun' => 2032,
            'status' => TaxStatus::Paid,
            'created_at' => now()->subDays(6),
        ]);

        $latestPembetulan = $this->createTaxFixture($primaryObject, $wajibPajak->user, [
            'billing_code' => '352210100000261002',
            'masa_pajak_bulan' => 1,
            'masa_pajak_tahun' => 2032,
            'status' => TaxStatus::Pending,
            'parent_tax_id' => $historicalBilling->id,
            'pembetulan_ke' => 1,
            'revision_attempt_no' => 1,
            'created_at' => now()->subDays(2),
        ]);

        $pendingBilling = $this->createTaxFixture($secondaryObject, $wajibPajak->user, [
            'billing_code' => '352210100000261003',
            'masa_pajak_bulan' => 2,
            'masa_pajak_tahun' => 2032,
            'status' => TaxStatus::Pending,
            'created_at' => now()->subDay(),
        ]);

        $billingWithPendingRequest = $this->createTaxFixture($secondaryObject, $wajibPajak->user, [
            'billing_code' => '352210100000261004',
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2032,
            'status' => TaxStatus::Verified,
        ]);

        PembetulanRequest::create([
            'tax_id' => $billingWithPendingRequest->id,
            'user_id' => $wajibPajak->user->id,
            'alasan' => 'Perlu koreksi omzet karena input awal tidak sesuai transaksi sebenarnya.',
            'omzet_baru' => 1500000,
            'status' => 'pending',
        ]);

        $this->createTaxFixture($otherObject, $otherWajibPajak->user, [
            'billing_code' => '352210100000269999',
            'masa_pajak_bulan' => 4,
            'masa_pajak_tahun' => 2032,
            'status' => TaxStatus::Pending,
        ]);

        $response = $this->actingAs($wajibPajak->user)
            ->get(route('portal.pembetulan.index'));

        $response->assertOk()
            ->assertSee('Pilih Billing untuk Pembetulan')
            ->assertSee($latestPembetulan->billing_code)
            ->assertSee($pendingBilling->billing_code)
            ->assertSee($billingWithPendingRequest->billing_code)
            ->assertDontSee($historicalBilling->billing_code)
            ->assertDontSee('352210100000269999')
            ->assertSee(route('portal.pembetulan.create', $latestPembetulan->id), false)
            ->assertSee(route('portal.pembetulan.create', $pendingBilling->id), false)
            ->assertDontSee(route('portal.pembetulan.create', $billingWithPendingRequest->id), false)
            ->assertSee('Menunggu Review');
    }

    public function test_portal_pembetulan_index_is_paginated_and_preserves_search_query_in_links(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'pembetulan-pagination@example.test',
        ]);
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $search = '352210100000262';
        $billingCodes = [];

        foreach (range(1, 11) as $sequence) {
            $billingCode = $search . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            $billingCodes[] = $billingCode;

            $this->createTaxFixture($taxObject, $wajibPajak->user, [
                'billing_code' => $billingCode,
                'masa_pajak_bulan' => $sequence,
                'masa_pajak_tahun' => 2033,
                'created_at' => now()->subMinutes($sequence),
            ]);
        }

        $firstPageResponse = $this->actingAs($wajibPajak->user)
            ->get(route('portal.pembetulan.index', [
                'search' => $search,
            ]));

        $firstPageResponse->assertOk()
            ->assertSee('Menampilkan 1–10 dari 11 billing')
            ->assertSee($billingCodes[0])
            ->assertSee($billingCodes[9])
            ->assertDontSee($billingCodes[10])
            ->assertSee('search=' . $search, false)
            ->assertSee('page=2', false);

        $secondPageResponse = $this->actingAs($wajibPajak->user)
            ->get(route('portal.pembetulan.index', [
                'search' => $search,
                'page' => 2,
            ]));

        $secondPageResponse->assertOk()
            ->assertSee('Menampilkan 11–11 dari 11 billing')
            ->assertSee($billingCodes[10])
            ->assertDontSee($billingCodes[0]);
    }

    public function test_portal_pembetulan_create_page_includes_attachment_preview_and_auto_compress_hooks(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'pembetulan-form@example.test',
        ]);
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $tax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'billing_code' => '352210100000263001',
            'status' => TaxStatus::Pending,
        ]);

        $response = $this->actingAs($wajibPajak->user)
            ->get(route('portal.pembetulan.create', $tax->id));

        $response->assertOk()
            ->assertSee('id="inputLampiranPembetulan"', false)
            ->assertSee('data-max-file-size="1048576"', false)
            ->assertSee('data-auto-compress-images="true"', false)
            ->assertSee('id="pembetulanAttachmentPreviewCard"', false)
            ->assertSee('id="pembetulanAttachmentPreviewImage"', false)
            ->assertSee('id="pembetulanAttachmentPreviewPdf"', false)
            ->assertSee('id="pembetulanAttachmentClientError"', false);
    }
}