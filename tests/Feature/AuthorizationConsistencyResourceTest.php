<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Village;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Shared\Models\ActivityLog;
use App\Domain\Tax\Models\HargaPatokanMblb;
use App\Domain\Tax\Models\HargaPatokanSarangWalet;
use App\Domain\Tax\Models\HargaSatuanListrik;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Filament\Resources\ActivityLogResource;
use App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs;
use App\Filament\Resources\DaftarWajibPajakResource;
use App\Filament\Resources\DistrictResource;
use App\Filament\Resources\HargaPatokanMblbResource;
use App\Filament\Resources\HargaPatokanSarangWaletResource;
use App\Filament\Resources\HargaSatuanListrikResource;
use App\Filament\Resources\PimpinanResource;
use App\Filament\Resources\TaxObjectResource;
use App\Filament\Resources\PimpinanResource\Pages\ListPimpinans;
use App\Filament\Resources\VillageResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthorizationConsistencyResourceTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('activityLogRoleProvider')]
    public function test_activity_log_resource_matches_documented_access_matrix(string $role): void
    {
        $actor = $this->createAdminPanelUser('admin');
        $viewer = $this->createAdminPanelUser($role);
        $record = ActivityLog::create([
            'actor_id' => $actor->id,
            'actor_type' => 'admin',
            'action' => 'TEST_ACCESS',
            'target_table' => 'users',
            'target_id' => $actor->id,
            'description' => 'Audit entry for authorization test',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->actingAs($viewer);

        $indexResponse = $this->get(ActivityLogResource::getUrl('index'));
        $viewResponse = $this->get(ActivityLogResource::getUrl('view', ['record' => $record]));

        $this->assertSame(200, $indexResponse->getStatusCode(), "Expected activity log index to be accessible for {$role}.");
        $this->assertSame(200, $viewResponse->getStatusCode(), "Expected activity log detail to be accessible for {$role}.");

        $this->assertTrue($viewer->can('viewAny', ActivityLog::class), "Expected viewAny gate to allow {$role} for activity log.");
        $this->assertTrue($viewer->can('view', $record), "Expected view gate to allow {$role} for activity log.");
        $this->assertFalse($viewer->can('create', ActivityLog::class), "Expected create gate to remain disabled for activity log.");

        Livewire::test(ListActivityLogs::class)
            ->assertCanSeeTableRecords([$record])
            ->assertTableActionVisible('view', $record);
    }

    #[DataProvider('pimpinanRoleProvider')]
    public function test_pimpinan_resource_and_policy_stay_admin_only(string $role, bool $isAllowed): void
    {
        $record = Pimpinan::create([
            'kab' => 'Bojonegoro',
            'opd' => 'Bapenda',
            'jabatan' => 'Kepala Badan',
            'bidang' => 'Pendapatan',
            'sub_bidang' => 'PBB',
            'nama' => 'Pimpinan Uji',
            'pangkat' => 'Pembina',
            'nip' => '197001011990031001',
        ]);
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $indexResponse = $this->get(PimpinanResource::getUrl('index'));
        $createResponse = $this->get(PimpinanResource::getUrl('create'));
        $editResponse = $this->get(PimpinanResource::getUrl('edit', ['record' => $record]));

        $this->assertAccessExpectation($indexResponse->getStatusCode(), $isAllowed, "pimpinan index for {$role}");
        $this->assertAccessExpectation($createResponse->getStatusCode(), $isAllowed, "pimpinan create for {$role}");
        $this->assertAccessExpectation($editResponse->getStatusCode(), $isAllowed, "pimpinan edit for {$role}");

        $this->assertSame($isAllowed, $user->can('viewAny', Pimpinan::class), "Unexpected viewAny gate result for pimpinan and {$role}.");
        $this->assertSame($isAllowed, $user->can('create', Pimpinan::class), "Unexpected create gate result for pimpinan and {$role}.");
        $this->assertSame($isAllowed, $user->can('view', $record), "Unexpected view gate result for pimpinan and {$role}.");
        $this->assertSame($isAllowed, $user->can('update', $record), "Unexpected update gate result for pimpinan and {$role}.");
        $this->assertSame($isAllowed, $user->can('delete', $record), "Unexpected delete gate result for pimpinan and {$role}.");

        if (! $isAllowed) {
            return;
        }

        Livewire::test(ListPimpinans::class)
            ->assertCanSeeTableRecords([$record])
            ->assertTableActionVisible('edit', $record)
            ->assertTableActionVisible('delete', $record)
            ->assertTableBulkActionVisible('delete');
    }

    public static function activityLogRoleProvider(): array
    {
        return [
            'admin' => ['admin'],
            'verifikator' => ['verifikator'],
            'petugas' => ['petugas'],
        ];
    }

    public static function pimpinanRoleProvider(): array
    {
        return [
            'admin' => ['admin', true],
            'verifikator' => ['verifikator', false],
            'petugas' => ['petugas', false],
        ];
    }

    #[DataProvider('policyBackedNavigationProvider')]
    public function test_admin_only_resources_keep_navigation_and_access_in_sync_with_policies(
        string $resourceClass,
        string $modelClass,
        string $role,
        bool $expected
    ): void {
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $this->assertSame($expected, $user->can('viewAny', $modelClass), "Unexpected viewAny gate result for {$modelClass} and {$role}.");
        $this->assertSame($expected, $resourceClass::shouldRegisterNavigation(), "Unexpected navigation visibility for {$resourceClass} and {$role}.");
        $this->assertSame($expected, $resourceClass::canAccess(), "Unexpected canAccess result for {$resourceClass} and {$role}.");
    }

    public static function policyBackedNavigationProvider(): array
    {
        $resources = [
            'district' => [DistrictResource::class, District::class],
            'village' => [VillageResource::class, Village::class],
            'pimpinan' => [PimpinanResource::class, Pimpinan::class],
            'harga-patokan-mblb' => [HargaPatokanMblbResource::class, HargaPatokanMblb::class],
            'harga-patokan-sarang-walet' => [HargaPatokanSarangWaletResource::class, HargaPatokanSarangWalet::class],
            'harga-satuan-listrik' => [HargaSatuanListrikResource::class, HargaSatuanListrik::class],
        ];

        $roles = [
            'admin' => ['admin', true],
            'verifikator' => ['verifikator', false],
            'petugas' => ['petugas', false],
        ];

        $data = [];

        foreach ($resources as $resourceName => [$resourceClass, $modelClass]) {
            foreach ($roles as $roleName => [$role, $expected]) {
                $data["{$resourceName}-{$roleName}"] = [$resourceClass, $modelClass, $role, $expected];
            }
        }

        return $data;
    }

    #[DataProvider('pendaftaranOverrideProvider')]
    public function test_pendaftaran_resources_keep_intentional_business_override_over_broader_policies(
        string $resourceClass,
        string $modelClass,
        string $role,
        bool $policyAllowsViewAny,
        bool $resourceAllowsAccess,
        ?bool $resourceAllowsCreate = null
    ): void {
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $this->assertSame($policyAllowsViewAny, $user->can('viewAny', $modelClass), "Unexpected policy viewAny result for {$modelClass} and {$role}.");
        $this->assertSame($resourceAllowsAccess, $resourceClass::shouldRegisterNavigation(), "Unexpected navigation result for {$resourceClass} and {$role}.");
        $this->assertSame($resourceAllowsAccess, $resourceClass::canAccess(), "Unexpected canAccess result for {$resourceClass} and {$role}.");

        if ($resourceAllowsCreate !== null) {
            $this->assertSame($resourceAllowsCreate, $resourceClass::canCreate(), "Unexpected canCreate result for {$resourceClass} and {$role}.");
        }
    }

    public static function pendaftaranOverrideProvider(): array
    {
        return [
            'tax-object-admin' => [TaxObjectResource::class, TaxObject::class, 'admin', true, true, null],
            'tax-object-petugas' => [TaxObjectResource::class, TaxObject::class, 'petugas', true, true, null],
            'tax-object-verifikator' => [TaxObjectResource::class, TaxObject::class, 'verifikator', true, false, null],
            'daftar-wp-admin' => [DaftarWajibPajakResource::class, WajibPajak::class, 'admin', true, true, true],
            'daftar-wp-petugas' => [DaftarWajibPajakResource::class, WajibPajak::class, 'petugas', true, true, true],
            'daftar-wp-verifikator' => [DaftarWajibPajakResource::class, WajibPajak::class, 'verifikator', true, false, false],
        ];
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