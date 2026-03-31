<?php

namespace Tests\Concerns;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\TaxStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait BuildsDomainFixtures
{
    protected function createPortalUserFixture(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Portal User ' . Str::random(6),
            'nama_lengkap' => 'Portal User',
            'email' => sprintf('portal-%s@example.test', Str::random(8)),
            'password' => Hash::make('password'),
            'nik' => str_pad((string) random_int(0, 9999999999999999), 16, '0', STR_PAD_LEFT),
            'alamat' => 'Jl. Veteran No. 12',
            'role' => 'user',
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
        ], $overrides));
    }

    protected function createApprovedWajibPajakFixture(array $wpOverrides = [], array $userOverrides = []): WajibPajak
    {
        $user = $this->createPortalUserFixture($userOverrides);
        $tipeWajibPajak = $wpOverrides['tipe_wajib_pajak'] ?? 'perorangan';

        return WajibPajak::create(array_merge([
            'user_id' => $user->id,
            'nik' => $userOverrides['nik'] ?? $user->nik,
            'nama_lengkap' => $userOverrides['nama_lengkap'] ?? $user->nama_lengkap,
            'alamat' => $userOverrides['alamat'] ?? $user->alamat,
            'asal_wilayah' => 'bojonegoro',
            'tipe_wajib_pajak' => $tipeWajibPajak,
            'nama_perusahaan' => $tipeWajibPajak === 'perusahaan'
                ? ($wpOverrides['nama_perusahaan'] ?? 'PT Uji Fixture')
                : null,
            'status' => 'disetujui',
            'tanggal_daftar' => now()->subDays(7),
            'tanggal_verifikasi' => now()->subDays(6),
            'npwpd' => WajibPajak::generateNpwpd($tipeWajibPajak),
            'nopd' => 1,
        ], $wpOverrides));
    }

    protected function createTaxObjectFixture(WajibPajak $wajibPajak, string $jenisPajakKode = '41102', array $overrides = []): TaxObject
    {
        $jenisPajak = JenisPajak::where('kode', $jenisPajakKode)->firstOrFail();
        $subJenisPajak = isset($overrides['sub_jenis_pajak_id'])
            ? SubJenisPajak::findOrFail($overrides['sub_jenis_pajak_id'])
            : SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();

        $existingNopd = TaxObject::where('npwpd', $wajibPajak->npwpd)->max('nopd');

        return TaxObject::create(array_merge([
            'nik' => $wajibPajak->nik,
            'nama_objek_pajak' => 'Objek Pajak ' . Str::random(5),
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => $wajibPajak->npwpd,
            'nopd' => (int) ($existingNopd ?? 0) + 1,
            'alamat_objek' => 'Jl. Objek Pajak No. 1',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tarif_persen' => (float) ($subJenisPajak->tarif_persen ?? 10),
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'is_insidentil' => false,
            'status' => 'aktif',
        ], $overrides));
    }

    protected function createTaxFixture(TaxObject $taxObject, ?User $user = null, array $overrides = []): Tax
    {
        $user ??= User::findOrFail(
            WajibPajak::where('npwpd', $taxObject->npwpd)->firstOrFail()->user_id
        );

        $jenisPajak = JenisPajak::findOrFail($overrides['jenis_pajak_id'] ?? $taxObject->jenis_pajak_id);
        $subJenisPajakId = $overrides['sub_jenis_pajak_id']
            ?? $taxObject->sub_jenis_pajak_id
            ?? SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->value('id');

        return Tax::create(array_merge([
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajakId,
            'tax_object_id' => $taxObject->id,
            'user_id' => $user->id,
            'amount' => 1_000_000,
            'omzet' => 10_000_000,
            'sanksi' => 100_000,
            'tarif_persentase' => 10,
            'status' => TaxStatus::Pending,
            'billing_code' => Tax::generateBillingCode($jenisPajak->kode),
            'payment_expired_at' => now()->subDays(30),
            'masa_pajak_bulan' => 1,
            'masa_pajak_tahun' => 2030,
            'pembetulan_ke' => 0,
            'billing_sequence' => 0,
        ], $overrides));
    }

    protected function createPermohonanSewaReklameFixture(array $overrides = []): PermohonanSewaReklame
    {
        $aset = AsetReklamePemkab::where('kode_aset', $overrides['kode_aset'] ?? 'NB001')->firstOrFail();
        unset($overrides['kode_aset']);

        return PermohonanSewaReklame::create(array_merge([
            'aset_reklame_pemkab_id' => $aset->id,
            'nik' => str_pad((string) random_int(0, 9999999999999999), 16, '0', STR_PAD_LEFT),
            'nama' => 'Pemohon ' . Str::random(5),
            'alamat' => 'Jl. Permohonan No. 1',
            'no_telepon' => '081234567890',
            'nama_usaha' => 'Usaha Fixture',
            'email' => sprintf('permohonan-%s@example.test', Str::random(8)),
            'nomor_registrasi_izin' => 'DPMPTSP-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'jenis_reklame_dipasang' => 'Branding Produk',
            'durasi_sewa_hari' => 180,
            'tanggal_mulai_diinginkan' => now()->addMonth()->toDateString(),
            'status' => 'diajukan',
            'tanggal_pengajuan' => now(),
        ], $overrides));
    }

    protected function seedPermohonanSewaReklameFixtures(): array
    {
        return [
            'diajukan' => $this->createPermohonanSewaReklameFixture([
                'kode_aset' => 'NB001',
                'nama' => 'Pemohon Diajukan',
                'status' => 'diajukan',
                'tanggal_pengajuan' => now()->subDays(4),
            ]),
            'diproses' => $this->createPermohonanSewaReklameFixture([
                'kode_aset' => 'BB005',
                'nama' => 'Pemohon Diproses',
                'status' => 'diproses',
                'tanggal_pengajuan' => now()->subDays(3),
                'tanggal_diproses' => now()->subDays(2),
                'petugas_nama' => 'Petugas Fixture',
            ]),
            'perlu_revisi' => $this->createPermohonanSewaReklameFixture([
                'kode_aset' => 'BB010',
                'nama' => 'Pemohon Revisi',
                'status' => 'perlu_revisi',
                'tanggal_pengajuan' => now()->subDays(2),
                'tanggal_diproses' => now()->subDay(),
                'catatan_petugas' => 'Mohon lengkapi dokumen pendukung.',
                'petugas_nama' => 'Petugas Fixture',
            ]),
            'ditolak' => $this->createPermohonanSewaReklameFixture([
                'kode_aset' => 'NB004',
                'nama' => 'Pemohon Ditolak',
                'status' => 'ditolak',
                'tanggal_pengajuan' => now()->subDay(),
                'tanggal_diproses' => now()->subHours(18),
                'tanggal_selesai' => now()->subHours(12),
                'catatan_petugas' => 'Konten reklame tidak memenuhi ketentuan.',
                'petugas_nama' => 'Petugas Fixture',
            ]),
        ];
    }
}