<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\HargaSatuanListrik;
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

class PortalPpjWorkflowTest extends TestCase
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

    public function test_portal_ppj_sumber_lain_requires_pokok_pajak(): void
    {
        Storage::fake('local');

        $context = $this->createPortalPpjContext('PPJ_SUMBER_LAIN');

        $response = $this->actingAs($context['portalUser'])->from(route('portal.self-assessment.create', $context['jenisPajak']->id))
            ->post(route('portal.self-assessment.store'), [
                'tax_object_id' => $context['taxObject']->id,
                'attachment' => UploadedFile::fake()->create('lampiran.pdf', 200, 'application/pdf'),
                'bulan' => 3,
                'tahun' => 2026,
            ]);

        $response->assertRedirect(route('portal.self-assessment.create', $context['jenisPajak']->id));
        $response->assertSessionHasErrors('pokok_pajak');
        $this->assertDatabaseCount('taxes', 0);
    }

    public function test_portal_ppj_sumber_lain_generates_billing_and_back_calculates_dpp(): void
    {
        Storage::fake('local');

        $context = $this->createPortalPpjContext('PPJ_SUMBER_LAIN');

        $response = $this->actingAs($context['portalUser'])->post(route('portal.self-assessment.store'), [
            'tax_object_id' => $context['taxObject']->id,
            'pokok_pajak' => 150000,
            'attachment' => UploadedFile::fake()->create('lampiran.pdf', 200, 'application/pdf'),
            'bulan' => 3,
            'tahun' => 2026,
        ]);

        $tax = Tax::query()->firstOrFail();

        $response->assertRedirect(route('portal.self-assessment.success', $tax->id));

        $this->assertSame($context['portalUser']->id, $tax->user_id);
        $this->assertSame($context['taxObject']->id, $tax->tax_object_id);
        $this->assertEquals(150000.0, (float) $tax->amount);
        $this->assertEquals(1500000.0, (float) $tax->omzet);
        $this->assertSame(TaxStatus::Pending, $tax->status);
        $this->assertSame(3, $tax->masa_pajak_bulan);
        $this->assertSame(2026, $tax->masa_pajak_tahun);
        $this->assertNull($tax->ppjDetail);
        $this->assertNotNull($tax->attachment_url);

        Storage::disk('local')->assertExists($tax->attachment_url);
    }

    public function test_portal_ppj_non_pln_requires_component_fields(): void
    {
        Storage::fake('local');

        $context = $this->createPortalPpjContext('PPJ_DIHASILKAN_SENDIRI');

        $response = $this->actingAs($context['portalUser'])->from(route('portal.self-assessment.create', $context['jenisPajak']->id))
            ->post(route('portal.self-assessment.store'), [
                'tax_object_id' => $context['taxObject']->id,
                'kapasitas_kva' => 120,
                'attachment' => UploadedFile::fake()->create('lampiran.pdf', 200, 'application/pdf'),
                'bulan' => 3,
                'tahun' => 2026,
            ]);

        $response->assertRedirect(route('portal.self-assessment.create', $context['jenisPajak']->id));
        $response->assertSessionHasErrors([
            'tingkat_penggunaan_persen',
            'jangka_waktu_jam',
            'harga_satuan_listrik_id',
        ]);
        $this->assertDatabaseCount('taxes', 0);
    }

    public function test_portal_ppj_non_pln_generates_billing_and_detail(): void
    {
        Storage::fake('local');

        $context = $this->createPortalPpjContext('PPJ_DIHASILKAN_SENDIRI');

        $response = $this->actingAs($context['portalUser'])->post(route('portal.self-assessment.store'), [
            'tax_object_id' => $context['taxObject']->id,
            'kapasitas_kva' => 100,
            'tingkat_penggunaan_persen' => 80,
            'jangka_waktu_jam' => 10,
            'harga_satuan_listrik_id' => $context['hargaSatuanListrik']->id,
            'attachment' => UploadedFile::fake()->create('lampiran.pdf', 200, 'application/pdf'),
            'bulan' => 3,
            'tahun' => 2026,
        ]);

        $tax = Tax::with('ppjDetail')->firstOrFail();

        $response->assertRedirect(route('portal.self-assessment.success', $tax->id));

        $this->assertEquals(1200000.0, (float) $tax->omzet);
        $this->assertEquals(18000.0, (float) $tax->amount);
        $this->assertSame(TaxStatus::Pending, $tax->status);
        $this->assertNotNull($tax->ppjDetail);
        $this->assertSame($context['hargaSatuanListrik']->id, $tax->ppjDetail->harga_satuan_listrik_id);
        $this->assertEquals(100.0, (float) $tax->ppjDetail->kapasitas_kva);
        $this->assertEquals(80.0, (float) $tax->ppjDetail->tingkat_penggunaan_persen);
        $this->assertEquals(10.0, (float) $tax->ppjDetail->jangka_waktu_jam);
        $this->assertEquals(1500.0, (float) $tax->ppjDetail->harga_satuan);
        $this->assertEquals(1200000.0, (float) $tax->ppjDetail->njtl);
        $this->assertEquals(1200000.0, (float) $tax->ppjDetail->subtotal_dpp);

        Storage::disk('local')->assertExists($tax->attachment_url);
    }

    public function test_portal_ppj_prefills_next_period_from_last_active_billing(): void
    {
        Storage::fake('local');

        $context = $this->createPortalPpjContext('PPJ_SUMBER_LAIN');

        $existingTax = Tax::create([
            'jenis_pajak_id' => $context['jenisPajak']->id,
            'sub_jenis_pajak_id' => $context['subJenisPajak']->id,
            'tax_object_id' => $context['taxObject']->id,
            'user_id' => $context['portalUser']->id,
            'amount' => 100000,
            'omzet' => 1000000,
            'tarif_persentase' => (float) $context['taxObject']->tarif_persen,
            'status' => TaxStatus::Pending,
            'billing_code' => Tax::generateBillingCode($context['jenisPajak']->kode),
            'payment_expired_at' => Tax::hitungJatuhTempoSelfAssessment(2, 2026),
            'masa_pajak_bulan' => 2,
            'masa_pajak_tahun' => 2026,
        ]);

        $response = $this->actingAs($context['portalUser'])->post(route('portal.self-assessment.store'), [
            'tax_object_id' => $context['taxObject']->id,
            'pokok_pajak' => 90000,
            'attachment' => UploadedFile::fake()->create('lampiran.pdf', 200, 'application/pdf'),
        ]);

        $tax = Tax::query()
            ->where('tax_object_id', $context['taxObject']->id)
            ->where('id', '!=', $existingTax->id)
            ->firstOrFail();

        $response->assertRedirect(route('portal.self-assessment.success', $tax->id));
        $this->assertSame(3, $tax->masa_pajak_bulan);
        $this->assertSame(2026, $tax->masa_pajak_tahun);
    }

    private function createPortalPpjContext(string $subJenisKode): array
    {
        $portalUser = User::create([
            'name' => 'Portal PPJ User',
            'nama_lengkap' => 'Portal PPJ User',
            'email' => sprintf('portal-ppj-%s@example.test', Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => '3522012234567890',
            'alamat' => 'Jl. Pemuda No. 45',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        $jenisPajak = JenisPajak::where('kode', '41105')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('kode', $subJenisKode)->firstOrFail();

        $taxObject = TaxObject::create([
            'nik' => '3522012234567890',
            'nama_objek_pajak' => 'Objek PPJ ' . $subJenisPajak->nama,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000155',
            'nopd' => 7101,
            'alamat_objek' => 'Jl. PPJ Raya No. 7',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'is_insidentil' => false,
            'tarif_persen' => (float) $subJenisPajak->tarif_persen,
        ]);

        $hargaSatuanListrik = HargaSatuanListrik::create([
            'nama_wilayah' => 'Kabupaten Bojonegoro',
            'harga_per_kwh' => 1500,
            'dasar_hukum' => 'Peraturan Harga Satuan Listrik',
            'berlaku_mulai' => now()->startOfYear(),
            'berlaku_sampai' => null,
            'is_active' => true,
        ]);

        return compact('portalUser', 'jenisPajak', 'subJenisPajak', 'taxObject', 'hargaSatuanListrik');
    }
}