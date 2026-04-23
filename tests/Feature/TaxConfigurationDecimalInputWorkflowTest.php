<?php

use App\Domain\AirTanah\Models\NpaAirTanah;
use App\Domain\Auth\Models\User;
use App\Domain\Tax\Models\HargaPatokanMblb;
use App\Domain\Tax\Models\HargaPatokanSarangWalet;
use App\Domain\Tax\Models\HargaSatuanListrik;
use App\Filament\Resources\HargaPatokanMblbResource\Pages\CreateHargaPatokanMblb;
use App\Filament\Resources\HargaPatokanSarangWaletResource\Pages\CreateHargaPatokanSarangWalet;
use App\Filament\Resources\HargaSatuanListrikResource\Pages\CreateHargaSatuanListrik;
use App\Filament\Resources\NpaAirTanahResource\Pages\CreateNpaAirTanah;
use App\Filament\Resources\NpaAirTanahResource\Pages\EditNpaAirTanah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates npa air tanah tiers from comma decimal input', function () {
    $admin = createTaxConfigDecimalAdminFixture();

    $this->actingAs($admin);

    Livewire::test(CreateNpaAirTanah::class)
        ->fillForm([
            'kelompok_pemakaian' => 'Kelompok Uji Desimal',
            'kriteria_sda' => 'Sumber air uji',
            'berlaku_mulai' => '2026-04-01',
            'berlaku_sampai' => null,
            'dasar_hukum' => 'Pergub 35/2026',
            'is_active' => true,
            'npa_tiers' => [
                [
                    'min_vol' => '0,25',
                    'max_vol' => '50,75',
                    'npa' => '1250,50',
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $record = NpaAirTanah::query()->firstOrFail();

    expect((float) $record->npa_tiers[0]['min_vol'])->toBe(0.25)
        ->and((float) $record->npa_tiers[0]['max_vol'])->toBe(50.75)
        ->and((float) $record->npa_tiers[0]['npa'])->toBe(1250.5);
});

it('updates npa air tanah tiers from comma decimal input', function () {
    $admin = createTaxConfigDecimalAdminFixture();
    $record = NpaAirTanah::create([
        'kelompok_pemakaian' => 'Kelompok Lama',
        'kriteria_sda' => 'SDA Lama',
        'npa_tiers' => [
            [
                'min_vol' => 0,
                'max_vol' => 25,
                'npa' => 1000,
            ],
        ],
        'berlaku_mulai' => now()->startOfMonth(),
        'berlaku_sampai' => null,
        'dasar_hukum' => 'Pergub 10/2025',
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(EditNpaAirTanah::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'kelompok_pemakaian' => 'Kelompok Baru',
            'kriteria_sda' => 'SDA Baru',
            'berlaku_mulai' => '2026-04-01',
            'berlaku_sampai' => null,
            'dasar_hukum' => 'Pergub 11/2026',
            'is_active' => true,
            'npa_tiers' => [
                [
                    'min_vol' => '1,25',
                    'max_vol' => '60,50',
                    'npa' => '1400,75',
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $record->refresh();

    expect($record->kelompok_pemakaian)->toBe('Kelompok Baru')
        ->and((float) $record->npa_tiers[0]['min_vol'])->toBe(1.25)
        ->and((float) $record->npa_tiers[0]['max_vol'])->toBe(60.5)
        ->and((float) $record->npa_tiers[0]['npa'])->toBe(1400.75);
});

it('creates harga configuration records from comma decimal input', function (string $pageClass, string $modelClass, array $formData, string $decimalField, float $expectedValue) {
    $admin = createTaxConfigDecimalAdminFixture();

    $this->actingAs($admin);

    Livewire::test($pageClass)
        ->fillForm($formData)
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $record = $modelClass::query()->latest('created_at')->firstOrFail();

    expect((float) $record->{$decimalField})->toBe($expectedValue);
})->with([
    'harga satuan listrik' => [
        CreateHargaSatuanListrik::class,
        HargaSatuanListrik::class,
        [
            'nama_wilayah' => 'Kabupaten Desimal',
            'harga_per_kwh' => '1500,75',
            'dasar_hukum' => 'Perbup 1/2026',
            'berlaku_mulai' => '2026-04-01',
            'berlaku_sampai' => null,
            'is_active' => true,
            'keterangan' => 'Uji desimal',
        ],
        'harga_per_kwh',
        1500.75,
    ],
    'harga patokan mblb' => [
        CreateHargaPatokanMblb::class,
        HargaPatokanMblb::class,
        [
            'nama_mineral' => 'Pasir Desimal',
            'nama_alternatif' => ['Pasir Uji'],
            'harga_patokan' => '100000,50',
            'satuan' => 'm3',
            'dasar_hukum' => 'Kepgub 2/2026',
            'is_active' => true,
            'keterangan' => 'Uji desimal',
        ],
        'harga_patokan',
        100000.5,
    ],
    'harga patokan sarang walet' => [
        CreateHargaPatokanSarangWalet::class,
        HargaPatokanSarangWalet::class,
        [
            'nama_jenis' => 'Mangkuk Desimal',
            'harga_patokan' => '6000000,25',
            'satuan' => 'kg',
            'dasar_hukum' => 'Perda 8/2026',
            'berlaku_mulai' => '2026-04-01',
            'berlaku_sampai' => null,
            'is_active' => true,
            'keterangan' => 'Uji desimal',
        ],
        'harga_patokan',
        6000000.25,
    ],
]);

function createTaxConfigDecimalAdminFixture(): User
{
    return User::create([
        'name' => 'Admin Decimal Test',
        'nama_lengkap' => 'Admin Decimal Test',
        'email' => sprintf('admin-decimal-%s@example.test', Str::random(8)),
        'password' => Hash::make('password'),
        'role' => 'admin',
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}