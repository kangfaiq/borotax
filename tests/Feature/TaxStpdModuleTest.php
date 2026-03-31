<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\Tax\Models\Tax;
use App\Enums\TaxStatus;
use App\Filament\Pages\BuatStpd;
use App\Filament\Resources\StpdManualResource;
use App\Filament\Resources\TaxResource;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TaxStpdModuleTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('roleProvider')]
    public function test_tax_and_stpd_module_role_access_and_data_visibility_behave_as_expected(
        string $role,
        bool $canAccessStpdManual
    ): void
    {
        $this->seed([
            AdminUserSeeder::class,
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $this->seedPimpinanReferences();
        $user = $this->createAdminPanelUser($role);
        $taxObject = $this->createTaxObjectFixture($this->createApprovedWajibPajakFixture());
        $tax = $this->createTaxFixture($taxObject, overrides: [
            'status' => TaxStatus::Verified,
            'payment_expired_at' => now()->subDays(45),
            'masa_pajak_bulan' => 11,
            'masa_pajak_tahun' => 2025,
        ]);

        StpdManual::create([
            'tax_id' => $tax->id,
            'tipe' => 'pokok_sanksi',
            'status' => 'draft',
            'bulan_terlambat' => 2,
            'sanksi_dihitung' => 25_000,
            'pokok_belum_dibayar' => (float) $tax->amount,
            'catatan_petugas' => 'Fixture STPD manual.',
            'petugas_id' => $user->id ?? null,
            'petugas_nama' => 'Fixture Petugas',
            'tanggal_buat' => now()->subHour(),
        ]);

        $this->actingAs($user);
        $this->get(TaxResource::getUrl('index'))->assertOk();

        $stpdManualIndex = $this->get(StpdManualResource::getUrl('index'));
        $this->assertAccessExpectation($stpdManualIndex->getStatusCode(), $canAccessStpdManual, "stpd manual index for {$role}");

        $this->get(BuatStpd::getUrl())->assertOk();
    }

    public static function roleProvider(): array
    {
        return [
            'admin' => ['admin', true],
            'petugas' => ['petugas', false],
            'verifikator' => ['verifikator', true],
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