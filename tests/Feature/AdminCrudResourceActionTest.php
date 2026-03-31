<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\CMS\Models\Destination;
use App\Domain\CMS\Models\News;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Region\Models\Village;
use App\Filament\Resources\DestinationResource;
use App\Filament\Resources\DestinationResource\Pages\ListDestinations;
use App\Filament\Resources\DistrictResource;
use App\Filament\Resources\DistrictResource\Pages\ListDistricts;
use App\Filament\Resources\JenisPajakResource;
use App\Filament\Resources\JenisPajakResource\Pages\ListJenisPajaks;
use App\Filament\Resources\NewsResource;
use App\Filament\Resources\NewsResource\Pages\ListNews;
use App\Filament\Resources\SubJenisPajakResource;
use App\Filament\Resources\SubJenisPajakResource\Pages\ListSubJenisPajaks;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\VillageResource;
use App\Filament\Resources\VillageResource\Pages\ListVillages;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminCrudResourceActionTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('resourceProvider')]
    public function test_admin_only_crud_resources_expose_expected_actions_per_role(
        string $resourceClass,
        string $listPageClass,
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
            'user' => [UserResource::class, ListUsers::class, 'user', true, true],
            'news' => [NewsResource::class, ListNews::class, 'news', true, true],
            'destination' => [DestinationResource::class, ListDestinations::class, 'destination', true, true],
            'jenis-pajak' => [JenisPajakResource::class, ListJenisPajaks::class, 'jenis_pajak', true, true],
            'sub-jenis-pajak' => [SubJenisPajakResource::class, ListSubJenisPajaks::class, 'sub_jenis_pajak', true, true],
            'district' => [DistrictResource::class, ListDistricts::class, 'district', false, true],
            'village' => [VillageResource::class, ListVillages::class, 'village', false, true],
        ];

        $roles = [
            'admin' => ['admin', true],
            'petugas' => ['petugas', false],
            'verifikator' => ['verifikator', false],
        ];

        $data = [];

        foreach ($cases as $caseName => [$resourceClass, $listPageClass, $fixtureKey, $hasRowDelete, $hasBulkDelete]) {
            foreach ($roles as $roleName => [$role, $isAllowed]) {
                $data["{$caseName}-{$roleName}"] = [
                    $resourceClass,
                    $listPageClass,
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
            'user' => $this->makeManagedUser(),
            'news' => $this->makeNews(),
            'destination' => $this->makeDestination(),
            'jenis_pajak' => $this->makeJenisPajak(),
            'sub_jenis_pajak' => $this->makeSubJenisPajak(),
            'district' => $this->makeRegionFixture()['district'],
            'village' => $this->makeRegionFixture()['village'],
        };
    }

    private function makeManagedUser(): User
    {
        return User::create([
            'name' => 'Managed User',
            'nama_lengkap' => 'Managed User',
            'email' => 'managed-' . Str::random(6) . '@example.test',
            'password' => Hash::make('password'),
            'role' => 'petugas',
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
        ]);
    }

    private function makeNews(): News
    {
        return News::create([
            'title' => 'Berita Uji ' . Str::random(4),
            'excerpt' => 'Ringkasan berita uji.',
            'content' => 'Konten berita uji.',
            'image_url' => 'news/sample.jpg',
            'published_at' => now(),
            'category' => 'pengumuman',
            'author' => 'Admin QA',
            'view_count' => 0,
            'is_featured' => false,
        ]);
    }

    private function makeDestination(): Destination
    {
        return Destination::create([
            'name' => 'Destinasi Uji ' . Str::random(4),
            'description' => 'Deskripsi destinasi uji.',
            'address' => 'Jl. Wisata No. 1',
            'category' => 'wisata',
            'image_url' => 'destinations/sample.jpg',
            'rating' => 4.5,
            'review_count' => 10,
            'price_range' => 'gratis',
            'facilities' => ['parkir'],
            'phone' => '081234567890',
            'website' => 'https://example.test',
            'latitude' => -7.15000000,
            'longitude' => 111.88000000,
            'is_featured' => false,
        ]);
    }

    private function makeJenisPajak(): JenisPajak
    {
        $this->seed(JenisPajakSeeder::class);

        return JenisPajak::firstOrFail();
    }

    private function makeSubJenisPajak(): SubJenisPajak
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        return SubJenisPajak::firstOrFail();
    }

    private function makeRegionFixture(): array
    {
        $province = Province::firstOrCreate([
            'code' => '35',
        ], [
            'name' => 'Jawa Timur',
        ]);

        $regency = Regency::firstOrCreate([
            'code' => '35.22',
        ], [
            'province_code' => $province->code,
            'name' => 'Bojonegoro',
        ]);

        $district = District::firstOrCreate([
            'code' => '35.22.01',
        ], [
            'regency_code' => $regency->code,
            'name' => 'Bojonegoro Kota',
        ]);

        $village = Village::firstOrCreate([
            'code' => '35.22.01.1001',
        ], [
            'district_code' => $district->code,
            'name' => 'Kadipaten',
            'postal_code' => '62111',
        ]);

        return [
            'district' => $district,
            'village' => $village,
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