<?php

use App\Domain\Auth\Models\User;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Services\BillingService;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\TaxStatus;
use App\Filament\Pages\BuatStpd;
use App\Filament\Pages\LunasBayarManual;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    test()->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
    ]);
});

it('persists overdue billing as expired and keeps it searchable on the manual payment page', function () {
    $wajibPajak = $this->createApprovedWajibPajakFixture();
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
    $tax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
        'status' => TaxStatus::Pending,
        'billing_code' => '352210100000269811',
        'payment_expired_at' => now()->subDays(7),
    ]);

    $this->actingAs(createAdminPanelUserForExpiredWorkflow('admin'));

    Livewire::test(LunasBayarManual::class)
        ->set('searchBillingCode', $tax->billing_code)
        ->call('searchBilling')
        ->assertSet('foundTax.id', $tax->id)
        ->assertSet('taxDetails.status', TaxStatus::Expired->value)
        ->assertSet('taxDetails.status_label', 'Kedaluwarsa')
        ->assertSee('KEDALUWARSA');

    expect($tax->fresh()->status)->toBe(TaxStatus::Expired);
});

it('persists overdue billing as expired and keeps it eligible on the stpd page', function () {
    $wajibPajak = $this->createApprovedWajibPajakFixture([], [
        'email' => 'expired-stpd@example.test',
    ]);
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
    $tax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
        'status' => TaxStatus::Verified,
        'billing_code' => '352210100000269812',
        'payment_expired_at' => now()->subDays(10),
    ]);

    $this->actingAs(createAdminPanelUserForExpiredWorkflow('petugas'));

    Livewire::test(BuatStpd::class)
        ->set('searchKeyword', $tax->billing_code)
        ->call('cariBilling')
        ->assertSet('selectedTaxId', $tax->id)
        ->assertSet('taxData.status', TaxStatus::Expired->value)
        ->assertSet('taxData.status_label', 'Kedaluwarsa')
        ->assertSee('KEDALUWARSA');

    expect($tax->fresh()->status)->toBe(TaxStatus::Expired);
});

it('keeps persisted expired billing visible on portal pembetulan screens', function () {
    $wajibPajak = $this->createApprovedWajibPajakFixture([], [
        'email' => 'expired-pembetulan@example.test',
    ]);
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
    $tax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
        'status' => TaxStatus::Pending,
        'billing_code' => '352210100000269813',
        'payment_expired_at' => now()->subDays(14),
    ]);

    $this->actingAs($wajibPajak->user)
        ->get(route('portal.pembetulan.index'))
        ->assertOk()
        ->assertSee($tax->billing_code)
        ->assertSee('Kedaluwarsa')
        ->assertSee(route('portal.pembetulan.create', $tax->id), false);

    $this->actingAs($wajibPajak->user)
        ->get(route('portal.pembetulan.create', $tax->id))
        ->assertOk()
        ->assertSee('Kedaluwarsa');

    expect($tax->fresh()->status)->toBe(TaxStatus::Expired);
});

it('still treats expired billing as an active duplicate-blocking billing for the same period', function () {
    $wajibPajak = $this->createApprovedWajibPajakFixture([], [
        'email' => 'expired-duplicate@example.test',
    ]);
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
    $tax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
        'status' => TaxStatus::Expired,
        'billing_code' => '352210100000269814',
        'masa_pajak_bulan' => 9,
        'masa_pajak_tahun' => 2032,
    ]);

    $billingService = app(BillingService::class);

    expect($billingService->billingExistsForPeriod($taxObject->id, 9, 2032))->toBeTrue()
        ->and($billingService->findExistingBillingForPeriod($taxObject->id, 9, 2032)?->is($tax))->toBeTrue();
});

function createAdminPanelUserForExpiredWorkflow(string $role): User
{
    return User::create([
        'name' => Str::headline($role) . ' Expired Workflow',
        'nama_lengkap' => Str::headline($role) . ' Expired Workflow',
        'email' => sprintf('%s-expired-%s@example.test', $role, Str::random(8)),
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}