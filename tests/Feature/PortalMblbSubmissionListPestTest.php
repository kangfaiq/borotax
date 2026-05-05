<?php

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\PortalMblbSubmission;
use App\Domain\Tax\Models\TaxObject;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
    ]);
});

it('shows pending portal mblb submissions in the dedicated portal menu page', function (): void {
    $portalUser = User::create([
        'name' => 'Portal Submission User',
        'nama_lengkap' => 'Portal Submission User',
        'email' => sprintf('portal-submission-%s@example.test', Str::random(6)),
        'password' => Hash::make('password'),
        'nik' => '3522011234567111',
        'alamat' => 'Jl. Pengajuan MBLB No. 1',
        'role' => 'wajibPajak',
        'status' => 'verified',
        'email_verified_at' => now(),
    ]);

    $otherUser = User::create([
        'name' => 'Portal Submission Other User',
        'nama_lengkap' => 'Portal Submission Other User',
        'email' => sprintf('portal-submission-other-%s@example.test', Str::random(6)),
        'password' => Hash::make('password'),
        'nik' => '3522011234567222',
        'alamat' => 'Jl. Pengajuan MBLB No. 2',
        'role' => 'wajibPajak',
        'status' => 'verified',
        'email_verified_at' => now(),
    ]);

    $jenisPajak = JenisPajak::where('kode', '41106')->firstOrFail();
    $subJenisPajak = SubJenisPajak::where('kode', 'MBLB_WAPU')->firstOrFail();

    $taxObject = TaxObject::create([
        'nik' => $portalUser->nik,
        'nama_objek_pajak' => 'Tambang Pending Portal',
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'npwpd' => 'P100000000811',
        'nopd' => 8111,
        'alamat_objek' => 'Jl. Tambang Pending KM 1',
        'kelurahan' => 'Sukorejo',
        'kecamatan' => 'Bojonegoro',
        'tanggal_daftar' => now()->toDateString(),
        'is_active' => true,
        'is_opd' => false,
        'is_insidentil' => false,
        'tarif_persen' => 20,
    ]);

    $otherTaxObject = TaxObject::create([
        'nik' => $otherUser->nik,
        'nama_objek_pajak' => 'Tambang User Lain',
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'npwpd' => 'P100000000822',
        'nopd' => 8222,
        'alamat_objek' => 'Jl. Tambang Pending KM 2',
        'kelurahan' => 'Campurejo',
        'kecamatan' => 'Bojonegoro',
        'tanggal_daftar' => now()->toDateString(),
        'is_active' => true,
        'is_opd' => false,
        'is_insidentil' => false,
        'tarif_persen' => 20,
    ]);

    $pendingSubmission = PortalMblbSubmission::create([
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
        'detail_items' => [['nama_mineral' => 'Batu Gamping', 'volume' => 5]],
        'attachment_path' => 'self-assessment/attachments/pending.pdf',
        'notes' => 'Pengajuan portal pending',
        'status' => 'pending',
    ]);

    PortalMblbSubmission::create([
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'tax_object_id' => $otherTaxObject->id,
        'user_id' => $otherUser->id,
        'masa_pajak_bulan' => 5,
        'masa_pajak_tahun' => 2026,
        'tarif_persen' => 20,
        'opsen_persen' => 25,
        'total_dpp' => 600000,
        'pokok_pajak' => 120000,
        'opsen' => 30000,
        'detail_items' => [['nama_mineral' => 'Sirtu', 'volume' => 6]],
        'attachment_path' => 'self-assessment/attachments/other.pdf',
        'notes' => 'Pengajuan user lain',
        'status' => 'pending',
    ]);

    PortalMblbSubmission::create([
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'tax_object_id' => $taxObject->id,
        'user_id' => $portalUser->id,
        'masa_pajak_bulan' => 4,
        'masa_pajak_tahun' => 2026,
        'tarif_persen' => 20,
        'opsen_persen' => 25,
        'total_dpp' => 700000,
        'pokok_pajak' => 140000,
        'opsen' => 35000,
        'detail_items' => [['nama_mineral' => 'Batu Putih', 'volume' => 7]],
        'attachment_path' => 'self-assessment/attachments/approved.pdf',
        'notes' => 'Pengajuan approved',
        'status' => 'approved',
    ]);

    $response = $this->actingAs($portalUser)
        ->get(route('portal.mblb-submissions.index', ['status' => 'pending']));

    $response->assertOk()
        ->assertSee('Pengajuan MBLB Portal', false)
        ->assertSee('Tambang Pending Portal')
        ->assertSee('Menunggu Verifikasi')
        ->assertSee(route('portal.mblb-submissions.show', $pendingSubmission->id), false)
        ->assertDontSee('Tambang User Lain')
        ->assertDontSee('Pengajuan approved');
});