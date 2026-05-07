<?php

namespace Tests\Feature;

use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Filament\Resources\SkpdAirTanahResource;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SkpdAirTanahDetailViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_skpd_air_tanah_detail_shows_non_meter_object_information(): void
    {
        [$skpd, $portalUser] = $this->createSkpdAirTanahFixture(false);

        $this->actingAs($portalUser)
            ->get(route('portal.air-tanah.skpd-detail', $skpd->id))
            ->assertOk()
            ->assertSee('Jenis Objek Air Tanah')
            ->assertSee('Objek Non Meter Air')
            ->assertSee('Penggunaan Langsung')
            ->assertSee('Riwayat Verifikasi');
    }

    public function test_backoffice_skpd_air_tanah_view_shows_meter_object_information(): void
    {
        [$skpd] = $this->createSkpdAirTanahFixture(true);
        $admin = $this->createAdminPanelUser('admin');

        $this->actingAs($admin)
            ->get(SkpdAirTanahResource::getUrl('view', ['record' => $skpd]))
            ->assertOk()
            ->assertSee('Jenis Objek Air Tanah')
            ->assertSee('Objek Meter Air')
            ->assertSee('Meter Awal')
            ->assertSee('Meter Akhir')
            ->assertSee('Riwayat Verifikasi');
    }

    private function createSkpdAirTanahFixture(bool $usesMeter): array
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $this->seedPimpinanReferences();

        $jenisPajak = JenisPajak::where('kode', '41108')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->first();
        $petugas = $this->createAdminPanelUser('petugas');
        $portalUser = $this->createPortalUser('3522011234567890', 'Portal Air User');

        $waterObject = WaterObject::create([
            'nik' => '3522011234567890',
            'nama_objek_pajak' => $usesMeter ? 'Sumur Produksi Utama' : 'Sumber Air Non Meter',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak?->id,
            'jenis_sumber' => 'sumurBor',
            'npwpd' => 'P100000000010',
            'nopd' => $usesMeter ? 2001 : 2002,
            'alamat_objek' => 'Jl. Gajah Mada No. 5',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'last_meter_reading' => $usesMeter ? 130 : null,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'tarif_persen' => 20,
            'kelompok_pemakaian' => '1',
            'kriteria_sda' => '1',
            'uses_meter' => $usesMeter,
        ]);

        $skpd = SkpdAirTanah::create([
            'nomor_skpd' => $usesMeter ? 'SKPD-ABT/2030/01/000011' : 'SKPD-ABT/2030/01/000012',
            'tax_object_id' => $waterObject->id,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak?->id,
            'nik_wajib_pajak' => '3522011234567890',
            'nama_wajib_pajak' => 'Portal Air User',
            'alamat_wajib_pajak' => 'Jl. Diponegoro No. 9',
            'nama_objek' => $usesMeter ? 'Sumur Produksi Utama' : 'Sumber Air Non Meter',
            'alamat_objek' => 'Jl. Gajah Mada No. 5',
            'nopd' => $usesMeter ? '2001' : '2002',
            'kecamatan' => 'Bojonegoro',
            'kelurahan' => 'Kadipaten',
            'meter_reading_before' => $usesMeter ? 100 : 0,
            'meter_reading_after' => $usesMeter ? 130 : 0,
            'usage' => 30,
            'periode_bulan' => '2030-01',
            'jatuh_tempo' => now()->addMonth(),
            'tarif_per_m3' => json_encode([
                ['min_vol' => 0, 'max_vol' => 100, 'npa' => 1000],
            ]),
            'dasar_pengenaan' => 30000,
            'tarif_persen' => 20,
            'jumlah_pajak' => 6000,
            'status' => 'disetujui',
            'tanggal_buat' => now()->subHours(2),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
            'pimpinan_id' => Pimpinan::firstOrFail()->id,
            'kode_billing' => $usesMeter ? '352210800030000011' : '352210800030000012',
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