<?php

use App\Domain\AirTanah\Models\MeterReport;
use App\Domain\AirTanah\Models\NpaAirTanah;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Filament\Pages\DaftarSkpdSaya;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('revises rejected reklame skpd from comma decimal input', function () {
    $this->seedReklameTaxReferences();

    $petugas = createDaftarSkpdSayaDecimalPetugasFixture();
    $jenisPajak = JenisPajak::where('kode', '41104')->firstOrFail();
    $subJenisPajak = SubJenisPajak::where('kode', 'REKLAME_TETAP')->firstOrFail();
    $hargaPatokanReklame = HargaPatokanReklame::where('kode', 'RKL_NEON_BOX')->firstOrFail();

    $draft = SkpdReklame::create([
        'nomor_skpd' => SkpdReklame::generateNomorSkpd() . ' (DRAFT)',
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'harga_patokan_reklame_id' => $hargaPatokanReklame->id,
        'npwpd' => 'P100000000777',
        'nik_wajib_pajak' => '3522011234567888',
        'nama_wajib_pajak' => 'WP Reklame Decimal',
        'alamat_wajib_pajak' => 'Jl. Veteran No. 7',
        'nama_reklame' => 'Reklame Ditolak',
        'jenis_reklame' => $hargaPatokanReklame->nama,
        'alamat_reklame' => 'Jl. Panglima Sudirman No. 9',
        'kelompok_lokasi' => 'A',
        'bentuk' => 'persegi',
        'panjang' => 4,
        'lebar' => 2,
        'luas_m2' => 8,
        'jumlah_muka' => 1,
        'lokasi_penempatan' => 'luar_ruangan',
        'jenis_produk' => 'non_rokok',
        'jumlah_reklame' => 1,
        'satuan_waktu' => 'perTahun',
        'durasi' => 1,
        'tarif_pokok' => 120000,
        'nspr' => 0,
        'njopr' => 480000,
        'penyesuaian_lokasi' => 1,
        'penyesuaian_produk' => 1,
        'nilai_strategis' => 0,
        'pokok_pajak_dasar' => 960000,
        'masa_berlaku_mulai' => '2026-01-01',
        'masa_berlaku_sampai' => '2026-12-31',
        'dasar_pengenaan' => 960000,
        'jumlah_pajak' => 960000,
        'status' => 'ditolak',
        'tanggal_buat' => now()->subDay(),
        'petugas_id' => $petugas->id,
        'petugas_nama' => $petugas->nama_lengkap,
        'catatan_verifikasi' => 'Perlu revisi dimensi reklame.',
    ]);

    $this->actingAs($petugas);

    Livewire::test(DaftarSkpdSaya::class)
        ->set('jenisSkpd', 'reklame')
        ->callTableAction('revisi', $draft, [
            'nama_reklame' => 'Reklame Decimal Final',
            'alamat_reklame' => 'Jl. Panglima Sudirman No. 19',
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'kelompok_lokasi' => 'A',
            'bentuk' => 'persegi',
            'panjang' => '4,50',
            'lebar' => '2,25',
            'jumlah_muka' => 2,
            'satuan_waktu' => 'perTahun',
            'durasi' => 1,
            'jumlah_reklame' => 1,
            'lokasi_penempatan' => 'luar_ruangan',
            'jenis_produk' => 'non_rokok',
            'masa_berlaku_mulai' => '2026-02-01',
        ])
        ->assertHasNoTableActionErrors();

    $draft->refresh();

    expect($draft->nama_reklame)->toBe('Reklame Decimal Final')
        ->and((float) $draft->panjang)->toBe(4.5)
        ->and((float) $draft->lebar)->toBe(2.25)
        ->and((float) $draft->luas_m2)->toBe(10.13)
        ->and($draft->jumlah_muka)->toBe(2)
        ->and($draft->status)->toBe('draft')
        ->and($draft->catatan_verifikasi)->toBeNull();
});

