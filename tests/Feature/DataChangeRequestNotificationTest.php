<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Models\DataChangeRequest;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\ReklameSubJenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DataChangeRequestNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_request_notifies_admin_and_verifikator(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
            ReklameSubJenisPajakSeeder::class,
        ]);

        $admin = $this->createAdminPanelUser('admin');
        $verifikator = $this->createAdminPanelUser('verifikator');
        $petugas = $this->createAdminPanelUser('petugas');

        $taxObject = $this->createTaxObjectFixture(
            $this->createApprovedWajibPajakFixture(),
            '41104',
            ['nama_objek_pajak' => 'Lama'],
        );

        $this->actingAs($petugas);

        DataChangeRequest::createRequest(
            entity: $taxObject,
            fieldChanges: ['nama_objek_pajak' => 'Nama Baru'],
            alasanPerubahan: 'Update nama',
        );

        $this->assertSame(1, $admin->fresh()->notifications()->count(), 'Admin harus menerima notifikasi.');
        $this->assertSame(1, $verifikator->fresh()->notifications()->count(), 'Verifikator harus menerima notifikasi.');
        $this->assertSame(0, $petugas->fresh()->notifications()->count(), 'Petugas (pengaju) tidak perlu notifikasi.');

        $this->assertStringContainsString(
            'Permintaan Perubahan Data',
            (string) $admin->fresh()->notifications()->first()->data['title'] ?? '',
        );
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
}
