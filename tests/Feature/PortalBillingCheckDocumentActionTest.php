<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxPayment;
use App\Enums\TaxStatus;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        $this->createPrincipalPayment($tax);

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
        $this->createPrincipalPayment($tax);

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
        $this->createPrincipalPayment($original);

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

    public function test_partially_paid_billing_keeps_sptpd_and_stpd_actions_when_principal_remains_paid_after_penalty_rollback(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $tax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
            'status' => TaxStatus::PartiallyPaid,
            'billing_code' => '352210100000260305',
            'sptpd_number' => '352210100000260305',
            'stpd_number' => 'STPD/2030/01/000305',
            'stpd_payment_code' => Tax::generateManualStpdPaymentCode('352210100000260305'),
            'payment_expired_at' => now()->subDays(3),
            'paid_at' => now()->subHour(),
        ]);

        $this->createPrincipalPayment($tax);
        $this->createApprovedSanksiOnlyStpd($tax);

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

    private function createPrincipalPayment(Tax $tax): TaxPayment
    {
        return TaxPayment::create([
            'tax_id' => $tax->id,
            'external_ref' => 'PORTAL-DOC-' . Str::random(6),
            'amount_paid' => (float) $tax->amount,
            'principal_paid' => (float) $tax->amount,
            'penalty_paid' => 0,
            'payment_channel' => 'MANUAL',
            'paid_at' => now()->subMinute(),
            'description' => 'Fixture pembayaran pokok untuk akses dokumen portal',
            'raw_response' => ['note' => 'Generated in PortalBillingCheckDocumentActionTest'],
        ]);
    }

    private function createApprovedSanksiOnlyStpd(Tax $tax): StpdManual
    {
        $pimpinan = Pimpinan::first() ?? Pimpinan::create([
            'kab' => 'Bener Meriah',
            'opd' => 'Bapenda',
            'jabatan' => 'Kepala Bapenda',
            'bidang' => 'Pendapatan',
            'sub_bidang' => 'Pajak Daerah',
            'nama' => 'Pimpinan Portal Billing',
            'pangkat' => 'Pembina Utama Muda',
            'nip' => '197001011995031002',
        ]);

        $petugas = $this->createAdminPanelUser('petugas');
        $verifikator = $this->createAdminPanelUser('verifikator', $pimpinan->id);

        return StpdManual::create([
            'tax_id' => $tax->id,
            'tipe' => 'sanksi_saja',
            'nomor_stpd' => $tax->stpd_number,
            'status' => 'disetujui',
            'proyeksi_tanggal_bayar' => now()->addDays(7),
            'bulan_terlambat' => 1,
            'sanksi_dihitung' => (float) $tax->sanksi,
            'pokok_belum_dibayar' => 0,
            'catatan_petugas' => 'Fixture STPD sanksi portal',
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
            'tanggal_buat' => now()->subDay(),
            'verifikator_id' => $verifikator->id,
            'verifikator_nama' => $verifikator->nama_lengkap,
            'tanggal_verifikasi' => now()->subHours(12),
            'pimpinan_id' => $pimpinan->id,
        ]);
    }

    private function createAdminPanelUser(string $role, ?string $pimpinanId = null): User
    {
        return User::create([
            'name' => Str::headline($role) . ' Portal Billing',
            'nama_lengkap' => Str::headline($role) . ' Portal Billing',
            'email' => sprintf('%s-portal-billing-%s@example.test', $role, Str::random(8)),
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
            'pimpinan_id' => $pimpinanId,
        ]);
    }
}