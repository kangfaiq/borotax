<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Region\Models\Village;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Filament\Resources\DaftarWajibPajakResource;
use App\Filament\Resources\DaftarWajibPajakResource\Pages\CreateDaftarWajibPajak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PendaftaranWorkflowTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('registrationRoleProvider')]
    public function test_admin_and_petugas_can_register_wajib_pajak_from_filament_form(string $role): void
    {
        $this->seedMinimalRegionFixtures();

        $petugas = $this->createAdminPanelUser($role);
        $email = sprintf('wp-%s@example.test', Str::lower($role));

        $this->actingAs($petugas);

        Livewire::test(CreateDaftarWajibPajak::class)
            ->fillForm([
                'nik' => '3522011234567890',
                'nama_lengkap' => 'Wajib Pajak Baru',
                'alamat' => 'Jl. Panglima Sudirman No. 1',
                'no_whatsapp' => '081234567890',
                'no_telp' => '(0353) 881826',
                'email' => $email,
                'asal_wilayah' => 'bojonegoro',
                'district_code' => '35.22.01',
                'village_code' => '35.22.01.2001',
                'tipe_wajib_pajak' => 'perorangan',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(DaftarWajibPajakResource::getUrl('index'));

        $user = User::where('email', $email)->firstOrFail();
        $wajibPajak = WajibPajak::where('user_id', $user->id)->firstOrFail();

        $this->assertSame('wajibPajak', $user->role);
        $this->assertTrue((bool) $user->must_change_password);
        $this->assertSame('verified', $user->status);

        $this->assertSame('Wajib Pajak Baru', $wajibPajak->nama_lengkap);
        $this->assertSame('3522011234567890', $wajibPajak->nik);
        $this->assertSame('disetujui', $wajibPajak->status);
        $this->assertSame('35', $wajibPajak->province_code);
        $this->assertSame('35.22', $wajibPajak->regency_code);
        $this->assertSame('35.22.01', $wajibPajak->district_code);
        $this->assertSame('35.22.01.2001', $wajibPajak->village_code);
        $this->assertSame($petugas->id, $wajibPajak->petugas_id);
        $this->assertStringStartsWith('P1', $wajibPajak->npwpd);
        $this->assertNotNull($wajibPajak->tanggal_daftar);
        $this->assertNotNull($wajibPajak->tanggal_verifikasi);
    }

    public static function registrationRoleProvider(): array
    {
        return [
            'admin' => ['admin'],
            'petugas' => ['petugas'],
        ];
    }

    private function seedMinimalRegionFixtures(): void
    {
        Province::create([
            'code' => '35',
            'name' => 'Jawa Timur',
        ]);

        Regency::create([
            'province_code' => '35',
            'code' => '35.22',
            'name' => 'Kabupaten Bojonegoro',
        ]);

        District::create([
            'regency_code' => '35.22',
            'code' => '35.22.01',
            'name' => 'Bojonegoro',
        ]);

        Village::create([
            'district_code' => '35.22.01',
            'code' => '35.22.01.2001',
            'name' => 'Kadipaten',
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
}