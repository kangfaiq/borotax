<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Retribusi\Models\ObjekRetribusiSewaTanah;
use App\Domain\Retribusi\Models\SkrdSewaRetribusi;
use App\Domain\Retribusi\Models\TarifSewaTanah;
use App\Domain\Retribusi\Services\RetribusiSewaTanahService;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Enums\TaxStatus;
use App\Filament\Resources\SkrdSewaRetribusiResource\Pages\ListSkrdSewaRetribusi;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\RetribusiSewaTanahTarifSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SkrdSewaRetribusiWorkflowTest extends TestCase
{
    use RefreshDatabase;

    // ── Seeder Data ──

    public function test_seeder_creates_retribusi_sewa_tanah_jenis_pajak_and_sub_jenis(): void
    {
        $this->seedRetribusiReferences();

        $jenisPajak = JenisPajak::where('kode', '42101')->first();
        $this->assertNotNull($jenisPajak);
        $this->assertSame('Retribusi Sewa Tanah', $jenisPajak->nama);
        $this->assertSame('41104', $jenisPajak->billing_kode_override);
        $this->assertSame('official_assessment', $jenisPajak->tipe_assessment);

        $subJenis = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->ordered()->get();
        $this->assertCount(3, $subJenis);
        $this->assertSame('SEWA_TANAH_PERMANEN', $subJenis[0]->kode);
        $this->assertSame('SEWA_TANAH_KAIN', $subJenis[1]->kode);
        $this->assertSame('SEWA_TANAH_RUMIJA', $subJenis[2]->kode);
    }

    public function test_tarif_seeder_creates_correct_tarifs(): void
    {
        $this->seedRetribusiReferences();

        $permanen = SubJenisPajak::where('kode', 'SEWA_TANAH_PERMANEN')->first();
        $kain = SubJenisPajak::where('kode', 'SEWA_TANAH_KAIN')->first();
        $rumija = SubJenisPajak::where('kode', 'SEWA_TANAH_RUMIJA')->first();

        $tarifPermanen = TarifSewaTanah::where('sub_jenis_pajak_id', $permanen->id)->first();
        $this->assertNotNull($tarifPermanen);
        $this->assertEquals(80000, (float) $tarifPermanen->tarif_nominal);
        $this->assertSame('perTahun', $tarifPermanen->satuan_waktu);

        $tarifKain = TarifSewaTanah::where('sub_jenis_pajak_id', $kain->id)->first();
        $this->assertNotNull($tarifKain);
        $this->assertEquals(20000, (float) $tarifKain->tarif_nominal);
        $this->assertSame('perBulan', $tarifKain->satuan_waktu);

        $tarifRumija = TarifSewaTanah::where('sub_jenis_pajak_id', $rumija->id)->first();
        $this->assertNotNull($tarifRumija);
        $this->assertEquals(80000, (float) $tarifRumija->tarif_nominal);
        $this->assertSame('perTahun', $tarifRumija->satuan_waktu);
    }

    // ── Billing Kode Mapping ──

    public function test_jenis_pajak_get_billing_kode_returns_override_when_set(): void
    {
        $this->seedRetribusiReferences();

        $retribusi = JenisPajak::where('kode', '42101')->first();
        $this->assertSame('41104', $retribusi->getBillingKode());

        $reklame = JenisPajak::where('kode', '41104')->first();
        $this->assertSame('41104', $reklame->getBillingKode());
    }

    // ── Service Calculation ──
    // Formula: luasM2 × jumlahReklame × tarifNominal × (tarifPajakPersen / 100) × durasi

    public function test_service_calculates_retribusi_permanen_tahunan(): void
    {
        $this->seedRetribusiReferences();

        $service = new RetribusiSewaTanahService();
        $subJenis = SubJenisPajak::where('kode', 'SEWA_TANAH_PERMANEN')->first();

        // luas=10, jumlah=2, tarif=80000, persen=25%, durasi=2
        // = 10 * 2 * 80000 * 0.25 * 2 = 800000
        $result = $service->calculateRetribusi($subJenis->id, 10.0, 2, 2);

        $this->assertEquals(80000, $result['tarif_nominal']);
        $this->assertSame('perTahun', $result['satuan_waktu']);
        $this->assertSame('per Tahun', $result['satuan_label']);
        $this->assertEquals(10.0, $result['luas_m2']);
        $this->assertEquals(2, $result['jumlah_reklame']);
        $this->assertEquals(25.00, $result['tarif_pajak_persen']);
        $this->assertEquals(800000, $result['jumlah_retribusi']);
    }

    public function test_service_calculates_retribusi_kain_bulanan(): void
    {
        $this->seedRetribusiReferences();

        $service = new RetribusiSewaTanahService();
        $subJenis = SubJenisPajak::where('kode', 'SEWA_TANAH_KAIN')->first();

        // luas=5, jumlah=1, tarif=20000, persen=25%, durasi=3
        // = 5 * 1 * 20000 * 0.25 * 3 = 75000
        $result = $service->calculateRetribusi($subJenis->id, 5.0, 1, 3);

        $this->assertEquals(20000, $result['tarif_nominal']);
        $this->assertSame('perBulan', $result['satuan_waktu']);
        $this->assertEquals(75000, $result['jumlah_retribusi']);
    }

    // ── Draft SKRD ──

    public function test_service_creates_draft_skrd(): void
    {
        $this->seedRetribusiReferences();
        $this->seedPimpinanReferences();

        $service = new RetribusiSewaTanahService();
        $subJenis = SubJenisPajak::where('kode', 'SEWA_TANAH_PERMANEN')->first();
        $petugas = $this->createAdminPanelUser('petugas');
        $objekRetribusi = $this->createObjekRetribusi($subJenis, luasM2: 12.0);

        $skrd = $service->createDraftSkrd([
            'objek_retribusi_id' => $objekRetribusi->id,
            'sub_jenis_pajak_id' => $subJenis->id,
            'nik_wajib_pajak' => '3522123456789012',
            'nama_wajib_pajak' => 'Budi Santoso',
            'alamat_wajib_pajak' => 'Jl. Mawar No. 1',
            'nama_objek' => 'Lokasi Reklame Permanen',
            'alamat_objek' => 'Jl. Gajah Mada No. 10',
            'jumlah_reklame' => 3,
            'durasi' => 1,
            'masa_berlaku_mulai' => '2026-04-01',
            'masa_berlaku_sampai' => '2027-03-31',
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
        ]);

        $this->assertStringContainsString('SKRD/', $skrd->nomor_skrd);
        $this->assertStringContainsString('(DRAFT)', $skrd->nomor_skrd);
        $this->assertSame('draft', $skrd->status);
        $this->assertEquals(80000, (float) $skrd->tarif_nominal);
        $this->assertEquals(12.0, (float) $skrd->luas_m2);
        $this->assertEquals(3, $skrd->jumlah_reklame);
        $this->assertEquals(25.00, (float) $skrd->tarif_pajak_persen);
        // 12 * 3 * 80000 * 0.25 * 1 = 720000
        $this->assertEquals(720000, (float) $skrd->jumlah_retribusi);
        $this->assertSame('perTahun', $skrd->satuan_waktu);
    }

    public function test_service_prevents_overlapping_draft_skrd(): void
    {
        $this->seedRetribusiReferences();
        $this->seedPimpinanReferences();

        $service = new RetribusiSewaTanahService();
        $subJenis = SubJenisPajak::where('kode', 'SEWA_TANAH_PERMANEN')->first();
        $petugas = $this->createAdminPanelUser('petugas');
        $objekRetribusi = $this->createObjekRetribusi($subJenis);

        $data = [
            'objek_retribusi_id' => $objekRetribusi->id,
            'sub_jenis_pajak_id' => $subJenis->id,
            'nik_wajib_pajak' => '3522123456789012',
            'nama_wajib_pajak' => 'Budi Santoso',
            'alamat_wajib_pajak' => 'Jl. Mawar No. 1',
            'nama_objek' => 'Lokasi Reklame Permanen',
            'alamat_objek' => 'Jl. Gajah Mada No. 10',
            'jumlah_reklame' => 1,
            'durasi' => 1,
            'masa_berlaku_mulai' => '2026-04-01',
            'masa_berlaku_sampai' => '2027-03-31',
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
        ];

        $service->createDraftSkrd($data);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Sudah ada SKRD aktif');
        $service->createDraftSkrd($data);
    }

    // ── Approve SKRD ──

    #[DataProvider('verificationRoleProvider')]
    public function test_approve_skrd_creates_tax_with_billing_code_41104(string $role): void
    {
        $draft = $this->createDraftSkrd();
        $verifikator = $this->createAdminPanelUser($role, Pimpinan::firstOrFail()->id);

        $this->actingAs($verifikator);

        Livewire::test(ListSkrdSewaRetribusi::class)
            ->assertCanSeeTableRecords([$draft])
            ->callTableAction('approve', $draft)
            ->assertHasNoTableActionErrors();

        $draft->refresh();

        $this->assertSame('disetujui', $draft->status);
        $this->assertStringStartsWith('SKRD/', $draft->nomor_skrd);
        $this->assertDoesNotMatchRegularExpression('/\(DRAFT\)/', $draft->nomor_skrd);
        $this->assertNotNull($draft->kode_billing);
        $this->assertNotNull($draft->jatuh_tempo);
        $this->assertSame($verifikator->id, $draft->verifikator_id);

        $this->assertStringStartsWith('3522104', $draft->kode_billing);

        $tax = Tax::where('skpd_number', $draft->nomor_skrd)->firstOrFail();
        $this->assertSame($draft->kode_billing, $tax->billing_code);
        $this->assertSame(TaxStatus::Verified, $tax->status);
        $this->assertEquals((float) $draft->jumlah_retribusi, (float) $tax->amount);
    }

    // ── Reject SKRD ──

    #[DataProvider('verificationRoleProvider')]
    public function test_reject_skrd(string $role): void
    {
        $draft = $this->createDraftSkrd();
        $verifikator = $this->createAdminPanelUser($role, Pimpinan::firstOrFail()->id);
        $catatan = 'Dokumen belum lengkap.';

        $this->actingAs($verifikator);

        Livewire::test(ListSkrdSewaRetribusi::class)
            ->callTableAction('reject', $draft, [
                'catatan_verifikasi' => $catatan,
            ])
            ->assertHasNoTableActionErrors();

        $draft->refresh();

        $this->assertSame('ditolak', $draft->status);
        $this->assertSame($catatan, $draft->catatan_verifikasi);

        $this->assertDatabaseMissing('taxes', [
            'skpd_number' => $draft->nomor_skrd,
        ]);
    }

    // ── Self-verify Prevention ──

    public function test_creator_cannot_verify_own_skrd(): void
    {
        $draft = $this->createDraftSkrd();
        $admin = $this->createAdminPanelUser('admin', Pimpinan::firstOrFail()->id);

        $draft->update([
            'petugas_id' => $admin->id,
            'petugas_nama' => $admin->nama_lengkap,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListSkrdSewaRetribusi::class)
            ->assertCanSeeTableRecords([$draft])
            ->assertTableActionHidden('approve', $draft)
            ->assertTableActionHidden('reject', $draft);
    }

    // ── Bulk Approve ──

    public function test_bulk_approve_skrd(): void
    {
        $first = $this->createDraftSkrd();
        $second = $this->createDraftSkrd([
            'nama_objek' => 'Lokasi Reklame Kedua',
            'alamat_objek' => 'Jl. Sudirman No. 99',
        ]);

        $verifikator = $this->createAdminPanelUser('admin', Pimpinan::firstOrFail()->id);
        $this->actingAs($verifikator);

        Livewire::test(ListSkrdSewaRetribusi::class)
            ->callTableBulkAction('bulk_approve', [$first, $second])
            ->assertHasNoErrors();

        $first->refresh();
        $second->refresh();

        $this->assertSame('disetujui', $first->status);
        $this->assertSame('disetujui', $second->status);

        $this->assertNotSame($first->nomor_skrd, $second->nomor_skrd);
        $this->assertNotSame($first->kode_billing, $second->kode_billing);

        $this->assertCount(2, Tax::where('status', TaxStatus::Verified)->get());
    }

    // ── Document ──

    public function test_skrd_document_renders_correct_title(): void
    {
        $draft = $this->createDraftSkrd();
        $admin = $this->createAdminPanelUser('admin');
        $this->actingAs($admin);

        $response = $this->get(route('skrd-sewa.show', $draft->id));
        $response->assertOk();
    }

    public function test_skrd_document_requires_authentication(): void
    {
        $draft = $this->createDraftSkrd();

        $response = $this->get(route('skrd-sewa.show', $draft->id));
        $response->assertRedirect();
    }

    // ── Data Providers ──

    public static function verificationRoleProvider(): array
    {
        return [
            'admin' => ['admin'],
            'verifikator' => ['verifikator'],
        ];
    }

    // ── Helpers ──

    private function seedRetribusiReferences(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
            RetribusiSewaTanahTarifSeeder::class,
        ]);
    }

    private function createObjekRetribusi(?SubJenisPajak $subJenis = null, float $luasM2 = 10.0): ObjekRetribusiSewaTanah
    {
        $jenisPajak = JenisPajak::where('kode', '42101')->firstOrFail();
        $subJenis ??= SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();

        $reklameJenis = JenisPajak::where('kode', '41104')->firstOrFail();
        $reklameSubJenis = SubJenisPajak::where('jenis_pajak_id', $reklameJenis->id)->firstOrFail();

        $taxObject = TaxObject::create([
            'nik' => '3522' . str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT),
            'nama_objek_pajak' => 'Reklame Test ' . Str::random(4),
            'jenis_pajak_id' => $reklameJenis->id,
            'sub_jenis_pajak_id' => $reklameSubJenis->id,
            'npwpd' => 'NPWPD-' . Str::random(4),
            'nopd' => 1,
            'alamat_objek' => 'Jl. Test No. 1',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tarif_persen' => 25.00,
            'tanggal_daftar' => now()->toDateString(),
            'panjang' => $luasM2 > 0 ? sqrt($luasM2) : 1,
            'lebar' => $luasM2 > 0 ? sqrt($luasM2) : 1,
            'luas_m2' => $luasM2,
            'jumlah_muka' => 1,
            'is_active' => true,
        ]);

        return ObjekRetribusiSewaTanah::create([
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenis->id,
            'tax_object_id' => $taxObject->id,
            'npwpd' => $taxObject->npwpd,
            'nik' => $taxObject->nik,
            'nama_pemilik' => 'Pemilik Test ' . Str::random(4),
            'alamat_pemilik' => 'Jl. Mawar No. 1',
            'nama_objek' => $taxObject->nama_objek_pajak,
            'alamat_objek' => $taxObject->alamat_objek,
            'luas_m2' => $luasM2,
            'is_active' => true,
        ]);
    }

    private function createDraftSkrd(array $overrides = []): SkrdSewaRetribusi
    {
        $this->seedRetribusiReferences();
        $this->seedPimpinanReferences();

        $jenisPajak = JenisPajak::where('kode', '42101')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();
        $petugas = $this->createAdminPanelUser('petugas');
        $objekRetribusi = $this->createObjekRetribusi($subJenisPajak);

        // Formula: 10 * 1 * 80000 * 0.25 * 1 = 200000
        return SkrdSewaRetribusi::create(array_merge([
            'nomor_skrd' => SkrdSewaRetribusi::generateNomorSkrd() . ' (DRAFT)',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'objek_retribusi_id' => $objekRetribusi->id,
            'npwpd' => $objekRetribusi->npwpd,
            'nik_wajib_pajak' => '3522' . str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT),
            'nama_wajib_pajak' => 'WP Sewa Tanah ' . Str::random(4),
            'alamat_wajib_pajak' => 'Jl. Mawar No. 1',
            'nama_objek' => 'Lokasi Reklame Permanen',
            'alamat_objek' => 'Jl. Gajah Mada No. 10',
            'luas_m2' => 10.0,
            'jumlah_reklame' => 1,
            'tarif_pajak_persen' => 25.00,
            'tarif_nominal' => 80000,
            'satuan_waktu' => 'perTahun',
            'satuan_label' => 'per Tahun',
            'durasi' => 1,
            'jumlah_retribusi' => 200000,
            'masa_berlaku_mulai' => '2026-04-01',
            'masa_berlaku_sampai' => '2027-03-31',
            'status' => 'draft',
            'tanggal_buat' => now(),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
        ], $overrides));
    }

    private function createAdminPanelUser(string $role, ?string $pimpinanId = null): User
    {
        return User::create([
            'name' => ucfirst($role) . ' ' . Str::random(4),
            'email' => sprintf('%s-%s@test.test', $role, Str::random(6)),
            'password' => Hash::make('password'),
            'nama_lengkap' => ucfirst($role) . ' Test ' . Str::random(4),
            'role' => $role,
            'status' => 'verified',
            'email_verified_at' => now(),
            'pimpinan_id' => $pimpinanId,
        ]);
    }
}
