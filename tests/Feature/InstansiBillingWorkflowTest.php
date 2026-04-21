<?php

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Instansi;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\HargaPatokanMblb;
use App\Domain\Tax\Models\PortalMblbSubmission;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Services\PortalMblbSubmissionService;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\InstansiKategori;
use App\Filament\Pages\BuatBillingMblb;
use App\Filament\Pages\BuatBillingSelfAssessment;
use App\Filament\Resources\InstansiResource;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('restricts instansi master data pages to admin users', function (string $role, bool $isAllowed) {
    $instansi = createInstansiFixture();
    $user = createAdminPanelUserFixture($role);

    $this->actingAs($user);

    $indexResponse = $this->get(InstansiResource::getUrl('index'));
    $createResponse = $this->get(InstansiResource::getUrl('create'));
    $editResponse = $this->get(InstansiResource::getUrl('edit', ['record' => $instansi]));

    assertAdminResourceAccess($indexResponse->getStatusCode(), $isAllowed, "instansi index for {$role}");
    assertAdminResourceAccess($createResponse->getStatusCode(), $isAllowed, "instansi create for {$role}");
    assertAdminResourceAccess($editResponse->getStatusCode(), $isAllowed, "instansi edit for {$role}");

    if ($isAllowed) {
        $indexResponse->assertSee($instansi->nama);
        $editResponse->assertSee($instansi->nama);
    }
})->with([
    'admin' => ['admin', true],
    'petugas' => ['petugas', false],
    'verifikator' => ['verifikator', false],
]);

it('stores instansi snapshots on self-assessment opd billing', function () {
    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
    ]);

    $petugas = createAdminPanelUserFixture('petugas');
    $wajibPajak = $this->createApprovedWajibPajakFixture();
    $subJenisPajak = SubJenisPajak::where('kode', 'PBJT_RESTORAN')->firstOrFail();
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102', [
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'nama_objek_pajak' => 'Kantin Sekretariat Daerah',
        'is_opd' => true,
        'is_insidentil' => false,
    ]);
    $instansi = createInstansiFixture([
        'nama' => 'Sekretariat Daerah Kabupaten Bojonegoro',
        'kategori' => InstansiKategori::Opd,
    ]);

    $this->actingAs($petugas);

    Livewire::test(BuatBillingSelfAssessment::class)
        ->set('selectedTaxObjectId', $taxObject->id)
        ->set('selectedTaxObjectData', [
            'id' => $taxObject->id,
            'nama' => $taxObject->nama_objek_pajak,
            'alamat' => $taxObject->alamat_objek,
            'npwpd' => $taxObject->npwpd,
            'nopd' => $taxObject->nopd,
            'nik_hash' => $taxObject->nik_hash,
            'sub_jenis' => $subJenisPajak->nama,
            'jenis_pajak_nama' => 'PBJT Makanan dan Minuman',
            'tarif_persen' => 10,
            'jenis_pajak_id' => $taxObject->jenis_pajak_id,
            'sub_jenis_pajak_id' => $taxObject->sub_jenis_pajak_id,
            'next_bulan' => 4,
            'next_tahun' => 2026,
            'next_label' => 'April 2026',
            'is_new' => false,
            'is_opd' => true,
            'is_insidentil' => false,
            'is_multi_billing' => true,
            'sub_jenis_kode' => $subJenisPajak->kode,
        ])
        ->set('wajibPajakData', [
            'id' => $wajibPajak->id,
            'user_id' => $wajibPajak->user_id,
            'nama_lengkap' => $wajibPajak->nama_lengkap,
            'npwpd' => $wajibPajak->npwpd,
            'tipe' => $wajibPajak->tipe_wajib_pajak,
        ])
        ->set('masaPajakBulan', 4)
        ->set('masaPajakTahun', 2026)
        ->set('omzet', 2500000)
        ->set('keterangan', 'Konsumsi rapat koordinasi perangkat daerah')
        ->set('instansiId', $instansi->id)
        ->call('terbitkanBilling')
        ->assertSet('billingResult.instansi', $instansi->nama);

    $tax = Tax::query()->firstOrFail();

    expect($tax->instansi_id)->toBe($instansi->id)
        ->and($tax->instansi_nama)->toBe($instansi->nama)
        ->and($tax->instansi_kategori)->toBe(InstansiKategori::Opd);
});

