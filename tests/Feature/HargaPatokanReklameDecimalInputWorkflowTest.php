<?php

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Filament\Resources\HargaPatokanReklameResource\Pages\CreateHargaPatokanReklame;
use App\Filament\Resources\HargaPatokanReklameResource\Pages\EditHargaPatokanReklame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates harga patokan reklame tariffs from comma decimal input', function () {
    $this->seedReklameTaxReferences();

    $admin = createHargaPatokanReklameDecimalAdminFixture();
    $subJenisPajak = SubJenisPajak::where('kode', 'REKLAME_TETAP')->firstOrFail();

    $this->actingAs($admin);

    Livewire::test(CreateHargaPatokanReklame::class)
        ->fillForm([
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'kode' => 'RKL_DECIMAL_TEST',
            'nama' => 'Reklame Decimal Test',
            'nama_lengkap' => 'Reklame Decimal Test Lengkap',
            'urutan' => 99,
            'is_insidentil' => false,
            'is_active' => true,
            'reklameTariffs' => [
                [
                    'kelompok_lokasi' => 'A',
                    'satuan_waktu' => 'perTahun',
                    'satuan_label' => 'Per Tahun',
                    'nspr' => '1.250,50',
                    'njopr' => '480.000,75',
                    'tarif_pokok' => '120.500,25',
                    'berlaku_mulai' => '2026-04-01',
                    'berlaku_sampai' => null,
                    'is_active' => true,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $record = HargaPatokanReklame::query()->where('kode', 'RKL_DECIMAL_TEST')->firstOrFail();
    $tariff = $record->reklameTariffs()->firstOrFail();

    expect((float) $tariff->nspr)->toBe(1250.5)
        ->and((float) $tariff->njopr)->toBe(480000.75)
        ->and((float) $tariff->tarif_pokok)->toBe(120500.25);
});

it('updates harga patokan reklame tariffs from comma decimal input', function () {
    $this->seedReklameTaxReferences();

    $admin = createHargaPatokanReklameDecimalAdminFixture();
    $subJenisPajak = SubJenisPajak::where('kode', 'REKLAME_TETAP')->firstOrFail();

    $record = HargaPatokanReklame::create([
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'kode' => 'RKL_DECIMAL_EDIT',
        'nama' => 'Reklame Decimal Edit',
        'nama_lengkap' => 'Reklame Decimal Edit Lengkap',
        'is_insidentil' => false,
        'is_active' => true,
        'urutan' => 2,
    ]);

    $record->reklameTariffs()->create([
        'kelompok_lokasi' => 'B',
        'satuan_waktu' => 'perBulan',
        'satuan_label' => 'Per Bulan',
        'nspr' => 1000,
        'njopr' => 250000,
        'tarif_pokok' => 50000,
        'berlaku_mulai' => '2026-01-01',
        'berlaku_sampai' => null,
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(EditHargaPatokanReklame::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'kode' => 'RKL_DECIMAL_EDIT',
            'nama' => 'Reklame Decimal Edit Baru',
            'nama_lengkap' => 'Reklame Decimal Edit Lengkap Baru',
            'urutan' => 3,
            'is_insidentil' => false,
            'is_active' => true,
            'reklameTariffs' => [
                [
                    'kelompok_lokasi' => 'B',
                    'satuan_waktu' => 'perBulan',
                    'satuan_label' => 'Per Bulan',
                    'nspr' => '1.100,25',
                    'njopr' => '255.500,50',
                    'tarif_pokok' => '55.250,75',
                    'berlaku_mulai' => '2026-05-01',
                    'berlaku_sampai' => null,
                    'is_active' => true,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $record->refresh();
    $tariff = $record->reklameTariffs()->firstOrFail();

    expect($record->nama)->toBe('Reklame Decimal Edit Baru')
        ->and((float) $tariff->nspr)->toBe(1100.25)
        ->and((float) $tariff->njopr)->toBe(255500.5)
        ->and((float) $tariff->tarif_pokok)->toBe(55250.75);
});

function createHargaPatokanReklameDecimalAdminFixture(): User
{
    return User::create([
        'name' => 'Admin Harga Patokan Reklame',
        'nama_lengkap' => 'Admin Harga Patokan Reklame',
        'email' => sprintf('admin-harga-patokan-%s@example.test', Str::random(8)),
        'password' => Hash::make('password'),
        'role' => 'admin',
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}