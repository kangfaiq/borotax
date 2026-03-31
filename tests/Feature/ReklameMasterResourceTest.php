<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Domain\Reklame\Models\KelompokLokasiJalan;
use App\Filament\Resources\HargaPatokanReklameResource;
use App\Filament\Resources\KelompokLokasiJalanResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReklameMasterResourceTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('roleProvider')]
    public function test_reklame_master_resources_are_admin_only(string $role, bool $isAllowed): void
    {
        $this->seedReklameTaxReferences();

        $hargaPatokan = HargaPatokanReklame::where('kode', 'RKL_NEON_BOX')->firstOrFail();
        $kelompokLokasi = KelompokLokasiJalan::query()->firstOrFail();
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $hargaIndex = $this->get(HargaPatokanReklameResource::getUrl('index'));
        $this->assertAccessExpectation($hargaIndex->getStatusCode(), $isAllowed, "harga patokan reklame index for {$role}");
        if ($isAllowed) {
            $hargaIndex->assertSee('RKL_NEON_BOX');
        }

        $hargaCreate = $this->get(HargaPatokanReklameResource::getUrl('create'));
        $this->assertAccessExpectation($hargaCreate->getStatusCode(), $isAllowed, "harga patokan reklame create for {$role}");

        $hargaEdit = $this->get(HargaPatokanReklameResource::getUrl('edit', ['record' => $hargaPatokan]));
        $this->assertAccessExpectation($hargaEdit->getStatusCode(), $isAllowed, "harga patokan reklame edit for {$role}");

        $lokasiIndex = $this->get(KelompokLokasiJalanResource::getUrl('index'));
        $this->assertAccessExpectation($lokasiIndex->getStatusCode(), $isAllowed, "kelompok lokasi jalan index for {$role}");
        if ($isAllowed) {
            $lokasiIndex->assertSee((string) $kelompokLokasi->nama_jalan);
        }

        $lokasiCreate = $this->get(KelompokLokasiJalanResource::getUrl('create'));
        $this->assertAccessExpectation($lokasiCreate->getStatusCode(), $isAllowed, "kelompok lokasi jalan create for {$role}");

        $lokasiEdit = $this->get(KelompokLokasiJalanResource::getUrl('edit', ['record' => $kelompokLokasi]));
        $this->assertAccessExpectation($lokasiEdit->getStatusCode(), $isAllowed, "kelompok lokasi jalan edit for {$role}");
    }

    public static function roleProvider(): array
    {
        return [
            'admin' => ['admin', true],
            'petugas' => ['petugas', false],
            'verifikator' => ['verifikator', false],
        ];
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