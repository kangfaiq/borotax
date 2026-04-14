<?php

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Shared\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seedReklameTaxReferences();
    $this->seedPimpinanReferences();

    Storage::fake('public');
    Storage::fake('local');
});

it('renders historical object photo previews on the portal object detail page', function () {
    $fixture = createPortalReklameObjectFixture();
    $admin = createAdminPanelUserFixture('admin');

    $oldPath = 'reklame-history/foto-objek-lama.jpg';
    $newPath = 'reklame-history/foto-objek-baru.jpg';

    Storage::disk('public')->put($oldPath, 'old photo content');
    Storage::disk('public')->put($newPath, 'new photo content');

    $log = ActivityLog::log(
        action: 'UPDATE_TAX_OBJECT_PHOTO',
        actorId: $admin->id,
        targetTable: 'tax_objects',
        targetId: $fixture['object']->id,
        description: 'Foto objek reklame diperbarui.',
        oldValues: ['foto_objek_path' => $oldPath],
        newValues: ['foto_objek_path' => $newPath],
    );

    $this->actingAs($fixture['owner'])
        ->get(route('portal.reklame.object-detail', ['objectId' => $fixture['object']->id]))
        ->assertOk()
        ->assertSee('Histori Foto Objek')
        ->assertSee(basename($oldPath))
        ->assertSee(basename($newPath))
        ->assertSee(route('activity-logs.file-preview', [
            'activityLog' => $log,
            'version' => 'old',
            'field' => 'foto_objek_path',
        ], false), false)
        ->assertSee(route('activity-logs.file-preview', [
            'activityLog' => $log,
            'version' => 'new',
            'field' => 'foto_objek_path',
        ], false), false);
});

it('renders historical reklame material previews on the portal skpd detail page', function () {
    $fixture = createPortalSkpdFixture();
    $petugas = createAdminPanelUserFixture('petugas');

    $oldPath = 'reklame-history/materi-lama.pdf';
    $newPath = 'reklame-history/materi-baru.pdf';

    Storage::disk('local')->put($oldPath, 'old material content');
    Storage::disk('local')->put($newPath, 'new material content');

    $log = ActivityLog::log(
        action: ActivityLog::ACTION_UPDATE_REKLAME_MATERIAL_FILE,
        actorId: $petugas->id,
        targetTable: 'permohonan_sewa_reklame',
        targetId: $fixture['permohonan']->id,
        description: 'Materi reklame diperbarui saat revisi permohonan.',
        oldValues: ['file_desain_reklame' => $oldPath],
        newValues: ['file_desain_reklame' => $newPath],
    );

    $this->actingAs($fixture['owner'])
        ->get(route('portal.reklame.skpd-detail', ['skpdId' => $fixture['skpd']->id]))
        ->assertOk()
        ->assertSee('Histori Materi Reklame')
        ->assertSee('Perubahan File Materi Reklame')
        ->assertSee(basename($oldPath))
        ->assertSee(basename($newPath))
        ->assertSee(route('activity-logs.file-preview', [
            'activityLog' => $log,
            'version' => 'new',
            'field' => 'file_desain_reklame',
        ], false), false);
});

