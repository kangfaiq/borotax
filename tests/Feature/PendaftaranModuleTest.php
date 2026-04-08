<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Filament\Resources\DaftarWajibPajakResource;
use App\Filament\Resources\WajibPajakResource;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PendaftaranModuleTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('roleProvider')]
    public function test_pendaftaran_module_role_access_behaves_as_expected(
        string $role,
        bool $canRegisterWajibPajak,
        bool $canEditApprovedWajibPajak
    ): void
    {
        $this->seed([
            AdminUserSeeder::class,
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();

        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);
        $this->get(WajibPajakResource::getUrl('index'))->assertOk();
        $this->get(WajibPajakResource::getUrl('view', ['record' => $wajibPajak]))->assertOk()->assertSee((string) $wajibPajak->npwpd);

        $editResponse = $this->get(WajibPajakResource::getUrl('edit', ['record' => $wajibPajak]));
        $this->assertAccessExpectation($editResponse->getStatusCode(), $canEditApprovedWajibPajak, "wajib pajak edit for {$role}");

        $this->assertSame($canEditApprovedWajibPajak, $user->can('update', $wajibPajak), "Unexpected update gate result for wajib pajak and {$role}.");

        $daftarIndex = $this->get(DaftarWajibPajakResource::getUrl('index'));
        $this->assertAccessExpectation($daftarIndex->getStatusCode(), $canRegisterWajibPajak, "daftar wajib pajak index for {$role}");

        $daftarCreate = $this->get(DaftarWajibPajakResource::getUrl('create'));
        $this->assertAccessExpectation($daftarCreate->getStatusCode(), $canRegisterWajibPajak, "daftar wajib pajak create for {$role}");
    }

    public static function roleProvider(): array
    {
        return [
            'admin' => ['admin', true, true],
            'petugas' => ['petugas', true, true],
            'verifikator' => ['verifikator', false, false],
        ];
    }

    public function test_wajib_pajak_list_and_detail_show_login_source_badges(): void
    {
        $this->seed([
            AdminUserSeeder::class,
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $generatedWp = $this->createApprovedWajibPajakFixture([], [
            'email' => 'siti.kadipaten.7890.abcd@generated.local',
        ]);
        $regularWp = $this->createApprovedWajibPajakFixture([], [
            'email' => 'wajib.pajak@example.test',
        ]);
        $admin = $this->createAdminPanelUser('admin');

        $this->actingAs($admin);

        $this->get(DaftarWajibPajakResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Username Otomatis')
            ->assertSee('Email WP');

        $this->get(DaftarWajibPajakResource::getUrl('view', ['record' => $generatedWp]))
            ->assertOk()
            ->assertSee('Username Otomatis')
            ->assertSee('Gunakan username login ini saat menyampaikan akun ke wajib pajak.');

        $this->get(WajibPajakResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Username Otomatis')
            ->assertSee('Email WP');

        $this->get(WajibPajakResource::getUrl('view', ['record' => $regularWp]))
            ->assertOk()
            ->assertSee('Email WP')
            ->assertSee('Email di atas adalah email milik wajib pajak.');
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