<?php

namespace Tests\Feature;

use App\Filament\Resources\ObjekRetribusiSewaTanahResource;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\BuildsDomainFixtures;
use Tests\TestCase;

class ObjekRetribusiSewaTanahResourceTest extends TestCase
{
    use BuildsDomainFixtures;
    use RefreshDatabase;

    public function test_search_approved_wajib_pajak_options_supports_npwpd_nik_and_name(): void
    {
        $this->seedReferences();

        $budi = $this->createApprovedWajibPajakFixture([
            'npwpd' => 'P100000000101',
        ], [
            'nik' => '3522123412341301',
            'nama_lengkap' => 'Budi Santoso',
        ]);

        $this->createApprovedWajibPajakFixture([
            'npwpd' => 'P100000000102',
        ], [
            'nik' => '3522123412341302',
            'nama_lengkap' => 'Siti Aminah',
        ]);

        $npwpdResults = ObjekRetribusiSewaTanahResource::searchApprovedWajibPajakOptions('0000101');
        $nikResults = ObjekRetribusiSewaTanahResource::searchApprovedWajibPajakOptions('3522123412341301');
        $nameResults = ObjekRetribusiSewaTanahResource::searchApprovedWajibPajakOptions('budi santoso');

        $this->assertArrayHasKey($budi->npwpd, $npwpdResults);
        $this->assertArrayHasKey($budi->npwpd, $nikResults);
        $this->assertArrayHasKey($budi->npwpd, $nameResults);
        $this->assertSame('P100000000101 - Budi Santoso', $nameResults[$budi->npwpd]);
    }

    public function test_sync_owner_data_uses_wajib_pajak_from_selected_npwpd(): void
    {
        $this->seedReferences();

        $wajibPajak = $this->createApprovedWajibPajakFixture([
            'npwpd' => 'P100000000001',
        ], [
            'nik' => '3522123412341234',
            'nama_lengkap' => 'Budi Santoso',
            'alamat' => 'Jl. Pemilik No. 1',
        ]);

        $taxObject = $this->createTaxObjectFixture($wajibPajak, '41104', [
            'nama_objek_pajak' => 'Reklame Simpang Lima',
            'alamat_objek' => 'Jl. Veteran No. 10',
            'kecamatan' => 'Bojonegoro',
            'kelurahan' => 'Kadipaten',
            'luas_m2' => 12.5,
        ]);

        $data = ObjekRetribusiSewaTanahResource::syncOwnerData([
            'npwpd' => $wajibPajak->npwpd,
            'tax_object_id' => $taxObject->id,
            'nama_objek' => 'Objek Retribusi Sewa Tanah',
            'alamat_objek' => 'Jl. Retribusi No. 5',
            'kecamatan' => 'Bojonegoro',
            'kelurahan' => 'Kadipaten',
        ]);

        $this->assertSame($wajibPajak->npwpd, $data['npwpd']);
        $this->assertSame($wajibPajak->nik, $data['nik']);
        $this->assertSame($wajibPajak->nik_hash, $data['nik_hash']);
        $this->assertSame($wajibPajak->nama_lengkap, $data['nama_pemilik']);
        $this->assertSame($wajibPajak->alamat, $data['alamat_pemilik']);
        $this->assertSame('Objek Retribusi Sewa Tanah', $data['nama_objek']);
        $this->assertSame('Jl. Retribusi No. 5', $data['alamat_objek']);
        $this->assertSame('Bojonegoro', $data['kecamatan']);
        $this->assertSame('Kadipaten', $data['kelurahan']);
        $this->assertEquals(12.5, $data['luas_m2']);
    }

    public function test_sync_owner_data_rejects_mismatched_npwpd_and_reklame_object(): void
    {
        $this->seedReferences();

        $wajibPajakPertama = $this->createApprovedWajibPajakFixture([
            'npwpd' => 'P100000000010',
        ], [
            'nik' => '3522123412341201',
            'nama_lengkap' => 'WP Pertama',
        ]);

        $wajibPajakKedua = $this->createApprovedWajibPajakFixture([
            'npwpd' => 'P100000000011',
        ], [
            'nik' => '3522123412341202',
            'nama_lengkap' => 'WP Kedua',
        ]);

        $taxObject = $this->createTaxObjectFixture($wajibPajakKedua, '41104', [
            'nama_objek_pajak' => 'Reklame Milik WP Kedua',
        ]);

        $this->expectException(ValidationException::class);

        ObjekRetribusiSewaTanahResource::syncOwnerData([
            'npwpd' => $wajibPajakPertama->npwpd,
            'tax_object_id' => $taxObject->id,
            'nama_objek' => 'Objek Retribusi Tidak Valid',
            'alamat_objek' => 'Jl. Tidak Valid',
            'kecamatan' => 'Bojonegoro',
            'kelurahan' => 'Kadipaten',
        ]);
    }

    private function seedReferences(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);
    }
}