it('stores instansi snapshots on backoffice mblb wapu billing', function () {
    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
    ]);

    $petugas = createAdminPanelUserFixture('petugas');
    $wajibPajak = $this->createApprovedWajibPajakFixture();
    $subJenisPajak = SubJenisPajak::where('kode', 'MBLB_WAPU')->firstOrFail();
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41106', [
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'nama_objek_pajak' => 'Tambang WAPU Proyek Jalan',
        'tarif_persen' => 20,
    ]);
    $mineral = HargaPatokanMblb::create([
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'nama_mineral' => 'Batu Split',
        'harga_patokan' => 125000,
        'satuan' => 'm3',
        'dasar_hukum' => 'Peraturan Harga Patokan MBLB',
        'is_active' => true,
    ]);
    $instansi = createInstansiFixture([
        'nama' => 'Balai Besar Pelaksanaan Jalan Nasional',
        'kategori' => InstansiKategori::Instansi,
    ]);

    $this->actingAs($petugas);

    Livewire::test(BuatBillingMblb::class)
        ->set('selectedTaxObjectId', $taxObject->id)
        ->set('selectedTaxObjectData', [
            'id' => $taxObject->id,
            'nama' => $taxObject->nama_objek_pajak,
            'alamat' => $taxObject->alamat_objek,
            'npwpd' => $taxObject->npwpd,
            'nopd' => $taxObject->nopd,
            'nik_hash' => $taxObject->nik_hash,
            'sub_jenis' => $subJenisPajak->nama,
            'jenis_pajak_nama' => 'MBLB',
            'tarif_persen' => 20,
            'jenis_pajak_id' => $taxObject->jenis_pajak_id,
            'sub_jenis_pajak_id' => $taxObject->sub_jenis_pajak_id,
            'next_bulan' => 4,
            'next_tahun' => 2026,
            'next_label' => 'April 2026',
            'is_new' => false,
            'is_opd' => false,
            'is_insidentil' => false,
            'is_multi_billing' => true,
            'sub_jenis_kode' => 'MBLB_WAPU',
        ])
        ->set('wajibPajakData', [
            'id' => $wajibPajak->id,
            'user_id' => $wajibPajak->user_id,
            'nama_lengkap' => $wajibPajak->nama_lengkap,
            'npwpd' => $wajibPajak->npwpd,
            'tipe' => $wajibPajak->tipe_wajib_pajak,
        ])
        ->set('masaPajakBulan', 4)
        ->set('masaPajakTahun', 2026)
        ->set('keterangan', 'Pengambilan material untuk proyek preservasi jalan nasional')
        ->set('instansiId', $instansi->id)
        ->set('mineralItems', [[
            'id' => $mineral->id,
            'nama_mineral' => $mineral->nama_mineral,
            'nama_alternatif' => [],
            'harga_patokan' => (float) $mineral->harga_patokan,
            'satuan' => $mineral->satuan,
            'volume' => 3.25,
        ]])
        ->call('terbitkanBilling')
        ->assertSet('billingResult.instansi', $instansi->nama);

    $tax = Tax::query()->firstOrFail();

    expect($tax->instansi_id)->toBe($instansi->id)
        ->and($tax->instansi_nama)->toBe($instansi->nama)
        ->and($tax->instansi_kategori)->toBe(InstansiKategori::Instansi)
        ->and($tax->mblbDetails()->count())->toBe(1);
});

it('stores instansi snapshots on portal mblb wapu submissions and approved billing', function () {
    Storage::fake('public');

    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
    ]);

    $wajibPajak = $this->createApprovedWajibPajakFixture([], [
        'email' => 'wapu-portal@example.test',
        'nik' => '3522011234567890',
        'role' => 'user',
    ]);
    $subJenisPajak = SubJenisPajak::where('kode', 'MBLB_WAPU')->firstOrFail();
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41106', [
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'nama_objek_pajak' => 'Tambang Portal WAPU',
        'tarif_persen' => 20,
    ]);
    $mineral = HargaPatokanMblb::create([
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'nama_mineral' => 'Sirtu',
        'harga_patokan' => 90000,
        'satuan' => 'm3',
        'dasar_hukum' => 'Peraturan Harga Patokan MBLB',
        'is_active' => true,
    ]);
    $instansi = createInstansiFixture([
        'nama' => 'Dinas Pekerjaan Umum Sumber Daya Air',
        'kategori' => InstansiKategori::Opd,
    ]);

    $response = $this->actingAs($wajibPajak->user)
        ->post(route('portal.self-assessment.store'), [
            'tax_object_id' => $taxObject->id,
            'attachment' => UploadedFile::fake()->image('lampiran.jpg', 2400, 1800)->size(2500),
            'volumes' => [
                $mineral->id => '4.50',
            ],
            'bulan' => 4,
            'tahun' => 2026,
            'keterangan' => 'Pengambilan material untuk pekerjaan bronjong sungai',
            'instansi_id' => $instansi->id,
        ]);

    $submission = PortalMblbSubmission::query()->firstOrFail();
    $response->assertRedirect(route('portal.self-assessment.submission-success', $submission->id));

    expect($submission->instansi_id)->toBe($instansi->id)
        ->and($submission->instansi_nama)->toBe($instansi->nama)
        ->and($submission->instansi_kategori)->toBe(InstansiKategori::Opd);

    $reviewer = createAdminPanelUserFixture('admin');
    $tax = app(PortalMblbSubmissionService::class)->approveSubmission($submission, $reviewer, 'Valid dan sesuai dokumen.');

    expect($tax->instansi_id)->toBe($instansi->id)
        ->and($tax->instansi_nama)->toBe($instansi->nama)
        ->and($tax->instansi_kategori)->toBe(InstansiKategori::Opd);
});

function createAdminPanelUserFixture(string $role): User
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

function createInstansiFixture(array $overrides = []): Instansi
{
    return Instansi::create(array_merge([
        'kode' => 'INS-' . Str::upper(Str::random(5)),
        'nama' => 'Instansi Contoh',
        'kategori' => InstansiKategori::Instansi,
        'alamat' => 'Jl. Panglima Sudirman No. 1',
        'keterangan' => 'Dipakai untuk pengujian.',
        'is_active' => true,
    ], $overrides));
}

function assertAdminResourceAccess(int $statusCode, bool $isAllowed, string $context): void
{
    if ($isAllowed) {
        expect($statusCode)->toBe(200, "Expected 200 for {$context}, got {$statusCode}.");

        return;
    }

    expect(in_array($statusCode, [403, 404], true))->toBeTrue("Expected 403/404 for {$context}, got {$statusCode}.");
}