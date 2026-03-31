<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\HargaPatokanSarangWalet;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Enums\TaxStatus;
use Carbon\Carbon;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalSelfAssessmentBranchWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-03-30 10:00:00');

        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_portal_standard_self_assessment_generates_billing(): void
    {
        Storage::fake('local');

        $context = $this->createStandardPortalContext();

        $response = $this->actingAs($context['portalUser'])->post(route('portal.self-assessment.store'), [
            'tax_object_id' => $context['taxObject']->id,
            'omzet' => 2000000,
            'attachment' => UploadedFile::fake()->create('lampiran.pdf', 200, 'application/pdf'),
            'bulan' => 3,
            'tahun' => 2026,
        ]);

        $tax = Tax::query()->firstOrFail();

        $response->assertRedirect(route('portal.self-assessment.success', $tax->id));

        $this->assertSame($context['portalUser']->id, $tax->user_id);
        $this->assertSame($context['taxObject']->id, $tax->tax_object_id);
        $this->assertEquals(2000000.0, (float) $tax->omzet);
        $this->assertEquals(200000.0, (float) $tax->amount);
        $this->assertSame(3, $tax->masa_pajak_bulan);
        $this->assertSame(2026, $tax->masa_pajak_tahun);
        $this->assertSame(TaxStatus::Pending, $tax->status);
        $this->assertNotNull($tax->attachment_url);

        Storage::disk('local')->assertExists($tax->attachment_url);
    }

    public function test_portal_standard_multi_billing_requires_keterangan(): void
    {
        Storage::fake('local');

        $context = $this->createStandardPortalContext(isOpd: true, subJenisKode: 'PBJT_KATERING');

        $response = $this->actingAs($context['portalUser'])
            ->from(route('portal.self-assessment.create', $context['jenisPajak']->id))
            ->post(route('portal.self-assessment.store'), [
                'tax_object_id' => $context['taxObject']->id,
                'omzet' => 500000,
                'attachment' => UploadedFile::fake()->create('lampiran.pdf', 200, 'application/pdf'),
            ]);

        $response->assertRedirect(route('portal.self-assessment.create', $context['jenisPajak']->id));
        $response->assertSessionHasErrors('keterangan');
        $this->assertDatabaseCount('taxes', 0);
    }

    public function test_portal_sarang_walet_generates_billing_and_detail(): void
    {
        Storage::fake('local');

        $context = $this->createSarangWaletPortalContext();

        $response = $this->actingAs($context['portalUser'])->post(route('portal.self-assessment.store'), [
            'tax_object_id' => $context['taxObject']->id,
            'jenis_sarang_id' => $context['hargaPatokan']->id,
            'volume_kg' => 2.5,
            'attachment' => UploadedFile::fake()->create('lampiran.pdf', 200, 'application/pdf'),
            'tahun' => 2026,
        ]);

        $tax = Tax::with('sarangWaletDetail')->firstOrFail();

        $response->assertRedirect(route('portal.self-assessment.success', $tax->id));

        $this->assertSame($context['portalUser']->id, $tax->user_id);
        $this->assertSame($context['taxObject']->id, $tax->tax_object_id);
        $this->assertNull($tax->masa_pajak_bulan);
        $this->assertSame(2026, $tax->masa_pajak_tahun);
        $this->assertEquals(15000000.0, (float) $tax->omzet);
        $this->assertEquals(1500000.0, (float) $tax->amount);
        $this->assertSame(TaxStatus::Pending, $tax->status);
        $this->assertNotNull($tax->sarangWaletDetail);
        $this->assertSame($context['hargaPatokan']->id, $tax->sarangWaletDetail->harga_patokan_sarang_walet_id);
        $this->assertEquals(2.5, (float) $tax->sarangWaletDetail->volume_kg);
        $this->assertEquals(6000000.0, (float) $tax->sarangWaletDetail->harga_patokan);
        $this->assertEquals(15000000.0, (float) $tax->sarangWaletDetail->subtotal_dpp);

        Storage::disk('local')->assertExists($tax->attachment_url);
    }

    public function test_portal_sarang_walet_prefills_next_year_from_last_active_billing(): void
    {
        Storage::fake('local');

        $context = $this->createSarangWaletPortalContext();

        $existingTax = Tax::create([
            'jenis_pajak_id' => $context['jenisPajak']->id,
            'sub_jenis_pajak_id' => $context['subJenisPajak']->id,
            'tax_object_id' => $context['taxObject']->id,
            'user_id' => $context['portalUser']->id,
            'amount' => 1000000,
            'omzet' => 10000000,
            'tarif_persentase' => (float) $context['taxObject']->tarif_persen,
            'status' => TaxStatus::Pending,
            'billing_code' => Tax::generateBillingCode($context['jenisPajak']->kode),
            'payment_expired_at' => now()->addDays(7),
            'masa_pajak_tahun' => 2026,
        ]);

        $response = $this->actingAs($context['portalUser'])->post(route('portal.self-assessment.store'), [
            'tax_object_id' => $context['taxObject']->id,
            'jenis_sarang_id' => $context['hargaPatokan']->id,
            'volume_kg' => 1.25,
            'attachment' => UploadedFile::fake()->create('lampiran.pdf', 200, 'application/pdf'),
        ]);

        $tax = Tax::query()
            ->where('tax_object_id', $context['taxObject']->id)
            ->where('id', '!=', $existingTax->id)
            ->firstOrFail();

        $response->assertRedirect(route('portal.self-assessment.success', $tax->id));
        $this->assertNull($tax->masa_pajak_bulan);
        $this->assertSame(2027, $tax->masa_pajak_tahun);
    }

    private function createStandardPortalContext(bool $isOpd = false, ?string $subJenisKode = null): array
    {
        $portalUser = $this->createPortalUser('portal-standard');
        $jenisPajak = JenisPajak::where('kode', $isOpd ? '41102' : '41101')->firstOrFail();
        $subJenisPajak = $subJenisKode
            ? SubJenisPajak::where('kode', $subJenisKode)->firstOrFail()
            : SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->orderBy('urutan')->firstOrFail();

        $taxObject = TaxObject::create([
            'nik' => $portalUser->nik,
            'nama_objek_pajak' => 'Objek Standar ' . $subJenisPajak->nama,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000211',
            'nopd' => $isOpd ? 7202 : 7201,
            'alamat_objek' => 'Jl. Standar Raya No. 12',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => $isOpd,
            'is_insidentil' => false,
            'tarif_persen' => (float) ($subJenisPajak->tarif_persen ?? 10),
        ]);

        return compact('portalUser', 'jenisPajak', 'subJenisPajak', 'taxObject');
    }

    private function createSarangWaletPortalContext(): array
    {
        $portalUser = $this->createPortalUser('portal-walet');
        $jenisPajak = JenisPajak::where('kode', '41109')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->orderBy('urutan')->firstOrFail();

        $taxObject = TaxObject::create([
            'nik' => $portalUser->nik,
            'nama_objek_pajak' => 'Rumah Walet Sekarjati',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000311',
            'nopd' => 7301,
            'alamat_objek' => 'Jl. Walet Makmur No. 8',
            'kelurahan' => 'Sukorejo',
            'kecamatan' => 'Bojonegoro',
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'is_insidentil' => false,
            'tarif_persen' => 10,
        ]);

        $hargaPatokan = HargaPatokanSarangWalet::create([
            'nama_jenis' => 'Sarang Walet Mangkuk',
            'harga_patokan' => 6000000,
            'satuan' => 'kg',
            'dasar_hukum' => 'Peraturan Harga Patokan Sarang Walet',
            'berlaku_mulai' => now()->startOfYear(),
            'berlaku_sampai' => null,
            'is_active' => true,
        ]);

        return compact('portalUser', 'jenisPajak', 'subJenisPajak', 'taxObject', 'hargaPatokan');
    }

    private function createPortalUser(string $prefix): User
    {
        return User::create([
            'name' => 'Portal Workflow User',
            'nama_lengkap' => 'Portal Workflow User',
            'email' => sprintf('%s-%s@example.test', $prefix, Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => (string) random_int(3522011000000000, 3522019999999999),
            'alamat' => 'Jl. Pengujian No. 1',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);
    }
}