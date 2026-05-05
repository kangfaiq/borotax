<?php

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Domain\Reklame\Models\KelompokLokasiJalan;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Filament\Pages\BuatSkpdReklame;
use Database\Seeders\AsetReklamePemkabSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createSkpdReklamePageUser(string $role = 'petugas'): User
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

it('stores optional isi materi reklame for object mode skpd draft', function (): void {
    $this->seedReklameTaxReferences();

    $petugas = createSkpdReklamePageUser();
    $subJenisPajak = SubJenisPajak::where('kode', 'REKLAME_TETAP')->firstOrFail();
    $hargaPatokanReklame = HargaPatokanReklame::where('kode', 'RKL_NEON_BOX')->firstOrFail();
    $lokasiJalan = KelompokLokasiJalan::where('nama_jalan', 'Jalan Panglima Sudirman')->firstOrFail();
    $wajibPajak = $this->createApprovedWajibPajakFixture([
        'npwpd' => 'P100000000321',
    ], [
        'nik' => '3522011234567890',
        'nama_lengkap' => 'Existing WP',
        'alamat' => 'Jl. Existing No. 1',
    ]);

    $reklameObject = ReklameObject::create([
        'nik' => $wajibPajak->nik,
        'nama_objek_pajak' => 'Reklame Neon Box Sentosa',
        'jenis_pajak_id' => $subJenisPajak->jenis_pajak_id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'npwpd' => $wajibPajak->npwpd,
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

    Livewire::test(BuatSkpdReklame::class)
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
        ->set('satuanWaktu', 'perTahun')
        ->set('durasi', 1)
        ->set('jumlahReklame', 1)
        ->set('lokasiPenempatan', 'luar_ruangan')
        ->set('jenisProduk', 'non_rokok')
        ->set('isiMateriReklame', 'Promo Grand Opening Sentosa')
        ->set('masaBerlakuMulai', '2030-01-01')
        ->call('hitungMasaBerlakuSampai')
        ->call('buatSkpd')
        ->assertHasNoErrors();

    $skpd = SkpdReklame::firstOrFail();

    expect($skpd->nama_reklame)->toBe('Reklame Neon Box Sentosa')
        ->and($skpd->isi_materi_reklame)->toBe('Promo Grand Opening Sentosa');
});

it('uses jenis reklame dipasang from online permohonan for aset pemkab mode', function (): void {
    $this->seedReklameTaxReferences([
        AsetReklamePemkabSeeder::class,
    ]);

    $petugas = createSkpdReklamePageUser();
    $wajibPajak = $this->createApprovedWajibPajakFixture([], [
        'nik' => '3522011234567888',
        'nama_lengkap' => 'Pemohon Sewa',
        'alamat' => 'Jl. Pemohon No. 12',
    ]);
    $aset = AsetReklamePemkab::where('kode_aset', 'NB001')->firstOrFail();
    $permohonan = $this->createPermohonanSewaReklameFixture([
        'aset_reklame_pemkab_id' => $aset->id,
        'nik' => $wajibPajak->nik,
        'nama' => $wajibPajak->nama_lengkap,
        'alamat' => $wajibPajak->alamat,
        'npwpd' => $wajibPajak->npwpd,
        'jenis_reklame_dipasang' => 'Iklan Produk Musiman 2026',
    ]);
    $subJenis = SubJenisPajak::where('kode', 'REKLAME_TETAP')->firstOrFail();
    $hargaPatokanReklame = HargaPatokanReklame::where('kode', 'RKL_NEON_BOX')->firstOrFail();

    $this->actingAs($petugas);

    Livewire::test(BuatSkpdReklame::class)
        ->set('mode', 'aset_pemkab')
        ->set('permohonanId', $permohonan->id)
        ->set('permohonanData', [
            'id' => $permohonan->id,
            'nik' => $permohonan->nik,
            'nama' => $permohonan->nama,
            'alamat' => $permohonan->alamat,
            'no_telepon' => $permohonan->no_telepon,
            'jenis_reklame_dipasang' => $permohonan->jenis_reklame_dipasang,
            'durasi_sewa_hari' => $permohonan->durasi_sewa_hari,
            'tanggal_mulai_diinginkan' => $permohonan->tanggal_mulai_diinginkan?->format('Y-m-d'),
        ])
        ->set('selectedAsetPemkabId', $aset->id)
        ->set('selectedAsetPemkabData', [
            'id' => $aset->id,
            'kode_aset' => $aset->kode_aset,
            'nama' => $aset->nama,
            'jenis' => $aset->jenis,
            'lokasi' => $aset->lokasi,
            'kawasan' => $aset->kawasan,
            'panjang' => $aset->panjang,
            'lebar' => $aset->lebar,
            'luas_m2' => $aset->luas_m2,
            'jumlah_muka' => $aset->jumlah_muka,
            'kelompok_lokasi' => $aset->kelompok_lokasi,
            'status_ketersediaan' => $aset->status_ketersediaan,
            'status_label' => $aset->statusLabel,
            'status_color' => $aset->statusColor,
            'ukuran_formatted' => $aset->ukuranFormatted,
            'harga_sewa_per_tahun' => $aset->harga_sewa_per_tahun,
            'harga_sewa_per_bulan' => $aset->harga_sewa_per_bulan,
            'harga_sewa_per_minggu' => $aset->harga_sewa_per_minggu,
        ])
        ->set('selectedWpData', [
            'id' => $wajibPajak->id,
            'npwpd' => $wajibPajak->npwpd,
            'nik' => $wajibPajak->nik,
            'nama_lengkap' => $wajibPajak->nama_lengkap,
            'alamat' => $wajibPajak->alamat,
        ])
        ->set('subJenisPajakId', $subJenis->id)
        ->set('hargaPatokanReklameId', $hargaPatokanReklame->id)
        ->set('satuanWaktu', 'perMinggu')
        ->set('durasi', 2)
        ->set('jumlahMuka', (int) $aset->jumlah_muka)
        ->set('luasM2', (float) $aset->luas_m2)
        ->set('masaBerlakuMulai', '2030-01-01')
        ->set('masaBerlakuSampai', '2030-01-14')
        ->call('buatSkpdAsetPemkab')
        ->assertHasNoErrors();

    $skpd = SkpdReklame::firstOrFail();

    expect($skpd->nama_reklame)->toBe($aset->nama)
        ->and($skpd->isi_materi_reklame)->toBe('Iklan Produk Musiman 2026');
});
