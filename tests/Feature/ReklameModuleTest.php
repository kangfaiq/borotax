<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Reklame\Models\ReklameRequest;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\AsetReklamePemkabResource;
use App\Filament\Resources\PermohonanSewaReklameResource;
use App\Filament\Resources\ReklameRequestResource;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\AsetReklamePemkabSeeder;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
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
        bool $canAccessPortalReklame,
        bool $canAccessPermohonan
    ): void
    {
        $this->seed([
            AdminUserSeeder::class,
            AsetReklamePemkabSeeder::class,
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $aset = AsetReklamePemkab::where('kode_aset', 'NB001')->firstOrFail();
        $fixtures = $this->seedPermohonanSewaReklameFixtures();
        $permohonan = $fixtures['ditolak'];
        $reklameRequest = $this->createPortalReklameRequest();

        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);
        $this->get(AsetReklamePemkabResource::getUrl('index'))->assertOk();

        $asetEdit = $this->get(AsetReklamePemkabResource::getUrl('edit', ['record' => $aset]));
        $this->assertAccessExpectation($asetEdit->getStatusCode(), $canEditAset, "aset reklame edit for {$role}");
        if ($canEditAset) {
            $asetEdit->assertSee('NB001');
        }

        $portalReklameIndex = $this->get(ReklameRequestResource::getUrl('index'));
        $this->assertAccessExpectation($portalReklameIndex->getStatusCode(), $canAccessPortalReklame, "portal reklame index for {$role}");
        if ($canAccessPortalReklame) {
            $portalReklameIndex->assertSee((string) $reklameRequest->reklameObject->nama_objek_pajak);
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

    #[DataProvider('dashboardShortcutRoleProvider')]
    public function test_dashboard_shortcuts_for_reklame_follow_role_rules(
        string $role,
        bool $shouldSeePortalReklameShortcut
    ): void {
        $this->seed([
            AdminUserSeeder::class,
            AsetReklamePemkabSeeder::class,
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $this->createPortalReklameRequest();
        $this->seedPermohonanSewaReklameFixtures();

        $this->actingAs($this->createAdminPanelUser($role));

        $response = $this->get(Dashboard::getUrl());
        $response->assertOk();

        if ($shouldSeePortalReklameShortcut) {
            $response->assertSee('Pengajuan Reklame Portal');
            $response->assertSee('/admin/reklame-requests');

            return;
        }

        $response->assertDontSee('/admin/reklame-requests');
    }

    public static function roleProvider(): array
    {
        return [
            'admin' => ['admin', true, true, false],
            'petugas' => ['petugas', false, true, true],
            'verifikator' => ['verifikator', false, false, false],
        ];
    }

    public static function dashboardShortcutRoleProvider(): array
    {
        return [
            'admin' => ['admin', true],
            'petugas' => ['petugas', true],
            'verifikator' => ['verifikator', false],
        ];
    }

    private function createPortalReklameRequest(): ReklameRequest
    {
        $jenisPajak = \App\Domain\Master\Models\JenisPajak::where('kode', '41104')->firstOrFail();
        $subJenisPajak = \App\Domain\Master\Models\SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();

        $reklameObject = ReklameObject::create([
            'nik' => '3522011234567890',
            'nama_objek_pajak' => 'Reklame Portal Test',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000001',
            'nopd' => 1001,
            'alamat_objek' => 'Jl. Ahmad Yani No. 1',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tarif_persen' => 25,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'bentuk' => 'persegi',
            'panjang' => 4,
            'lebar' => 2,
            'jumlah_muka' => 1,
            'status' => 'aktif',
            'kelompok_lokasi' => 'A',
        ]);

        $portalUser = User::create([
            'name' => 'Portal Reklame User',
            'nama_lengkap' => 'Portal Reklame User',
            'email' => sprintf('portal-reklame-%s@example.test', Str::random(8)),
            'password' => Hash::make('password'),
            'nik' => '3522011234567890',
            'alamat' => 'Jl. Ahmad Yani No. 1',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        return ReklameRequest::create([
            'tax_object_id' => $reklameObject->id,
            'user_id' => $portalUser->id,
            'user_nik' => '3522011234567890',
            'user_name' => 'Portal Reklame User',
            'tanggal_pengajuan' => now()->subDay(),
            'durasi_perpanjangan_hari' => 30,
            'catatan_pengajuan' => 'Pengajuan reklame dari portal.',
            'status' => 'diajukan',
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