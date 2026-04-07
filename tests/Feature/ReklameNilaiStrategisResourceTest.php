<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Reklame\Models\ReklameNilaiStrategis;
use App\Filament\Resources\ReklameNilaiStrategisResource;
use App\Filament\Resources\ReklameNilaiStrategisResource\Pages\ListReklameNilaiStrategis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReklameNilaiStrategisResourceTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('roleProvider')]
    public function test_reklame_nilai_strategis_resource_is_admin_only(string $role, bool $isAllowed): void
    {
        $record = ReklameNilaiStrategis::create([
            'kelas_kelompok' => 'A',
            'luas_min' => 10,
            'luas_max' => 24.99,
            'tarif_per_tahun' => 5000000,
            'tarif_per_bulan' => 500000,
            'is_active' => true,
            'berlaku_mulai' => '2026-01-01',
            'berlaku_sampai' => null,
        ]);
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $indexResponse = $this->get(ReklameNilaiStrategisResource::getUrl('index'));
        $createResponse = $this->get(ReklameNilaiStrategisResource::getUrl('create'));
        $editResponse = $this->get(ReklameNilaiStrategisResource::getUrl('edit', ['record' => $record]));

        $this->assertAccessExpectation($indexResponse->getStatusCode(), $isAllowed, "reklame nilai strategis index for {$role}");
        $this->assertAccessExpectation($createResponse->getStatusCode(), $isAllowed, "reklame nilai strategis create for {$role}");
        $this->assertAccessExpectation($editResponse->getStatusCode(), $isAllowed, "reklame nilai strategis edit for {$role}");

        $this->assertSame($isAllowed, $user->can('viewAny', ReklameNilaiStrategis::class), "Unexpected viewAny gate result for reklame nilai strategis and {$role}.");
        $this->assertSame($isAllowed, $user->can('create', ReklameNilaiStrategis::class), "Unexpected create gate result for reklame nilai strategis and {$role}.");
        $this->assertSame($isAllowed, $user->can('view', $record), "Unexpected view gate result for reklame nilai strategis and {$role}.");
        $this->assertSame($isAllowed, $user->can('update', $record), "Unexpected update gate result for reklame nilai strategis and {$role}.");
        $this->assertSame($isAllowed, $user->can('delete', $record), "Unexpected delete gate result for reklame nilai strategis and {$role}.");

        if (! $isAllowed) {
            return;
        }

        Livewire::test(ListReklameNilaiStrategis::class)
            ->assertCanSeeTableRecords([$record])
            ->assertTableActionVisible('edit', $record)
            ->assertTableActionVisible('delete', $record)
            ->assertTableBulkActionVisible('delete');
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