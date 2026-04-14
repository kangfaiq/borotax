<?php

use App\Enums\TaxStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    test()->seed([
        Database\Seeders\JenisPajakSeeder::class,
        Database\Seeders\SubJenisPajakSeeder::class,
    ]);
});

it('derives expired display status without changing the stored workflow status', function () {
    $wajibPajak = $this->createApprovedWajibPajakFixture();
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');

    $tax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
        'status' => TaxStatus::Pending,
        'billing_code' => '352210100000269801',
        'payment_expired_at' => now()->subDays(3),
    ]);

    expect($tax->status)->toBe(TaxStatus::Pending)
        ->and($tax->display_status)->toBe(TaxStatus::Expired)
        ->and($tax->display_status_label)->toBe('Kedaluwarsa');
});

it('shows overdue billing as expired on the portal billing check page', function () {
    $wajibPajak = $this->createApprovedWajibPajakFixture();
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');

    $tax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
        'status' => TaxStatus::Pending,
        'billing_code' => '352210100000269802',
        'payment_expired_at' => now()->subDays(5),
    ]);

    $this->actingAs($wajibPajak->user)
        ->get(route('portal.billing', ['code' => $tax->billing_code]))
        ->assertOk()
        ->assertSee('Kedaluwarsa')
        ->assertSee('badge-expired', false);
});

it('shows overdue billing as expired in portal dashboard and history badges', function () {
    $wajibPajak = $this->createApprovedWajibPajakFixture([], [
        'email' => 'billing-expired-display@example.test',
    ]);
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');

    $tax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
        'status' => TaxStatus::Pending,
        'billing_code' => '352210100000269803',
        'payment_expired_at' => now()->subDays(2),
        'masa_pajak_bulan' => 6,
        'masa_pajak_tahun' => 2031,
    ]);

    $this->actingAs($wajibPajak->user)
        ->get(route('portal.dashboard'))
        ->assertOk()
        ->assertSee($tax->billing_code)
        ->assertSee('Kedaluwarsa');

    $this->actingAs($wajibPajak->user)
        ->get(route('portal.history'))
        ->assertOk()
        ->assertSee($tax->billing_code)
        ->assertSee('Kedaluwarsa');
});