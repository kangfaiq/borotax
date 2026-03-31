<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxPayment;
use App\Enums\TaxStatus;
use App\Filament\Pages\BatalBayar;
use App\Filament\Pages\LunasBayarManual;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class StpdPaymentCodeLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_billing_check_can_find_tax_by_stpd_payment_code(): void
    {
        $tax = $this->createApprovedManualSanksiSajaTaxFixture();

        $this->get(route('billing.check', ['code' => $tax->stpd_payment_code]))
            ->assertOk()
            ->assertSeeText('Kode yang Dicek')
            ->assertSeeText('Billing Sumber')
            ->assertSeeText('Alias Pembayaran STPD Manual')
            ->assertSeeText($tax->billing_code)
            ->assertSeeText($tax->stpd_payment_code)
            ->assertViewHas('billing', fn (?Tax $billing) => $billing?->is($tax));
    }

    public function test_api_billing_check_can_find_tax_by_stpd_payment_code(): void
    {
        $tax = $this->createApprovedManualSanksiSajaTaxFixture();

        $this->getJson('/api/v1/billing/check?code=' . $tax->stpd_payment_code)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $tax->id);
    }

    public function test_manual_payment_and_cancel_pages_can_search_using_stpd_payment_code(): void
    {
        $tax = $this->createApprovedManualSanksiSajaTaxFixture();
        $admin = $this->createAdminPanelUser('admin');

        $this->actingAs($admin);

        Livewire::test(LunasBayarManual::class)
            ->set('searchBillingCode', $tax->stpd_payment_code)
            ->call('searchBilling')
            ->assertSet('foundTax.id', $tax->id)
            ->assertSet('taxDetails.billing_code', $tax->stpd_payment_code)
            ->assertSet('taxDetails.source_billing_code', $tax->billing_code)
            ->assertSet('taxDetails.stpd_payment_code', $tax->stpd_payment_code)
            ->assertSet('taxDetails.has_stpd_alias', true);

        Livewire::test(BatalBayar::class)
            ->set('searchBillingCode', $tax->stpd_payment_code)
            ->call('searchBilling')
            ->assertSet('foundTax.id', $tax->id)
            ->assertSet('taxDetails.billing_code', $tax->stpd_payment_code)
            ->assertSet('taxDetails.source_billing_code', $tax->billing_code)
            ->assertSet('taxDetails.stpd_payment_code', $tax->stpd_payment_code)
            ->assertSet('taxDetails.has_stpd_alias', true);
    }

    private function createApprovedManualSanksiSajaTaxFixture(): Tax
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $this->seedPimpinanReferences();

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak);
        $tax = $this->createTaxFixture($taxObject, overrides: [
            'status' => TaxStatus::Paid,
            'paid_at' => now(),
            'payment_expired_at' => now()->subDays(45),
            'masa_pajak_bulan' => 12,
            'masa_pajak_tahun' => 2025,
        ]);

        TaxPayment::create([
            'tax_id' => $tax->id,
            'external_ref' => 'TEST-LOOKUP-' . Str::random(6),
            'amount_paid' => (float) $tax->amount,
            'principal_paid' => (float) $tax->amount,
            'penalty_paid' => 0,
            'payment_channel' => 'MANUAL',
            'paid_at' => now(),
            'description' => 'Pembayaran pokok untuk uji lookup STPD manual',
            'raw_response' => ['note' => 'Generated in StpdPaymentCodeLookupTest'],
        ]);

        $petugas = $this->createAdminPanelUser('petugas');
        $verifikator = $this->createAdminPanelUser('verifikator', Pimpinan::firstOrFail()->id);

        $stpd = StpdManual::create([
            'tax_id' => $tax->id,
            'tipe' => 'sanksi_saja',
            'nomor_stpd' => 'STPD/2026/03/000001',
            'status' => 'disetujui',
            'bulan_terlambat' => 1,
            'sanksi_dihitung' => (float) $tax->sanksi,
            'pokok_belum_dibayar' => 0,
            'catatan_petugas' => 'Uji alias pembayaran STPD manual',
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
            'tanggal_buat' => now()->subDay(),
            'verifikator_id' => $verifikator->id,
            'verifikator_nama' => $verifikator->nama_lengkap,
            'tanggal_verifikasi' => now()->subHours(12),
            'pimpinan_id' => Pimpinan::firstOrFail()->id,
        ]);

        $tax->syncApprovedManualStpd($stpd);

        return $tax->fresh();
    }

    private function createAdminPanelUser(string $role, ?string $pimpinanId = null): User
    {
        return User::create([
            'name' => Str::headline($role) . ' User',
            'nama_lengkap' => Str::headline($role) . ' User',
            'email' => sprintf('%s-%s@example.test', $role, Str::random(8)),
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
            'pimpinan_id' => $pimpinanId,
        ]);
    }
}