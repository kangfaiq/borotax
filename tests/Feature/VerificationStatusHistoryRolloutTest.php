<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Gebyar\Models\GebyarSubmission;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Reklame\Models\ReklameRequest;
use App\Domain\Tax\Models\PembetulanRequest;
use App\Domain\Tax\Models\StpdManual;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class VerificationStatusHistoryRolloutTest extends TestCase
{
    use RefreshDatabase;

    public function test_pembetulan_request_records_submission_and_rejection_history(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $tax = $this->createTaxFixture($this->createTaxObjectFixture($wajibPajak), $wajibPajak->user);
        $verifikator = $this->createAdminPanelUser('verifikator');

        $request = PembetulanRequest::create([
            'tax_id' => $tax->id,
            'user_id' => $wajibPajak->user_id,
            'alasan' => 'Omzet yang dilaporkan perlu diperbaiki.',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('verification_status_histories', [
            'subject_type' => PembetulanRequest::class,
            'subject_id' => $request->id,
            'action' => 'submitted',
            'to_status' => 'pending',
        ]);

        $this->actingAs($verifikator);

        $request->update([
            'status' => 'ditolak',
            'processed_by' => $verifikator->id,
            'processed_at' => now(),
            'catatan_petugas' => 'Lampiran belum sesuai.',
        ]);

        $this->assertDatabaseHas('verification_status_histories', [
            'subject_type' => PembetulanRequest::class,
            'subject_id' => $request->id,
            'action' => 'rejected',
            'from_status' => 'pending',
            'to_status' => 'ditolak',
            'note' => 'Lampiran belum sesuai.',
        ]);
    }

    public function test_reklame_request_records_processing_and_approval_history(): void
    {
        $this->seedReklameTaxReferences();

        $portalUser = $this->createPortalUserFixture([
            'nik' => '3522011234567890',
            'nama_lengkap' => 'Portal Reklame User',
        ]);
        $petugas = $this->createAdminPanelUser('petugas');
        $jenisPajak = JenisPajak::where('kode', '41104')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();

        $reklameObject = ReklameObject::create([
            'nik' => $portalUser->nik,
            'nama_objek_pajak' => 'Reklame Uji Histori',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P100000000099',
            'nopd' => 1099,
            'alamat_objek' => 'Jl. Teuku Umar No. 3',
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
            'user_id' => $portalUser->id,
            'user_nik' => $portalUser->nik,
            'user_name' => $portalUser->nama_lengkap,
            'tanggal_pengajuan' => now(),
            'durasi_perpanjangan_hari' => 30,
            'catatan_pengajuan' => 'Mohon proses perpanjangan.',
            'status' => 'diajukan',
        ]);

        $this->assertDatabaseHas('verification_status_histories', [
            'subject_type' => ReklameRequest::class,
            'subject_id' => $request->id,
            'action' => 'submitted',
            'to_status' => 'diajukan',
        ]);

        $this->actingAs($petugas);

        $request->update([
            'status' => 'diproses',
            'tanggal_diproses' => now(),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
        ]);

        $request->update([
            'status' => 'disetujui',
            'tanggal_selesai' => now(),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
            'catatan_petugas' => 'Sudah sesuai.',
        ]);

        $this->assertDatabaseHas('verification_status_histories', [
            'subject_type' => ReklameRequest::class,
            'subject_id' => $request->id,
            'action' => 'processing_started',
            'from_status' => 'diajukan',
            'to_status' => 'diproses',
        ]);

        $this->assertDatabaseHas('verification_status_histories', [
            'subject_type' => ReklameRequest::class,
            'subject_id' => $request->id,
            'action' => 'approved',
            'from_status' => 'diproses',
            'to_status' => 'disetujui',
            'note' => 'Sudah sesuai.',
        ]);
    }

    public function test_stpd_manual_records_draft_and_approval_history(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $tax = $this->createTaxFixture($this->createTaxObjectFixture($wajibPajak), $wajibPajak->user);
        $petugas = $this->createAdminPanelUser('petugas');
        $verifikator = $this->createAdminPanelUser('verifikator');

        $stpd = StpdManual::create([
            'tax_id' => $tax->id,
            'tipe' => 'sanksi_saja',
            'status' => 'draft',
            'bulan_terlambat' => 2,
            'sanksi_dihitung' => 25000,
            'pokok_belum_dibayar' => 0,
            'catatan_petugas' => 'Draft STPD dibuat.',
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
            'tanggal_buat' => now(),
        ]);

        $this->assertDatabaseHas('verification_status_histories', [
            'subject_type' => StpdManual::class,
            'subject_id' => $stpd->id,
            'action' => 'draft_created',
            'to_status' => 'draft',
        ]);

        $this->actingAs($verifikator);

        $stpd->update([
            'status' => 'disetujui',
            'verifikator_id' => $verifikator->id,
            'verifikator_nama' => $verifikator->nama_lengkap,
            'tanggal_verifikasi' => now(),
            'catatan_verifikasi' => 'STPD disetujui.',
        ]);

        $this->assertDatabaseHas('verification_status_histories', [
            'subject_type' => StpdManual::class,
            'subject_id' => $stpd->id,
            'action' => 'approved',
            'from_status' => 'draft',
            'to_status' => 'disetujui',
            'note' => 'STPD disetujui.',
        ]);
    }

    public function test_gebyar_submission_records_submission_and_rejection_history(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
        ]);

        $portalUser = $this->createPortalUserFixture([
            'nama_lengkap' => 'Portal Gebyar User',
        ]);
        $verifikator = $this->createAdminPanelUser('verifikator');
        $jenisPajak = JenisPajak::firstOrFail();

        $submission = GebyarSubmission::create([
            'user_id' => $portalUser->id,
            'user_nik' => $portalUser->nik,
            'user_name' => $portalUser->nama_lengkap,
            'jenis_pajak_id' => $jenisPajak->id,
            'place_name' => 'Warung Pajak Tertib',
            'transaction_date' => now()->toDateString(),
            'transaction_amount' => 150000,
            'transaction_amount_hash' => hash('sha256', '150000'),
            'image_url' => 'gebyar-submissions/test.jpg',
            'status' => 'pending',
            'period_year' => now()->year,
            'kupon_count' => 1,
        ]);

        $this->assertDatabaseHas('verification_status_histories', [
            'subject_type' => GebyarSubmission::class,
            'subject_id' => $submission->id,
            'action' => 'submitted',
            'to_status' => 'pending',
        ]);

        $this->actingAs($verifikator);

        $submission->update([
            'status' => 'rejected',
            'rejection_reason' => 'Bukti transaksi tidak terbaca.',
            'verified_at' => now(),
        ]);

        $this->assertDatabaseHas('verification_status_histories', [
            'subject_type' => GebyarSubmission::class,
            'subject_id' => $submission->id,
            'action' => 'rejected',
            'from_status' => 'pending',
            'to_status' => 'rejected',
            'note' => 'Bukti transaksi tidak terbaca.',
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
