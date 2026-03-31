<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Filament\Resources\AsetReklamePemkabResource;
use App\Filament\Resources\PermohonanSewaReklameResource;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\AsetReklamePemkabSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReklameModuleTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('roleProvider')]
    public function test_reklame_module_role_access_and_record_pages_behave_as_expected(
        string $role,
        bool $canEditAset,
        bool $canAccessPermohonan
    ): void
    {
        $this->seed([
            AdminUserSeeder::class,
            AsetReklamePemkabSeeder::class,
        ]);

        $aset = AsetReklamePemkab::where('kode_aset', 'NB001')->firstOrFail();
        $fixtures = $this->seedPermohonanSewaReklameFixtures();
        $permohonan = $fixtures['ditolak'];

        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);
        $this->get(AsetReklamePemkabResource::getUrl('index'))->assertOk();

        $asetEdit = $this->get(AsetReklamePemkabResource::getUrl('edit', ['record' => $aset]));
        $this->assertAccessExpectation($asetEdit->getStatusCode(), $canEditAset, "aset reklame edit for {$role}");
        if ($canEditAset) {
            $asetEdit->assertSee('NB001');
        }

        $permohonanIndex = $this->get(PermohonanSewaReklameResource::getUrl('index'));
        $this->assertAccessExpectation($permohonanIndex->getStatusCode(), $canAccessPermohonan, "permohonan sewa index for {$role}");
        if ($canAccessPermohonan) {
            $permohonanIndex->assertSee((string) $permohonan->nomor_tiket);
        }

        $permohonanView = $this->get(PermohonanSewaReklameResource::getUrl('view', ['record' => $permohonan]));
        $this->assertAccessExpectation($permohonanView->getStatusCode(), $canAccessPermohonan, "permohonan sewa view for {$role}");
        if ($canAccessPermohonan) {
            $permohonanView->assertSee((string) $permohonan->nomor_tiket);
        }
    }

    public static function roleProvider(): array
    {
        return [
            'admin' => ['admin', true, false],
            'petugas' => ['petugas', true, true],
            'verifikator' => ['verifikator', false, false],
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