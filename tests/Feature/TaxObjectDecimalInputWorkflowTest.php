<?php

use App\Domain\Auth\Models\User;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Region\Models\Village;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Domain\Reklame\Models\KelompokLokasiJalan;
use App\Domain\Shared\Models\DataChangeRequest;
use App\Domain\Tax\Models\TaxObject;
use App\Filament\Resources\TaxObjectResource;
use App\Filament\Resources\TaxObjectResource\Pages\CreateTaxObject;
use App\Filament\Resources\TaxObjectResource\Pages\EditTaxObject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates reklame tax objects from comma decimal input', function () {
    $this->seedReklameTaxReferences();
    seedTaxObjectDecimalRegionFixtures();

    $petugas = createTaxObjectDecimalAdminFixture('petugas');
    $wajibPajak = $this->createApprovedWajibPajakFixture();
    $subJenis = SubJenisPajak::where('kode', 'REKLAME_TETAP')->firstOrFail();
    $hargaPatokan = HargaPatokanReklame::where('kode', 'RKL_NEON_BOX')->firstOrFail();
    $lokasiJalan = KelompokLokasiJalan::where('nama_jalan', 'Jalan Panglima Sudirman')->firstOrFail();

    $this->actingAs($petugas);

    Livewire::test(CreateTaxObject::class)
        ->set('data.npwpd', $wajibPajak->npwpd)
        ->set('data.jenis_pajak_id', $subJenis->jenis_pajak_id)
        ->set('data.sub_jenis_pajak_id', $subJenis->id)
        ->set('data.nama_objek_pajak', 'Reklame Desimal Baru')
        ->set('data.alamat_objek', 'Jl. Panglima Sudirman No. 10')
        ->set('data.kecamatan', 'Bojonegoro')
        ->set('data.kelurahan', 'Kadipaten')
        ->set('data.tarif_persen', '25,50')
        ->set('data.bentuk', 'persegi')
        ->set('data.panjang', '12,50')
        ->set('data.lebar', '8,25')
        ->set('data.jumlah_muka', 2)
        ->set('data.harga_patokan_reklame_id', $hargaPatokan->id)
        ->set('data.lokasi_jalan_id', $lokasiJalan->id)
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect(TaxObjectResource::getUrl('index'));

    $taxObject = TaxObject::query()->latest('created_at')->firstOrFail();

    expect($taxObject->npwpd)->toBe($wajibPajak->npwpd)
        ->and($taxObject->bentuk)->toBe('persegi')
        ->and($taxObject->kelompok_lokasi)->toBe($lokasiJalan->kelompok)
        ->and((float) $taxObject->tarif_persen)->toBe(25.5)
        ->and((float) $taxObject->panjang)->toBe(12.5)
        ->and((float) $taxObject->lebar)->toBe(8.25)
        ->and($taxObject->jumlah_muka)->toBe(2);
});

it('queues normalized decimal changes when editing reklame tax objects', function () {
    $this->seedReklameTaxReferences();
    seedTaxObjectDecimalRegionFixtures();

    $petugas = createTaxObjectDecimalAdminFixture('petugas');
    $wajibPajak = $this->createApprovedWajibPajakFixture();
    $subJenis = SubJenisPajak::where('kode', 'REKLAME_TETAP')->firstOrFail();
    $hargaPatokan = HargaPatokanReklame::where('kode', 'RKL_NEON_BOX')->firstOrFail();
    $lokasiJalan = KelompokLokasiJalan::where('nama_jalan', 'Jalan Panglima Sudirman')->firstOrFail();
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41104', [
        'sub_jenis_pajak_id' => $subJenis->id,
        'nama_objek_pajak' => 'Reklame Existing',
        'alamat_objek' => 'Jl. Panglima Sudirman No. 8',
        'kecamatan' => 'Bojonegoro',
        'kelurahan' => 'Kadipaten',
        'tarif_persen' => 20,
        'bentuk' => 'persegi',
        'panjang' => 10,
        'lebar' => 6,
        'jumlah_muka' => 1,
        'harga_patokan_reklame_id' => $hargaPatokan->id,
        'lokasi_jalan_id' => $lokasiJalan->id,
        'kelompok_lokasi' => $lokasiJalan->kelompok,
    ]);

    $this->actingAs($petugas);

    Livewire::test(EditTaxObject::class, ['record' => $taxObject->getRouteKey()])
        ->set('data.npwpd', $taxObject->npwpd)
        ->set('data.jenis_pajak_id', $taxObject->jenis_pajak_id)
        ->set('data.sub_jenis_pajak_id', $taxObject->sub_jenis_pajak_id)
        ->set('data.nama_objek_pajak', $taxObject->nama_objek_pajak)
        ->set('data.alamat_objek', $taxObject->alamat_objek)
        ->set('data.kecamatan', $taxObject->kecamatan)
        ->set('data.kelurahan', $taxObject->kelurahan)
        ->set('data.tarif_persen', '27,75')
        ->set('data.bentuk', 'persegi')
        ->set('data.panjang', '14,50')
        ->set('data.lebar', '9,25')
        ->set('data.jumlah_muka', $taxObject->jumlah_muka)
        ->set('data.harga_patokan_reklame_id', $taxObject->harga_patokan_reklame_id)
        ->set('data.lokasi_jalan_id', $taxObject->lokasi_jalan_id)
        ->call('save')
        ->assertHasNoFormErrors();

    $taxObject->refresh();
    $request = DataChangeRequest::where('entity_type', 'tax_objects')->firstOrFail();

    expect($request->status)->toBe('pending')
        ->and($request->requested_by)->toBe($petugas->id)
        ->and((float) $request->field_changes['tarif_persen']['new'])->toBe(27.75)
        ->and((float) $request->field_changes['panjang']['new'])->toBe(14.5)
        ->and((float) $request->field_changes['lebar']['new'])->toBe(9.25)
        ->and((float) $taxObject->tarif_persen)->toBe(20.0)
        ->and((float) $taxObject->panjang)->toBe(10.0)
        ->and((float) $taxObject->lebar)->toBe(6.0);
});

function seedTaxObjectDecimalRegionFixtures(): void
{
    Province::create([
        'code' => '35',
        'name' => 'Jawa Timur',
    ]);

    Regency::create([
        'province_code' => '35',
        'code' => '35.22',
        'name' => 'Kabupaten Bojonegoro',
    ]);

    District::create([
        'regency_code' => '35.22',
        'code' => '35.22.01',
        'name' => 'Bojonegoro',
    ]);

    Village::create([
        'district_code' => '35.22.01',
        'code' => '35.22.01.2001',
        'name' => 'Kadipaten',
    ]);
}

function createTaxObjectDecimalAdminFixture(string $role): User
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