<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Reklame\Models\ReklameRequest;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Shared\Models\ActivityLog;
use App\Domain\Tax\Models\Tax;
use App\Enums\TaxStatus;
use App\Filament\Resources\SkpdReklameResource\Pages\ListSkpdReklames;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SkpdReklameVerificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('verificationRoleProvider')]
    public function test_admin_and_verifikator_can_approve_draft_skpd_reklame_and_create_tax_billing(string $role): void
    {
        $draft = $this->createDraftSkpdReklame();
        $verifikator = $this->createAdminPanelUser($role, Pimpinan::firstOrFail()->id);
        $draftNomorSkpd = $draft->nomor_skpd;
        $reklameObjectBeforeApproval = ReklameObject::findOrFail($draft->tax_object_id);
        $oldNamaObjek = $reklameObjectBeforeApproval->nama_objek_pajak;
        $oldAlamatObjek = $reklameObjectBeforeApproval->alamat_objek;
        $oldKelompokLokasi = $reklameObjectBeforeApproval->kelompok_lokasi;

        $this->actingAs($verifikator);

        Livewire::test(ListSkpdReklames::class)
            ->assertCanSeeTableRecords([$draft])
            ->callTableAction('approve', $draft)
            ->assertHasNoTableActionErrors();

        $draft->refresh();
        $request = $draft->reklameRequest()->firstOrFail();
        $reklameObject = ReklameObject::findOrFail($draft->tax_object_id);
        $tax = Tax::where('skpd_number', $draft->nomor_skpd)->firstOrFail();

        $this->assertSame('disetujui', $draft->status);
        $this->assertNotNull($draft->nomor_skpd);
        $this->assertDoesNotMatchRegularExpression('/\(DRAFT\)$/', $draft->nomor_skpd);
        $this->assertNotNull($draft->kode_billing);
        $this->assertNotNull($draft->jatuh_tempo);
        $this->assertSame($verifikator->id, $draft->verifikator_id);
        $this->assertSame($verifikator->nama_lengkap, $draft->verifikator_nama);
        $this->assertSame(Pimpinan::firstOrFail()->id, $draft->pimpinan_id);

        $this->assertSame('disetujui', $request->status);
        $this->assertNotNull($request->tanggal_selesai);

        $this->assertSame($draft->nama_reklame, $reklameObject->nama_objek_pajak);
        $this->assertSame($draft->alamat_reklame, $reklameObject->alamat_objek);
        $this->assertSame($draft->sub_jenis_pajak_id, $reklameObject->sub_jenis_pajak_id);
        $this->assertSame($draft->kelompok_lokasi, $reklameObject->kelompok_lokasi);
        $this->assertEquals((float) $draft->panjang, (float) $reklameObject->panjang);
        $this->assertEquals((float) $draft->lebar, (float) $reklameObject->lebar);
        $this->assertEquals((float) $draft->luas_m2, (float) $reklameObject->luas_m2);
        $this->assertSame($draft->jumlah_muka, $reklameObject->jumlah_muka);

        $this->assertSame($draft->kode_billing, $tax->billing_code);
        $this->assertSame($draft->nomor_skpd, $tax->skpd_number);
        $this->assertSame(TaxStatus::Verified, $tax->status);
        $this->assertSame($request->user_id, $tax->user_id);
        $this->assertEquals((float) $draft->jumlah_pajak, (float) $tax->amount);

        $activityLog = ActivityLog::query()
            ->where('action', 'UPDATE_TAX_OBJECT_FROM_SKPD_REKLAME_APPROVAL')
            ->where('target_table', 'tax_objects')
            ->where('target_id', $reklameObject->id)
            ->latest()
            ->first();

        $this->assertNotNull($activityLog);
        $this->assertSame($verifikator->id, $activityLog->actor_id);
        $this->assertSame($oldNamaObjek, $activityLog->old_values['nama_objek_pajak'] ?? null);
        $this->assertSame($draft->nama_reklame, $activityLog->new_values['nama_objek_pajak'] ?? null);
        $this->assertSame($oldAlamatObjek, $activityLog->old_values['alamat_objek'] ?? null);
        $this->assertSame($draft->alamat_reklame, $activityLog->new_values['alamat_objek'] ?? null);
        $this->assertSame($oldKelompokLokasi, $activityLog->old_values['kelompok_lokasi'] ?? null);
        $this->assertSame($draft->kelompok_lokasi, $activityLog->new_values['kelompok_lokasi'] ?? null);
        $this->assertStringContainsString('Nomor draft: ' . $draftNomorSkpd, (string) $activityLog->description);
        $this->assertStringContainsString('Nomor final: ' . $draft->nomor_skpd, (string) $activityLog->description);
        $this->assertStringContainsString('Request ID: ' . $draft->request_id, (string) $activityLog->description);
        $this->assertStringContainsString('Petugas draft: ' . $draft->petugas_nama, (string) $activityLog->description);
        $this->assertStringContainsString('Verifikator penyetuju: ' . $verifikator->nama_lengkap, (string) $activityLog->description);
    }

    #[DataProvider('verificationRoleProvider')]
    public function test_admin_and_verifikator_can_reject_draft_skpd_reklame_and_mark_request_rejected(string $role): void
    {
        $draft = $this->createDraftSkpdReklame();
        $verifikator = $this->createAdminPanelUser($role, Pimpinan::firstOrFail()->id);
        $catatan = 'Materi reklame belum memenuhi ketentuan verifikasi.';

        $this->actingAs($verifikator);

        Livewire::test(ListSkpdReklames::class)
            ->assertCanSeeTableRecords([$draft])
            ->callTableAction('reject', $draft, [
                'catatan_verifikasi' => $catatan,
            ])
            ->assertHasNoTableActionErrors();

        $draft->refresh();
        $request = $draft->reklameRequest()->firstOrFail();

        $this->assertSame('ditolak', $draft->status);
        $this->assertSame($catatan, $draft->catatan_verifikasi);
        $this->assertSame($verifikator->id, $draft->verifikator_id);
        $this->assertSame($verifikator->nama_lengkap, $draft->verifikator_nama);

        $this->assertSame('ditolak', $request->status);
        $this->assertStringContainsString($catatan, (string) $request->catatan_petugas);

        $this->assertDatabaseMissing('taxes', [
            'skpd_number' => $draft->nomor_skpd,
        ]);
    }

    public function test_document_creator_cannot_verify_own_skpd_reklame_draft(): void
    {
        $draft = $this->createDraftSkpdReklame();
        $admin = $this->createAdminPanelUser('admin', Pimpinan::firstOrFail()->id);

        $draft->update([
            'petugas_id' => $admin->id,
            'petugas_nama' => $admin->nama_lengkap,
        ]);

        $this->actingAs($admin);

        $this->assertFalse($admin->can('verify', $draft));

        Livewire::test(ListSkpdReklames::class)
            ->assertCanSeeTableRecords([$draft])
            ->assertTableActionHidden('approve', $draft)
            ->assertTableActionHidden('reject', $draft);
    }

    public function test_bulk_approve_skpd_reklame_syncs_objects_and_creates_activity_logs(): void
    {
        $firstDraft = $this->createDraftSkpdReklame();
        $secondDraft = $this->createDraftSkpdReklame([
            'nama_reklame' => 'Reklame Kedua Bulk',
            'alamat_reklame' => 'Jl. Panglima Sudirman No. 88',
            'kelompok_lokasi' => 'C',
            'panjang' => 6,
            'lebar' => 3,
            'luas_m2' => 18,
            'jumlah_muka' => 2,
        ], [
            'nama_objek_pajak' => 'Reklame Lama Bulk',
            'alamat_objek' => 'Jl. Diponegoro No. 12',
            'kelompok_lokasi' => 'B',
            'panjang' => 4,
            'lebar' => 2,
            'luas_m2' => 8,
            'jumlah_muka' => 1,
        ]);
        $verifikator = $this->createAdminPanelUser('admin', Pimpinan::firstOrFail()->id);

        $this->actingAs($verifikator);

        Livewire::test(ListSkpdReklames::class)
            ->assertCanSeeTableRecords([$firstDraft, $secondDraft])
            ->callTableBulkAction('bulk_approve', [$firstDraft, $secondDraft])
            ->assertHasNoErrors();

        $firstDraft->refresh();
        $secondDraft->refresh();

        $this->assertSame('disetujui', $firstDraft->status);
        $this->assertSame('disetujui', $secondDraft->status);

        $bulkLogs = ActivityLog::query()
            ->where('action', 'UPDATE_TAX_OBJECT_FROM_SKPD_REKLAME_APPROVAL')
            ->whereIn('target_id', [$firstDraft->tax_object_id, $secondDraft->tax_object_id])
            ->get();

        $this->assertCount(2, $bulkLogs);
        $this->assertTrue($bulkLogs->every(fn (ActivityLog $log) => $log->actor_id === $verifikator->id));
        $this->assertTrue($bulkLogs->contains(fn (ActivityLog $log) => str_contains((string) $log->description, 'Nomor final: ' . $firstDraft->nomor_skpd)));
        $this->assertTrue($bulkLogs->contains(fn (ActivityLog $log) => str_contains((string) $log->description, 'Nomor final: ' . $secondDraft->nomor_skpd)));
    }

    public function test_approve_skpd_reklame_does_not_create_object_sync_log_when_no_object_field_changes(): void
    {
        $draft = $this->createDraftSkpdReklame([], [
            'nama_objek_pajak' => 'Reklame Toko Sentosa',
            'alamat_objek' => 'Jl. MH Thamrin No. 20',
            'sub_jenis_pajak_id' => fn (SubJenisPajak $subJenisPajak) => $subJenisPajak->id,
            'kelompok_lokasi' => 'A',
            'bentuk' => 'persegi',
            'panjang' => 4,
            'lebar' => 2,
            'tinggi' => null,
            'sisi_atas' => null,
            'sisi_bawah' => null,
            'diameter' => null,
            'diameter2' => null,
            'alas' => null,
            'luas_m2' => 8,
            'jumlah_muka' => 1,
        ]);
        $verifikator = $this->createAdminPanelUser('verifikator', Pimpinan::firstOrFail()->id);

        $this->actingAs($verifikator);

        Livewire::test(ListSkpdReklames::class)
            ->callTableAction('approve', $draft)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('activity_logs', [
            'action' => 'UPDATE_TAX_OBJECT_FROM_SKPD_REKLAME_APPROVAL',
            'target_table' => 'tax_objects',
            'target_id' => $draft->tax_object_id,
        ]);
    }

    private function createDraftSkpdReklame(array $draftOverrides = [], array $objectOverrides = []): SkpdReklame
    {
        $this->ensureReferenceData();
        $this->seedPimpinanReferences();

        $jenisPajak = JenisPajak::where('kode', '41104')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();
        $petugas = $this->createAdminPanelUser('petugas');
        $token = Str::upper(Str::random(6));
        $nik = '3522' . str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        $npwpd = 'P' . str_pad((string) random_int(1, 999999999999), 12, '0', STR_PAD_LEFT);
        $nopd = random_int(1000, 9999);
        $wajibPajakUser = User::create([
            'name' => 'Portal Reklame User ' . $token,
            'email' => sprintf('portal-reklame-%s@example.test', Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => $nik,
            'nama_lengkap' => 'Portal Reklame User ' . $token,
            'alamat' => 'Jl. Teuku Umar No. 15',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        $reklameObject = ReklameObject::create(array_merge([
            'nik' => $nik,
            'nama_objek_pajak' => 'Reklame Lama ' . $token,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => $npwpd,
            'nopd' => $nopd,
            'alamat_objek' => 'Jl. Veteran No. 10',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tarif_persen' => 25,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'bentuk' => 'persegi',
            'panjang' => 3,
            'lebar' => 2,
            'jumlah_muka' => 1,
            'status' => 'aktif',
            'kelompok_lokasi' => 'B',
        ], $this->resolveCallableOverrides($objectOverrides, $subJenisPajak)));

        $request = ReklameRequest::create([
            'tax_object_id' => $reklameObject->id,
            'user_id' => $wajibPajakUser->id,
            'user_nik' => $nik,
            'user_name' => $wajibPajakUser->nama_lengkap,
            'tanggal_pengajuan' => now()->subDays(2),
            'durasi_perpanjangan_hari' => 30,
            'catatan_pengajuan' => 'Perpanjangan reklame bulanan.',
            'status' => 'diproses',
            'tanggal_diproses' => now()->subDay(),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
        ]);

        return SkpdReklame::create(array_merge([
            'nomor_skpd' => SkpdReklame::generateNomorSkpd() . ' (DRAFT)',
            'tax_object_id' => $reklameObject->id,
            'request_id' => $request->id,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => $npwpd,
            'nik_wajib_pajak' => $nik,
            'nama_wajib_pajak' => $wajibPajakUser->nama_lengkap,
            'alamat_wajib_pajak' => 'Jl. Teuku Umar No. 15',
            'nama_reklame' => 'Reklame Toko Sentosa',
            'jenis_reklame' => $subJenisPajak->nama,
            'alamat_reklame' => 'Jl. MH Thamrin No. 20',
            'kelompok_lokasi' => 'A',
            'bentuk' => 'persegi',
            'panjang' => 4,
            'lebar' => 2,
            'luas_m2' => 8,
            'jumlah_muka' => 1,
            'lokasi_penempatan' => 'luar_ruangan',
            'jenis_produk' => 'non_rokok',
            'jumlah_reklame' => 1,
            'satuan_waktu' => 'perBulan',
            'satuan_label' => 'per Bulan',
            'durasi' => 1,
            'tarif_pokok' => 100000,
            'nspr' => 0,
            'njopr' => 0,
            'penyesuaian_lokasi' => 1,
            'penyesuaian_produk' => 1,
            'nilai_strategis' => 0,
            'pokok_pajak_dasar' => 100000,
            'masa_berlaku_mulai' => now()->toDateString(),
            'masa_berlaku_sampai' => now()->addMonth()->toDateString(),
            'dasar_pengenaan' => 100000,
            'jumlah_pajak' => 100000,
            'status' => 'draft',
            'tanggal_buat' => now()->subHours(2),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
        ], $draftOverrides));
    }

    private function ensureReferenceData(): void
    {
        if (! JenisPajak::where('kode', '41104')->exists()) {
            $this->seed([
                JenisPajakSeeder::class,
                SubJenisPajakSeeder::class,
            ]);
        }
    }

    private function resolveCallableOverrides(array $attributes, SubJenisPajak $subJenisPajak): array
    {
        foreach ($attributes as $key => $value) {
            if ($value instanceof \Closure) {
                $attributes[$key] = $value($subJenisPajak);
            }
        }

        return $attributes;
    }

    public static function verificationRoleProvider(): array
    {
        return [
            'admin' => ['admin'],
            'verifikator' => ['verifikator'],
        ];
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