it('logs old and new reklame material files when a public sewa revision uploads a new file', function () {
    $owner = createPortalUserFixtureForHistory('3522011234567991');
    $aset = createAsetReklameFixture();
    $oldPath = 'sewa-reklame/desain/materi-awal.pdf';

    Storage::disk('local')->put($oldPath, 'old design content');

    $permohonan = PermohonanSewaReklame::create([
        'aset_reklame_pemkab_id' => $aset->id,
        'user_id' => $owner->id,
        'nik' => $owner->nik,
        'nama' => $owner->nama_lengkap,
        'alamat' => $owner->alamat,
        'no_telepon' => '081234567891',
        'email' => 'revisi-materi@example.test',
        'nama_usaha' => 'CV Revisi Materi',
        'nomor_registrasi_izin' => 'REG-RKL-REV-001',
        'jenis_reklame_dipasang' => 'Materi awal',
        'durasi_sewa_hari' => 30,
        'satuan_sewa' => 'bulan',
        'tanggal_mulai_diinginkan' => now()->addDays(7)->toDateString(),
        'catatan' => 'Mohon revisi materi.',
        'file_desain_reklame' => $oldPath,
        'status' => 'perlu_revisi',
        'tanggal_pengajuan' => now()->subDay(),
    ]);

    $response = $this->put(route('sewa-reklame.update', ['nomorTiket' => $permohonan->nomor_tiket]), [
        'jenis_reklame_dipasang' => 'Materi revisi',
        'jumlah_sewa' => 1,
        'satuan_sewa' => 'bulan',
        'tanggal_mulai_diinginkan' => now()->addDays(10)->toDateString(),
        'email' => 'revisi-materi@example.test',
        'nomor_registrasi_izin' => 'REG-RKL-REV-001',
        'catatan' => 'File materi sudah diperbarui.',
        'file_desain_reklame' => UploadedFile::fake()->image('materi-baru.jpg'),
        'npwpd' => 'P100000000321',
    ]);

    $permohonan->refresh();
    $log = ActivityLog::query()
        ->where('action', ActivityLog::ACTION_UPDATE_REKLAME_MATERIAL_FILE)
        ->where('target_table', 'permohonan_sewa_reklame')
        ->where('target_id', $permohonan->id)
        ->latest('created_at')
        ->first();

    $response->assertRedirect(route('sewa-reklame.detail', ['nomorTiket' => $permohonan->nomor_tiket]));

    expect($permohonan->status)->toBe('diajukan')
        ->and($permohonan->file_desain_reklame)->not->toBe($oldPath)
        ->and($log)->not->toBeNull()
        ->and(data_get($log?->old_values, 'file_desain_reklame'))->toBe($oldPath)
        ->and(data_get($log?->new_values, 'file_desain_reklame'))->toBe($permohonan->file_desain_reklame);

    Storage::disk('local')->assertExists($permohonan->file_desain_reklame);
});

it('limits historical file preview access to the permohonan owner or backoffice roles', function () {
    $owner = createPortalUserFixtureForHistory('3522011234567992');
    $otherUser = createPortalUserFixtureForHistory('3522011234567993');
    $admin = createAdminPanelUserFixture('admin');
    $aset = createAsetReklameFixture(['kode_aset' => 'NB202']);
    $filePath = 'reklame-history/preview-materi.pdf';

    Storage::disk('local')->put($filePath, 'historical preview content');

    $permohonan = PermohonanSewaReklame::create([
        'aset_reklame_pemkab_id' => $aset->id,
        'user_id' => $owner->id,
        'nik' => $owner->nik,
        'nama' => $owner->nama_lengkap,
        'alamat' => $owner->alamat,
        'no_telepon' => '081234567892',
        'email' => 'preview-materi@example.test',
        'nama_usaha' => 'CV Preview Materi',
        'nomor_registrasi_izin' => 'REG-RKL-PREVIEW-001',
        'jenis_reklame_dipasang' => 'Preview materi',
        'durasi_sewa_hari' => 30,
        'satuan_sewa' => 'bulan',
        'tanggal_mulai_diinginkan' => now()->addDays(7)->toDateString(),
        'status' => 'diproses',
        'tanggal_pengajuan' => now()->subDay(),
    ]);

    $log = ActivityLog::log(
        action: ActivityLog::ACTION_UPDATE_REKLAME_MATERIAL_FILE,
        actorId: $admin->id,
        targetTable: 'permohonan_sewa_reklame',
        targetId: $permohonan->id,
        description: 'Uji akses preview file historis.',
        oldValues: ['file_desain_reklame' => $filePath],
        newValues: ['file_desain_reklame' => $filePath],
    );

    $route = route('activity-logs.file-preview', [
        'activityLog' => $log,
        'version' => 'old',
        'field' => 'file_desain_reklame',
    ]);

    $this->actingAs($otherUser)
        ->get($route)
        ->assertNotFound();

    $this->actingAs($owner)
        ->get($route)
        ->assertOk();

    $this->actingAs($admin)
        ->get($route)
        ->assertOk();
});

