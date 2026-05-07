<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Models\DataChangeRequest;
use App\Filament\Resources\DataChangeRequestResource;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\ReklameSubJenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DataChangeRequestViewPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_page_renders_when_field_changes_payload_was_not_encrypted(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
            ReklameSubJenisPajakSeeder::class,
        ]);

        $taxObject = $this->createTaxObjectFixture(
            $this->createApprovedWajibPajakFixture(),
            '41104',
            ['bentuk' => 'persegi'],
        );

        $admin = $this->createAdminPanelUser('admin');
        $this->actingAs($admin);

        $request = DataChangeRequest::createRequest(
            entity: $taxObject,
            fieldChanges: ['nama_objek_pajak' => 'Nama Baru'],
            alasanPerubahan: 'Test legacy payload',
        );

        if ($request->requested_by === null) {
            $request->forceFill(['requested_by' => $admin->id])->save();
        }

        DB::table('data_change_requests')
            ->where('id', $request->id)
            ->update([
                'field_changes' => json_encode([
                    'nama_objek_pajak' => ['old' => 'Lama', 'new' => 'Nama Baru'],
                ]),
            ]);

        $response = $this->get(DataChangeRequestResource::getUrl('view', ['record' => $request->id]));

        $response->assertOk()
            ->assertSee('Nama Baru')
            ->assertSee('Lama')
            ->assertSee('Riwayat Verifikasi');
    }

    public function test_view_page_renders_for_normal_encrypted_payload(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
            ReklameSubJenisPajakSeeder::class,
        ]);

        $taxObject = $this->createTaxObjectFixture(
            $this->createApprovedWajibPajakFixture(),
            '41104',
            ['nama_objek_pajak' => 'Lama'],
        );

        $admin = $this->createAdminPanelUser('admin');
        $this->actingAs($admin);

        $request = DataChangeRequest::createRequest(
            entity: $taxObject,
            fieldChanges: ['nama_objek_pajak' => 'Nama Baru'],
            alasanPerubahan: 'Test',
        );

        if ($request->requested_by === null) {
            $request->forceFill(['requested_by' => $admin->id])->save();
        }

        $response = $this->get(DataChangeRequestResource::getUrl('view', ['record' => $request->id]));

        $response->assertOk()
            ->assertSee('Nama Baru')
            ->assertSee('Riwayat Verifikasi');
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
