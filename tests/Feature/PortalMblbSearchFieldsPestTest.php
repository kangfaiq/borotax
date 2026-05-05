<?php

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Instansi;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\HargaPatokanMblb;
use App\Domain\Tax\Models\TaxObject;
use App\Enums\InstansiKategori;
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

it('shows searchable material and instansi fields on portal mblb create page', function (): void {
    $portalUser = User::create([
        'name' => 'Portal MBLB Search User',
        'nama_lengkap' => 'Portal MBLB Search User',
        'email' => sprintf('portal-mblb-search-%s@example.test', Str::random(6)),
        'password' => Hash::make('password'),
        'nik' => '3522011234567001',
        'alamat' => 'Jl. Portal Search No. 1',
        'role' => 'wajibPajak',
        'status' => 'verified',
        'email_verified_at' => now(),
    ]);

    $jenisPajak = JenisPajak::where('kode', '41106')->firstOrFail();
    $subJenisPajak = SubJenisPajak::where('kode', 'MBLB_WAPU')->firstOrFail();

    TaxObject::create([
        'nik' => $portalUser->nik,
        'nama_objek_pajak' => 'Tambang Search WAPU',
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'npwpd' => 'P100000000777',
        'nopd' => 7771,
        'alamat_objek' => 'Jl. Tambang Search KM 7',
        'kelurahan' => 'Sukorejo',
        'kecamatan' => 'Bojonegoro',
        'tanggal_daftar' => now()->toDateString(),
        'is_active' => true,
        'is_opd' => false,
        'is_insidentil' => false,
        'tarif_persen' => 20,
    ]);

    HargaPatokanMblb::create([
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'nama_mineral' => 'Batu Gamping Search',
        'harga_patokan' => 120000,
        'satuan' => 'm3',
        'dasar_hukum' => 'Peraturan Harga Patokan MBLB',
        'is_active' => true,
    ]);

    Instansi::create([
        'kode' => 'INST-SEARCH',
        'nama' => 'Dinas Pekerjaan Umum Search',
        'kategori' => InstansiKategori::Instansi,
        'alamat' => 'Jl. Instansi Search No. 1',
        'asal_wilayah' => 'bojonegoro',
        'is_active' => true,
    ]);

    $response = $this->actingAs($portalUser)
        ->get(route('portal.self-assessment.create', $jenisPajak->id));

    $response->assertOk()
        ->assertSee('id="inputMineralCombobox"', false)
        ->assertSee('id="mineralCombobox"', false)
        ->assertSee('data-mineral-label="batu gamping search m3"', false)
        ->assertSee('id="inputInstansiCombobox"', false)
        ->assertSee('id="instansiCombobox"', false)
        ->assertSee('Cari instansi / lembaga...', false)
        ->assertDontSee('id="inputMineralSearch"', false)
        ->assertDontSee('id="inputInstansiSearch"', false);
});