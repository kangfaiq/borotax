<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Reklame\Models\ReklameRequest;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Tax\Models\Tax;
use App\Enums\TaxStatus;
use App\Filament\Resources\SkpdReklameResource\Pages\ListSkpdReklames;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SkpdReklameVerificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('verificationRoleProvider')]
    public function test_admin_and_verifikator_can_approve_draft_skpd_reklame_and_create_tax_billing(string $role): void
    {
        $draft = $this->createDraftSkpdReklame();
        $verifikator = $this->createAdminPanelUser($role, Pimpinan::firstOrFail()->id);

        $this->actingAs($verifikator);

        Livewire::test(ListSkpdReklames::class)
            ->assertCanSeeTableRecords([$draft])
            ->callTableAction('approve', $draft)
            ->assertHasNoTableActionErrors();

        $draft->refresh();
        $request = $draft->reklameRequest()->firstOrFail();
        $reklameObject = ReklameObject::findOrFail($draft->tax_object_id);
        $tax = Tax::where('skpd_number', $draft->nomor_skpd)->firstOrFail();

        $this->assertSame('disetujui', $draft->status);
        $this->assertNotNull($draft->nomor_skpd);
        $this->assertDoesNotMatchRegularExpression('/\(DRAFT\)$/', $draft->nomor_skpd);
        $this->assertNotNull($draft->kode_billing);
        $this->assertNotNull($draft->jatuh_tempo);
        $this->assertSame($verifikator->id, $draft->verifikator_id);
        $this->assertSame($verifikator->nama_lengkap, $draft->verifikator_nama);
        $this->assertSame(Pimpinan::firstOrFail()->id, $draft->pimpinan_id);

        $this->assertSame('disetujui', $request->status);
        $this->assertNotNull($request->tanggal_selesai);

        $this->assertSame($draft->nama_reklame, $reklameObject->nama_objek_pajak);
        $this->assertSame($draft->alamat_reklame, $reklameObject->alamat_objek);
        $this->assertSame($draft->sub_jenis_pajak_id, $reklameObject->sub_jenis_pajak_id);
        $this->assertSame($draft->kelompok_lokasi, $reklameObject->kelompok_lokasi);
        $this->assertEquals((float) $draft->panjang, (float) $reklameObject->panjang);
        $this->assertEquals((float) $draft->lebar, (float) $reklameObject->lebar);
        $this->assertEquals((float) $draft->luas_m2, (float) $reklameObject->luas_m2);
        $this->assertSame($draft->jumlah_muka, $reklameObject->jumlah_muka);

        $this->assertSame($draft->kode_billing, $tax->billing_code);
        $this->assertSame($draft->nomor_skpd, $tax->skpd_number);
        $this->assertSame(TaxStatus::Verified, $tax->status);
        $this->assertSame($request->user_id, $tax->user_id);
        $this->assertEquals((float) $draft->jumlah_pajak, (float) $tax->amount);
    }

    #[DataProvider('verificationRoleProvider')]
    public function test_admin_and_verifikator_can_reject_draft_skpd_reklame_and_mark_request_rejected(string $role): void
    {
        $draft = $this->createDraftSkpdReklame();
        $verifikator = $this->createAdminPanelUser($role, Pimpinan::firstOrFail()->id);
        $catatan = 'Materi reklame belum memenuhi ketentuan verifikasi.';

        $this->actingAs($verifikator);

        Livewire::test(ListSkpdReklames::class)
            ->assertCanSeeTableRecords([$draft])
            ->callTableAction('reject', $draft, [
                'catatan_verifikasi' => $catatan,
            ])
            ->assertHasNoTableActionErrors();

        $draft->refresh();
        $request = $draft->reklameRequest()->firstOrFail();

        $this->assertSame('ditolak', $draft->status);
        $this->assertSame($catatan, $draft->catatan_verifikasi);
        $this->assertSame($verifikator->id, $draft->verifikator_id);
        $this->assertSame($verifikator->nama_lengkap, $draft->verifikator_nama);

        $this->assertSame('ditolak', $request->status);
        $this->assertStringContainsString($catatan, (string) $request->catatan_petugas);

        $this->assertDatabaseMissing('taxes', [
            'skpd_number' => $draft->nomor_skpd,
        ]);
    }

    public function test_document_creator_cannot_verify_own_skpd_reklame_draft(): void
    {
        $draft = $this->createDraftSkpdReklame();
        $admin = $this->createAdminPanelUser('admin', Pimpinan::firstOrFail()->id);

        $draft->update([
            'petugas_id' => $admin->id,
            'petugas_nama' => $admin->nama_lengkap,
        ]);

        $this->actingAs($admin);

        $this->assertFalse($admin->can('verify', $draft));

        Livewire::test(ListSkpdReklames::class)
            ->assertCanSeeTableRecords([$draft])
            ->assertTableActionHidden('approve', $draft)
            ->assertTableActionHidden('reject', $draft);
    }

    private function createDraftSkpdReklame(): SkpdReklame
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $this->seedPimpinanReferences();

        $jenisPajak = JenisPajak::where('kode', '41104')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();
        $petugas = $this->createAdminPanelUser('petugas');
        $wajibPajakUser = User::create([
            'name' => 'Portal Reklame User',
            'email' => sprintf('portal-reklame-%s@example.test', Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => '3522011234567890',
            'nama_lengkap' => 'Portal Reklame User',
            'alamat' => 'Jl. Teuku Umar No. 15',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        $reklameObject = ReklameObject::create([
            'nik' => '3522011234567890',
            'nama_objek_pajak' => 'Reklame Toko Sentosa',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000001',
            'nopd' => 1001,
            'alamat_objek' => 'Jl. MH Thamrin No. 20',
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

        $request = ReklameRequest::create([
            'tax_object_id' => $reklameObject->id,
            'user_id' => $wajibPajakUser->id,
            'user_nik' => '3522011234567890',
            'user_name' => 'Portal Reklame User',
            'tanggal_pengajuan' => now()->subDays(2),
            'durasi_perpanjangan_hari' => 30,
            'catatan_pengajuan' => 'Perpanjangan reklame bulanan.',
            'status' => 'diproses',
            'tanggal_diproses' => now()->subDay(),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
        ]);

        return SkpdReklame::create([
            'nomor_skpd' => SkpdReklame::generateNomorSkpd() . ' (DRAFT)',
            'tax_object_id' => $reklameObject->id,
            'request_id' => $request->id,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000001',
            'nik_wajib_pajak' => '3522011234567890',
            'nama_wajib_pajak' => 'Portal Reklame User',
            'alamat_wajib_pajak' => 'Jl. Teuku Umar No. 15',
            'nama_reklame' => 'Reklame Toko Sentosa',
            'jenis_reklame' => $subJenisPajak->nama,
            'alamat_reklame' => 'Jl. MH Thamrin No. 20',
            'kelompok_lokasi' => 'A',
            'bentuk' => 'persegi',
            'panjang' => 4,
            'lebar' => 2,
            'luas_m2' => 8,
            'jumlah_muka' => 1,
            'lokasi_penempatan' => 'luar_ruangan',
            'jenis_produk' => 'non_rokok',
            'jumlah_reklame' => 1,
            'satuan_waktu' => 'perBulan',
            'satuan_label' => 'per Bulan',
            'durasi' => 1,
            'tarif_pokok' => 100000,
            'nspr' => 0,
            'njopr' => 0,
            'penyesuaian_lokasi' => 1,
            'penyesuaian_produk' => 1,
            'nilai_strategis' => 0,
            'pokok_pajak_dasar' => 100000,
            'masa_berlaku_mulai' => now()->toDateString(),
            'masa_berlaku_sampai' => now()->addMonth()->toDateString(),
            'dasar_pengenaan' => 100000,
            'jumlah_pajak' => 100000,
            'status' => 'draft',
            'tanggal_buat' => now()->subHours(2),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
        ]);
    }

    public static function verificationRoleProvider(): array
    {
        return [
            'admin' => ['admin'],
            'verifikator' => ['verifikator'],
        ];
    }

    private function createAdminPanelUser(string $role, ?string $pimpinanId = null): User
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
            'pimpinan_id' => $pimpinanId,
        ]);
    }
}