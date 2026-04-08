<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Region\Models\Village;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Filament\Resources\AsetReklamePemkabResource\Pages\EditAsetReklamePemkab;
use App\Filament\Resources\PermohonanSewaReklameResource\Pages\ListPermohonanSewaReklame;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\AsetReklamePemkabSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReklameWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_can_process_permohonan_sewa_reklame_from_table_action(): void
    {
        $this->seed([
            AdminUserSeeder::class,
            AsetReklamePemkabSeeder::class,
        ]);
        $this->seedPermohonanSewaReklameFixtures();

        $petugas = $this->createAdminPanelUser('petugas');
        $permohonan = PermohonanSewaReklame::where('status', 'diajukan')->oldest('tanggal_pengajuan')->firstOrFail();

        $this->actingAs($petugas);

        Livewire::test(ListPermohonanSewaReklame::class)
            ->assertCanSeeTableRecords([$permohonan])
            ->callTableAction('proses', $permohonan)
            ->assertHasNoTableActionErrors();

        $permohonan->refresh();

        $this->assertSame('diproses', $permohonan->status);
        $this->assertSame($petugas->id, $permohonan->petugas_id);
        $this->assertSame($petugas->nama_lengkap, $permohonan->petugas_nama);
        $this->assertNotNull($permohonan->tanggal_diproses);
    }

    public function test_petugas_can_link_existing_npwpd_from_permohonan_action(): void
    {
        $this->seed([
            AdminUserSeeder::class,
            AsetReklamePemkabSeeder::class,
        ]);
        $this->seedPermohonanSewaReklameFixtures();

        $petugas = $this->createAdminPanelUser('petugas');
        $permohonan = PermohonanSewaReklame::where('status', 'diproses')
            ->whereNull('npwpd')
            ->oldest('tanggal_pengajuan')
            ->firstOrFail();
        $wajibPajak = $this->createApprovedWajibPajakForNik($permohonan->nik, 'P10000000999');

        $this->actingAs($petugas);

        Livewire::test(ListPermohonanSewaReklame::class)
            ->assertCanSeeTableRecords([$permohonan])
            ->callTableAction('cek_npwpd', $permohonan, [
                'npwpd_cari' => $wajibPajak->npwpd,
            ])
            ->assertHasNoTableActionErrors();

        $permohonan->refresh();

        $this->assertSame($wajibPajak->npwpd, $permohonan->npwpd);
    }

    public function test_petugas_can_create_new_npwpd_from_permohonan_action(): void
    {
        $this->seed([
            AdminUserSeeder::class,
            AsetReklamePemkabSeeder::class,
        ]);
        $this->seedPermohonanSewaReklameFixtures();

        $petugas = $this->createAdminPanelUser('petugas');
        $permohonan = PermohonanSewaReklame::where('status', 'diproses')
            ->whereNull('npwpd')
            ->oldest('tanggal_pengajuan')
            ->firstOrFail();
        $region = $this->createRegionFixture();

        $this->actingAs($petugas);

        Livewire::test(ListPermohonanSewaReklame::class)
            ->assertCanSeeTableRecords([$permohonan])
            ->callTableAction('buat_npwpd', $permohonan, [
                'nik' => $permohonan->nik,
                'nama_lengkap' => $permohonan->nama,
                'alamat' => $permohonan->alamat,
                'email' => $permohonan->email,
                'tipe_wajib_pajak' => 'perusahaan',
                'nama_perusahaan' => $permohonan->nama_usaha,
                'asal_wilayah' => 'bojonegoro',
                'province_code' => $region['province']->code,
                'regency_code' => $region['regency']->code,
                'district_code' => $region['district']->code,
                'village_code' => $region['village']->code,
            ])
            ->assertHasNoTableActionErrors();

        $permohonan->refresh();
        $wajibPajak = WajibPajak::where('nik_hash', WajibPajak::generateHash($permohonan->nik))->first();

        $this->assertNotNull($permohonan->npwpd);
        $this->assertNotNull($wajibPajak);
        $this->assertSame($permohonan->npwpd, $wajibPajak->npwpd);
        $this->assertSame('disetujui', $wajibPajak->status);
        $this->assertSame('perusahaan', $wajibPajak->tipe_wajib_pajak);
        $this->assertSame($petugas->id, $wajibPajak->petugas_id);
        $this->assertSame($petugas->nama_lengkap, $wajibPajak->petugas_nama);
        $this->assertDatabaseHas('users', [
            'id' => $wajibPajak->user_id,
            'role' => 'user',
        ]);

        $user = User::findOrFail($wajibPajak->user_id);
        $this->assertSame(str($permohonan->email)->lower()->value(), $user->email);
    }

    public function test_petugas_can_create_npwpd_with_generated_login_email_when_email_is_blank(): void
    {
        $this->seed([
            AdminUserSeeder::class,
            AsetReklamePemkabSeeder::class,
        ]);
        $this->seedPermohonanSewaReklameFixtures();

        $petugas = $this->createAdminPanelUser('petugas');
        $permohonan = PermohonanSewaReklame::where('status', 'diproses')
            ->whereNull('npwpd')
            ->oldest('tanggal_pengajuan')
            ->firstOrFail();
        $region = $this->createRegionFixture();

        $this->actingAs($petugas);

        Livewire::test(ListPermohonanSewaReklame::class)
            ->assertCanSeeTableRecords([$permohonan])
            ->callTableAction('buat_npwpd', $permohonan, [
                'nik' => $permohonan->nik,
                'nama_lengkap' => 'Budi Santoso',
                'alamat' => 'Jl. Teuku Umar No. 12',
                'email' => null,
                'tipe_wajib_pajak' => 'perorangan',
                'asal_wilayah' => 'bojonegoro',
                'province_code' => $region['province']->code,
                'regency_code' => $region['regency']->code,
                'district_code' => $region['district']->code,
                'village_code' => $region['village']->code,
            ])
            ->assertHasNoTableActionErrors();

        $wajibPajak = WajibPajak::where('nik_hash', WajibPajak::generateHash($permohonan->nik))->firstOrFail();
        $user = User::findOrFail($wajibPajak->user_id);

        $this->assertMatchesRegularExpression('/^budi-santoso\.teuku-umar\.\d{4}\.[a-z0-9]{4}@generated\.local$/', $user->email);
        $this->assertSame('user', $user->role);
    }

    public function test_petugas_can_create_draft_skpd_from_processed_permohonan_with_npwpd(): void
    {
        $this->seedReklameTaxReferences([
            AdminUserSeeder::class,
            AsetReklamePemkabSeeder::class,
        ]);
        $this->seedPermohonanSewaReklameFixtures();

        $petugas = $this->createAdminPanelUser('petugas');
        $permohonan = PermohonanSewaReklame::where('status', 'diproses')
            ->whereNull('npwpd')
            ->whereNull('skpd_id')
            ->oldest('tanggal_pengajuan')
            ->firstOrFail();
        $permohonan->update([
            'npwpd' => 'P10000000123',
            'satuan_sewa' => 'tahun',
        ]);

        $this->actingAs($petugas);

        Livewire::test(ListPermohonanSewaReklame::class)
            ->assertCanSeeTableRecords([$permohonan])
            ->callTableAction('buat_skpd', $permohonan)
            ->assertHasNoTableActionErrors();

        $permohonan->refresh();
        $skpd = SkpdReklame::findOrFail($permohonan->skpd_id);

        $this->assertSame($permohonan->id, $skpd->permohonan_sewa_id);
        $this->assertSame('draft', $skpd->status);
        $this->assertStringContainsString('(DRAFT)', $skpd->nomor_skpd);
        $this->assertSame($permohonan->npwpd, $skpd->npwpd);
        $this->assertSame($petugas->id, $skpd->petugas_id);
        $this->assertSame($petugas->nama_lengkap, $skpd->petugas_nama);
        $this->assertNotNull($skpd->jumlah_pajak);
        $this->assertGreaterThan(0, (float) $skpd->jumlah_pajak);
    }

    public function test_petugas_can_mark_processed_permohonan_for_revision(): void
    {
        $this->seed([
            AdminUserSeeder::class,
            AsetReklamePemkabSeeder::class,
        ]);
        $this->seedPermohonanSewaReklameFixtures();

        $petugas = $this->createAdminPanelUser('petugas');
        $permohonan = PermohonanSewaReklame::where('status', 'diproses')
            ->oldest('tanggal_pengajuan')
            ->firstOrFail();
        $catatan = 'Mohon lengkapi dokumen desain reklame dan perjelas lokasi pemasangan.';

        $this->actingAs($petugas);

        Livewire::test(ListPermohonanSewaReklame::class)
            ->assertCanSeeTableRecords([$permohonan])
            ->callTableAction('perlu_revisi', $permohonan, [
                'catatan_petugas' => $catatan,
            ])
            ->assertHasNoTableActionErrors();

        $permohonan->refresh();

        $this->assertSame('perlu_revisi', $permohonan->status);
        $this->assertSame($catatan, $permohonan->catatan_petugas);
        $this->assertSame($petugas->id, $permohonan->petugas_id);
        $this->assertSame($petugas->nama_lengkap, $permohonan->petugas_nama);
    }

    public function test_petugas_can_reject_permohonan_from_table_action(): void
    {
        $this->seed([
            AdminUserSeeder::class,
            AsetReklamePemkabSeeder::class,
        ]);
        $this->seedPermohonanSewaReklameFixtures();

        $petugas = $this->createAdminPanelUser('petugas');
        $permohonan = PermohonanSewaReklame::where('status', 'diajukan')
            ->oldest('tanggal_pengajuan')
            ->firstOrFail();
        $catatan = 'Konten reklame tidak memenuhi ketentuan penayangan pada aset Pemkab.';

        $this->actingAs($petugas);

        Livewire::test(ListPermohonanSewaReklame::class)
            ->assertCanSeeTableRecords([$permohonan])
            ->callTableAction('tolak', $permohonan, [
                'catatan_petugas' => $catatan,
            ])
            ->assertHasNoTableActionErrors();

        $permohonan->refresh();

        $this->assertSame('ditolak', $permohonan->status);
        $this->assertSame($catatan, $permohonan->catatan_petugas);
        $this->assertSame($petugas->id, $permohonan->petugas_id);
        $this->assertSame($petugas->nama_lengkap, $permohonan->petugas_nama);
        $this->assertNotNull($permohonan->tanggal_selesai);
    }

    #[DataProvider('editableRoleProvider')]
    public function test_admin_and_petugas_can_update_aset_reklame_and_luas_is_recalculated(string $role): void
    {
        $this->seed([
            AdminUserSeeder::class,
            AsetReklamePemkabSeeder::class,
        ]);

        $user = $this->createAdminPanelUser($role);
        $aset = AsetReklamePemkab::where('kode_aset', 'NB001')->firstOrFail();

        $this->actingAs($user);

        Livewire::test(EditAsetReklamePemkab::class, ['record' => $aset->getRouteKey()])
            ->fillForm([
                'panjang' => 8,
                'lebar' => 4,
                'lokasi' => 'Jl. Veteran No. 1',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $aset->refresh();

        $this->assertSame('Jl. Veteran No. 1', $aset->lokasi);
        $this->assertEquals(8.0, (float) $aset->panjang);
        $this->assertEquals(4.0, (float) $aset->lebar);
        $this->assertEquals(32.0, (float) $aset->luas_m2);
    }

    public static function editableRoleProvider(): array
    {
        return [
            'admin' => ['admin'],
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

    private function createApprovedWajibPajakForNik(string $nik, string $npwpd): WajibPajak
    {
        $user = User::create([
            'name' => 'Existing WP',
            'nama_lengkap' => 'Existing WP',
            'email' => sprintf('existing-wp-%s@example.test', Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => $nik,
            'alamat' => 'Jl. Existing No. 1',
            'role' => 'user',
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
        ]);

        return WajibPajak::create([
            'user_id' => $user->id,
            'nik' => $nik,
            'nama_lengkap' => 'Existing WP',
            'alamat' => 'Jl. Existing No. 1',
            'tipe_wajib_pajak' => 'perorangan',
            'status' => 'disetujui',
            'tanggal_daftar' => now()->subDays(7),
            'tanggal_verifikasi' => now()->subDays(6),
            'petugas_id' => null,
            'petugas_nama' => null,
            'npwpd' => $npwpd,
            'nopd' => 1,
        ]);
    }

    private function createRegionFixture(): array
    {
        $province = Province::firstOrCreate([
            'code' => '35',
        ], [
            'name' => 'Jawa Timur',
        ]);

        $regency = Regency::firstOrCreate([
            'code' => '35.22',
        ], [
            'province_code' => $province->code,
            'name' => 'Bojonegoro',
        ]);

        $district = District::firstOrCreate([
            'code' => '35.22.01',
        ], [
            'regency_code' => $regency->code,
            'name' => 'Bojonegoro Kota',
        ]);

        $village = Village::firstOrCreate([
            'code' => '35.22.01.1001',
        ], [
            'district_code' => $district->code,
            'name' => 'Kadipaten',
            'postal_code' => '62111',
        ]);

        return [
            'province' => $province,
            'regency' => $regency,
            'district' => $district,
            'village' => $village,
        ];
    }
}