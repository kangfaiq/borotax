<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Filament\Resources\WajibPajakResource\Pages\ListWajibPajaks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class WajibPajakVerificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifikator_can_approve_pending_wajib_pajak_and_generate_npwpd(): void
    {
        $wajibPajak = $this->createPendingWajibPajak('perorangan');
        $verifikator = $this->createAdminPanelUser('verifikator');

        $this->actingAs($verifikator);

        Livewire::test(ListWajibPajaks::class)
            ->assertCanSeeTableRecords([$wajibPajak])
            ->callTableAction('approve', $wajibPajak)
            ->assertHasNoTableActionErrors();

        $wajibPajak->refresh();

        $this->assertSame('disetujui', $wajibPajak->status);
        $this->assertNotNull($wajibPajak->npwpd);
        $this->assertStringStartsWith('P1', $wajibPajak->npwpd);
        $this->assertNotNull($wajibPajak->tanggal_verifikasi);
        $this->assertSame($verifikator->id, $wajibPajak->petugas_id);
        $this->assertSame($verifikator->nama_lengkap, $wajibPajak->petugas_nama);
    }

    public function test_verifikator_can_reject_pending_wajib_pajak_with_reason(): void
    {
        $wajibPajak = $this->createPendingWajibPajak('perusahaan');
        $verifikator = $this->createAdminPanelUser('verifikator');
        $alasanPenolakan = 'Dokumen identitas tidak sesuai dengan data pendaftaran.';

        $this->actingAs($verifikator);

        Livewire::test(ListWajibPajaks::class)
            ->assertCanSeeTableRecords([$wajibPajak])
            ->mountTableAction('reject', $wajibPajak)
            ->set('mountedActions.0.data.catatan_verifikasi', $alasanPenolakan)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $wajibPajak->refresh();

        $this->assertSame('ditolak', $wajibPajak->status);
        $this->assertNull($wajibPajak->npwpd);
        $this->assertNotNull($wajibPajak->tanggal_verifikasi);
        $this->assertSame($verifikator->id, $wajibPajak->petugas_id);
        $this->assertSame($verifikator->nama_lengkap, $wajibPajak->petugas_nama);
        $this->assertSame($alasanPenolakan, $wajibPajak->catatan_verifikasi);
    }

    public function test_verifikator_can_request_revision_for_pending_wajib_pajak(): void
    {
        $wajibPajak = $this->createPendingWajibPajak('perorangan');
        $verifikator = $this->createAdminPanelUser('verifikator');
        $catatanPerbaikan = 'Foto KTP buram dan alamat domisili perlu dilengkapi.';

        $this->actingAs($verifikator);

        Livewire::test(ListWajibPajaks::class)
            ->assertCanSeeTableRecords([$wajibPajak])
            ->mountTableAction('requestRevision', $wajibPajak)
            ->set('mountedActions.0.data.catatan_verifikasi', $catatanPerbaikan)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $wajibPajak->refresh();

        $this->assertSame('perluPerbaikan', $wajibPajak->status);
        $this->assertNull($wajibPajak->npwpd);
        $this->assertNotNull($wajibPajak->tanggal_verifikasi);
        $this->assertSame($verifikator->id, $wajibPajak->petugas_id);
        $this->assertSame($verifikator->nama_lengkap, $wajibPajak->petugas_nama);
        $this->assertSame($catatanPerbaikan, $wajibPajak->catatan_verifikasi);
    }

    private function createPendingWajibPajak(string $tipe): WajibPajak
    {
        $user = User::create([
            'name' => 'Portal User ' . Str::random(6),
            'email' => sprintf('portal-%s@example.test', Str::random(8)),
            'password' => Hash::make('password'),
            'nik' => '3522011234567890',
            'nama_lengkap' => 'Portal User',
            'alamat' => 'Jl. Ahmad Yani No. 10',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        return WajibPajak::create([
            'user_id' => $user->id,
            'nik' => '3522011234567890',
            'nama_lengkap' => $tipe === 'perusahaan' ? 'PT Uji Verifikasi' : 'Budi Verifikasi',
            'alamat' => 'Jl. Veteran No. 12',
            'asal_wilayah' => 'bojonegoro',
            'district_code' => null,
            'village_code' => null,
            'tipe_wajib_pajak' => $tipe,
            'nama_perusahaan' => $tipe === 'perusahaan' ? 'PT Uji Verifikasi' : null,
            'status' => 'menungguVerifikasi',
            'tanggal_daftar' => now()->subDay(),
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