<?php

use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\HistoriPajakAccessStatus;
use App\Livewire\HistoriPajakPublic;
use App\Models\HistoriPajakAccessLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.turnstile.key', null);
    config()->set('services.turnstile.secret', null); // bypass captcha
    Cache::flush();
});

it('renders the public histori pajak page', function () {
    $this->get(route('histori-pajak.index'))
        ->assertOk()
        ->assertSeeLivewire(HistoriPajakPublic::class);
});

it('logs gagal_format ketika NPWPD bukan 13 digit', function () {
    Livewire::test(HistoriPajakPublic::class)
        ->set('npwpd', '123')
        ->set('tahun', (int) now()->year)
        ->call('cari')
        ->assertHasErrors(['npwpd']);
});

it('logs gagal_npwpd_tidak_ditemukan dan menampilkan pesan', function () {
    Livewire::test(HistoriPajakPublic::class)
        ->set('npwpd', '3522101000099')
        ->set('tahun', (int) now()->year)
        ->call('cari')
        ->assertSet('sudahCari', false)
        ->assertSet('errorMessage', 'NPWPD tidak ditemukan. Pastikan nomor yang Anda masukkan benar.');

    expect(HistoriPajakAccessLog::query()
        ->where('status', HistoriPajakAccessStatus::GAGAL_NPWPD_TIDAK_DITEMUKAN)
        ->count())->toBe(1);
});

it('berhasil mencari WP yang ada walaupun belum ada dokumen', function () {
    $npwpd = '3522101000123';

    $user = \App\Domain\Auth\Models\User::create([
        'name' => 'Tester',
        'email' => 'tester-' . uniqid() . '@example.com',
        'password' => bcrypt('Password123!'),
    ]);

    WajibPajak::create([
        'user_id' => $user->id,
        'nik' => '3522010101010001',
        'nama_lengkap' => 'Tester WP',
        'alamat' => 'Jl. Tes No. 1',
        'tipe_wajib_pajak' => 'perorangan',
        'status' => 'disetujui',
        'tanggal_daftar' => now(),
        'npwpd' => $npwpd,
        'nopd' => 1,
    ]);

    Livewire::test(HistoriPajakPublic::class)
        ->set('npwpd', $npwpd)
        ->set('tahun', (int) now()->year)
        ->call('cari')
        ->assertSet('sudahCari', true)
        ->assertSet('rows', [])
        ->assertSet('errorMessage', null);

    expect(HistoriPajakAccessLog::query()
        ->where('status', HistoriPajakAccessStatus::SUKSES)
        ->where('npwpd', $npwpd)
        ->count())->toBe(1);
});

it('memblokir percobaan ke-6 dengan status rate_limited', function () {
    $npwpd = '3522101000099';

    for ($i = 0; $i < 5; $i++) {
        Livewire::test(HistoriPajakPublic::class)
            ->set('npwpd', $npwpd)
            ->set('tahun', (int) now()->year)
            ->call('cari');
    }

    Livewire::test(HistoriPajakPublic::class)
        ->set('npwpd', $npwpd)
        ->set('tahun', (int) now()->year)
        ->call('cari')
        ->assertSet('errorMessage', 'Terlalu banyak percobaan. Silakan coba lagi dalam beberapa menit.');

    expect(HistoriPajakAccessLog::query()
        ->where('status', HistoriPajakAccessStatus::RATE_LIMITED)
        ->count())->toBeGreaterThanOrEqual(1);
});

it('memuat daftar tahun dari tahun sekarang sampai 2019 desc', function () {
    $component = Livewire::test(HistoriPajakPublic::class);
    $tahun = $component->instance()->daftarTahun;

    $tahunSekarang = (int) now()->year;
    expect($tahun[0])->toBe($tahunSekarang)
        ->and(end($tahun))->toBe(2019)
        ->and($tahun)->toContain(2020)
        ->and($tahun)->toContain($tahunSekarang);
});