function createPortalReklameObjectFixture(): array
{
    $wajibPajak = test()->createApprovedWajibPajakFixture([], [
        'email' => 'reklame-object-history-' . Str::lower(Str::random(6)) . '@example.test',
        'nik' => '3522011234567990',
        'password_changed_at' => now(),
        'must_change_password' => false,
    ]);

    $object = test()->createTaxObjectFixture($wajibPajak, '41104', [
        'nama_objek_pajak' => 'Reklame Gerai Sentosa',
        'alamat_objek' => 'Jl. Veteran No. 1',
        'bentuk' => 'persegi',
        'panjang' => 4,
        'lebar' => 2,
        'jumlah_muka' => 1,
        'kelompok_lokasi' => 'A',
        'masa_berlaku_sampai' => now()->addDays(30)->toDateString(),
        'status' => 'aktif',
    ]);

    return [
        'owner' => $wajibPajak->user,
        'object' => ReklameObject::findOrFail($object->id),
    ];
}

function createPortalSkpdFixture(): array
{
    $wajibPajak = test()->createApprovedWajibPajakFixture([], [
        'email' => 'reklame-skpd-history-' . Str::lower(Str::random(6)) . '@example.test',
        'nik' => '3522011234567989',
        'password_changed_at' => now(),
        'must_change_password' => false,
    ]);

    $petugas = createAdminPanelUserFixture('petugas');
    $verifikator = createAdminPanelUserFixture('verifikator', Pimpinan::firstOrFail()->id);
    $object = test()->createTaxObjectFixture($wajibPajak, '41104', [
        'nama_objek_pajak' => 'Reklame Koridor Kota',
        'alamat_objek' => 'Jl. Panglima Sudirman No. 8',
        'bentuk' => 'persegi',
        'panjang' => 5,
        'lebar' => 3,
        'jumlah_muka' => 2,
        'kelompok_lokasi' => 'A',
        'masa_berlaku_sampai' => now()->addMonth()->toDateString(),
        'status' => 'aktif',
    ]);

    $aset = createAsetReklameFixture(['kode_aset' => 'NB201']);

    $permohonan = PermohonanSewaReklame::create([
        'aset_reklame_pemkab_id' => $aset->id,
        'user_id' => $wajibPajak->user_id,
        'nik' => $wajibPajak->nik,
        'nama' => $wajibPajak->nama_lengkap,
        'alamat' => $wajibPajak->alamat,
        'no_telepon' => '081234567890',
        'email' => $wajibPajak->user->email,
        'nama_usaha' => 'PT Histori Reklame',
        'nomor_registrasi_izin' => 'REG-RKL-SKPD-001',
        'jenis_reklame_dipasang' => 'Branding koridor kota',
        'durasi_sewa_hari' => 30,
        'satuan_sewa' => 'bulan',
        'tanggal_mulai_diinginkan' => now()->addDays(5)->toDateString(),
        'status' => 'disetujui',
        'tanggal_pengajuan' => now()->subDays(2),
        'petugas_id' => $petugas->id,
        'petugas_nama' => $petugas->nama_lengkap,
        'tanggal_diproses' => now()->subDay(),
        'tanggal_selesai' => now()->subHours(12),
        'npwpd' => $object->npwpd,
    ]);

    $skpd = SkpdReklame::create([
        'nomor_skpd' => 'SKPD-RKL/2030/04/000201',
        'tax_object_id' => $object->id,
        'aset_reklame_pemkab_id' => $aset->id,
        'permohonan_sewa_id' => $permohonan->id,
        'jenis_pajak_id' => JenisPajak::where('kode', '41104')->value('id'),
        'sub_jenis_pajak_id' => SubJenisPajak::where('jenis_pajak_id', JenisPajak::where('kode', '41104')->value('id'))->value('id'),
        'npwpd' => $object->npwpd,
        'nik_wajib_pajak' => $wajibPajak->nik,
        'nama_wajib_pajak' => $wajibPajak->nama_lengkap,
        'alamat_wajib_pajak' => $wajibPajak->alamat,
        'nama_reklame' => $object->nama_objek_pajak,
        'jenis_reklame' => 'Reklame Tetap',
        'alamat_reklame' => $object->alamat_objek,
        'kelompok_lokasi' => 'A',
        'bentuk' => 'persegi',
        'panjang' => 5,
        'lebar' => 3,
        'luas_m2' => 15,
        'jumlah_muka' => 2,
        'lokasi_penempatan' => 'luar_ruangan',
        'jenis_produk' => 'non_rokok',
        'jumlah_reklame' => 1,
        'satuan_waktu' => 'perBulan',
        'satuan_label' => 'per Bulan',
        'durasi' => 1,
        'tarif_pokok' => 150000,
        'nspr' => 0,
        'njopr' => 0,
        'penyesuaian_lokasi' => 1,
        'penyesuaian_produk' => 1,
        'nilai_strategis' => 0,
        'pokok_pajak_dasar' => 150000,
        'masa_berlaku_mulai' => now()->toDateString(),
        'masa_berlaku_sampai' => now()->addMonth()->toDateString(),
        'jatuh_tempo' => now()->addMonth(),
        'dasar_pengenaan' => 150000,
        'jumlah_pajak' => 150000,
        'status' => 'disetujui',
        'tanggal_buat' => now()->subHours(3),
        'petugas_id' => $petugas->id,
        'petugas_nama' => $petugas->nama_lengkap,
        'tanggal_verifikasi' => now()->subHours(2),
        'verifikator_id' => $verifikator->id,
        'verifikator_nama' => $verifikator->nama_lengkap,
        'pimpinan_id' => Pimpinan::firstOrFail()->id,
        'kode_billing' => '352210400030000201',
        'dasar_hukum' => 'Peraturan Uji Reklame',
    ]);

    return [
        'owner' => $wajibPajak->user,
        'object' => ReklameObject::findOrFail($object->id),
        'permohonan' => $permohonan,
        'skpd' => $skpd,
    ];
}

