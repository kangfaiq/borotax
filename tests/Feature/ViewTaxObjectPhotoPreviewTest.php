<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Tax\Models\TaxObject;
use App\Filament\Resources\TaxObjectResource;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ViewTaxObjectPhotoPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_tax_object_page_renders_stored_photo_in_readonly_mode(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('foto-objek-pajak/sample.jpg', 'fake-image-content');

        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $taxObject = $this->createTaxObjectFixture(
            $this->createApprovedWajibPajakFixture(),
            '41102',
            [
                'foto_objek_path' => 'foto-objek-pajak/sample.jpg',
            ],
        );

        $this->actingAs($this->createAdminPanelUser('admin'));

        $response = $this->get(TaxObjectResource::getUrl('view', ['record' => $taxObject]));

        $response->assertOk()
            ->assertSee('File tersimpan')
            ->assertSee('foto-objek-pajak/sample.jpg', false)
            ->assertDontSee('Ganti file')
            ->assertDontSee('Klik untuk upload');
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