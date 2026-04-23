<?php

use App\Domain\Auth\Models\User;
use App\Domain\Reklame\Models\ReklameNilaiStrategis;
use App\Filament\Resources\ReklameNilaiStrategisResource;
use App\Filament\Resources\ReklameNilaiStrategisResource\Pages\CreateReklameNilaiStrategis;
use App\Filament\Resources\ReklameNilaiStrategisResource\Pages\EditReklameNilaiStrategis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates reklame nilai strategis from comma decimal input', function () {
    $admin = createReklameStrategisDecimalAdminFixture();

    $this->actingAs($admin);

    Livewire::test(CreateReklameNilaiStrategis::class)
        ->fillForm([
            'kelas_kelompok' => 'A',
            'luas_min' => '10,50',
            'luas_max' => '24,75',
            'tarif_per_tahun' => '5000000,25',
            'tarif_per_bulan' => '500000,50',
            'berlaku_mulai' => '2026-01-01',
            'berlaku_sampai' => null,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $record = ReklameNilaiStrategis::query()->firstOrFail();

    expect((float) $record->luas_min)->toBe(10.5)
        ->and((float) $record->luas_max)->toBe(24.75)
        ->and((float) $record->tarif_per_tahun)->toBe(5000000.25)
        ->and((float) $record->tarif_per_bulan)->toBe(500000.5);
});

it('updates reklame nilai strategis from comma decimal input', function () {
    $admin = createReklameStrategisDecimalAdminFixture();
    $record = ReklameNilaiStrategis::create([
        'kelas_kelompok' => 'B',
        'luas_min' => 10,
        'luas_max' => 20,
        'tarif_per_tahun' => 4000000,
        'tarif_per_bulan' => 400000,
        'berlaku_mulai' => '2026-01-01',
        'berlaku_sampai' => null,
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(EditReklameNilaiStrategis::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'kelas_kelompok' => 'C',
            'luas_min' => '11,25',
            'luas_max' => '30,50',
            'tarif_per_tahun' => '4250000,75',
            'tarif_per_bulan' => '425000,25',
            'berlaku_mulai' => '2026-02-01',
            'berlaku_sampai' => null,
            'is_active' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $record->refresh();

    expect($record->kelas_kelompok)->toBe('C')
        ->and((float) $record->luas_min)->toBe(11.25)
        ->and((float) $record->luas_max)->toBe(30.5)
        ->and((float) $record->tarif_per_tahun)->toBe(4250000.75)
        ->and((float) $record->tarif_per_bulan)->toBe(425000.25);
});

function createReklameStrategisDecimalAdminFixture(): User
{
    return User::create([
        'name' => 'Admin Reklame Decimal',
        'nama_lengkap' => 'Admin Reklame Decimal',
        'email' => sprintf('admin-reklame-%s@example.test', Str::random(8)),
        'password' => Hash::make('password'),
        'role' => 'admin',
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}