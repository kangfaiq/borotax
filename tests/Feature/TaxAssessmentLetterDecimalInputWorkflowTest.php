<?php

use App\Domain\Auth\Models\User;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxAssessmentLetter;
use App\Domain\Tax\Services\TaxAssessmentLetterService;
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

uses(RefreshDatabase::class);

it('creates tax assessment letter draft from comma decimal base amount input', function () {
    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
    ]);

    $wajibPajak = $this->createApprovedWajibPajakFixture();
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
    $sourceTax = $this->createTaxFixture($taxObject, User::findOrFail($wajibPajak->user_id), [
        'payment_expired_at' => now()->subMonths(4),
    ]);
    $petugas = createTaxAssessmentDecimalAdminFixture('petugas');

    $this->actingAs($petugas);

    Livewire::test(CreateTaxAssessmentLetter::class)
        ->fillForm([
            'source_tax_id' => $sourceTax->id,
            'letter_type' => TaxAssessmentLetterType::SKPDKB->value,
            'issuance_reason' => TaxAssessmentReason::JabatanTidakKooperatif->value,
            'issue_date' => now()->toDateString(),
            'base_amount' => '1.000.000,50',
            'interest_months' => 2,
            'notes' => 'Uji decimal surat ketetapan.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $letter = TaxAssessmentLetter::query()->firstOrFail();

    expect((float) $letter->base_amount)->toBe(1000000.5)
        ->and((float) $letter->interest_amount)->toBe(44000.02)
        ->and((float) $letter->surcharge_amount)->toBe(500000.25)
        ->and((float) $letter->total_assessment)->toBe(1544000.77);
});

it('allocates tax assessment credit from comma decimal input', function () {
    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
    ]);
    $this->seedPimpinanReferences();

    $wajibPajak = $this->createApprovedWajibPajakFixture();
    $portalUser = User::findOrFail($wajibPajak->user_id);
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');
    $sourceTax = $this->createTaxFixture($taxObject, $portalUser, [
        'amount' => 1000000,
        'sanksi' => 0,
    ]);
    $targetTax = $this->createTaxFixture($taxObject, $portalUser, [
        'amount' => 700000,
        'sanksi' => 100000,
        'masa_pajak_bulan' => 2,
        'billing_code' => Tax::generateBillingCode('41102'),
        'status' => TaxStatus::Pending,
    ]);
    $creator = createTaxAssessmentDecimalAdminFixture('petugas');
    $verifier = createTaxAssessmentDecimalAdminFixture('verifikator', true);

    $letter = TaxAssessmentLetter::create(
        app(TaxAssessmentLetterService::class)->prepareDraftPayload([
            'source_tax_id' => $sourceTax->id,
            'letter_type' => TaxAssessmentLetterType::SKPDLB->value,
            'issuance_reason' => TaxAssessmentReason::LebihBayar->value,
            'issue_date' => now()->toDateString(),
            'base_amount' => 900000,
            'interest_months' => 0,
            'notes' => 'Kredit lebih bayar untuk decimal allocation.',
        ], $creator)
    );

    app(TaxAssessmentLetterService::class)->approve($letter, $verifier, 'Disetujui untuk kompensasi decimal.');

    $this->actingAs($creator);

    Livewire::test(ListTaxAssessmentLetters::class)
        ->assertCanSeeTableRecords([$letter])
        ->callTableAction('allocate_credit', $letter, [
            'target_billing_code' => $targetTax->billing_code,
            'allocation_amount' => '300.000,50',
        ])
        ->assertHasNoTableActionErrors();

    $letter->refresh();
    $targetTax->refresh();

    expect((float) $letter->available_credit)->toBe(599999.5)
        ->and($targetTax->status)->toBe(TaxStatus::PartiallyPaid)
        ->and((float) $targetTax->getTotalPaid())->toBe(300000.5);
});

function createTaxAssessmentDecimalAdminFixture(string $role, bool $withPimpinan = false): User
{
    return User::create([
        'name' => Str::headline($role) . ' Decimal User',
        'nama_lengkap' => Str::headline($role) . ' Decimal User',
        'email' => sprintf('%s-decimal-%s@example.test', $role, Str::random(8)),
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
        'pimpinan_id' => $withPimpinan ? \App\Domain\Master\Models\Pimpinan::firstOrFail()->id : null,
    ]);
}