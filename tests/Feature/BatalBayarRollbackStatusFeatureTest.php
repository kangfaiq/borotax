<?php

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxPayment;
use App\Enums\TaxStatus;
use App\Filament\Pages\BatalBayar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    test()->seed([
        Database\Seeders\JenisPajakSeeder::class,
        Database\Seeders\SubJenisPajakSeeder::class,
    ]);
});

it('returns self assessment billing to pending after full payment rollback when due date has not passed', function () {
    $tax = createRollbackTaxFixture('41102', TaxStatus::Paid, now()->addDays(7), '352210100000269831');
    $payment = createRollbackPaymentFixture($tax, (float) $tax->amount + (float) $tax->sanksi);

    $this->actingAs(createRollbackAdmin());

    Livewire::test(BatalBayar::class)
        ->set('searchBillingCode', $tax->billing_code)
        ->call('searchBilling')
        ->set('cancelPaymentId', $payment->id)
        ->set('isConfirmingCancel', true)
        ->set('cancelReason', 'Uji rollback self assessment')
        ->call('executeCancelPayment');

    expect($tax->fresh()->status)->toBe(TaxStatus::Pending);
});

it('returns official assessment billing to verified after full payment rollback when due date has not passed', function () {
    $tax = createRollbackTaxFixture('41104', TaxStatus::Paid, now()->addDays(7), '352210100000269832', [
        'skpd_number' => 'SKPD/RKL/2026/0001',
    ]);
    $payment = createRollbackPaymentFixture($tax, (float) $tax->amount + (float) $tax->sanksi);

    $this->actingAs(createRollbackAdmin());

    Livewire::test(BatalBayar::class)
        ->set('searchBillingCode', $tax->billing_code)
        ->call('searchBilling')
        ->set('cancelPaymentId', $payment->id)
        ->set('isConfirmingCancel', true)
        ->set('cancelReason', 'Uji rollback official assessment')
        ->call('executeCancelPayment');

    expect($tax->fresh()->status)->toBe(TaxStatus::Verified);
});

it('returns overdue billing to expired after full payment rollback', function () {
    $tax = createRollbackTaxFixture('41102', TaxStatus::Paid, now()->subDays(4), '352210100000269833');
    $payment = createRollbackPaymentFixture($tax, (float) $tax->amount + (float) $tax->sanksi);

    $this->actingAs(createRollbackAdmin());

    Livewire::test(BatalBayar::class)
        ->set('searchBillingCode', $tax->billing_code)
        ->call('searchBilling')
        ->set('cancelPaymentId', $payment->id)
        ->set('isConfirmingCancel', true)
        ->set('cancelReason', 'Uji rollback overdue billing')
        ->call('executeCancelPayment');

    expect($tax->fresh()->status)->toBe(TaxStatus::Expired);
});

it('keeps sptpd and stpd documents accessible after partial rollback when principal remains fully paid', function () {
    $tax = createRollbackTaxFixture('41102', TaxStatus::Paid, now()->subDays(4), '352210100000269834', [
        'sptpd_number' => '352210100000269834',
        'stpd_number' => 'STPD/2026/04/000001',
        'stpd_payment_code' => Tax::generateManualStpdPaymentCode('352210100000269834'),
    ]);

    createRollbackPaymentFixture($tax, (float) $tax->amount, [
        'principal_paid' => (float) $tax->amount,
        'penalty_paid' => 0,
        'paid_at' => now()->subDays(2),
    ]);

    createApprovedRollbackManualStpd($tax);

    $payment = createRollbackPaymentFixture($tax, (float) $tax->sanksi, [
        'principal_paid' => 0,
        'penalty_paid' => (float) $tax->sanksi,
        'paid_at' => now()->subHour(),
    ]);

    $this->actingAs(createRollbackAdmin());

    Livewire::test(BatalBayar::class)
        ->set('searchBillingCode', $tax->billing_code)
        ->call('searchBilling')
        ->set('cancelPaymentId', $payment->id)
        ->set('isConfirmingCancel', true)
        ->set('cancelReason', 'Uji retensi dokumen saat rollback parsial sanksi')
        ->call('executeCancelPayment');

    $tax->refresh();

    expect($tax->status)->toBe(TaxStatus::PartiallyPaid)
        ->and($tax->sptpd_number)->toBe('352210100000269834')
        ->and($tax->stpd_number)->toBe('STPD/2026/04/000001')
        ->and($tax->stpd_payment_code)->toBe(Tax::generateManualStpdPaymentCode('352210100000269834'))
        ->and($tax->canExposeSptpdDocument())->toBeTrue()
        ->and($tax->canExposeStpdDocument())->toBeTrue();

    $this->actingAs($tax->user)
        ->get(route('portal.sptpd.show', $tax->id))
        ->assertOk();

    $this->actingAs($tax->user)
        ->get(route('portal.stpd.show', $tax->id))
        ->assertOk();

    $this->actingAs($tax->user)
        ->get(route('portal.billing', ['code' => $tax->billing_code]))
        ->assertOk()
        ->assertSee('Cetak SPTPD')
        ->assertSee('Cetak STPD');
});

