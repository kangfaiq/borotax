<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\Tax\Models\TaxPayment;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Enums\TaxStatus;
use App\Filament\Pages\BuatStpd;
use App\Filament\Resources\StpdManualResource\Pages\ListStpdManuals;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TaxStpdWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_can_create_stpd_manual_draft_for_partially_paid_billing(): void
    {
        $this->seed([
            AdminUserSeeder::class,
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);
        $tax = $this->createTaxFixture(
            $this->createTaxObjectFixture($this->createApprovedWajibPajakFixture()),
            overrides: [
                'status' => TaxStatus::Verified,
                'payment_expired_at' => now()->subDays(45),
                'masa_pajak_bulan' => 10,
                'masa_pajak_tahun' => 2025,
            ],
        );

        $petugas = $this->createAdminPanelUser('petugas');
        $proyeksiTanggalBayar = now()->addDays(10)->format('Y-m-d');

        $this->actingAs($petugas);

        Livewire::test(BuatStpd::class)
            ->set('searchKeyword', $tax->billing_code)
            ->call('cariBilling')
            ->assertSet('selectedTaxId', $tax->id)
            ->set('tipeStpd', 'pokok_sanksi')
            ->set('proyeksiTanggalBayar', $proyeksiTanggalBayar)
            ->call('hitungProyeksi')
            ->call('buatStpd');

        $stpd = StpdManual::where('tax_id', $tax->id)->firstOrFail();

        $this->assertSame('draft', $stpd->status);
        $this->assertSame('pokok_sanksi', $stpd->tipe);
        $this->assertSame($petugas->id, $stpd->petugas_id);
        $this->assertGreaterThan(0, (float) $stpd->sanksi_dihitung);
        $this->assertGreaterThan(0, (float) $stpd->pokok_belum_dibayar);
    }

    public function test_stpd_is_auto_issued_only_after_quarter_is_complete(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);
        $this->createTaxObjectFixture($this->createApprovedWajibPajakFixture());

        $januaryTax = $this->createPaidQuarterBillingWithSanksi(1);
        $februaryTax = $this->createPaidQuarterBillingWithSanksi(2);

        $this->assertNull($januaryTax->fresh()->stpd_number);
        $this->assertNull($februaryTax->fresh()->stpd_number);

        $marchTax = $this->createPaidQuarterBillingWithSanksi(3);

        $januaryTax->refresh();
        $februaryTax->refresh();
        $marchTax->refresh();

        $this->assertSame($januaryTax->billing_code, $januaryTax->stpd_number);
        $this->assertSame($februaryTax->billing_code, $februaryTax->stpd_number);
        $this->assertSame($marchTax->billing_code, $marchTax->stpd_number);
    }

    public function test_paid_quarter_documents_are_available_only_after_quarter_is_complete(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);
        $this->createTaxObjectFixture($this->createApprovedWajibPajakFixture());

        $januaryTax = $this->createPaidQuarterBillingWithSanksi(1);
        $februaryTax = $this->createPaidQuarterBillingWithSanksi(2);

        $this->actingAs($januaryTax->user);

        $this->get(route('portal.sptpd.show', $januaryTax->id))->assertNotFound();
        $this->get(route('portal.stpd.show', $januaryTax->id))->assertNotFound();

        $marchTax = $this->createPaidQuarterBillingWithSanksi(3);

        $this->assertNotNull($januaryTax->fresh()->sptpd_number);
        $this->assertNotNull($januaryTax->fresh()->stpd_number);
        $this->assertNotNull($februaryTax->fresh()->sptpd_number);
        $this->assertNotNull($februaryTax->fresh()->stpd_number);
        $this->assertNotNull($marchTax->fresh()->sptpd_number);
        $this->assertNotNull($marchTax->fresh()->stpd_number);

        $this->get(route('portal.sptpd.show', $januaryTax->id))->assertOk();
        $this->get(route('portal.stpd.show', $januaryTax->id))->assertOk();
    }

    public function test_portal_document_routes_are_limited_to_tax_owner_or_backoffice_roles(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);
        $this->createTaxObjectFixture($this->createApprovedWajibPajakFixture());

        $januaryTax = $this->createPaidQuarterBillingWithSanksi(1);
        $this->createPaidQuarterBillingWithSanksi(2);
        $this->createPaidQuarterBillingWithSanksi(3);

        $portalUserLain = User::create([
            'name' => 'Portal User Lain',
            'nama_lengkap' => 'Portal User Lain',
            'email' => 'portal-user-lain@example.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($portalUserLain);
        $this->get(route('portal.sptpd.show', $januaryTax->id))->assertNotFound();
        $this->get(route('portal.stpd.show', $januaryTax->id))->assertNotFound();

        $admin = $this->createAdminPanelUser('admin');

        $this->actingAs($admin);
        $this->get(route('portal.sptpd.show', $januaryTax->id))->assertOk();
        $this->get(route('portal.stpd.show', $januaryTax->id))->assertOk();
    }

    #[DataProvider('verificationRoleProvider')]
    public function test_admin_and_verifikator_can_approve_draft_stpd_and_sync_tax_data(string $role): void
    {
        $this->seedStpdWorkflowRequirements();

        $draft = $this->createDraftStpd();
        $tax = $draft->tax()->firstOrFail();
        $verifikator = $this->createAdminPanelUser($role, Pimpinan::firstOrFail()->id);

        $this->actingAs($verifikator);

        Livewire::test(ListStpdManuals::class)
            ->assertCanSeeTableRecords([$draft])
            ->callTableAction('approve', $draft)
            ->assertHasNoTableActionErrors();

        $draft->refresh();
        $tax->refresh();

        $this->assertSame('disetujui', $draft->status);
        $this->assertNotNull($draft->nomor_stpd);
        $this->assertSame($verifikator->id, $draft->verifikator_id);
        $this->assertSame($verifikator->nama_lengkap, $draft->verifikator_nama);
        $this->assertSame(Pimpinan::firstOrFail()->id, $draft->pimpinan_id);
        $this->assertMatchesRegularExpression('/^STPD\/\d{4}\/\d{2}\/\d{6}$/', $draft->nomor_stpd);

        $this->assertSame($draft->nomor_stpd, $tax->stpd_number);
        $this->assertNull($tax->stpd_payment_code);
        $this->assertEquals((float) $draft->sanksi_dihitung, (float) $tax->sanksi);
    }

    public function test_verifikator_approval_for_sanksi_saja_generates_stpd_payment_code(): void
    {
        $this->seedStpdWorkflowRequirements();

        $draft = $this->createDraftStpd('sanksi_saja');
        $tax = $draft->tax()->firstOrFail();
        $verifikator = $this->createAdminPanelUser('verifikator', Pimpinan::firstOrFail()->id);

        $this->actingAs($verifikator);

        Livewire::test(ListStpdManuals::class)
            ->assertCanSeeTableRecords([$draft])
            ->callTableAction('approve', $draft)
            ->assertHasNoTableActionErrors();

        $draft->refresh();
        $tax->refresh();

        $this->assertSame('disetujui', $draft->status);
        $this->assertSame($draft->nomor_stpd, $tax->stpd_number);
        $this->assertSame(
            Tax::generateManualStpdPaymentCode($tax->billing_code),
            $tax->stpd_payment_code,
        );
        $this->assertNotSame($tax->billing_code, $tax->stpd_payment_code);
    }

    #[DataProvider('verificationRoleProvider')]
    public function test_admin_and_verifikator_can_reject_draft_stpd_with_reason(string $role): void
    {
        $this->seedStpdWorkflowRequirements();

        $draft = $this->createDraftStpd();
        $tax = $draft->tax()->firstOrFail();
        $verifikator = $this->createAdminPanelUser($role, Pimpinan::firstOrFail()->id);
        $alasanPenolakan = 'Data perhitungan sanksi perlu diperbaiki terlebih dahulu.';

        $this->actingAs($verifikator);

        Livewire::test(ListStpdManuals::class)
            ->assertCanSeeTableRecords([$draft])
            ->callTableAction('reject', $draft, [
                'catatan_verifikasi' => $alasanPenolakan,
            ])
            ->assertHasNoTableActionErrors();

        $draft->refresh();
        $tax->refresh();

        $this->assertSame('ditolak', $draft->status);
        $this->assertSame($alasanPenolakan, $draft->catatan_verifikasi);
        $this->assertSame($verifikator->id, $draft->verifikator_id);
        $this->assertSame($verifikator->nama_lengkap, $draft->verifikator_nama);
        $this->assertNull($draft->nomor_stpd);
        $this->assertNull($tax->stpd_number);
    }

    private function seedStpdWorkflowRequirements(): void
    {
        $this->seed([
            AdminUserSeeder::class,
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);
        $this->createTaxFixture(
            $this->createTaxObjectFixture($this->createApprovedWajibPajakFixture()),
            overrides: [
                'status' => TaxStatus::Verified,
                'payment_expired_at' => now()->subDays(45),
                'masa_pajak_bulan' => 9,
                'masa_pajak_tahun' => 2025,
            ],
        );

        $this->seedPimpinanReferences();
    }

    public static function verificationRoleProvider(): array
    {
        return [
            'admin' => ['admin'],
            'verifikator' => ['verifikator'],
        ];
    }

    private function createDraftStpd(string $tipe = 'pokok_sanksi'): StpdManual
    {
        $petugas = $this->createAdminPanelUser('petugas');
        $tax = $tipe === 'sanksi_saja'
            ? $this->createPaidBillingWithOutstandingPenalty()
            : Tax::whereIn('status', [TaxStatus::Pending, TaxStatus::Verified])->firstOrFail();

        $this->actingAs($petugas);

        $component = Livewire::test(BuatStpd::class)
            ->set('searchKeyword', $tax->billing_code)
            ->call('cariBilling')
            ->assertSet('selectedTaxId', $tax->id);

        if ($tipe === 'sanksi_saja') {
            $component
                ->assertSet('tipeStpd', 'sanksi_saja')
                ->call('buatStpd');
        } else {
            $component
                ->set('tipeStpd', 'pokok_sanksi')
                ->set('proyeksiTanggalBayar', now()->addDays(10)->format('Y-m-d'))
                ->call('hitungProyeksi')
                ->call('buatStpd');
        }

        return StpdManual::where('tax_id', $tax->id)->firstOrFail();
    }

    private function createPaidBillingWithOutstandingPenalty(): Tax
    {
        $taxObject = TaxObject::query()->first();

        if (!$taxObject) {
            $taxObject = $this->createTaxObjectFixture($this->createApprovedWajibPajakFixture());
        }

        $tax = $this->createTaxFixture($taxObject, overrides: [
            'status' => TaxStatus::Paid,
            'paid_at' => now(),
            'payment_expired_at' => now()->subDays(45),
            'masa_pajak_bulan' => 11,
            'masa_pajak_tahun' => 2025,
        ]);

        TaxPayment::create([
            'tax_id' => $tax->id,
            'external_ref' => 'TEST-STPD-SANKSI-' . Str::random(6),
            'amount_paid' => (float) $tax->amount,
            'principal_paid' => (float) $tax->amount,
            'penalty_paid' => 0,
            'payment_channel' => 'MANUAL',
            'paid_at' => now(),
            'description' => 'Pembayaran pokok penuh tanpa sanksi untuk uji STPD manual sanksi saja',
            'raw_response' => ['note' => 'Generated in TaxStpdWorkflowTest'],
        ]);

        return $tax->fresh();
    }

    private function createPaidQuarterBillingWithSanksi(int $bulan): Tax
    {
        $wajibPajak = WajibPajak::where('status', 'disetujui')->firstOrFail();
        $user = User::findOrFail($wajibPajak->user_id);
        $jenisPajak = JenisPajak::where('kode', '41102')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();

        $taxObject = TaxObject::where('npwpd', $wajibPajak->npwpd)
            ->where('jenis_pajak_id', $jenisPajak->id)
            ->where('is_opd', false)
            ->where('is_insidentil', false)
            ->firstOrFail();

        $tax = new Tax();
        $tax->fill([
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'tax_object_id' => $taxObject->id,
            'user_id' => $user->id,
            'amount' => 1000000 + ($bulan * 10000),
            'omzet' => 10000000 + ($bulan * 100000),
            'sanksi' => 100000,
            'tarif_persentase' => 10,
            'status' => TaxStatus::Pending,
            'billing_code' => Tax::generateBillingCode($jenisPajak->kode),
            'payment_expired_at' => now()->addDays(30),
            'masa_pajak_bulan' => $bulan,
            'masa_pajak_tahun' => 2030,
            'pembetulan_ke' => 0,
            'billing_sequence' => 0,
        ]);
        $tax->saveQuietly();

        TaxPayment::create([
            'tax_id' => $tax->id,
            'external_ref' => 'TEST-STPD-' . $bulan . '-' . Str::random(6),
            'amount_paid' => (float) $tax->amount + (float) $tax->sanksi,
            'principal_paid' => (float) $tax->amount,
            'penalty_paid' => (float) $tax->sanksi,
            'payment_channel' => 'BJATIM',
            'paid_at' => now(),
            'description' => 'Test pembayaran penuh untuk auto STPD',
            'raw_response' => ['note' => 'Generated in TaxStpdWorkflowTest'],
        ]);

        $tax->status = TaxStatus::Paid;
        $tax->paid_at = now();
        $tax->payment_channel = 'BJATIM';
        $tax->save();

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