function createPortalUserFixtureForHistory(string $nik): User
{
    return test()->createPortalUserFixture([
        'nik' => $nik,
        'email' => 'portal-history-' . Str::lower(Str::random(6)) . '@example.test',
        'password_changed_at' => now(),
        'must_change_password' => false,
    ]);
}

function createAdminPanelUserFixture(string $role, ?string $pimpinanId = null): User
{
    return User::create([
        'name' => Str::headline($role) . ' User',
        'nama_lengkap' => Str::headline($role) . ' User',
        'email' => sprintf('%s-%s@example.test', $role, Str::lower(Str::random(8))),
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => 'verified',
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'must_change_password' => false,
        'navigation_mode' => 'topbar',
        'pimpinan_id' => $pimpinanId,
    ]);
}

function createAsetReklameFixture(array $overrides = []): AsetReklamePemkab
{
    return AsetReklamePemkab::create(array_merge([
        'kode_aset' => 'NB101',
        'nama' => 'Neon Box Histori',
        'jenis' => 'neon_box',
        'lokasi' => 'Jl. Diponegoro No. 10',
        'keterangan' => 'Aset untuk uji histori media reklame',
        'kawasan' => 'Pusat Kota',
        'traffic' => 'Tinggi',
        'kelompok_lokasi' => 'A',
        'panjang' => 5,
        'lebar' => 3,
        'luas_m2' => 15,
        'jumlah_muka' => 2,
        'harga_sewa_per_bulan' => 150000,
        'status_ketersediaan' => 'tersedia',
        'is_active' => true,
    ], $overrides));
}