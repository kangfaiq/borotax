<?php

namespace Tests\Feature;

use App\Domain\AirTanah\Models\MeterReport;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\ReklameObject;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_submit_report_rejects_water_object_owned_by_another_user(): void
    {
        $this->seed([JenisPajakSeeder::class, SubJenisPajakSeeder::class]);

        $owner = $this->createUser('owner@example.test', '3522011234567890', 'Owner User', 'user');
        $intruder = $this->createUser('intruder@example.test', '3522011234567891', 'Intruder User', 'user');
        $waterObject = $this->createWaterObjectFor($owner, 'Objek Sumur Owner');

        Sanctum::actingAs($intruder);

        $response = $this->postJson('/api/v1/water-reports', [
            'tax_object_id' => $waterObject->id,
            'meter_reading_before' => 100,
            'meter_reading_after' => 120,
            'foto_meter' => UploadedFile::fake()->image('meter.jpg'),
            'latitude' => -7.1500000,
            'longitude' => 111.8800000,
        ]);

        $response
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Objek air tanah tidak ditemukan atau bukan milik Anda.',
            ]);

        $this->assertDatabaseCount('meter_reports', 0);
        $this->assertNull(MeterReport::first());
    }

    public function test_api_submit_extension_rejects_reklame_object_that_is_not_yet_eligible(): void
    {
        $this->seedReklameTaxReferences();

        $user = $this->createUser('reklame@example.test', '3522011234567892', 'Reklame User', 'user');
        $object = $this->createReklameObjectFor($user, now()->addDays(45));

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/reklame-extensions', [
            'tax_object_id' => $object->id,
            'durasi_perpanjangan_hari' => 30,
            'catatan_pengajuan' => 'Ajukan terlalu awal',
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Perpanjangan hanya dapat diajukan ketika objek sudah kedaluwarsa atau sisa masa berlaku maksimal 30 hari.',
            ]);

        $this->assertDatabaseCount('reklame_requests', 0);
    }

    public function test_portal_store_extension_rejects_reklame_object_that_is_not_yet_eligible(): void
    {
        $this->seedReklameTaxReferences();

        $user = $this->createUser('portal-reklame@example.test', '3522011234567893', 'Portal Reklame User', 'wajibPajak');
        $object = $this->createReklameObjectFor($user, now()->addDays(45));

        $this->actingAs($user);

        $response = $this->post(route('portal.reklame.store-extension', ['objectId' => $object->id]), [
            'durasi_perpanjangan_hari' => 30,
            'catatan_pengajuan' => 'Ajukan terlalu awal via portal',
        ]);

        $response
            ->assertRedirect(route('portal.reklame.object-detail', ['objectId' => $object->id]))
            ->assertSessionHas('error', 'Perpanjangan hanya dapat diajukan ketika objek sudah kedaluwarsa atau sisa masa berlaku maksimal 30 hari.');

        $this->assertDatabaseCount('reklame_requests', 0);
    }

    private function createUser(string $email, string $nik, string $namaLengkap, string $role): User
    {
        return User::create([
            'name' => $namaLengkap,
            'nama_lengkap' => $namaLengkap,
            'email' => $email,
            'password' => Hash::make('password'),
            'nik' => $nik,
            'nik_hash' => User::generateHash($nik),
            'alamat' => 'Jl. Panglima Sudirman No. 1',
            'role' => $role,
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
        ]);
    }

    private function createWaterObjectFor(User $user, string $name): WaterObject
    {
        $jenisPajak = JenisPajak::where('kode', '41108')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();

        return WaterObject::create([
            'nik' => $user->nik,
            'nik_hash' => WaterObject::generateHash($user->nik),
            'nama_objek_pajak' => $name,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'jenis_sumber' => 'sumurBor',
            'npwpd' => 'P10000001001',
            'nopd' => 1001,
            'alamat_objek' => 'Jl. Veteran No. 12',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tarif_persen' => 20,
            'tanggal_daftar' => now()->subMonth(),
            'is_active' => true,
        ]);
    }

    private function createReklameObjectFor(User $user, $masaBerlakuSampai): ReklameObject
    {
        $jenisPajak = JenisPajak::where('kode', '41104')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->firstOrFail();

        return ReklameObject::create([
            'nik' => $user->nik,
            'nik_hash' => ReklameObject::generateHash($user->nik),
            'nama_objek_pajak' => 'Billboard Simpang Lima',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P10000001002',
            'nopd' => 1002,
            'alamat_objek' => 'Jl. Ahmad Yani No. 10',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'panjang' => 4,
            'lebar' => 6,
            'bentuk' => 'persegi',
            'jumlah_muka' => 1,
            'status' => 'aktif',
            'tarif_persen' => 25,
            'tanggal_daftar' => now()->subMonths(6),
            'tanggal_pasang' => now()->subMonths(6),
            'masa_berlaku_sampai' => $masaBerlakuSampai,
            'is_active' => true,
        ]);
    }
}