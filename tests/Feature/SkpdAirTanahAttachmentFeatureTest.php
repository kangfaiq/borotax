<?php

namespace Tests\Feature;

use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Filament\Pages\BuatSkpdAirTanah;
use App\Filament\Resources\SkpdAirTanahResource;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class SkpdAirTanahAttachmentFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_can_create_draft_skpd_air_tanah_with_optional_attachment(): void
    {
        Storage::fake('public');

        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $jenisPajak = JenisPajak::where('kode', '41108')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->first();
        $petugas = $this->createAdminPanelUser('petugas');

        $waterObject = WaterObject::create([
            'nik' => '3522011234567890',
            'nama_objek_pajak' => 'Sumber Air Non Meter',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak?->id,
            'jenis_sumber' => 'sumurBor',
            'npwpd' => 'P100000000010',
            'nopd' => 2002,
            'alamat_objek' => 'Jl. Gajah Mada No. 5',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'last_meter_reading' => null,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'tarif_persen' => 20,
            'kelompok_pemakaian' => '1',
            'kriteria_sda' => '1',
            'uses_meter' => false,
        ]);

        $this->actingAs($petugas);

        Livewire::test(BuatSkpdAirTanah::class)
            ->set('selectedWaterObjectId', $waterObject->id)
            ->set('selectedWaterObjectData', [
                'id' => $waterObject->id,
                'nama' => 'Sumber Air Non Meter',
                'alamat' => 'Jl. Gajah Mada No. 5',
                'npwpd' => 'P100000000010',
                'nopd' => 2002,
                'nik_hash' => $waterObject->nik_hash,
                'jenis_pajak_id' => $jenisPajak->id,
                'last_meter_reading' => null,
                'uses_meter' => false,
                'kelurahan' => 'Kadipaten',
                'kecamatan' => 'Bojonegoro',
            ])
            ->set('wajibPajakData', [
                'nik' => '3522011234567890',
                'nama_lengkap' => 'Portal Air User',
                'alamat' => 'Jl. Diponegoro No. 9',
                'npwpd' => 'P100000000010',
            ])
            ->set('usesMeter', false)
            ->set('periodeBulan', '2030-01')
            ->set('directUsage', 30)
            ->set('tarifTiers', [
                ['min_vol' => 0, 'max_vol' => 100, 'npa' => 1000],
            ])
            ->set('tarifPersen', 20)
            ->set('lampiranUploadTemp', UploadedFile::fake()->image('meter.jpg', 1400, 900)->size(500))
            ->call('buatSkpd')
            ->assertHasNoErrors();

        $skpd = SkpdAirTanah::firstOrFail();

        $this->assertNotNull($skpd->lampiran_path);
        Storage::disk('public')->assertExists($skpd->lampiran_path);
    }

    public function test_portal_and_backoffice_detail_show_attachment_link_when_available(): void
    {
        Storage::fake('public');

        [$skpd, $portalUser] = $this->createSkpdAirTanahFixtureWithAttachment();
        $admin = $this->createAdminPanelUser('admin');

        $this->actingAs($portalUser)
            ->get(route('portal.air-tanah.skpd-detail', $skpd->id))
            ->assertOk()
            ->assertSee('Lampiran Pendukung')
            ->assertSee('Lihat lampiran pendukung');

        $this->actingAs($admin)
            ->get(SkpdAirTanahResource::getUrl('view', ['record' => $skpd]))
            ->assertOk()
            ->assertSee('Dokumen Pendukung')
            ->assertSee('Lihat Lampiran');
    }

    public function test_portal_list_and_backoffice_index_show_attachment_access_when_available(): void
    {
        Storage::fake('public');

        [$skpd, $portalUser] = $this->createSkpdAirTanahFixtureWithAttachment();
        $admin = $this->createAdminPanelUser('admin');

        $this->actingAs($portalUser)
            ->get(route('portal.air-tanah.skpd-list', ['tab' => 'proses']))
            ->assertOk()
            ->assertSee('Lihat Lampiran');

        $this->actingAs($admin)
            ->get(SkpdAirTanahResource::getUrl('index'))
            ->assertOk()
            ->assertSee((string) $skpd->nomor_skpd)
            ->assertSee('Ada Lampiran');
    }

    private function createSkpdAirTanahFixtureWithAttachment(): array
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $this->seedPimpinanReferences();

        Storage::disk('public')->put('skpd-air-tanah/lampiran/2030/01/lampiran-air-tanah.pdf', 'dummy-pdf-content');

        $jenisPajak = JenisPajak::where('kode', '41108')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->first();
        $petugas = $this->createAdminPanelUser('petugas');
        $portalUser = $this->createPortalUser('3522011234567890', 'Portal Air User');

        $waterObject = WaterObject::create([
            'nik' => '3522011234567890',
            'nama_objek_pajak' => 'Sumber Air Non Meter',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak?->id,
            'jenis_sumber' => 'sumurBor',
            'npwpd' => 'P100000000010',
            'nopd' => 2002,
            'alamat_objek' => 'Jl. Gajah Mada No. 5',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'last_meter_reading' => null,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'tarif_persen' => 20,
            'kelompok_pemakaian' => '1',
            'kriteria_sda' => '1',
            'uses_meter' => false,
        ]);

        $skpd = SkpdAirTanah::create([
            'nomor_skpd' => 'SKPD-ABT/2030/01/000013',
            'tax_object_id' => $waterObject->id,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak?->id,
            'nik_wajib_pajak' => '3522011234567890',
            'nama_wajib_pajak' => 'Portal Air User',
            'alamat_wajib_pajak' => 'Jl. Diponegoro No. 9',
            'nama_objek' => 'Sumber Air Non Meter',
            'alamat_objek' => 'Jl. Gajah Mada No. 5',
            'nopd' => '2002',
            'kecamatan' => 'Bojonegoro',
            'kelurahan' => 'Kadipaten',
            'meter_reading_before' => 0,
            'meter_reading_after' => 0,
            'usage' => 30,
            'periode_bulan' => '2030-01',
            'jatuh_tempo' => now()->addMonth(),
            'tarif_per_m3' => json_encode([
                ['min_vol' => 0, 'max_vol' => 100, 'npa' => 1000],
            ]),
            'dasar_pengenaan' => 30000,
            'tarif_persen' => 20,
            'jumlah_pajak' => 6000,
            'status' => 'draft',
            'tanggal_buat' => now()->subHours(2),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
            'pimpinan_id' => Pimpinan::firstOrFail()->id,
            'lampiran_path' => 'skpd-air-tanah/lampiran/2030/01/lampiran-air-tanah.pdf',
            'dasar_hukum' => 'Peraturan Uji ABT',
        ]);

        return [$skpd, $portalUser];
    }

    private function createPortalUser(string $nik, string $name): User
    {
        return User::create([
            'name' => $name,
            'nama_lengkap' => $name,
            'email' => sprintf('%s-%s@example.test', str()->slug($name), Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => $nik,
            'alamat' => 'Jl. Diponegoro No. 9',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);
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