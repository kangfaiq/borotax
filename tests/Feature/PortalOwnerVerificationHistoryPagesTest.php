<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Gebyar\Models\GebyarSubmission;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Shared\Models\DataChangeRequest;
use App\Domain\Tax\Models\PembetulanRequest;
use App\Domain\Tax\Models\StpdManual;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalOwnerVerificationHistoryPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pembetulan_pages_show_owner_request_history(): void
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
            'alasan' => 'Omzet pada billing ini perlu dikoreksi karena ada transaksi yang belum tercatat.',
            'status' => 'pending',
        ]);

        $this->actingAs($verifikator);
        $request->update([
            'status' => 'ditolak',
            'processed_by' => $verifikator->id,
            'processed_at' => now(),
            'catatan_petugas' => 'Dokumen pendukung belum lengkap.',
        ]);

        $this->actingAs($wajibPajak->user)
            ->get(route('portal.pembetulan.index'))
            ->assertOk()
            ->assertSee('Riwayat Permohonan Pembetulan')
            ->assertSee($tax->billing_code);

        $this->actingAs($wajibPajak->user)
            ->get(route('portal.pembetulan.show', $request->id))
            ->assertOk()
            ->assertSee('Riwayat Verifikasi')
            ->assertSee('Dokumen pendukung belum lengkap.');
    }

    public function test_data_change_request_detail_page_renders_owner_history(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $wajibPajak = $this->createApprovedWajibPajakFixture();
        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41104', ['nama_objek_pajak' => 'Objek Lama']);
        $admin = $this->createAdminPanelUser('admin');

        $this->actingAs($admin);
        $request = DataChangeRequest::createRequest(
            entity: $taxObject,
            fieldChanges: ['nama_objek_pajak' => 'Objek Baru'],
            alasanPerubahan: 'Perlu menyesuaikan nama objek pajak.',
        );
        $request->reject('Perubahan belum dapat diverifikasi.');

        $this->actingAs($wajibPajak->user)
            ->get(route('portal.data-change-requests.show', $request->id))
            ->assertOk()
            ->assertSee('Riwayat Verifikasi')
            ->assertSee('Objek Baru')
            ->assertSee('Perubahan belum dapat diverifikasi.');
    }

    public function test_stpd_manual_detail_page_renders_owner_history(): void
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
            'sanksi_dihitung' => 45000,
            'pokok_belum_dibayar' => 0,
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
            'tanggal_buat' => now(),
        ]);

        $this->actingAs($verifikator);
        $stpd->update([
            'status' => 'disetujui',
            'nomor_stpd' => 'STPD/2030/05/000001',
            'verifikator_id' => $verifikator->id,
            'verifikator_nama' => $verifikator->nama_lengkap,
            'tanggal_verifikasi' => now(),
            'catatan_verifikasi' => 'STPD manual disetujui.',
        ]);

        $this->actingAs($wajibPajak->user)
            ->get(route('portal.stpd-manual.show', $stpd->id))
            ->assertOk()
            ->assertSee('Riwayat Verifikasi')
            ->assertSee('Lihat Dokumen')
            ->assertSee('STPD manual disetujui.');
    }

    public function test_gebyar_pages_render_owner_history(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
        ]);

        $portalUser = $this->createPortalUserFixture([
            'nama_lengkap' => 'Portal Gebyar User',
            'role' => 'wajibPajak',
        ]);
        $verifikator = $this->createAdminPanelUser('verifikator');
        $jenisPajak = JenisPajak::firstOrFail();

        $submission = GebyarSubmission::create([
            'user_id' => $portalUser->id,
            'user_nik' => $portalUser->nik,
            'user_name' => $portalUser->nama_lengkap,
            'jenis_pajak_id' => $jenisPajak->id,
            'place_name' => 'Warung Gebyar',
            'transaction_date' => now()->toDateString(),
            'transaction_amount' => 175000,
            'transaction_amount_hash' => hash('sha256', '175000'),
            'image_url' => 'gebyar-submissions/test.jpg',
            'status' => 'pending',
            'period_year' => now()->year,
            'kupon_count' => 1,
        ]);

        $this->actingAs($verifikator);
        $submission->update([
            'status' => 'rejected',
            'rejection_reason' => 'Bukti transaksi tidak jelas.',
            'verified_at' => now(),
        ]);

        $this->actingAs($portalUser)
            ->get(route('portal.gebyar.index'))
            ->assertOk()
            ->assertSee('Warung Gebyar');

        $this->actingAs($portalUser)
            ->get(route('portal.gebyar.show', $submission->id))
            ->assertOk()
            ->assertSee('Riwayat Verifikasi')
            ->assertSee('Bukti transaksi tidak jelas.');
    }

    public function test_other_owner_cannot_access_foreign_verification_detail_pages(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $owner = $this->createApprovedWajibPajakFixture();
        $otherOwner = $this->createApprovedWajibPajakFixture([
            'email' => 'other-owner@example.test',
            'nik' => '3525010101019998',
            'nama_lengkap' => 'Other Owner',
        ]);

        $taxObject = $this->createTaxObjectFixture($owner, '41104', ['nama_objek_pajak' => 'Objek Owner']);
        $tax = $this->createTaxFixture($this->createTaxObjectFixture($owner), $owner->user);
        $admin = $this->createAdminPanelUser('admin');
        $petugas = $this->createAdminPanelUser('petugas');

        $pembetulanRequest = PembetulanRequest::create([
            'tax_id' => $tax->id,
            'user_id' => $owner->user_id,
            'alasan' => 'Permohonan pembetulan pemilik pertama.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin);
        $dataChangeRequest = DataChangeRequest::createRequest(
            entity: $taxObject,
            fieldChanges: ['nama_objek_pajak' => 'Objek Owner Baru'],
            alasanPerubahan: 'Penyesuaian nama objek pajak.',
        );

        $stpd = StpdManual::create([
            'tax_id' => $tax->id,
            'tipe' => 'sanksi_saja',
            'status' => 'draft',
            'bulan_terlambat' => 1,
            'sanksi_dihitung' => 15000,
            'pokok_belum_dibayar' => 0,
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
            'tanggal_buat' => now(),
        ]);

        $portalGebyarUser = $this->createPortalUserFixture([
            'email' => 'owner-gebyar@example.test',
            'nik' => '3525010101018888',
            'nama_lengkap' => 'Owner Gebyar',
            'role' => 'wajibPajak',
        ]);
        $foreignGebyarUser = $this->createPortalUserFixture([
            'email' => 'foreign-gebyar@example.test',
            'nik' => '3525010101017777',
            'nama_lengkap' => 'Foreign Gebyar',
            'role' => 'wajibPajak',
        ]);
        $jenisPajak = JenisPajak::firstOrFail();

        $submission = GebyarSubmission::create([
            'user_id' => $portalGebyarUser->id,
            'user_nik' => $portalGebyarUser->nik,
            'user_name' => $portalGebyarUser->nama_lengkap,
            'jenis_pajak_id' => $jenisPajak->id,
            'place_name' => 'Gebyar Owner One',
            'transaction_date' => now()->toDateString(),
            'transaction_amount' => 200000,
            'transaction_amount_hash' => hash('sha256', '200000'),
            'image_url' => 'gebyar-submissions/owner-one-test.jpg',
            'status' => 'pending',
            'period_year' => now()->year,
            'kupon_count' => 1,
        ]);

        $this->actingAs($otherOwner->user)
            ->get(route('portal.pembetulan.show', $pembetulanRequest->id))
            ->assertNotFound();

        $this->actingAs($otherOwner->user)
            ->get(route('portal.data-change-requests.show', $dataChangeRequest->id))
            ->assertNotFound();

        $this->actingAs($otherOwner->user)
            ->get(route('portal.stpd-manual.show', $stpd->id))
            ->assertNotFound();

        $this->actingAs($foreignGebyarUser)
            ->get(route('portal.gebyar.show', $submission->id))
            ->assertNotFound();
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
