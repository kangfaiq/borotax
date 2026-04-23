<?php

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\HargaPatokanMblb;
use App\Domain\Tax\Models\HargaPatokanSarangWalet;
use App\Domain\Tax\Models\HargaSatuanListrik;
use App\Domain\Tax\Models\PortalMblbSubmission;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use Carbon\Carbon;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-03-30 10:00:00');

    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

it('accepts comma decimal input for portal ppj non pln billing', function () {
    Storage::fake('local');

    $portalUser = createPortalDecimalTestUser('portal-ppj');
    $jenisPajak = JenisPajak::where('kode', '41105')->firstOrFail();
    $subJenisPajak = SubJenisPajak::where('kode', 'PPJ_DIHASILKAN_SENDIRI')->firstOrFail();

    $taxObject = TaxObject::create([
        'nik' => $portalUser->nik,
        'nama_objek_pajak' => 'Objek PPJ Non PLN',
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'npwpd' => 'P100000000455',
        'nopd' => 7101,
        'alamat_objek' => 'Jl. PPJ Raya No. 7',
        'kelurahan' => 'Kadipaten',
        'kecamatan' => 'Bojonegoro',
        'tanggal_daftar' => now()->toDateString(),
        'is_active' => true,
        'is_opd' => false,
        'is_insidentil' => false,
        'tarif_persen' => (float) $subJenisPajak->tarif_persen,
    ]);

    $hargaSatuan = HargaSatuanListrik::create([
        'nama_wilayah' => 'Kabupaten Bojonegoro',
        'harga_per_kwh' => 1500,
        'dasar_hukum' => 'Peraturan Harga Satuan Listrik',
        'berlaku_mulai' => now()->startOfYear(),
        'berlaku_sampai' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($portalUser)->post(route('portal.self-assessment.store'), [
        'tax_object_id' => $taxObject->id,
        'kapasitas_kva' => '100,50',
        'tingkat_penggunaan_persen' => '80,25',
        'jangka_waktu_jam' => '10,75',
        'harga_satuan_listrik_id' => $hargaSatuan->id,
        'attachment' => UploadedFile::fake()->create('lampiran.pdf', 200, 'application/pdf'),
        'bulan' => 3,
        'tahun' => 2026,
    ]);

    $tax = Tax::with('ppjDetail')->firstOrFail();

    $response->assertRedirect(route('portal.self-assessment.success', $tax->id));

    expect((float) $tax->ppjDetail->kapasitas_kva)->toBe(100.5)
        ->and((float) $tax->ppjDetail->tingkat_penggunaan_persen)->toBe(80.25)
        ->and((float) $tax->ppjDetail->jangka_waktu_jam)->toBe(10.75);
});

it('accepts comma decimal input for portal sarang walet billing', function () {
    Storage::fake('local');

    $portalUser = createPortalDecimalTestUser('portal-walet');
    $jenisPajak = JenisPajak::where('kode', '41109')->firstOrFail();
    $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->orderBy('urutan')->firstOrFail();

    $taxObject = TaxObject::create([
        'nik' => $portalUser->nik,
        'nama_objek_pajak' => 'Rumah Walet Sekarjati',
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'npwpd' => 'P100000000311',
        'nopd' => 7301,
        'alamat_objek' => 'Jl. Walet Makmur No. 8',
        'kelurahan' => 'Sukorejo',
        'kecamatan' => 'Bojonegoro',
        'tanggal_daftar' => now()->toDateString(),
        'is_active' => true,
        'is_opd' => false,
        'is_insidentil' => false,
        'tarif_persen' => 10,
    ]);

    $hargaPatokan = HargaPatokanSarangWalet::create([
        'nama_jenis' => 'Sarang Walet Mangkuk',
        'harga_patokan' => 6000000,
        'satuan' => 'kg',
        'dasar_hukum' => 'Peraturan Harga Patokan Sarang Walet',
        'berlaku_mulai' => now()->startOfYear(),
        'berlaku_sampai' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($portalUser)->post(route('portal.self-assessment.store'), [
        'tax_object_id' => $taxObject->id,
        'jenis_sarang_id' => $hargaPatokan->id,
        'volume_kg' => '2,50',
        'attachment' => UploadedFile::fake()->create('lampiran.pdf', 200, 'application/pdf'),
        'tahun' => 2026,
    ]);

    $tax = Tax::with('sarangWaletDetail')->firstOrFail();

    $response->assertRedirect(route('portal.self-assessment.success', $tax->id));

    expect((float) $tax->sarangWaletDetail->volume_kg)->toBe(2.5)
        ->and((float) $tax->sarangWaletDetail->subtotal_dpp)->toBe(15000000.0);
});

it('accepts comma decimal input for portal mblb submission', function () {
    Storage::fake('public');

    $portalUser = createPortalDecimalTestUser('portal-mblb');
    $jenisPajak = JenisPajak::where('kode', '41106')->firstOrFail();
    $subJenisPajak = SubJenisPajak::where('kode', 'MBLB_WP')->firstOrFail();

    $taxObject = TaxObject::create([
        'nik' => $portalUser->nik,
        'nama_objek_pajak' => 'Tambang Desa Sumberrejo',
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'npwpd' => 'P100000000099',
        'nopd' => 6101,
        'alamat_objek' => 'Jl. Tambang Raya KM 3',
        'kelurahan' => 'Sumberrejo',
        'kecamatan' => 'Bojonegoro',
        'tanggal_daftar' => now()->toDateString(),
        'is_active' => true,
        'is_opd' => false,
        'is_insidentil' => false,
        'tarif_persen' => 20,
    ]);

    $mineral = HargaPatokanMblb::create([
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'nama_mineral' => 'Batu Andesit',
        'harga_patokan' => 100000,
        'satuan' => 'm3',
        'dasar_hukum' => 'Peraturan Harga Patokan MBLB',
        'is_active' => true,
    ]);

    $response = $this->actingAs($portalUser)->post(route('portal.self-assessment.store'), [
        'tax_object_id' => $taxObject->id,
        'attachment' => UploadedFile::fake()->image('lampiran.jpg', 2400, 1800)->size(2500),
        'volumes' => [
            $mineral->id => '3,50',
        ],
        'bulan' => now()->month,
        'tahun' => now()->year,
    ]);

    $submission = PortalMblbSubmission::query()->firstOrFail();

    $response->assertRedirect(route('portal.self-assessment.submission-success', $submission->id));

    expect($submission->detail_items)->toHaveCount(1)
        ->and((float) $submission->detail_items[0]['volume'])->toBe(3.5)
        ->and((float) $submission->total_dpp)->toBe(350000.0)
        ->and((float) $submission->pokok_pajak)->toBe(70000.0);
});

function createPortalDecimalTestUser(string $prefix): User
{
    return User::create([
        'name' => 'Portal Decimal User',
        'nama_lengkap' => 'Portal Decimal User',
        'email' => sprintf('%s-%s@example.test', $prefix, Str::random(6)),
        'password' => Hash::make('password'),
        'nik' => (string) random_int(3522011000000000, 3522019999999999),
        'alamat' => 'Jl. Pengujian No. 1',
        'role' => 'wajibPajak',
        'status' => 'verified',
        'email_verified_at' => now(),
    ]);
}