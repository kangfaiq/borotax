<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\HargaPatokanMblb;
use App\Domain\Tax\Models\PortalMblbSubmission;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\Tax\Services\PortalMblbSubmissionService;
use App\Enums\TaxStatus;
use App\Filament\Resources\PortalMblbSubmissionResource\Pages\ListPortalMblbSubmissions;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PortalMblbSubmissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_user_can_submit_mblb_and_image_is_compressed_before_review(): void
    {
        Storage::fake('public');

        $context = $this->createPortalMblbContext();

        $response = $this->actingAs($context['portalUser'])->post(route('portal.self-assessment.store'), [
            'tax_object_id' => $context['taxObject']->id,
            'attachment' => UploadedFile::fake()->image('lampiran.jpg', 2400, 1800)->size(2500),
            'volumes' => [
                $context['minerals'][0]->id => '3.50',
                $context['minerals'][1]->id => '0',
            ],
            'bulan' => now()->month,
            'tahun' => now()->year,
        ]);

        $submission = PortalMblbSubmission::query()->firstOrFail();

        $response->assertRedirect(route('portal.self-assessment.submission-success', $submission->id));

        $this->assertSame('pending', $submission->status);
        $this->assertNull($submission->approved_tax_id);
        $this->assertSame($context['portalUser']->id, $submission->user_id);
        $this->assertSame($context['taxObject']->id, $submission->tax_object_id);
        $this->assertSame(1, count($submission->detail_items ?? []));
        $this->assertSame('Batu Andesit', $submission->detail_items[0]['jenis_mblb']);
        $this->assertSame('portal-mblb-submissions/attachments', dirname($submission->attachment_path));
        $this->assertTrue(str_ends_with($submission->attachment_path, '.webp'));
        $this->assertDatabaseCount('taxes', 0);

        Storage::disk('public')->assertExists($submission->attachment_path);
        $this->assertLessThanOrEqual(1024 * 1024, Storage::disk('public')->size($submission->attachment_path));
    }

    #[DataProvider('verificationRoleProvider')]
    public function test_admin_and_verifikator_can_approve_pending_portal_mblb_submission(string $role): void
    {
        Storage::fake('public');

        $context = $this->createPortalMblbContext();
        $submission = $this->createPendingSubmission($context['portalUser'], $context['taxObject'], $context['minerals']);
        $verifier = $this->createAdminPanelUser($role);

        $this->actingAs($verifier);

        Livewire::test(ListPortalMblbSubmissions::class)
            ->assertCanSeeTableRecords([$submission])
            ->callTableAction('approve', $submission, [
                'review_notes' => 'Dokumen dan volume valid.',
            ])
            ->assertHasNoTableActionErrors();

        $submission->refresh();
        $tax = Tax::with('mblbDetails')->findOrFail($submission->approved_tax_id);

        $this->assertSame('approved', $submission->status);
        $this->assertSame($verifier->id, $submission->processed_by);
        $this->assertSame('Dokumen dan volume valid.', $submission->review_notes);
        $this->assertNotNull($tax->billing_code);
        $this->assertSame(TaxStatus::Pending, $tax->status);
        $this->assertSame($submission->attachment_path, $tax->attachment_url);
        $this->assertEquals((float) $submission->pokok_pajak, (float) $tax->amount);
        $this->assertEquals((float) $submission->total_dpp, (float) $tax->omzet);
        $this->assertEquals((float) $submission->opsen, (float) $tax->opsen);
        $this->assertCount(1, $tax->mblbDetails);
        $this->assertSame('Batu Andesit', $tax->mblbDetails->first()->jenis_mblb);
    }

    #[DataProvider('verificationRoleProvider')]
    public function test_admin_and_verifikator_can_reject_pending_portal_mblb_submission(string $role): void
    {
        Storage::fake('public');

        $context = $this->createPortalMblbContext();
        $submission = $this->createPendingSubmission($context['portalUser'], $context['taxObject'], $context['minerals']);
        $verifier = $this->createAdminPanelUser($role);

        $this->actingAs($verifier);

        Livewire::test(ListPortalMblbSubmissions::class)
            ->assertCanSeeTableRecords([$submission])
            ->callTableAction('reject', $submission, [
                'rejection_reason' => 'Lampiran belum cukup jelas untuk diverifikasi.',
            ])
            ->assertHasNoTableActionErrors();

        $submission->refresh();

        $this->assertSame('rejected', $submission->status);
        $this->assertSame($verifier->id, $submission->processed_by);
        $this->assertSame('Lampiran belum cukup jelas untuk diverifikasi.', $submission->rejection_reason);
        $this->assertNull($submission->approved_tax_id);
        $this->assertDatabaseCount('taxes', 0);
    }

    public static function verificationRoleProvider(): array
    {
        return [
            'admin' => ['admin'],
            'verifikator' => ['verifikator'],
        ];
    }

    private function createPendingSubmission(User $portalUser, TaxObject $taxObject, array $minerals): PortalMblbSubmission
    {
        return app(PortalMblbSubmissionService::class)->createSubmission(
            $portalUser,
            $taxObject,
            now()->month,
            now()->year,
            [
                $minerals[0]->id => '2.25',
                $minerals[1]->id => '0',
            ],
            UploadedFile::fake()->image('verifikasi.jpg', 2200, 1600)->size(1800),
            null,
        );
    }

    private function createPortalMblbContext(): array
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $portalUser = User::create([
            'name' => 'Portal MBLB User',
            'nama_lengkap' => 'Portal MBLB User',
            'email' => sprintf('portal-mblb-%s@example.test', Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => '3522011234567890',
            'alamat' => 'Jl. Veteran No. 12',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        $jenisPajak = JenisPajak::where('kode', '41106')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('kode', 'MBLB_WP')->firstOrFail();

        $taxObject = TaxObject::create([
            'nik' => '3522011234567890',
            'nama_objek_pajak' => 'Tambang Desa Sumberrejo',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000099',
            'nopd' => 6101,
            'alamat_objek' => 'Jl. Tambang Raya KM 3',
            'kelurahan' => 'Sumberrejo',
            'kecamatan' => 'Bojonegoro',
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'is_insidentil' => false,
            'tarif_persen' => 20,
        ]);

        $minerals = [
            HargaPatokanMblb::create([
                'sub_jenis_pajak_id' => $subJenisPajak->id,
                'nama_mineral' => 'Batu Andesit',
                'harga_patokan' => 100000,
                'satuan' => 'm3',
                'dasar_hukum' => 'Peraturan Harga Patokan MBLB',
                'is_active' => true,
            ]),
            HargaPatokanMblb::create([
                'sub_jenis_pajak_id' => $subJenisPajak->id,
                'nama_mineral' => 'Pasir Urug',
                'harga_patokan' => 50000,
                'satuan' => 'm3',
                'dasar_hukum' => 'Peraturan Harga Patokan MBLB',
                'is_active' => true,
            ]),
        ];

        return compact('portalUser', 'taxObject', 'minerals');
    }

    private function createAdminPanelUser(string $role): User
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
        ]);
    }
}