function createRollbackTaxFixture(string $jenisPajakKode, TaxStatus $status, $paymentExpiredAt, string $billingCode, array $overrides = []): Tax
{
    $wajibPajak = test()->createApprovedWajibPajakFixture([], [
        'email' => sprintf('rollback-%s@example.test', Str::random(8)),
    ]);
    $taxObject = test()->createTaxObjectFixture($wajibPajak, $jenisPajakKode);

    return test()->createTaxFixture($taxObject, $wajibPajak->user, array_merge([
        'status' => $status,
        'billing_code' => $billingCode,
        'paid_at' => now()->subHour(),
        'payment_expired_at' => $paymentExpiredAt,
    ], $overrides));
}

function createRollbackPaymentFixture(Tax $tax, float $amount, array $overrides = []): TaxPayment
{
    return TaxPayment::create(array_merge([
        'tax_id' => $tax->id,
        'external_ref' => 'ROLLBACK-' . Str::random(6),
        'amount_paid' => $amount,
        'principal_paid' => (float) $tax->amount,
        'penalty_paid' => max(0, $amount - (float) $tax->amount),
        'payment_channel' => 'MANUAL',
        'paid_at' => now()->subHour(),
        'description' => 'Fixture rollback pembayaran',
        'raw_response' => ['note' => 'Generated in BatalBayarRollbackStatusFeatureTest'],
    ], $overrides));
}

function createRollbackAdmin(): User
{
    return User::create([
        'name' => 'Rollback Admin',
        'nama_lengkap' => 'Rollback Admin',
        'email' => sprintf('rollback-admin-%s@example.test', Str::random(8)),
        'password' => Hash::make('password'),
        'role' => 'admin',
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}

function createRollbackPanelUser(string $role, ?string $pimpinanId = null): User
{
    return User::create([
        'name' => Str::headline($role) . ' Rollback User',
        'nama_lengkap' => Str::headline($role) . ' Rollback User',
        'email' => sprintf('%s-rollback-%s@example.test', $role, Str::random(8)),
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
        'pimpinan_id' => $pimpinanId,
    ]);
}

function createRollbackPimpinan(): Pimpinan
{
    return Pimpinan::first() ?? Pimpinan::create([
        'kab' => 'Bener Meriah',
        'opd' => 'Bapenda',
        'jabatan' => 'Kepala Bapenda',
        'bidang' => 'Pendapatan',
        'sub_bidang' => 'Pajak Daerah',
        'nama' => 'Pimpinan Rollback',
        'pangkat' => 'Pembina Utama Muda',
        'nip' => '197001011995031001',
    ]);
}

function createApprovedRollbackManualStpd(Tax $tax): StpdManual
{
    $petugas = createRollbackPanelUser('petugas');
    $pimpinan = createRollbackPimpinan();
    $verifikator = createRollbackPanelUser('verifikator', $pimpinan->id);

    return StpdManual::create([
        'tax_id' => $tax->id,
        'tipe' => 'sanksi_saja',
        'nomor_stpd' => $tax->stpd_number,
        'status' => 'disetujui',
        'proyeksi_tanggal_bayar' => now()->addDays(7),
        'bulan_terlambat' => 1,
        'sanksi_dihitung' => (float) $tax->sanksi,
        'pokok_belum_dibayar' => 0,
        'catatan_petugas' => 'Fixture rollback STPD manual',
        'petugas_id' => $petugas->id,
        'petugas_nama' => $petugas->nama_lengkap,
        'tanggal_buat' => now()->subDay(),
        'verifikator_id' => $verifikator->id,
        'verifikator_nama' => $verifikator->nama_lengkap,
        'tanggal_verifikasi' => now()->subHours(12),
        'pimpinan_id' => $pimpinan->id,
    ]);
}