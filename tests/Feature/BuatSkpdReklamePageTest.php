<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Domain\Reklame\Models\KelompokLokasiJalan;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Filament\Pages\BuatSkpdReklame;
use Database\Seeders\ReklameNilaiStrategisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class BuatSkpdReklamePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_can_create_draft_skpd_reklame_from_object_using_harga_patokan_detail(): void
    {
        $this->seedReklameTaxReferences();

        $petugas = $this->createAdminPanelUser('petugas');
        $subJenisPajak = SubJenisPajak::where('kode', 'REKLAME_TETAP')->firstOrFail();
        $hargaPatokanReklame = HargaPatokanReklame::where('kode', 'RKL_NEON_BOX')->firstOrFail();
        $lokasiJalan = KelompokLokasiJalan::where('nama_jalan', 'Jalan Panglima Sudirman')->firstOrFail();

        $this->createApprovedWajibPajakForNik('3522011234567890', 'P100000000321');

        $reklameObject = ReklameObject::create([
            'nik' => '3522011234567890',
            'nama_objek_pajak' => 'Reklame Neon Box Sentosa',
            'jenis_pajak_id' => $subJenisPajak->jenis_pajak_id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000321',
            'nopd' => 1001,
            'alamat_objek' => 'Jl. Panglima Sudirman No. 10',
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
            'harga_patokan_reklame_id' => $hargaPatokanReklame->id,
            'lokasi_jalan_id' => $lokasiJalan->id,
        ]);

        $this->actingAs($petugas);

        $component = Livewire::test(BuatSkpdReklame::class)
            ->set('searchResults', [[
                'id' => $reklameObject->id,
                'nama' => $reklameObject->nama_objek_pajak,
                'alamat' => $reklameObject->alamat_objek,
                'npwpd' => $reklameObject->npwpd,
                'nopd' => $reklameObject->nopd,
                'nik_hash' => $reklameObject->nik_hash,
                'sub_jenis' => $subJenisPajak->nama,
                'sub_jenis_pajak_id' => $subJenisPajak->id,
                'jenis_pajak_id' => $reklameObject->jenis_pajak_id,
                'harga_patokan_reklame_id' => $hargaPatokanReklame->id,
                'lokasi_jalan_id' => $lokasiJalan->id,
                'lokasi_jalan_label' => $lokasiJalan->nama_jalan,
                'kelompok_lokasi' => 'A',
                'bentuk' => 'persegi',
                'panjang' => 4.0,
                'lebar' => 2.0,
                'tinggi' => null,
                'sisi_atas' => null,
                'sisi_bawah' => null,
                'diameter' => null,
                'diameter2' => null,
                'alas' => null,
                'luas_m2' => 8.0,
                'jumlah_muka' => 1,
                'masa_berlaku_sampai' => null,
                'status' => 'aktif',
                'is_insidentil' => false,
                'ukuran_formatted' => '4.00 m × 2.00 m',
            ]])
            ->call('selectObject', $reklameObject->id)
            ->assertSet('subJenisPajakId', $subJenisPajak->id)
            ->assertSet('hargaPatokanReklameId', $hargaPatokanReklame->id)
            ->assertSet('lokasiJalanId', $lokasiJalan->id)
            ->assertSet('kelompokLokasi', 'A')
            ->assertSet('jumlahMuka', 1)
            ->set('satuanWaktu', 'perTahun')
            ->set('durasi', 1)
            ->set('jumlahReklame', 1)
            ->set('lokasiPenempatan', 'luar_ruangan')
            ->set('jenisProduk', 'non_rokok')
            ->set('masaBerlakuMulai', '2030-01-01')
            ->call('hitungMasaBerlakuSampai')
            ->call('buatSkpd')
            ->assertHasNoErrors();

        $skpd = SkpdReklame::firstOrFail();

        $this->assertSame($reklameObject->id, $skpd->tax_object_id);
        $this->assertSame($subJenisPajak->id, $skpd->sub_jenis_pajak_id);
        $this->assertSame($hargaPatokanReklame->id, $skpd->harga_patokan_reklame_id);
        $this->assertSame($hargaPatokanReklame->nama, $skpd->jenis_reklame);
        $this->assertSame('draft', $skpd->status);
        $this->assertGreaterThan(0, (float) $skpd->jumlah_pajak);
    }

    public function test_reference_date_switches_selected_road_to_matching_effective_version(): void
    {
        $this->seedReklameTaxReferences();

        $petugas = $this->createAdminPanelUser('petugas');
        $subJenisPajak = SubJenisPajak::where('kode', 'REKLAME_TETAP')->firstOrFail();
        $hargaPatokanReklame = HargaPatokanReklame::where('kode', 'RKL_NEON_BOX')->firstOrFail();

        $lokasiLama = KelompokLokasiJalan::create([
            'kelompok' => 'A',
            'nama_jalan' => 'Jalan Uji Versioning',
            'deskripsi' => 'Versi 2026',
            'is_active' => true,
            'berlaku_mulai' => '2026-01-01',
            'berlaku_sampai' => '2026-12-31',
        ]);

        $lokasiBaru = KelompokLokasiJalan::create([
            'kelompok' => 'B',
            'nama_jalan' => 'Jalan Uji Versioning',
            'deskripsi' => 'Versi 2027',
            'is_active' => true,
            'berlaku_mulai' => '2027-01-01',
            'berlaku_sampai' => null,
        ]);

        $reklameObject = ReklameObject::create([
            'nik' => '3522011234567001',
            'nama_objek_pajak' => 'Reklame Uji Versioning',
            'jenis_pajak_id' => $subJenisPajak->jenis_pajak_id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000999',
            'nopd' => 1999,
            'alamat_objek' => 'Jl. Uji Versioning No. 1',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tarif_persen' => 25,
            'tanggal_daftar' => '2026-06-01',
            'is_active' => true,
            'bentuk' => 'persegi',
            'panjang' => 4,
            'lebar' => 3,
            'jumlah_muka' => 1,
            'status' => 'aktif',
            'kelompok_lokasi' => 'A',
            'harga_patokan_reklame_id' => $hargaPatokanReklame->id,
            'lokasi_jalan_id' => $lokasiLama->id,
        ]);

        $this->actingAs($petugas);

        $component = Livewire::test(BuatSkpdReklame::class)
            ->set('searchResults', [[
                'id' => $reklameObject->id,
                'nama' => $reklameObject->nama_objek_pajak,
                'alamat' => $reklameObject->alamat_objek,
                'npwpd' => $reklameObject->npwpd,
                'nopd' => $reklameObject->nopd,
                'nik_hash' => $reklameObject->nik_hash,
                'sub_jenis' => $subJenisPajak->nama,
                'sub_jenis_pajak_id' => $subJenisPajak->id,
                'jenis_pajak_id' => $reklameObject->jenis_pajak_id,
                'harga_patokan_reklame_id' => $hargaPatokanReklame->id,
                'lokasi_jalan_id' => $lokasiLama->id,
                'lokasi_jalan_label' => $lokasiLama->nama_jalan,
                'kelompok_lokasi' => 'A',
                'bentuk' => 'persegi',
                'panjang' => 4.0,
                'lebar' => 3.0,
                'tinggi' => null,
                'sisi_atas' => null,
                'sisi_bawah' => null,
                'diameter' => null,
                'diameter2' => null,
                'alas' => null,
                'luas_m2' => 12.0,
                'jumlah_muka' => 1,
                'masa_berlaku_sampai' => null,
                'status' => 'aktif',
                'is_insidentil' => false,
                'ukuran_formatted' => '4.00 m × 3.00 m',
            ]])
            ->call('selectObject', $reklameObject->id)
            ->assertSet('lokasiJalanId', $lokasiLama->id)
            ->assertSet('kelompokLokasi', 'A')
            ->set('masaBerlakuMulai', '2027-06-01')
            ->assertSet('lokasiJalanId', $lokasiBaru->id)
            ->assertSet('kelompokLokasi', 'B');
    }

    public function test_preview_pajak_includes_nilai_strategis_for_reklame_tetap_minimum_ten_square_meters(): void
    {
        $this->seedReklameTaxReferences([
            ReklameNilaiStrategisSeeder::class,
        ]);

        $petugas = $this->createAdminPanelUser('petugas');
        $subJenisPajak = SubJenisPajak::where('kode', 'REKLAME_TETAP')->firstOrFail();
        $hargaPatokanReklame = HargaPatokanReklame::where('kode', 'RKL_BILLBOARD_GTE_10')->firstOrFail();
        $lokasiJalan = KelompokLokasiJalan::where('kelompok', 'A1')->firstOrFail();

        $this->createApprovedWajibPajakForNik('3522011234567891', 'P100000000654');

        $reklameObject = ReklameObject::create([
            'nik' => '3522011234567891',
            'nama_objek_pajak' => 'Billboard Simpang Provinsi',
            'jenis_pajak_id' => $subJenisPajak->jenis_pajak_id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000654',
            'nopd' => 1002,
            'alamat_objek' => 'Jl. Provinsi No. 1',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tarif_persen' => 25,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'bentuk' => 'persegi',
            'panjang' => 7.2,
            'lebar' => 3.4,
            'jumlah_muka' => 1,
            'status' => 'aktif',
            'kelompok_lokasi' => 'A1',
            'harga_patokan_reklame_id' => $hargaPatokanReklame->id,
            'lokasi_jalan_id' => $lokasiJalan->id,
        ]);

        $this->actingAs($petugas);

        $component = Livewire::test(BuatSkpdReklame::class)
            ->set('searchResults', [[
                'id' => $reklameObject->id,
                'nama' => $reklameObject->nama_objek_pajak,
                'alamat' => $reklameObject->alamat_objek,
                'npwpd' => $reklameObject->npwpd,
                'nopd' => $reklameObject->nopd,
                'nik_hash' => $reklameObject->nik_hash,
                'sub_jenis' => $subJenisPajak->nama,
                'sub_jenis_pajak_id' => $subJenisPajak->id,
                'jenis_pajak_id' => $reklameObject->jenis_pajak_id,
                'harga_patokan_reklame_id' => $hargaPatokanReklame->id,
                'lokasi_jalan_id' => $lokasiJalan->id,
                'lokasi_jalan_label' => $lokasiJalan->nama_jalan,
                'kelompok_lokasi' => 'A1',
                'bentuk' => 'persegi',
                'panjang' => 7.2,
                'lebar' => 3.4,
                'tinggi' => null,
                'sisi_atas' => null,
                'sisi_bawah' => null,
                'diameter' => null,
                'diameter2' => null,
                'alas' => null,
                'luas_m2' => 24.48,
                'jumlah_muka' => 1,
                'masa_berlaku_sampai' => null,
                'status' => 'aktif',
                'is_insidentil' => false,
                'ukuran_formatted' => '7.20 m × 3.40 m',
            ]])
            ->call('selectObject', $reklameObject->id)
            ->set('satuanWaktu', 'perTahun')
            ->set('durasi', 1)
            ->set('jumlahReklame', 1)
            ->set('lokasiPenempatan', 'luar_ruangan')
            ->set('jenisProduk', 'non_rokok')
            ->set('masaBerlakuMulai', '2026-01-01');

        $preview = $component->instance()->getPreviewPajak();

        $this->assertNotNull($preview);
        $this->assertSame(5000000.0, $preview['nilai_strategis']);
        $this->assertSame(10446800.0, $preview['jumlah_pajak']);
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

    private function createApprovedWajibPajakForNik(string $nik, string $npwpd): WajibPajak
    {
        $user = User::create([
            'name' => 'Existing WP',
            'nama_lengkap' => 'Existing WP',
            'email' => sprintf('existing-wp-%s@example.test', Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => $nik,
            'alamat' => 'Jl. Existing No. 1',
            'role' => 'user',
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
        ]);

        return WajibPajak::create([
            'user_id' => $user->id,
            'nik' => $nik,
            'nama_lengkap' => 'Existing WP',
            'alamat' => 'Jl. Existing No. 1',
            'tipe_wajib_pajak' => 'perorangan',
            'status' => 'disetujui',
            'tanggal_daftar' => now()->subDays(7),
            'tanggal_verifikasi' => now()->subDays(6),
            'petugas_id' => null,
            'petugas_nama' => null,
            'npwpd' => $npwpd,
            'nopd' => 1,
        ]);
    }
}