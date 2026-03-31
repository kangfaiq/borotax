<?php

namespace Tests\Feature;

use App\Domain\AirTanah\Models\NpaAirTanah;
use App\Domain\Auth\Models\User;
use App\Domain\Tax\Models\HargaPatokanMblb;
use App\Domain\Tax\Models\HargaPatokanSarangWalet;
use App\Domain\Tax\Models\HargaSatuanListrik;
use App\Filament\Resources\HargaPatokanMblbResource;
use App\Filament\Resources\HargaPatokanMblbResource\Pages\ListHargaPatokanMblbs;
use App\Filament\Resources\HargaPatokanSarangWaletResource;
use App\Filament\Resources\HargaPatokanSarangWaletResource\Pages\ListHargaPatokanSarangWalets;
use App\Filament\Resources\HargaSatuanListrikResource;
use App\Filament\Resources\HargaSatuanListrikResource\Pages\ListHargaSatuanListriks;
use App\Filament\Resources\NpaAirTanahResource;
use App\Filament\Resources\NpaAirTanahResource\Pages\ListNpaAirTanahs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TaxConfigurationResourceActionTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('resourceProvider')]
    public function test_tax_configuration_resources_follow_admin_only_rules(
        string $resourceClass,
        string $listPageClass,
        string $modelClass,
        string $fixtureKey,
        bool $hasRowDelete,
        bool $hasBulkDelete,
        string $role,
        bool $isAllowed
    ): void {
        $record = $this->makeFixture($fixtureKey);
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $indexResponse = $this->get($resourceClass::getUrl('index'));
        $createResponse = $this->get($resourceClass::getUrl('create'));
        $editResponse = $this->get($resourceClass::getUrl('edit', ['record' => $record]));

        $this->assertAccessExpectation($indexResponse->getStatusCode(), $isAllowed, "{$fixtureKey} index for {$role}");
        $this->assertAccessExpectation($createResponse->getStatusCode(), $isAllowed, "{$fixtureKey} create for {$role}");
        $this->assertAccessExpectation($editResponse->getStatusCode(), $isAllowed, "{$fixtureKey} edit for {$role}");

        $this->assertSame($isAllowed, $user->can('viewAny', $modelClass), "Unexpected viewAny gate result for {$fixtureKey} and {$role}.");
        $this->assertSame($isAllowed, $user->can('create', $modelClass), "Unexpected create gate result for {$fixtureKey} and {$role}.");
        $this->assertSame($isAllowed, $user->can('view', $record), "Unexpected view gate result for {$fixtureKey} and {$role}.");
        $this->assertSame($isAllowed, $user->can('update', $record), "Unexpected update gate result for {$fixtureKey} and {$role}.");
        $this->assertSame($isAllowed, $user->can('delete', $record), "Unexpected delete gate result for {$fixtureKey} and {$role}.");

        if (! $isAllowed) {
            return;
        }

        $component = Livewire::test($listPageClass)
            ->assertCanSeeTableRecords([$record])
            ->assertTableActionVisible('edit', $record);

        if ($hasRowDelete) {
            $component->assertTableActionVisible('delete', $record);
        }

        if ($hasBulkDelete) {
            $component->assertTableBulkActionVisible('delete');
        }
    }

    public static function resourceProvider(): array
    {
        $cases = [
            'npa-air-tanah' => [NpaAirTanahResource::class, ListNpaAirTanahs::class, NpaAirTanah::class, 'npa_air_tanah', false, true],
            'harga-patokan-mblb' => [HargaPatokanMblbResource::class, ListHargaPatokanMblbs::class, HargaPatokanMblb::class, 'harga_patokan_mblb', true, true],
            'harga-patokan-sarang-walet' => [HargaPatokanSarangWaletResource::class, ListHargaPatokanSarangWalets::class, HargaPatokanSarangWalet::class, 'harga_patokan_sarang_walet', true, true],
            'harga-satuan-listrik' => [HargaSatuanListrikResource::class, ListHargaSatuanListriks::class, HargaSatuanListrik::class, 'harga_satuan_listrik', true, true],
        ];

        $roles = [
            'admin' => ['admin', true],
            'petugas' => ['petugas', false],
            'verifikator' => ['verifikator', false],
        ];

        $data = [];

        foreach ($cases as $caseName => [$resourceClass, $listPageClass, $modelClass, $fixtureKey, $hasRowDelete, $hasBulkDelete]) {
            foreach ($roles as $roleName => [$role, $isAllowed]) {
                $data["{$caseName}-{$roleName}"] = [
                    $resourceClass,
                    $listPageClass,
                    $modelClass,
                    $fixtureKey,
                    $hasRowDelete,
                    $hasBulkDelete,
                    $role,
                    $isAllowed,
                ];
            }
        }

        return $data;
    }

    private function makeFixture(string $fixtureKey): mixed
    {
        return match ($fixtureKey) {
            'npa_air_tanah' => $this->makeNpaAirTanah(),
            'harga_patokan_mblb' => $this->makeHargaPatokanMblb(),
            'harga_patokan_sarang_walet' => $this->makeHargaPatokanSarangWalet(),
            'harga_satuan_listrik' => $this->makeHargaSatuanListrik(),
        };
    }

    private function makeNpaAirTanah(): NpaAirTanah
    {
        return NpaAirTanah::create([
            'kelompok_pemakaian' => 'Kelompok 1',
            'kriteria_sda' => 'Air Tanah Kualitas Baik, Ada Sumber Alternatif',
            'npa_per_m3' => 1250,
            'npa_tiers' => [
                [
                    'min_vol' => 0,
                    'max_vol' => 50,
                    'npa' => 1250,
                ],
            ],
            'berlaku_mulai' => now()->startOfMonth(),
            'berlaku_sampai' => null,
            'dasar_hukum' => 'Pergub 35/2025',
            'is_active' => true,
        ]);
    }

    private function makeHargaPatokanMblb(): HargaPatokanMblb
    {
        return HargaPatokanMblb::create([
            'nama_mineral' => 'Pasir Uji ' . Str::random(4),
            'nama_alternatif' => ['Pasir Pasang'],
            'harga_patokan' => 100000,
            'satuan' => 'm3',
            'dasar_hukum' => 'Kepgub Jatim 2025',
            'is_active' => true,
            'keterangan' => 'Data uji',
        ]);
    }

    private function makeHargaPatokanSarangWalet(): HargaPatokanSarangWalet
    {
        return HargaPatokanSarangWalet::create([
            'nama_jenis' => 'Mangkuk Uji ' . Str::random(4),
            'harga_patokan' => 6000000,
            'satuan' => 'kg',
            'dasar_hukum' => 'Perda 8/2025',
            'berlaku_mulai' => now()->startOfMonth(),
            'berlaku_sampai' => null,
            'is_active' => true,
            'keterangan' => 'Data uji',
        ]);
    }

    private function makeHargaSatuanListrik(): HargaSatuanListrik
    {
        return HargaSatuanListrik::create([
            'nama_wilayah' => 'Kabupaten Uji ' . Str::random(4),
            'harga_per_kwh' => 1500,
            'dasar_hukum' => 'Perbup 1/2025',
            'berlaku_mulai' => now()->startOfMonth(),
            'berlaku_sampai' => null,
            'is_active' => true,
            'keterangan' => 'Data uji',
        ]);
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

    private function assertAccessExpectation(int $statusCode, bool $isAllowed, string $context): void
    {
        if ($isAllowed) {
            $this->assertSame(200, $statusCode, "Expected 200 for {$context}, got {$statusCode}.");

            return;
        }

        $this->assertContains($statusCode, [403, 404], "Expected 403/404 for {$context}, got {$statusCode}.");
    }
}