it('revises rejected air tanah skpd from comma decimal input', function () {
    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
    ]);

    $petugas = createDaftarSkpdSayaDecimalPetugasFixture();
    $jenisPajak = JenisPajak::where('kode', '41108')->firstOrFail();
    $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->first();

    NpaAirTanah::create([
        'kelompok_pemakaian' => NpaAirTanah::resolveKelompok('1'),
        'kriteria_sda' => NpaAirTanah::resolveKriteria('1'),
        'npa_tiers' => [
            ['min_vol' => 0, 'max_vol' => 99999999, 'npa' => 1000],
        ],
        'berlaku_mulai' => '2026-01-01',
        'berlaku_sampai' => null,
        'dasar_hukum' => 'Pergub Uji ABT',
        'is_active' => true,
    ]);

    $wajibPajak = User::create([
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
        'nama_objek_pajak' => 'Sumur Produksi Decimal',
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak?->id,
        'jenis_sumber' => 'sumurBor',
        'npwpd' => 'P100000000021',
        'nopd' => 2021,
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
        'user_id' => $wajibPajak->id,
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

    $draft = SkpdAirTanah::create([
        'nomor_skpd' => SkpdAirTanah::generateNomorSkpd() . ' (DRAFT)',
        'meter_report_id' => $meterReport->id,
        'tax_object_id' => $waterObject->id,
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak?->id,
        'nik_wajib_pajak' => '3522011234567890',
        'nama_wajib_pajak' => 'Portal Air User',
        'alamat_wajib_pajak' => 'Jl. Diponegoro No. 9',
        'nama_objek' => 'Sumur Produksi Decimal',
        'alamat_objek' => 'Jl. Gajah Mada No. 5',
        'nopd' => '2021',
        'kecamatan' => 'Bojonegoro',
        'kelurahan' => 'Kadipaten',
        'meter_reading_before' => 100,
        'meter_reading_after' => 130,
        'usage' => 30,
        'periode_bulan' => '2026-02',
        'tarif_per_m3' => json_encode([
            ['min_vol' => 0, 'max_vol' => 99999999, 'npa' => 1000],
        ]),
        'dasar_pengenaan' => 30000,
        'tarif_persen' => 20,
        'jumlah_pajak' => 6000,
        'status' => 'ditolak',
        'tanggal_buat' => now()->subHours(2),
        'petugas_id' => $petugas->id,
        'petugas_nama' => $petugas->nama_lengkap,
        'catatan_verifikasi' => 'Revisi pembacaan meter.',
    ]);

    $this->actingAs($petugas);

    Livewire::test(DaftarSkpdSaya::class)
        ->set('jenisSkpd', 'air_tanah')
        ->callTableAction('revisi', $draft, [
            'nama_objek' => 'Sumur Produksi Decimal Final',
            'alamat_objek' => 'Jl. Gajah Mada No. 15',
            'kecamatan' => 'Bojonegoro',
            'kelurahan' => 'Kadipaten',
            'meter_reading_before' => '100,50',
            'meter_reading_after' => '130,75',
            'periode_bulan' => '2026-03',
        ])
        ->assertHasNoTableActionErrors();

    $draft->refresh();

    expect((float) $draft->meter_reading_before)->toBe(100.5)
        ->and((float) $draft->meter_reading_after)->toBe(130.75)
        ->and((float) $draft->usage)->toBe(30.25)
        ->and((float) $draft->dasar_pengenaan)->toBe(30250.0)
        ->and((float) $draft->jumlah_pajak)->toBe(6050.0)
        ->and($draft->status)->toBe('draft')
        ->and($draft->catatan_verifikasi)->toBeNull();
});

function createDaftarSkpdSayaDecimalPetugasFixture(): User
{
    return User::create([
        'name' => 'Petugas Decimal',
        'nama_lengkap' => 'Petugas Decimal',
        'email' => sprintf('petugas-decimal-%s@example.test', Str::random(8)),
        'password' => Hash::make('password'),
        'role' => 'petugas',
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}