<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxAssessmentLetter;
use App\Domain\Tax\Services\TaxAssessmentLetterService;
use App\Enums\TaxAssessmentLetterStatus;
use App\Enums\TaxAssessmentLetterType;
use App\Enums\TaxAssessmentReason;
use App\Enums\TaxStatus;
use App\Filament\Resources\TaxAssessmentLetterResource\Pages\CreateTaxAssessmentLetter;
use App\Filament\Resources\TaxAssessmentLetterResource\Pages\ListTaxAssessmentLetters;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use InvalidArgumentException;

class TaxAssessmentLetterWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_can_create_skpdkb_draft_from_filament_form(): void
    {
        $this->seedTaxReferences();

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $sourceTax = $this->createTaxFixture($taxObject, User::findOrFail($wajibPajak->user_id), [
            'payment_expired_at' => now()->subMonths(4),
        ]);
        $petugas = $this->createAdminPanelUser('petugas');

        $this->actingAs($petugas);

        Livewire::test(CreateTaxAssessmentLetter::class)
            ->fillForm([
                'source_tax_id' => $sourceTax->id,
                'letter_type' => TaxAssessmentLetterType::SKPDKB->value,
                'issuance_reason' => TaxAssessmentReason::JabatanTidakKooperatif->value,
                'issue_date' => now()->toDateString(),
                'base_amount' => 1_000_000,
                'interest_months' => 2,
                'notes' => 'Hasil pemeriksaan lapangan dan klarifikasi dokumen.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $letter = TaxAssessmentLetter::query()->firstOrFail();

        $this->assertSame(TaxAssessmentLetterStatus::Draft, $letter->status);
        $this->assertSame(TaxAssessmentLetterType::SKPDKB, $letter->letter_type);
        $this->assertSame(TaxAssessmentReason::JabatanTidakKooperatif, $letter->issuance_reason);
        $this->assertSame($sourceTax->id, $letter->source_tax_id);
        $this->assertNull($letter->document_number);
        $this->assertEquals(2.20, (float) $letter->interest_rate);
        $this->assertSame(2, $letter->interest_months);
        $this->assertEquals(44_000, (float) $letter->interest_amount);
        $this->assertEquals(50.00, (float) $letter->surcharge_rate);
        $this->assertEquals(500_000, (float) $letter->surcharge_amount);
        $this->assertEquals(1_544_000, (float) $letter->total_assessment);
        $this->assertSame($petugas->id, $letter->created_by);
    }

    #[DataProvider('verificationRoleProvider')]
    public function test_admin_and_verifikator_can_approve_draft_and_generate_billing(string $role): void
    {
        $this->seedTaxReferences();
        $this->seedPimpinanReferences();

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $sourceTax = $this->createTaxFixture(
            $this->createTaxObjectFixture($wajibPajak, '41102'),
            User::findOrFail($wajibPajak->user_id),
        );
        $creator = $this->createAdminPanelUser('petugas');
        $verifier = $this->createAdminPanelUser($role, true);

        $letter = TaxAssessmentLetter::create(
            app(TaxAssessmentLetterService::class)->prepareDraftPayload([
                'source_tax_id' => $sourceTax->id,
                'letter_type' => TaxAssessmentLetterType::SKPDKB->value,
                'issuance_reason' => TaxAssessmentReason::Pemeriksaan->value,
                'issue_date' => now()->toDateString(),
                'base_amount' => 750_000,
                'interest_months' => 3,
                'notes' => 'Uji approval ketetapan.',
            ], $creator)
        );

        $this->actingAs($verifier);

        Livewire::test(ListTaxAssessmentLetters::class)
            ->assertCanSeeTableRecords([$letter])
            ->callTableAction('approve', $letter, [
                'verification_notes' => 'Nilai ketetapan sesuai hasil verifikasi.',
            ])
            ->assertHasNoTableActionErrors();

        $letter->refresh();
        $generatedTax = Tax::findOrFail($letter->generated_tax_id);

        $this->assertSame(TaxAssessmentLetterStatus::Disetujui, $letter->status);
        $this->assertNotNull($letter->document_number);
        $this->assertSame($verifier->id, $letter->verified_by);
        $this->assertSame('Nilai ketetapan sesuai hasil verifikasi.', $letter->verification_notes);
        $this->assertNotNull($letter->verified_at);
        $this->assertSame($sourceTax->jenis_pajak_id, $generatedTax->jenis_pajak_id);
        $this->assertSame($sourceTax->tax_object_id, $generatedTax->tax_object_id);
        $this->assertSame($sourceTax->user_id, $generatedTax->user_id);
        $this->assertEquals((float) $letter->total_assessment, (float) $generatedTax->amount);
        $this->assertSame($letter->document_number, $generatedTax->skpd_number);
        $this->assertSame(TaxStatus::Pending, $generatedTax->status);
        $this->assertSame('19', substr($generatedTax->billing_code, -2));
        $this->assertSame(substr($sourceTax->billing_code, 0, 7), substr($generatedTax->billing_code, 0, 7));
    }

    public function test_skpdkbt_generated_billing_ends_with_19(): void
    {
        $this->seedTaxReferences();
        $this->seedPimpinanReferences();

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $sourceTax = $this->createTaxFixture(
            $this->createTaxObjectFixture($wajibPajak, '41102'),
            User::findOrFail($wajibPajak->user_id),
        );
        $creator = $this->createAdminPanelUser('petugas');
        $verifier = $this->createAdminPanelUser('verifikator', true);

        $letter = TaxAssessmentLetter::create(
            app(TaxAssessmentLetterService::class)->prepareDraftPayload([
                'source_tax_id' => $sourceTax->id,
                'letter_type' => TaxAssessmentLetterType::SKPDKBT->value,
                'issuance_reason' => TaxAssessmentReason::DataBaru->value,
                'issue_date' => now()->toDateString(),
                'base_amount' => 500_000,
                'interest_months' => 0,
                'notes' => 'Uji billing SKPDKBT.',
            ], $creator)
        );

        app(TaxAssessmentLetterService::class)->approve($letter, $verifier, 'Disetujui untuk billing 19.');

        $letter->refresh();
        $generatedTax = Tax::findOrFail($letter->generated_tax_id);

        $this->assertSame(TaxAssessmentLetterType::SKPDKBT, $letter->letter_type);
        $this->assertSame('19', substr($generatedTax->billing_code, -2));
        $this->assertSame(substr($sourceTax->billing_code, 0, 7), substr($generatedTax->billing_code, 0, 7));
    }

    public function test_petugas_can_allocate_skpdlb_credit_to_another_open_billing(): void
    {
        $this->seedTaxReferences();
        $this->seedPimpinanReferences();

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $portalUser = User::findOrFail($wajibPajak->user_id);
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
        $sourceTax = $this->createTaxFixture($taxObject, $portalUser, [
            'amount' => 1_000_000,
            'sanksi' => 0,
        ]);
        $targetTax = $this->createTaxFixture($taxObject, $portalUser, [
            'amount' => 700_000,
            'sanksi' => 100_000,
            'masa_pajak_bulan' => 2,
        ]);
        $creator = $this->createAdminPanelUser('petugas');
        $verifier = $this->createAdminPanelUser('verifikator', true);

        $letter = TaxAssessmentLetter::create(
            app(TaxAssessmentLetterService::class)->prepareDraftPayload([
                'source_tax_id' => $sourceTax->id,
                'letter_type' => TaxAssessmentLetterType::SKPDLB->value,
                'issuance_reason' => TaxAssessmentReason::LebihBayar->value,
                'issue_date' => now()->toDateString(),
                'base_amount' => 900_000,
                'interest_months' => 0,
                'notes' => 'Kredit lebih bayar untuk dikompensasikan.',
            ], $creator)
        );

        app(TaxAssessmentLetterService::class)->approve($letter, $verifier, 'Disetujui untuk kompensasi.');

        $this->actingAs($creator);

        Livewire::test(ListTaxAssessmentLetters::class)
            ->assertCanSeeTableRecords([$letter])
            ->callTableAction('allocate_credit', $letter, [
                'target_billing_code' => $targetTax->billing_code,
                'allocation_amount' => 300_000,
            ])
            ->assertHasNoTableActionErrors();

        $letter->refresh();
        $targetTax->refresh();

        $this->assertDatabaseCount('tax_assessment_compensations', 1);
        $this->assertDatabaseCount('tax_payments', 1);
        $this->assertEquals(600_000, (float) $letter->available_credit);
        $this->assertSame(TaxStatus::PartiallyPaid, $targetTax->status);
        $this->assertEquals(300_000, $targetTax->getTotalPaid());
    }

    public function test_approved_letter_document_is_accessible_to_owner_but_hidden_for_other_users(): void
    {
        $this->seedTaxReferences();
        $this->seedPimpinanReferences();

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $portalUser = User::findOrFail($wajibPajak->user_id);
        $sourceTax = $this->createTaxFixture($this->createTaxObjectFixture($wajibPajak, '41102'), $portalUser);
        $creator = $this->createAdminPanelUser('petugas');
        $verifier = $this->createAdminPanelUser('verifikator', true);

        $letter = TaxAssessmentLetter::create(
            app(TaxAssessmentLetterService::class)->prepareDraftPayload([
                'source_tax_id' => $sourceTax->id,
                'letter_type' => TaxAssessmentLetterType::SKPDKB->value,
                'issuance_reason' => TaxAssessmentReason::Pemeriksaan->value,
                'issue_date' => now()->toDateString(),
                'base_amount' => 650_000,
                'interest_months' => 1,
            ], $creator)
        );

        app(TaxAssessmentLetterService::class)->approve($letter, $verifier, 'Dokumen siap diunduh.');

        $response = $this->actingAs($portalUser)->get(route('tax-assessment-letters.show', $letter->id));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type', ''));

        $otherPortalUser = $this->createPortalUserFixture();

        $this->actingAs($otherPortalUser)
            ->get(route('tax-assessment-letters.show', $letter->id))
            ->assertNotFound();
    }

    public function test_document_creator_cannot_review_own_tax_assessment_letter_draft(): void
    {
        $this->seedTaxReferences();
        $this->seedPimpinanReferences();

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $sourceTax = $this->createTaxFixture(
            $this->createTaxObjectFixture($wajibPajak, '41102'),
            User::findOrFail($wajibPajak->user_id),
        );
        $creator = $this->createAdminPanelUser('admin', true);

        $letter = TaxAssessmentLetter::create(
            app(TaxAssessmentLetterService::class)->prepareDraftPayload([
                'source_tax_id' => $sourceTax->id,
                'letter_type' => TaxAssessmentLetterType::SKPDKB->value,
                'issuance_reason' => TaxAssessmentReason::Pemeriksaan->value,
                'issue_date' => now()->toDateString(),
                'base_amount' => 750_000,
                'interest_months' => 2,
            ], $creator)
        );

        $this->actingAs($creator);

        $this->assertFalse($creator->can('review', $letter));

        Livewire::test(ListTaxAssessmentLetters::class)
            ->assertCanSeeTableRecords([$letter])
            ->assertTableActionHidden('approve', $letter)
            ->assertTableActionHidden('reject', $letter);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Dokumen tidak dapat diverifikasi oleh pembuat draft yang sama.');

        app(TaxAssessmentLetterService::class)->approve($letter, $creator, 'Tidak boleh lolos.');
    }

    public static function verificationRoleProvider(): array
    {
        return [
            'admin' => ['admin'],
            'verifikator' => ['verifikator'],
        ];
    }

    private function seedTaxReferences(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);
    }

    private function createAdminPanelUser(string $role, bool $withPimpinan = false): User
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
            'pimpinan_id' => $withPimpinan ? \App\Domain\Master\Models\Pimpinan::firstOrFail()->id : null,
        ]);
    }
}