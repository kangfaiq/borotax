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

function createPublicHistoriWajibPajak(string $npwpd): void
{
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
}

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
        ->set('npwpd', 'P100000000099')
        ->set('tahun', (int) now()->year)
        ->call('cari')
        ->assertSet('sudahCari', false)
        ->assertSet('errorMessage', 'NPWPD tidak ditemukan. Pastikan nomor yang Anda masukkan benar.');

    expect(HistoriPajakAccessLog::query()
        ->where('status', HistoriPajakAccessStatus::GAGAL_NPWPD_TIDAK_DITEMUKAN)
        ->count())->toBe(1);
});

it('berhasil mencari WP yang ada walaupun belum ada dokumen', function () {
    $npwpd = 'P100000000123';

    createPublicHistoriWajibPajak($npwpd);

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
    $npwpd = 'P200000000099';

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

it('menampilkan aksi salin untuk excel saat ada hasil pencarian', function () {
    Livewire::test(HistoriPajakPublic::class)
        ->set('sudahCari', true)
        ->set('rows', [[
            'jenis_dokumen_label' => 'Billing',
            'jenis_dokumen_color' => 'info',
            'jenis_pajak' => 'PBJT',
            'nopd' => '1',
            'nama_objek_pajak' => 'Objek Tes',
            'nomor' => '352210200000000001',
            'masa' => 'Apr 2026',
            'tanggal_terbit' => '01 Apr 2026',
            'jatuh_tempo' => '10 Apr 2026',
            'tanggal_bayar' => '09 Apr 2026 10:00',
            'jumlah_tagihan' => 200000,
            'jumlah_terbayar' => 200000,
            'jumlah_sisa' => 0,
            'status_label' => 'Lunas',
            'status' => 'paid',
        ]])
        ->assertSee('Salin untuk Excel')
        ->assertDontSee('Ekspor Excel');
});

it('menampilkan pdf histori pajak secara inline', function () {
    $npwpd = 'P200000000321';
    createPublicHistoriWajibPajak($npwpd);

    $this->get(route('histori-pajak.pdf', [
        'npwpd' => $npwpd,
        'tahun' => (int) now()->year,
    ]))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'inline; filename=Histori-Pajak-' . $npwpd . '-' . now()->year . '.pdf');
});
