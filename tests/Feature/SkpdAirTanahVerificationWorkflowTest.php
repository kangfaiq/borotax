<?php

namespace Tests\Feature;

use App\Domain\AirTanah\Models\MeterReport;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\Tax;
use App\Enums\TaxStatus;
use App\Filament\Resources\SkpdAirTanahResource\Pages\ListSkpdAirTanahs;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SkpdAirTanahVerificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('verificationRoleProvider')]
    public function test_admin_and_verifikator_can_approve_draft_skpd_air_tanah_and_create_tax_billing(string $role): void
    {
        $draft = $this->createDraftSkpdAirTanah();
        $verifikator = $this->createAdminPanelUser($role, Pimpinan::firstOrFail()->id);

        $this->actingAs($verifikator);

        Livewire::test(ListSkpdAirTanahs::class)
            ->assertCanSeeTableRecords([$draft])
            ->callTableAction('approve', $draft)
            ->assertHasNoTableActionErrors();

        $draft->refresh();
        $meterReport = $draft->meterReport()->firstOrFail();
        $waterObject = WaterObject::findOrFail($draft->tax_object_id);
        $tax = Tax::where('skpd_number', $draft->nomor_skpd)->firstOrFail();

        $this->assertSame('disetujui', $draft->status);
        $this->assertNotNull($draft->nomor_skpd);
        $this->assertDoesNotMatchRegularExpression('/\(DRAFT\)$/', $draft->nomor_skpd);
        $this->assertNotNull($draft->kode_billing);
        $this->assertNotNull($draft->jatuh_tempo);
        $this->assertSame($verifikator->id, $draft->verifikator_id);
        $this->assertSame($verifikator->nama_lengkap, $draft->verifikator_nama);
        $this->assertSame(Pimpinan::firstOrFail()->id, $draft->pimpinan_id);

        $this->assertSame('approved', $meterReport->status);
        $this->assertEquals((float) $draft->meter_reading_after, (float) $waterObject->last_meter_reading);
        $this->assertSame($draft->nama_objek, $waterObject->nama_objek_pajak);
        $this->assertSame($draft->alamat_objek, $waterObject->alamat_objek);

        $this->assertSame($draft->kode_billing, $tax->billing_code);
        $this->assertSame($draft->nomor_skpd, $tax->skpd_number);
        $this->assertSame(TaxStatus::Verified, $tax->status);
        $this->assertSame($meterReport->user_id, $tax->user_id);
        $this->assertEquals((float) $draft->jumlah_pajak, (float) $tax->amount);
        $this->assertEquals((float) $draft->meter_reading_after, (float) $tax->meter_reading);
        $this->assertEquals((float) $draft->meter_reading_before, (float) $tax->previous_meter_reading);
    }

    #[DataProvider('verificationRoleProvider')]
    public function test_admin_and_verifikator_can_reject_draft_skpd_air_tanah_and_mark_meter_report_rejected(string $role): void
    {
        $draft = $this->createDraftSkpdAirTanah();
        $verifikator = $this->createAdminPanelUser($role, Pimpinan::firstOrFail()->id);
        $catatan = 'Data pembacaan meter belum valid untuk diterbitkan.';

        $this->actingAs($verifikator);

        Livewire::test(ListSkpdAirTanahs::class)
            ->assertCanSeeTableRecords([$draft])
            ->callTableAction('reject', $draft, [
                'catatan_verifikasi' => $catatan,
            ])
            ->assertHasNoTableActionErrors();

        $draft->refresh();
        $meterReport = $draft->meterReport()->firstOrFail();

        $this->assertSame('ditolak', $draft->status);
        $this->assertSame($catatan, $draft->catatan_verifikasi);
        $this->assertSame($verifikator->id, $draft->verifikator_id);
        $this->assertSame($verifikator->nama_lengkap, $draft->verifikator_nama);
        $this->assertSame('rejected', $meterReport->status);

        $this->assertDatabaseMissing('taxes', [
            'skpd_number' => $draft->nomor_skpd,
        ]);
    }

    private function createDraftSkpdAirTanah(): SkpdAirTanah
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $this->seedPimpinanReferences();

        $jenisPajak = JenisPajak::where('kode', '41108')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->first();
        $petugas = $this->createAdminPanelUser('petugas');
        $wajibPajakUser = User::create([
            'name' => 'Portal Air User',
            'email' => sprintf('portal-air-%s@example.test', Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => '3522011234567890',
            'nama_lengkap' => 'Portal Air User',
            'alamat' => 'Jl. Diponegoro No. 9',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        $waterObject = WaterObject::create([
            'nik' => '3522011234567890',
            'nama_objek_pajak' => 'Sumur Produksi Utama',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak?->id,
            'jenis_sumber' => 'sumurBor',
            'npwpd' => 'P100000000010',
            'nopd' => 2001,
            'alamat_objek' => 'Jl. Gajah Mada No. 5',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'last_meter_reading' => 100,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'tarif_persen' => 20,
            'kelompok_pemakaian' => '1',
            'kriteria_sda' => '1',
            'uses_meter' => true,
        ]);

        $meterReport = MeterReport::create([
            'tax_object_id' => $waterObject->id,
            'user_id' => $wajibPajakUser->id,
            'user_nik' => '3522011234567890',
            'user_name' => 'Portal Air User',
            'meter_reading_before' => 100,
            'meter_reading_after' => 130,
            'photo_url' => 'meter-reports/sample.jpg',
            'latitude' => '-7.1500000',
            'longitude' => '111.8800000',
            'location_verified' => true,
            'status' => 'processing',
            'reported_at' => now()->subDay(),
        ]);

        return SkpdAirTanah::create([
            'nomor_skpd' => SkpdAirTanah::generateNomorSkpd() . ' (DRAFT)',
            'meter_report_id' => $meterReport->id,
            'tax_object_id' => $waterObject->id,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak?->id,
            'nik_wajib_pajak' => '3522011234567890',
            'nama_wajib_pajak' => 'Portal Air User',
            'alamat_wajib_pajak' => 'Jl. Diponegoro No. 9',
            'nama_objek' => 'Sumur Produksi Utama',
            'alamat_objek' => 'Jl. Gajah Mada No. 5',
            'nopd' => '2001',
            'kecamatan' => 'Bojonegoro',
            'kelurahan' => 'Kadipaten',
            'meter_reading_before' => 100,
            'meter_reading_after' => 130,
            'usage' => 30,
            'periode_bulan' => '2026-02',
            'tarif_per_m3' => 1000,
            'dasar_pengenaan' => 30000,
            'tarif_persen' => 20,
            'jumlah_pajak' => 6000,
            'status' => 'draft',
            'tanggal_buat' => now()->subHours(2),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
            'dasar_hukum' => 'Peraturan Uji ABT',
        ]);
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