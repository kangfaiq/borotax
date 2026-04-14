<?php

use App\Domain\Auth\Models\User;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\PeminjamanAsetReklame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('automatically releases expired opd borrowing before public sewa form is shown', function () {
    $aset = createExpiredOpdBorrowedAsset();

    $this->get(route('sewa-reklame.form', ['asetId' => $aset->id]))
        ->assertOk();

    $aset->refresh();

    expect($aset->status_ketersediaan)->toBe('tersedia');
    expect($aset->peminjam_opd)->toBeNull();
    expect($aset->pinjam_selesai)->toBeNull();

    $this->assertDatabaseHas('peminjaman_aset_reklame', [
        'aset_reklame_pemkab_id' => $aset->id,
        'status' => 'selesai',
    ]);
});

it('includes expired opd assets in the tersedia api filter after automatic sync', function () {
    $user = createMobileUser();
    $aset = createExpiredOpdBorrowedAsset();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/reklame-aset-pemkab?status=tersedia')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonFragment([
            'id' => $aset->id,
            'status_ketersediaan' => 'tersedia',
        ]);

    expect($aset->fresh()->status_ketersediaan)->toBe('tersedia');
});

it('auto releases expired opd borrowing in the sync command', function () {
    $aset = createExpiredOpdBorrowedAsset();

    $this->artisan('reklame:sync-ketersediaan')->assertSuccessful();

    $aset->refresh();

    expect($aset->status_ketersediaan)->toBe('tersedia');
    expect($aset->peminjam_opd)->toBeNull();

    $this->assertDatabaseHas('peminjaman_aset_reklame', [
        'aset_reklame_pemkab_id' => $aset->id,
        'status' => 'selesai',
    ]);
});

function createExpiredOpdBorrowedAsset(): AsetReklamePemkab
{
    $aset = AsetReklamePemkab::create([
        'kode_aset' => 'AUTO001',
        'nama' => 'Billboard Auto Release',
        'jenis' => 'billboard',
        'lokasi' => 'Jl. Pemuda No. 1',
        'keterangan' => 'Uji auto release pinjam OPD',
        'kawasan' => 'Pusat Kota',
        'traffic' => 'Tinggi',
        'kelompok_lokasi' => 'A',
        'panjang' => 4,
        'lebar' => 6,
        'luas_m2' => 24,
        'jumlah_muka' => 1,
        'latitude' => -7.1500000,
        'longitude' => 111.8800000,
        'harga_sewa_per_tahun' => 24000000,
        'harga_sewa_per_bulan' => 2000000,
        'harga_sewa_per_minggu' => 600000,
        'status_ketersediaan' => 'dipinjam_opd',
        'catatan_status' => 'Dipinjam untuk kampanye OPD',
        'is_active' => true,
        'peminjam_opd' => 'Dinas Kominfo',
        'materi_pinjam' => 'Layanan publik digital',
        'pinjam_mulai' => now()->subDays(10)->toDateString(),
        'pinjam_selesai' => now()->subDay()->toDateString(),
        'catatan_pinjam' => 'Harus otomatis selesai ketika jatuh tempo lewat',
    ]);

    PeminjamanAsetReklame::create([
        'aset_reklame_pemkab_id' => $aset->id,
        'peminjam_opd' => 'Dinas Kominfo',
        'materi_pinjam' => 'Layanan publik digital',
        'pinjam_mulai' => now()->subDays(10)->toDateString(),
        'pinjam_selesai' => now()->subDay()->toDateString(),
        'catatan_pinjam' => 'Riwayat pinjam aktif',
        'status' => 'aktif',
        'petugas_id' => null,
        'petugas_nama' => 'System Test',
    ]);

    return $aset;
}

function createMobileUser(): User
{
    $nik = '3522011234567811';

    return User::create([
        'name' => 'Mobile Reklame User',
        'nama_lengkap' => 'Mobile Reklame User',
        'email' => 'mobile-reklame-user@example.test',
        'password' => Hash::make('password'),
        'nik' => $nik,
        'nik_hash' => User::generateHash($nik),
        'alamat' => 'Jl. Diponegoro No. 1',
        'role' => 'user',
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}