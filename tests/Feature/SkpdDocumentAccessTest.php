<?php

namespace Tests\Feature;

use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Reklame\Models\ReklameRequest;
use App\Domain\Reklame\Models\SkpdReklame;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class SkpdDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_skpd_air_tanah_document_routes_are_limited_to_owner_or_backoffice_roles(): void
    {
        $skpd = $this->createApprovedSkpdAirTanah();

        $this->actingAs($this->createPortalUser('3522019999999999', 'Portal User Lain'));
        $this->get(route('skpd-air-tanah.show', $skpd->id))->assertNotFound();

        $this->actingAs($skpd->meterReport()->firstOrFail()->user);
        $this->get(route('skpd-air-tanah.show', $skpd->id))->assertOk();

        $this->actingAs($this->createAdminPanelUser('admin'));
        $this->get(route('skpd-air-tanah.show', $skpd->id))->assertOk();
    }

    public function test_skpd_reklame_document_routes_are_limited_to_owner_or_backoffice_roles(): void
    {
        $skpd = $this->createApprovedSkpdReklame();

        $this->actingAs($this->createPortalUser('3522019999999999', 'Portal User Lain'));
        $this->get(route('skpd-reklame.show', $skpd->id))->assertNotFound();

        $this->actingAs($skpd->reklameRequest()->firstOrFail()->user);
        $this->get(route('skpd-reklame.show', $skpd->id))->assertOk();

        $this->actingAs($this->createAdminPanelUser('admin'));
        $this->get(route('skpd-reklame.show', $skpd->id))->assertOk();
    }

    public function test_signed_public_skpd_reklame_routes_allow_only_valid_signed_urls_for_public_records(): void
    {
        $skpd = $this->createPublicSkpdReklame('disetujui');

        $this->get(route('sewa-reklame.skpd.cetak', $skpd->id))->assertForbidden();
        $this->get(URL::signedRoute('sewa-reklame.skpd.cetak', ['skpdId' => $skpd->id]))->assertOk();
        $this->get(URL::signedRoute('sewa-reklame.skpd.unduh', ['skpdId' => $skpd->id]))->assertOk();
    }

    public function test_signed_public_skpd_reklame_routes_reject_non_public_records(): void
    {
        $nonSewaSkpd = $this->createApprovedSkpdReklame();
        $draftPublicSkpd = $this->createPublicSkpdReklame('draft');

        $this->get(URL::signedRoute('sewa-reklame.skpd.cetak', ['skpdId' => $nonSewaSkpd->id]))->assertNotFound();
        $this->get(URL::signedRoute('sewa-reklame.skpd.cetak', ['skpdId' => $draftPublicSkpd->id]))->assertNotFound();
    }

    public function test_permohonan_sewa_attachment_route_is_limited_to_owner_or_backoffice_roles(): void
    {
        $fixture = $this->createPermohonanSewaWithAttachment();

        $this->actingAs($this->createPortalUser('3522019999999999', 'Portal User Lain'));
        $this->get(route('permohonan-sewa.file', ['id' => $fixture['permohonan']->id, 'field' => 'file_ktp']))->assertNotFound();

        $this->actingAs($fixture['owner']);
        $this->get(route('permohonan-sewa.file', ['id' => $fixture['permohonan']->id, 'field' => 'file_ktp']))->assertOk();

        $this->actingAs($this->createAdminPanelUser('admin'));
        $this->get(route('permohonan-sewa.file', ['id' => $fixture['permohonan']->id, 'field' => 'file_ktp']))->assertOk();
    }

    public function test_permohonan_sewa_attachment_route_rejects_invalid_fields(): void
    {
        $fixture = $this->createPermohonanSewaWithAttachment();

        $this->actingAs($fixture['owner']);
        $this->get(route('permohonan-sewa.file', ['id' => $fixture['permohonan']->id, 'field' => 'file_rahasia']))->assertNotFound();
    }

    private function createApprovedSkpdAirTanah(): SkpdAirTanah
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
            'nama_objek_pajak' => 'Sumur Produksi Utama',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak?->id,
            'jenis_sumber' => 'sumurBor',
            'npwpd' => 'P100000000010',
            'nopd' => 2001,
            'alamat_objek' => 'Jl. Gajah Mada No. 5',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'last_meter_reading' => 130,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'tarif_persen' => 20,
            'kelompok_pemakaian' => '1',
            'kriteria_sda' => '1',
            'uses_meter' => true,
        ]);

        $meterReport = \App\Domain\AirTanah\Models\MeterReport::create([
            'tax_object_id' => $waterObject->id,
            'user_id' => $portalUser->id,
            'user_nik' => '3522011234567890',
            'user_name' => 'Portal Air User',
            'meter_reading_before' => 100,
            'meter_reading_after' => 130,
            'photo_url' => 'meter-reports/sample.jpg',
            'latitude' => '-7.1500000',
            'longitude' => '111.8800000',
            'location_verified' => true,
            'status' => 'approved',
            'reported_at' => now()->subDay(),
        ]);

        return SkpdAirTanah::create([
            'nomor_skpd' => 'SKPD-ABT/2030/01/000001',
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
            'kode_billing' => '352210800030000001',
            'dasar_hukum' => 'Peraturan Uji ABT',
        ]);
    }

    private function createApprovedSkpdReklame(): SkpdReklame
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $this->seedPimpinanReferences();

        $jenisPajak = JenisPajak::where('kode', '41104')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();
        $petugas = $this->createAdminPanelUser('petugas');
        $portalUser = $this->createPortalUser('3522011234567890', 'Portal Reklame User');

        $reklameObject = ReklameObject::create([
            'nik' => '3522011234567890',
            'nama_objek_pajak' => 'Reklame Toko Sentosa',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000001',
            'nopd' => 1001,
            'alamat_objek' => 'Jl. MH Thamrin No. 20',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tarif_persen' => 25,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'bentuk' => 'persegi',
            'panjang' => 4,
            'lebar' => 2,
            'jumlah_muka' => 1,
            'status' => 'aktif',
            'kelompok_lokasi' => 'A',
        ]);

        $request = ReklameRequest::create([
            'tax_object_id' => $reklameObject->id,
            'user_id' => $portalUser->id,
            'user_nik' => '3522011234567890',
            'user_name' => 'Portal Reklame User',
            'tanggal_pengajuan' => now()->subDays(2),
            'durasi_perpanjangan_hari' => 30,
            'catatan_pengajuan' => 'Perpanjangan reklame bulanan.',
            'status' => 'disetujui',
            'tanggal_diproses' => now()->subDay(),
            'tanggal_selesai' => now()->subHours(12),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
        ]);

        return SkpdReklame::create([
            'nomor_skpd' => 'SKPD-RKL/2030/01/000001',
            'tax_object_id' => $reklameObject->id,
            'request_id' => $request->id,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000001',
            'nik_wajib_pajak' => '3522011234567890',
            'nama_wajib_pajak' => 'Portal Reklame User',
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
            'jatuh_tempo' => now()->addMonth(),
            'dasar_pengenaan' => 100000,
            'jumlah_pajak' => 100000,
            'status' => 'disetujui',
            'tanggal_buat' => now()->subHours(2),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
            'pimpinan_id' => Pimpinan::firstOrFail()->id,
            'kode_billing' => '352210400030000001',
            'dasar_hukum' => 'Peraturan Uji Reklame',
        ]);
    }

    private function createPublicSkpdReklame(string $status): SkpdReklame
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $this->seedPimpinanReferences();

        $jenisPajak = JenisPajak::where('kode', '41104')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();
        $petugas = $this->createAdminPanelUser('petugas');
        $portalUser = $this->createPortalUser('3522011234567001', 'Portal Sewa Reklame User');

        $reklameObject = ReklameObject::create([
            'nik' => '3522011234567001',
            'nama_objek_pajak' => 'Reklame Aset Pemkab',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000099',
            'nopd' => 1099,
            'alamat_objek' => 'Jl. Veteran No. 12',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tarif_persen' => 25,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'bentuk' => 'persegi',
            'panjang' => 5,
            'lebar' => 3,
            'jumlah_muka' => 2,
            'status' => 'aktif',
            'kelompok_lokasi' => 'A',
        ]);

        $aset = AsetReklamePemkab::create([
            'kode_aset' => 'NB099',
            'nama' => 'Neon Box Pemkab Sudirman',
            'jenis' => 'neon_box',
            'lokasi' => 'Jl. Veteran No. 12',
            'keterangan' => 'Aset untuk uji signed URL',
            'kawasan' => 'Pusat Kota',
            'traffic' => 'Tinggi',
            'kelompok_lokasi' => 'A',
            'panjang' => 5,
            'lebar' => 3,
            'luas_m2' => 15,
            'jumlah_muka' => 2,
            'harga_sewa_per_bulan' => 150000,
            'status_ketersediaan' => 'tersedia',
            'is_active' => true,
        ]);

        $permohonan = PermohonanSewaReklame::create([
            'aset_reklame_pemkab_id' => $aset->id,
            'user_id' => $portalUser->id,
            'nik' => '3522011234567001',
            'nama' => 'Portal Sewa Reklame User',
            'alamat' => 'Jl. Veteran No. 12',
            'no_telepon' => '081234567890',
            'email' => 'portal-sewa@example.test',
            'nama_usaha' => 'CV Iklan Maju',
            'nomor_registrasi_izin' => 'REG-SEWA-2030-0001',
            'jenis_reklame_dipasang' => 'Promosi toko retail',
            'durasi_sewa_hari' => 30,
            'satuan_sewa' => 'bulanan',
            'tanggal_mulai_diinginkan' => now()->toDateString(),
            'catatan' => 'Uji signed public SKPD.',
            'status' => $status === 'disetujui' ? 'disetujui' : 'diproses',
            'tanggal_pengajuan' => now()->subDays(3),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
            'tanggal_diproses' => now()->subDays(2),
            'tanggal_selesai' => $status === 'disetujui' ? now()->subDay() : null,
            'npwpd' => 'P100000000099',
        ]);

        return SkpdReklame::create([
            'nomor_skpd' => $status === 'disetujui' ? 'SKPD-RKL/2030/02/000099' : 'SKPD-RKL/2030/02/000199',
            'tax_object_id' => $reklameObject->id,
            'aset_reklame_pemkab_id' => $aset->id,
            'permohonan_sewa_id' => $permohonan->id,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000099',
            'nik_wajib_pajak' => '3522011234567001',
            'nama_wajib_pajak' => 'Portal Sewa Reklame User',
            'alamat_wajib_pajak' => 'Jl. Veteran No. 12',
            'nama_reklame' => 'Reklame Aset Pemkab',
            'jenis_reklame' => $subJenisPajak->nama,
            'alamat_reklame' => 'Jl. Veteran No. 12',
            'kelompok_lokasi' => 'A',
            'bentuk' => 'persegi',
            'panjang' => 5,
            'lebar' => 3,
            'luas_m2' => 15,
            'jumlah_muka' => 2,
            'lokasi_penempatan' => 'luar_ruangan',
            'jenis_produk' => 'non_rokok',
            'jumlah_reklame' => 1,
            'satuan_waktu' => 'perBulan',
            'satuan_label' => 'per Bulan',
            'durasi' => 1,
            'tarif_pokok' => 150000,
            'nspr' => 0,
            'njopr' => 0,
            'penyesuaian_lokasi' => 1,
            'penyesuaian_produk' => 1,
            'nilai_strategis' => 0,
            'pokok_pajak_dasar' => 150000,
            'masa_berlaku_mulai' => now()->toDateString(),
            'masa_berlaku_sampai' => now()->addMonth()->toDateString(),
            'jatuh_tempo' => now()->addMonth(),
            'dasar_pengenaan' => 150000,
            'jumlah_pajak' => 150000,
            'status' => $status,
            'tanggal_buat' => now()->subHours(4),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
            'tanggal_verifikasi' => $status === 'disetujui' ? now()->subHours(2) : null,
            'verifikator_id' => $status === 'disetujui' ? $this->createAdminPanelUser('verifikator', Pimpinan::firstOrFail()->id)->id : null,
            'verifikator_nama' => $status === 'disetujui' ? 'Verifikator User' : null,
            'pimpinan_id' => Pimpinan::firstOrFail()->id,
            'kode_billing' => '352210400030000099',
            'dasar_hukum' => 'Peraturan Uji Sewa Reklame',
        ]);
    }

    private function createPermohonanSewaWithAttachment(): array
    {
        $owner = $this->createPortalUser('3522011234567002', 'Portal Lampiran Sewa User');

        $aset = AsetReklamePemkab::create([
            'kode_aset' => 'NB100',
            'nama' => 'Neon Box Lampiran',
            'jenis' => 'neon_box',
            'lokasi' => 'Jl. Panglima Sudirman No. 1',
            'keterangan' => 'Aset untuk uji lampiran permohonan',
            'kawasan' => 'Pusat Kota',
            'traffic' => 'Tinggi',
            'kelompok_lokasi' => 'A',
            'panjang' => 4,
            'lebar' => 2,
            'luas_m2' => 8,
            'jumlah_muka' => 1,
            'harga_sewa_per_bulan' => 100000,
            'status_ketersediaan' => 'tersedia',
            'is_active' => true,
        ]);

        $relativePath = 'test-attachments/' . Str::uuid() . '-ktp.txt';
        Storage::disk('local')->put($relativePath, 'lampiran permohonan sewa');

        $permohonan = PermohonanSewaReklame::create([
            'aset_reklame_pemkab_id' => $aset->id,
            'user_id' => $owner->id,
            'nik' => '3522011234567002',
            'nama' => 'Portal Lampiran Sewa User',
            'alamat' => 'Jl. Panglima Sudirman No. 1',
            'no_telepon' => '081234560001',
            'email' => 'lampiran-sewa@example.test',
            'nama_usaha' => 'CV Lampiran Maju',
            'nomor_registrasi_izin' => 'REG-SEWA-2030-ATTACH-1',
            'jenis_reklame_dipasang' => 'Promosi usaha',
            'durasi_sewa_hari' => 30,
            'satuan_sewa' => 'bulanan',
            'tanggal_mulai_diinginkan' => now()->toDateString(),
            'catatan' => 'Uji akses file lampiran.',
            'file_ktp' => $relativePath,
            'status' => 'diproses',
            'tanggal_pengajuan' => now()->subDay(),
        ]);

        return [
            'owner' => $owner,
            'permohonan' => $permohonan,
        ];
    }

    private function createPortalUser(string $nik, string $name): User
    {
        return User::create([
            'name' => $name,
            'nama_lengkap' => $name,
            'email' => sprintf('%s-%s@example.test', str()->slug($name), Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => $nik,
            'alamat' => 'Alamat uji',
            'role' => 'user',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);
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