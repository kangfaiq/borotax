<?php

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Instansi;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\HargaPatokanMblb;
use App\Domain\Tax\Models\PortalMblbSubmission;
use App\Domain\Tax\Models\TaxObject;
use App\Enums\InstansiKategori;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
    ]);
});

it('shows a neutral portal mblb detail page with verifier notes and revise action when rejected', function (): void {
    $portalUser = createPortalMblbSubmissionUser('detail');
    $reviewer = createPortalMblbReviewer();
    [$jenisPajak, $subJenisPajak, $taxObject] = createPortalMblbSubmissionTaxObject($portalUser, 'Tambang Detail Rejected');

    $submission = PortalMblbSubmission::create([
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'tax_object_id' => $taxObject->id,
        'user_id' => $portalUser->id,
        'masa_pajak_bulan' => 5,
        'masa_pajak_tahun' => 2026,
        'tarif_persen' => 20,
        'opsen_persen' => 25,
        'total_dpp' => 500000,
        'pokok_pajak' => 100000,
        'opsen' => 25000,
        'detail_items' => [[
            'harga_patokan_mblb_id' => 'mineral-1',
            'jenis_mblb' => 'Batu Gamping',
            'volume' => 5,
            'harga_patokan' => 100000,
        ]],
        'attachment_path' => 'portal-mblb-submissions/attachments/detail.pdf',
        'notes' => 'Catatan awal pemohon',
        'status' => 'rejected',
        'processed_by' => $reviewer->id,
        'processed_at' => now(),
        'rejection_reason' => 'Lampiran kurang jelas, mohon perbaiki volume dan unggah dokumen yang benar.',
    ]);

    $response = $this->actingAs($portalUser)
        ->get(route('portal.mblb-submissions.show', $submission->id));

    $response->assertOk()
        ->assertSee('Detail Pengajuan MBLB')
        ->assertSee('Ditolak')
        ->assertSee('Lampiran kurang jelas, mohon perbaiki volume dan unggah dokumen yang benar.')
        ->assertSee(route('portal.mblb-submissions.edit', $submission->id), false)
        ->assertSee('Tambang Detail Rejected')
        ->assertSee('Batu Gamping');
});

it('allows the owner to revise a rejected portal mblb submission and send it back for verification', function (): void {
    $portalUser = createPortalMblbSubmissionUser('revise');
    $reviewer = createPortalMblbReviewer();
    [$jenisPajak, $subJenisPajak, $taxObject] = createPortalMblbSubmissionTaxObject($portalUser, 'Tambang Revisi Portal');
    $hargaPatokan = HargaPatokanMblb::create([
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'nama_mineral' => 'Batu Gamping',
        'harga_patokan' => '100000',
        'satuan' => 'm3',
        'dasar_hukum' => 'Peraturan Uji',
        'nama_alternatif' => [],
        'is_active' => true,
    ]);

    $instansi = Instansi::create([
        'kode' => 'INST-MBLB-REV',
        'nama' => 'Dinas Sumber Daya Air',
        'kategori' => InstansiKategori::Instansi,
        'alamat' => 'Jl. Instansi Revisi No. 1',
        'asal_wilayah' => 'bojonegoro',
        'is_active' => true,
    ]);

    $submission = PortalMblbSubmission::create([
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'tax_object_id' => $taxObject->id,
        'user_id' => $portalUser->id,
        'masa_pajak_bulan' => 5,
        'masa_pajak_tahun' => 2026,
        'tarif_persen' => 20,
        'opsen_persen' => 25,
        'total_dpp' => 500000,
        'pokok_pajak' => 100000,
        'opsen' => 25000,
        'detail_items' => [[
            'harga_patokan_mblb_id' => $hargaPatokan->id,
            'jenis_mblb' => 'Batu Gamping',
            'volume' => 5,
            'harga_patokan' => 100000,
        ]],
        'attachment_path' => 'portal-mblb-submissions/attachments/rejected.pdf',
        'notes' => 'Catatan lama',
        'status' => 'rejected',
        'processed_by' => $reviewer->id,
        'processed_at' => now(),
        'rejection_reason' => 'Perbaiki data volume.',
    ]);

    $response = $this->actingAs($portalUser)
        ->from(route('portal.mblb-submissions.edit', $submission->id))
        ->post(route('portal.mblb-submissions.update', $submission->id), [
            'volumes' => [
                $hargaPatokan->id => '7,50',
            ],
            'instansi_id' => $instansi->id,
            'keterangan' => 'Data sudah diperbaiki sesuai arahan verifikator',
            'attachment' => UploadedFile::fake()->create('revisi.pdf', 300, 'application/pdf'),
        ]);

    $response->assertRedirect(route('portal.mblb-submissions.show', $submission->id));

    $submission->refresh();

    expect($submission->status)->toBe('pending')
        ->and($submission->rejection_reason)->toBeNull()
        ->and($submission->processed_by)->toBeNull()
        ->and($submission->notes)->toBe('Data sudah diperbaiki sesuai arahan verifikator')
        ->and($submission->instansi_id)->toBe($instansi->id)
        ->and((float) $submission->detail_items[0]['volume'])->toBe(7.5);
});

function createPortalMblbSubmissionUser(string $prefix): User
{
    return User::create([
        'name' => 'Portal Submission ' . ucfirst($prefix),
        'nama_lengkap' => 'Portal Submission ' . ucfirst($prefix),
        'email' => sprintf('portal-mblb-%s-%s@example.test', $prefix, Str::random(6)),
        'password' => Hash::make('password'),
        'nik' => (string) random_int(3522011000000000, 3522019999999999),
        'alamat' => 'Jl. Portal Submission No. 1',
        'role' => 'wajibPajak',
        'status' => 'verified',
        'email_verified_at' => now(),
    ]);
}

function createPortalMblbReviewer(): User
{
    return User::create([
        'name' => 'Reviewer Portal MBLB',
        'nama_lengkap' => 'Reviewer Portal MBLB',
        'email' => sprintf('reviewer-mblb-%s@example.test', Str::random(6)),
        'password' => Hash::make('password'),
        'nik' => (string) random_int(3522011000000000, 3522019999999999),
        'alamat' => 'Jl. Verifikator No. 1',
        'role' => 'admin',
        'status' => 'verified',
        'email_verified_at' => now(),
    ]);
}

function createPortalMblbSubmissionTaxObject(User $portalUser, string $objectName): array
{
    $jenisPajak = JenisPajak::where('kode', '41106')->firstOrFail();
    $subJenisPajak = SubJenisPajak::where('kode', 'MBLB_WAPU')->firstOrFail();

    $taxObject = TaxObject::create([
        'nik' => $portalUser->nik,
        'nama_objek_pajak' => $objectName,
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'npwpd' => 'P' . random_int(100000000000, 999999999999),
        'nopd' => random_int(6000, 9999),
        'alamat_objek' => 'Jl. Tambang Portal No. 1',
        'kelurahan' => 'Sukorejo',
        'kecamatan' => 'Bojonegoro',
        'tanggal_daftar' => now()->toDateString(),
        'is_active' => true,
        'is_opd' => false,
        'is_insidentil' => false,
        'tarif_persen' => 20,
    ]);

    return [$jenisPajak, $subJenisPajak, $taxObject];
}