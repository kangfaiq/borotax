<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Filament\Resources\TaxObjectResource\Pages\CreateTaxObject;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class TaxObjectRestaurantOpdToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_katering_sub_jenis_shows_opd_toggle(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $petugas = $this->createAdminPanelUser('petugas');
        $restoran = JenisPajak::where('kode', '41102')->firstOrFail();
        $katering = SubJenisPajak::where('kode', 'PBJT_KATERING')->firstOrFail();

        $this->actingAs($petugas);

        Livewire::test(CreateTaxObject::class)
            ->set('data.jenis_pajak_id', $restoran->id)
            ->set('data.sub_jenis_pajak_id', $katering->id)
            ->assertSee('Untuk OPD (Organisasi Perangkat Daerah)');
    }

    public function test_reklame_tax_object_form_shows_harga_patokan_detail_after_selecting_sub_jenis(): void
    {
        $this->seedReklameTaxReferences();

        $petugas = $this->createAdminPanelUser('petugas');
        $reklameTetap = SubJenisPajak::where('kode', 'REKLAME_TETAP')->firstOrFail();

        $this->actingAs($petugas);

        Livewire::test(CreateTaxObject::class)
            ->set('data.tanggal_daftar', '2026-06-01')
            ->set('data.jenis_pajak_id', $reklameTetap->jenis_pajak_id)
            ->set('data.sub_jenis_pajak_id', $reklameTetap->id)
            ->assertSee('Rincian Harga Patokan Reklame')
            ->assertSee('Neon Box')
            ->assertSee('Lokasi / Jalan Penempatan')
            ->assertSee('Jalan Panglima Sudirman');
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