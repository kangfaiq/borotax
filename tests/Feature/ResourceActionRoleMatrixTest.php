<?php

namespace Tests\Feature;

use App\Domain\AirTanah\Models\MeterReport;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\Auth\Models\User;
use App\Domain\Gebyar\Models\GebyarSubmission;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\PeminjamanAsetReklame;
use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Reklame\Models\ReklameRequest;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Shared\Models\DataChangeRequest;
use App\Domain\Tax\Models\PembetulanRequest;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\Tax\Models\Tax;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\TaxStatus;
use App\Filament\Resources\AsetReklamePemkabResource;
use App\Filament\Resources\AsetReklamePemkabResource\Pages\ListAsetReklamePemkab;
use App\Filament\Resources\MeterReportResource;
use App\Filament\Resources\MeterReportResource\Pages\ListMeterReports;
use App\Filament\Resources\PermohonanSewaReklameResource;
use App\Filament\Resources\PermohonanSewaReklameResource\Pages\ListPermohonanSewaReklame;
use App\Filament\Resources\ReklameRequestResource;
use App\Filament\Resources\ReklameRequestResource\Pages\ListReklameRequests;
use App\Filament\Resources\StpdManualResource;
use App\Filament\Resources\StpdManualResource\Pages\ListStpdManuals;
use App\Filament\Resources\WajibPajakResource;
use App\Filament\Resources\WajibPajakResource\Pages\ListWajibPajaks;
use App\Filament\Pages\BuatStpd;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\AsetReklamePemkabSeeder;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ResourceActionRoleMatrixTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('wajibPajakRoleProvider')]
    public function test_wajib_pajak_actions_follow_role_rules(
        string $role,
        bool $canVerify,
        bool $canEditApproved
    ): void {
        $pendingRecord = $this->createPendingWajibPajak('perorangan');
        $approvedRecord = $this->createApprovedWajibPajak();
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $indexResponse = $this->get(WajibPajakResource::getUrl('index'));
        $this->assertSame(200, $indexResponse->getStatusCode());

        $component = Livewire::test(ListWajibPajaks::class)
            ->assertCanSeeTableRecords([$pendingRecord, $approvedRecord]);

        if ($canVerify) {
            $component
                ->assertTableActionVisible('approve', $pendingRecord)
                ->assertTableActionVisible('reject', $pendingRecord)
                ->assertTableActionVisible('requestRevision', $pendingRecord);
        } else {
            $component
                ->assertTableActionHidden('approve', $pendingRecord)
                ->assertTableActionHidden('reject', $pendingRecord)
                ->assertTableActionHidden('requestRevision', $pendingRecord);
        }

        if ($canEditApproved) {
            $component->assertTableActionVisible('edit', $approvedRecord);
        } else {
            $component->assertTableActionHidden('edit', $approvedRecord);
        }
    }

    public function test_permohonan_sewa_reklame_actions_only_available_to_petugas(): void
    {
        $this->seed(AsetReklamePemkabSeeder::class);
        $this->seedPermohonanSewaReklameFixtures();

        $diajukan = PermohonanSewaReklame::where('status', 'diajukan')->firstOrFail();
        $diprosesTanpaNpwpd = PermohonanSewaReklame::where('status', 'diproses')->firstOrFail();
        $diprosesTanpaNpwpd->update(['npwpd' => null, 'skpd_id' => null]);

        $diprosesDenganNpwpd = PermohonanSewaReklame::where('status', 'perlu_revisi')->firstOrFail();
        $diprosesDenganNpwpd->update([
            'status' => 'diproses',
            'npwpd' => 'P100000000999',
            'skpd_id' => null,
        ]);

        foreach (['admin', 'verifikator', 'petugas'] as $role) {
            $user = $this->createAdminPanelUser($role);
            $this->actingAs($user);

            $indexResponse = $role !== 'petugas'
                ? $this->get(PermohonanSewaReklameResource::getUrl('index'))
                : $this->followingRedirects()->get(PermohonanSewaReklameResource::getUrl('index'));

            if ($role !== 'petugas') {
                $this->assertAccessExpectation($indexResponse->getStatusCode(), false, "permohonan sewa reklame index for {$role}");
                continue;
            }

            $this->assertSame(200, $indexResponse->getStatusCode());

            Livewire::test(ListPermohonanSewaReklame::class)
                ->assertCanSeeTableRecords([$diajukan, $diprosesTanpaNpwpd, $diprosesDenganNpwpd])
                ->assertTableActionVisible('proses', $diajukan)
                ->assertTableActionVisible('tolak', $diajukan)
                ->assertTableActionVisible('buat_npwpd', $diprosesTanpaNpwpd)
                ->assertTableActionVisible('cek_npwpd', $diprosesTanpaNpwpd)
                ->assertTableActionVisible('perlu_revisi', $diprosesTanpaNpwpd)
                ->assertTableActionVisible('tolak', $diprosesTanpaNpwpd)
                ->assertTableActionVisible('buat_skpd', $diprosesDenganNpwpd)
                ->assertTableActionVisible('perlu_revisi', $diprosesDenganNpwpd)
                ->assertTableActionVisible('tolak', $diprosesDenganNpwpd);
        }
    }

    #[DataProvider('stpdManualRoleProvider')]
    public function test_stpd_manual_verification_actions_follow_role_rules(string $role, bool $canVerify): void
    {
        $draft = $this->createDraftStpdManual();
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $indexResponse = $this->get(StpdManualResource::getUrl('index'));
        $this->assertAccessExpectation($indexResponse->getStatusCode(), $canVerify, "stpd manual index for {$role}");

        if (! $canVerify) {
            return;
        }

        Livewire::test(ListStpdManuals::class)
            ->assertCanSeeTableRecords([$draft])
            ->assertTableActionVisible('approve', $draft)
            ->assertTableActionVisible('reject', $draft)
            ->assertTableBulkActionVisible('bulk_approve')
            ->assertTableBulkActionVisible('bulk_reject');
    }

    #[DataProvider('meterReportRoleProvider')]
    public function test_meter_report_process_action_follows_role_rules(string $role, bool $canProcess): void
    {
        $report = $this->createSubmittedMeterReport();
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $indexResponse = $this->followingRedirects()->get(MeterReportResource::getUrl('index'));
        $this->assertSame(200, $indexResponse->getStatusCode());

        $component = Livewire::test(ListMeterReports::class)
            ->assertCanSeeTableRecords([$report]);

        if ($canProcess) {
            $component->assertTableActionVisible('process', $report);
        } else {
            $component->assertTableActionHidden('process', $report);
        }
    }

    #[DataProvider('asetReklameRoleProvider')]
    public function test_aset_reklame_operational_actions_follow_role_rules(
        string $role,
        bool $canEdit,
        bool $canManageMaintenanceAndPinjam,
        bool $canManageAdminOnlyOperationalActions
    ): void
    {
        $this->seed(AsetReklamePemkabSeeder::class);

        $tersedia = AsetReklamePemkab::where('kode_aset', 'NB001')->firstOrFail();
        $maintenance = AsetReklamePemkab::where('kode_aset', 'NB002')->firstOrFail();
        $maintenance->update(['status_ketersediaan' => 'maintenance']);

        $dipinjam = AsetReklamePemkab::where('kode_aset', 'NB003')->firstOrFail();
        $dipinjam->update([
            'status_ketersediaan' => 'dipinjam_opd',
            'peminjam_opd' => 'Dinas Kominfo',
            'materi_pinjam' => 'Kampanye Layanan Publik',
            'pinjam_mulai' => now()->subDays(2)->toDateString(),
            'pinjam_selesai' => now()->addDays(2)->toDateString(),
        ]);

        PeminjamanAsetReklame::create([
            'aset_reklame_pemkab_id' => $dipinjam->id,
            'peminjam_opd' => 'Dinas Kominfo',
            'materi_pinjam' => 'Kampanye Layanan Publik',
            'pinjam_mulai' => now()->subDays(2)->toDateString(),
            'pinjam_selesai' => now()->addDays(2)->toDateString(),
            'status' => 'aktif',
            'petugas_id' => $this->createAdminPanelUser('petugas')->id,
            'petugas_nama' => 'Petugas User',
        ]);

        AsetReklamePemkab::whereNotIn('id', [$tersedia->id, $maintenance->id, $dipinjam->id])->delete();

        $user = $this->createAdminPanelUser($role);
        $this->actingAs($user);

        $indexResponse = $this->followingRedirects()->get(AsetReklamePemkabResource::getUrl('index'));
        $this->assertSame(200, $indexResponse->getStatusCode());

        $component = Livewire::test(ListAsetReklamePemkab::class)
            ->assertCanSeeTableRecords([$tersedia, $maintenance, $dipinjam]);

        if ($canEdit) {
            $component->assertTableActionVisible('edit', $tersedia);
        } else {
            $component->assertTableActionHidden('edit', $tersedia);
        }

        if ($canManageMaintenanceAndPinjam) {
            $component
                ->assertTableActionVisible('set_maintenance', $tersedia)
                ->assertTableActionVisible('pinjam_opd', $tersedia);
        } else {
            $component
                ->assertTableActionHidden('set_maintenance', $tersedia)
                ->assertTableActionHidden('pinjam_opd', $tersedia);
        }

        if ($canManageAdminOnlyOperationalActions) {
            $component
                ->assertTableActionVisible('set_tersedia', $maintenance)
                ->assertTableActionVisible('set_tersedia', $dipinjam)
                ->assertTableActionVisible('selesai_pinjam', $dipinjam);
        } else {
            $component
                ->assertTableActionHidden('set_tersedia', $maintenance)
                ->assertTableActionHidden('set_tersedia', $dipinjam)
                ->assertTableActionHidden('selesai_pinjam', $dipinjam);
        }
    }

    #[DataProvider('portalReklameRoleProvider')]
    public function test_portal_reklame_actions_follow_role_rules(string $role, bool $canProcess): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $request = $this->createPendingPortalReklameRequest();
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $indexResponse = $this->get(ReklameRequestResource::getUrl('index'));
        $this->assertAccessExpectation($indexResponse->getStatusCode(), $canProcess, "portal reklame index for {$role}");

        if (! $canProcess) {
            return;
        }

        Livewire::test(ListReklameRequests::class)
            ->assertCanSeeTableRecords([$request])
            ->assertTableActionVisible('proses', $request)
            ->assertTableActionVisible('buat_skpd', $request)
            ->assertTableActionVisible('tolak', $request);
    }

    public static function wajibPajakRoleProvider(): array
    {
        return [
            'admin' => ['admin', true, true],
            'verifikator' => ['verifikator', true, false],
            'petugas' => ['petugas', false, true],
        ];
    }

    public static function meterReportRoleProvider(): array
    {
        return [
            'admin' => ['admin', true],
            'verifikator' => ['verifikator', false],
            'petugas' => ['petugas', true],
        ];
    }

    public static function stpdManualRoleProvider(): array
    {
        return [
            'admin' => ['admin', true],
            'verifikator' => ['verifikator', true],
            'petugas' => ['petugas', false],
        ];
    }

    public static function asetReklameRoleProvider(): array
    {
        return [
            'admin' => ['admin', true, true, true],
            'verifikator' => ['verifikator', false, true, false],
            'petugas' => ['petugas', false, true, false],
        ];
    }

    public static function portalReklameRoleProvider(): array
    {
        return [
            'admin' => ['admin', true],
            'verifikator' => ['verifikator', false],
            'petugas' => ['petugas', true],
        ];
    }

    private function createPendingWajibPajak(string $tipe): WajibPajak
    {
        $nik = str_pad((string) random_int(0, 9999999999999999), 16, '0', STR_PAD_LEFT);
        $user = User::create([
            'name' => 'Portal User ' . Str::random(6),
            'email' => sprintf('portal-%s@example.test', Str::random(8)),
            'password' => Hash::make('password'),
            'nik' => $nik,
            'nama_lengkap' => 'Portal User',
            'alamat' => 'Jl. Ahmad Yani No. 10',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        return WajibPajak::create([
            'user_id' => $user->id,
            'nik' => $nik,
            'nama_lengkap' => $tipe === 'perusahaan' ? 'PT Uji Verifikasi' : 'Budi Verifikasi',
            'alamat' => 'Jl. Veteran No. 12',
            'asal_wilayah' => 'bojonegoro',
            'tipe_wajib_pajak' => $tipe,
            'nama_perusahaan' => $tipe === 'perusahaan' ? 'PT Uji Verifikasi' : null,
            'status' => 'menungguVerifikasi',
            'tanggal_daftar' => now()->subDay(),
        ]);
    }

    private function createApprovedWajibPajak(): WajibPajak
    {
        $wajibPajak = $this->createPendingWajibPajak('perorangan');
        $wajibPajak->update([
            'status' => 'disetujui',
            'npwpd' => 'P1' . str_pad((string) random_int(1, 99999999999), 11, '0', STR_PAD_LEFT),
            'tanggal_verifikasi' => now(),
        ]);

        return $wajibPajak->refresh();
    }

    private function createDraftStpdManual(): StpdManual
    {
        $this->seed([
            AdminUserSeeder::class,
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);
        $this->createTaxFixture(
            $this->createTaxObjectFixture($this->createApprovedWajibPajakFixture()),
            overrides: [
                'status' => TaxStatus::Verified,
                'payment_expired_at' => now()->subDays(45),
                'masa_pajak_bulan' => 9,
                'masa_pajak_tahun' => 2025,
            ],
        );

        $petugas = $this->createAdminPanelUser('petugas');
        $tax = Tax::whereIn('status', [TaxStatus::Pending, TaxStatus::Verified])->firstOrFail();

        $this->actingAs($petugas);

        Livewire::test(BuatStpd::class)
            ->set('searchKeyword', $tax->billing_code)
            ->call('cariBilling')
            ->assertSet('selectedTaxId', $tax->id)
            ->set('tipeStpd', 'pokok_sanksi')
            ->set('proyeksiTanggalBayar', now()->addDays(10)->format('Y-m-d'))
            ->call('hitungProyeksi')
            ->call('buatStpd');

        return StpdManual::where('tax_id', $tax->id)->firstOrFail();
    }

    private function createDataChangeRequest(): DataChangeRequest
    {
        $petugas = $this->createAdminPanelUser('petugas');
        $wajibPajak = $this->createApprovedWajibPajak();

        return DataChangeRequest::create([
            'entity_type' => 'wajib_pajak',
            'entity_id' => $wajibPajak->id,
            'field_changes' => [
                'alamat' => [
                    'old' => 'Jl. Veteran No. 12',
                    'new' => 'Jl. Diponegoro No. 8',
                ],
            ],
            'alasan_perubahan' => 'Koreksi alamat wajib pajak.',
            'status' => 'pending',
            'requested_by' => $petugas->id,
        ]);
    }

    private function createPembetulanRequest(): PembetulanRequest
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $wajibPajak = $this->createApprovedWajibPajak();
        $jenisPajak = JenisPajak::where('kode', '41101')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();

        $tax = Tax::create([
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'user_id' => $wajibPajak->user_id,
            'amount' => 100000,
            'omzet' => 1000000,
            'tarif_persentase' => 10,
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000260001',
            'payment_expired_at' => now()->addDays(30),
            'masa_pajak_bulan' => 2,
            'masa_pajak_tahun' => 2026,
            'pembetulan_ke' => 0,
        ]);

        return PembetulanRequest::create([
            'tax_id' => $tax->id,
            'user_id' => $wajibPajak->user_id,
            'alasan' => 'Omzet yang dilaporkan perlu dikoreksi.',
            'omzet_baru' => 900000,
            'status' => 'pending',
        ]);
    }

    private function createDraftStpd(): StpdManual
    {
        $this->seed([
            AdminUserSeeder::class,
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);
        $this->createTaxFixture(
            $this->createTaxObjectFixture($this->createApprovedWajibPajakFixture()),
            overrides: [
                'status' => TaxStatus::Verified,
                'payment_expired_at' => now()->subDays(45),
                'masa_pajak_bulan' => 10,
                'masa_pajak_tahun' => 2025,
            ],
        );

        $this->seedPimpinanReferences();

        $petugas = $this->createAdminPanelUser('petugas');
        $tax = Tax::whereIn('status', [TaxStatus::Pending, TaxStatus::Verified])->firstOrFail();

        return StpdManual::create([
            'tax_id' => $tax->id,
            'jenis_pajak_id' => $tax->jenis_pajak_id,
            'sub_jenis_pajak_id' => $tax->sub_jenis_pajak_id,
            'billing_code' => $tax->billing_code,
            'npwpd' => $tax->taxObject?->npwpd,
            'nama_wajib_pajak' => $tax->user?->nama_lengkap ?? 'Wajib Pajak Uji',
            'alamat_wajib_pajak' => $tax->user?->alamat ?? 'Jl. Uji No. 1',
            'masa_pajak_bulan' => $tax->masa_pajak_bulan,
            'masa_pajak_tahun' => $tax->masa_pajak_tahun,
            'pokok_awal' => $tax->amount,
            'pokok_belum_dibayar' => $tax->amount,
            'sanksi_dihitung' => 25000,
            'total_tagihan_stpd' => $tax->amount + 25000,
            'tipe' => 'pokok_sanksi',
            'status' => 'draft',
            'tanggal_buat' => now()->subHour(),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
        ]);
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

    private function createPendingPortalReklameRequest(): ReklameRequest
    {
        $jenisPajak = JenisPajak::where('kode', '41104')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();

        $portalUser = User::create([
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
            'nama_objek_pajak' => 'Reklame Portal Baru',
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

        return ReklameRequest::create([
            'tax_object_id' => $reklameObject->id,
            'user_id' => $portalUser->id,
            'user_nik' => '3522011234567890',
            'user_name' => 'Portal Reklame User',
            'tanggal_pengajuan' => now()->subDay(),
            'durasi_perpanjangan_hari' => 30,
            'catatan_pengajuan' => 'Pengajuan reklame portal.',
            'status' => 'diajukan',
        ]);
    }

    private function createDraftSkpdAirTanah(): SkpdAirTanah
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $this->seedPimpinanReferences();

        $jenisPajak = JenisPajak::where('kode', '41108')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->first();
        $petugas = $this->createAdminPanelUser('petugas');
        $wajibPajakUser = User::create([
            'name' => 'Portal Air User',
            'email' => sprintf('portal-air-%s@example.test', Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => '3522011234567890',
            'nama_lengkap' => 'Portal Air User',
            'alamat' => 'Jl. Diponegoro No. 9',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        $waterObject = WaterObject::create([
            'nik' => '3522011234567890',
            'nama_objek_pajak' => 'Sumur Produksi Utama',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak?->id,
            'jenis_sumber' => 'sumurBor',
            'npwpd' => 'P100000000010',
            'nopd' => 2001,
            'alamat_objek' => 'Jl. Gajah Mada No. 5',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'last_meter_reading' => 100,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'tarif_persen' => 20,
            'kelompok_pemakaian' => '1',
            'kriteria_sda' => '1',
            'uses_meter' => true,
        ]);

        $meterReport = MeterReport::create([
            'tax_object_id' => $waterObject->id,
            'user_id' => $wajibPajakUser->id,
            'user_nik' => '3522011234567890',
            'user_name' => 'Portal Air User',
            'meter_reading_before' => 100,
            'meter_reading_after' => 130,
            'photo_url' => 'meter-reports/sample.jpg',
            'latitude' => '-7.1500000',
            'longitude' => '111.8800000',
            'location_verified' => true,
            'status' => 'processing',
            'reported_at' => now()->subDay(),
        ]);

        return SkpdAirTanah::create([
            'nomor_skpd' => SkpdAirTanah::generateNomorSkpd() . ' (DRAFT)',
            'meter_report_id' => $meterReport->id,
            'tax_object_id' => $waterObject->id,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak?->id,
            'nik_wajib_pajak' => '3522011234567890',
            'nama_wajib_pajak' => 'Portal Air User',
            'alamat_wajib_pajak' => 'Jl. Diponegoro No. 9',
            'nama_objek' => 'Sumur Produksi Utama',
            'alamat_objek' => 'Jl. Gajah Mada No. 5',
            'nopd' => '2001',
            'kecamatan' => 'Bojonegoro',
            'kelurahan' => 'Kadipaten',
            'meter_reading_before' => 100,
            'meter_reading_after' => 130,
            'usage' => 30,
            'periode_bulan' => '2026-02',
            'tarif_per_m3' => 1000,
            'dasar_pengenaan' => 30000,
            'tarif_persen' => 20,
            'jumlah_pajak' => 6000,
            'status' => 'draft',
            'tanggal_buat' => now()->subHours(2),
            'petugas_id' => $petugas->id,
            'petugas_nama' => $petugas->nama_lengkap,
            'dasar_hukum' => 'Peraturan Uji ABT',
        ]);
    }

    private function createSubmittedMeterReport(): MeterReport
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $jenisPajak = JenisPajak::where('kode', '41108')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->first();
        $wajibPajakUser = User::create([
            'name' => 'Portal Meter User',
            'email' => sprintf('portal-meter-%s@example.test', Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => '3522011234567891',
            'nama_lengkap' => 'Portal Meter User',
            'alamat' => 'Jl. Sudirman No. 4',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        $waterObject = WaterObject::create([
            'nik' => '3522011234567891',
            'nama_objek_pajak' => 'Sumur Cadangan',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak?->id,
            'jenis_sumber' => 'sumurBor',
            'npwpd' => 'P100000000011',
            'nopd' => 2002,
            'alamat_objek' => 'Jl. Rajawali No. 2',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'last_meter_reading' => 80,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'tarif_persen' => 20,
            'kelompok_pemakaian' => '1',
            'kriteria_sda' => '1',
            'uses_meter' => true,
        ]);

        return MeterReport::create([
            'tax_object_id' => $waterObject->id,
            'user_id' => $wajibPajakUser->id,
            'user_nik' => '3522011234567891',
            'user_name' => 'Portal Meter User',
            'meter_reading_before' => 80,
            'meter_reading_after' => 110,
            'usage' => 30,
            'photo_url' => 'meter-reports/sample-2.jpg',
            'latitude' => '-7.1500000',
            'longitude' => '111.8800000',
            'location_verified' => true,
            'status' => 'submitted',
            'reported_at' => now()->subDay(),
        ]);
    }

    private function createPendingGebyarSubmission(): GebyarSubmission
    {
        $this->seed(JenisPajakSeeder::class);

        $user = User::create([
            'name' => 'Gebyar User',
            'email' => sprintf('gebyar-%s@example.test', Str::random(6)),
            'password' => Hash::make('password'),
            'nik' => '3522011234567800',
            'nama_lengkap' => 'Gebyar User',
            'alamat' => 'Jl. Pemuda No. 8',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
            'total_kupon_undian' => 0,
        ]);

        return GebyarSubmission::create([
            'user_id' => $user->id,
            'user_nik' => '3522011234567800',
            'user_name' => 'Gebyar User',
            'jenis_pajak_id' => JenisPajak::firstOrFail()->id,
            'place_name' => 'Warung Uji',
            'transaction_date' => now()->subDay()->toDateString(),
            'transaction_amount' => '150000',
            'transaction_amount_hash' => User::generateHash('150000'),
            'image_url' => 'gebyar/sample.jpg',
            'original_image_url' => 'gebyar/sample-original.jpg',
            'status' => 'pending',
            'period_year' => (int) now()->format('Y'),
            'kupon_count' => 1,
        ]);
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

    private function assertAccessExpectation(int $statusCode, bool $isAllowed, string $context): void
    {
        if ($isAllowed) {
            $this->assertSame(200, $statusCode, "Expected 200 for {$context}, got {$statusCode}.");

            return;
        }

        $this->assertContains($statusCode, [302, 403, 404], "Expected 302/403/404 for {$context}, got {$statusCode}.");
    }
}