<?php

use App\Domain\Auth\Models\User;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Filament\Resources\AsetReklamePemkabResource\Pages\CreateAsetReklamePemkab;
use App\Filament\Resources\AsetReklamePemkabResource\Pages\EditAsetReklamePemkab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates aset reklame pemkab from comma decimal input', function () {
    $admin = createAsetReklameDecimalAdminFixture();

    $this->actingAs($admin);

    Livewire::test(CreateAsetReklamePemkab::class)
        ->fillForm([
            'kode_aset' => 'DEC001',
            'nama' => 'Billboard Desimal',
            'jenis' => 'billboard',
            'lokasi' => 'Jl. Veteran No. 11',
            'keterangan' => 'Uji input desimal aset reklame',
            'kawasan' => 'Kawasan Kota',
            'traffic' => 'Tinggi',
            'kelompok_lokasi' => 'A',
            'panjang' => '8,50',
            'lebar' => '4,25',
            'luas_m2' => null,
            'jumlah_muka' => 2,
            'latitude' => '-7,1523456',
            'longitude' => '111,8812345',
            'harga_sewa_per_tahun' => '12500000,75',
            'harga_sewa_per_bulan' => '1100000,50',
            'harga_sewa_per_minggu' => '350000,25',
            'status_ketersediaan' => 'tersedia',
            'catatan_status' => 'Siap dipakai',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $record = AsetReklamePemkab::where('kode_aset', 'DEC001')->firstOrFail();

    expect((float) $record->panjang)->toBe(8.5)
        ->and((float) $record->lebar)->toBe(4.25)
        ->and((float) $record->luas_m2)->toBe(36.13)
        ->and((float) $record->latitude)->toBe(-7.1523456)
        ->and((float) $record->longitude)->toBe(111.8812345)
        ->and((float) $record->harga_sewa_per_tahun)->toBe(12500000.75)
        ->and((float) $record->harga_sewa_per_bulan)->toBe(1100000.5)
        ->and((float) $record->harga_sewa_per_minggu)->toBe(350000.25);
});

it('updates aset reklame pemkab from comma decimal input', function () {
    $admin = createAsetReklameDecimalAdminFixture();
    $record = AsetReklamePemkab::create([
        'kode_aset' => 'DEC002',
        'nama' => 'Billboard Existing',
        'jenis' => 'billboard',
        'lokasi' => 'Jl. Basuki Rahmat No. 2',
        'keterangan' => 'Sebelum update',
        'kawasan' => 'Pusat Kota',
        'traffic' => 'Sedang',
        'kelompok_lokasi' => 'B',
        'panjang' => 6,
        'lebar' => 3,
        'luas_m2' => 18,
        'jumlah_muka' => 1,
        'latitude' => -7.15,
        'longitude' => 111.88,
        'harga_sewa_per_tahun' => 8000000,
        'harga_sewa_per_bulan' => 700000,
        'harga_sewa_per_minggu' => 250000,
        'status_ketersediaan' => 'tersedia',
        'catatan_status' => 'Awal',
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(EditAsetReklamePemkab::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'kode_aset' => 'DEC002',
            'nama' => 'Billboard Existing Update',
            'jenis' => 'billboard',
            'lokasi' => 'Jl. Basuki Rahmat No. 22',
            'keterangan' => 'Sesudah update',
            'kawasan' => 'Pusat Kota',
            'traffic' => 'Tinggi',
            'kelompok_lokasi' => 'A2',
            'panjang' => '7,75',
            'lebar' => '3,50',
            'luas_m2' => null,
            'jumlah_muka' => 2,
            'latitude' => '-7,1543210',
            'longitude' => '111,8898765',
            'harga_sewa_per_tahun' => '9100000,25',
            'harga_sewa_per_bulan' => '810000,50',
            'harga_sewa_per_minggu' => '280000,75',
            'status_ketersediaan' => 'maintenance',
            'catatan_status' => 'Perawatan rutin',
            'is_active' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $record->refresh();

    expect($record->lokasi)->toBe('Jl. Basuki Rahmat No. 22')
        ->and($record->kelompok_lokasi)->toBe('A2')
        ->and((float) $record->panjang)->toBe(7.75)
        ->and((float) $record->lebar)->toBe(3.5)
        ->and((float) $record->luas_m2)->toBe(27.13)
        ->and((float) $record->latitude)->toBe(-7.154321)
        ->and((float) $record->longitude)->toBe(111.8898765)
        ->and((float) $record->harga_sewa_per_tahun)->toBe(9100000.25)
        ->and((float) $record->harga_sewa_per_bulan)->toBe(810000.5)
        ->and((float) $record->harga_sewa_per_minggu)->toBe(280000.75);
});

function createAsetReklameDecimalAdminFixture(): User
{
    return User::create([
        'name' => 'Admin Aset Decimal',
        'nama_lengkap' => 'Admin Aset Decimal',
        'email' => sprintf('admin-aset-%s@example.test', Str::random(8)),
        'password' => Hash::make('password'),
        'role' => 'admin',
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}