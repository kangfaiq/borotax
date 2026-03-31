<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Region\Models\Village;
use App\Filament\Resources\DistrictResource;
use App\Filament\Resources\VillageResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MasterDataModuleTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('roleProvider')]
    public function test_master_data_region_resources_are_admin_only_on_list_create_and_edit(string $role, bool $isAllowed): void
    {
        [$district, $village] = $this->createRegionFixture();

        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);
        $districtIndex = $this->get(DistrictResource::getUrl('index'));
        $this->assertAccessExpectation($districtIndex->getStatusCode(), $isAllowed, "district index for {$role}");
        if ($isAllowed) {
            $districtIndex->assertSee('Bojonegoro Kota');
        }

        $districtCreate = $this->get(DistrictResource::getUrl('create'));
        $this->assertAccessExpectation($districtCreate->getStatusCode(), $isAllowed, "district create for {$role}");

        $districtEdit = $this->get(DistrictResource::getUrl('edit', ['record' => $district]));
        $this->assertAccessExpectation($districtEdit->getStatusCode(), $isAllowed, "district edit for {$role}");
        if ($isAllowed) {
            $districtEdit->assertSee('Bojonegoro Kota');
        }

        $villageIndex = $this->get(VillageResource::getUrl('index'));
        $this->assertAccessExpectation($villageIndex->getStatusCode(), $isAllowed, "village index for {$role}");
        if ($isAllowed) {
            $villageIndex->assertSee('Kadipaten');
        }

        $villageCreate = $this->get(VillageResource::getUrl('create'));
        $this->assertAccessExpectation($villageCreate->getStatusCode(), $isAllowed, "village create for {$role}");

        $villageEdit = $this->get(VillageResource::getUrl('edit', ['record' => $village]));
        $this->assertAccessExpectation($villageEdit->getStatusCode(), $isAllowed, "village edit for {$role}");
        if ($isAllowed) {
            $villageEdit->assertSee('Kadipaten');
        }
    }

    public static function roleProvider(): array
    {
        return [
            'admin' => ['admin', true],
            'petugas' => ['petugas', false],
            'verifikator' => ['verifikator', false],
        ];
    }

    private function createRegionFixture(): array
    {
        $province = Province::create([
            'code' => '35',
            'name' => 'Jawa Timur',
        ]);

        $regency = Regency::create([
            'province_code' => $province->code,
            'code' => '35.22',
            'name' => 'Bojonegoro',
        ]);

        $district = District::create([
            'regency_code' => $regency->code,
            'code' => '35.22.01',
            'name' => 'Bojonegoro Kota',
        ]);

        $village = Village::create([
            'district_code' => $district->code,
            'code' => '35.22.01.1001',
            'name' => 'Kadipaten',
            'postal_code' => '62111',
        ]);

        return [$district, $village];
    }

    private function createAdminPanelUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role) . ' User',
            'nama_lengkap' => ucfirst($role) . ' User',
            'email' => $role . '-' . uniqid() . '@example.test',
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