<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Region\Models\Village;
use App\Domain\Shared\Models\DataChangeRequest;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Filament\Resources\DataChangeRequestResource\Pages\ListDataChangeRequests;
use App\Filament\Resources\TaxObjectResource\Pages\EditTaxObject;
use App\Filament\Resources\WajibPajakResource\Pages\EditWajibPajak;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class DataChangeRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_can_submit_wajib_pajak_change_request_and_verifikator_can_approve_it(): void
    {
        $wajibPajak = $this->createApprovedWajibPajak();
        $petugas = $this->createAdminPanelUser('petugas');
        $verifikator = $this->createAdminPanelUser('verifikator');
        $namaBaru = 'Budi Perubahan';
        $alamatBaru = 'Jl. Panglima Sudirman No. 99';

        $this->actingAs($petugas);

        Livewire::test(EditWajibPajak::class, ['record' => $wajibPajak->getRouteKey()])
            ->fillForm([
                'nik' => '3522011234567890',
                'nama_lengkap' => $namaBaru,
                'alamat' => $alamatBaru,
                'tipe_wajib_pajak' => 'perorangan',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $wajibPajak->refresh();
        $request = DataChangeRequest::firstOrFail();

        $this->assertSame('Budi Lama', $wajibPajak->nama_lengkap);
        $this->assertSame('Jl. Veteran No. 12', $wajibPajak->alamat);
        $this->assertSame('pending', $request->status);
        $this->assertSame('wajib_pajak', $request->entity_type);
        $this->assertSame($wajibPajak->id, $request->entity_id);
        $this->assertSame($petugas->id, $request->requested_by);
        $this->assertSame('Budi Lama', $request->field_changes['nama_lengkap']['old']);
        $this->assertSame($namaBaru, $request->field_changes['nama_lengkap']['new']);
        $this->assertSame('Jl. Veteran No. 12', $request->field_changes['alamat']['old']);
        $this->assertSame($alamatBaru, $request->field_changes['alamat']['new']);

        $this->actingAs($verifikator);

        Livewire::test(ListDataChangeRequests::class)
            ->assertCanSeeTableRecords([$request])
            ->callTableAction('approve', $request, [
                'catatan_review' => 'Perubahan valid.',
            ])
            ->assertHasNoTableActionErrors();

        $request->refresh();
        $wajibPajak->refresh();

        $this->assertSame('approved', $request->status);
        $this->assertSame('Perubahan valid.', $request->catatan_review);
        $this->assertSame($verifikator->id, $request->reviewed_by);
        $this->assertNotNull($request->reviewed_at);
        $this->assertSame($namaBaru, $wajibPajak->nama_lengkap);
        $this->assertSame($alamatBaru, $wajibPajak->alamat);
    }

    public function test_verifikator_can_reject_pending_wajib_pajak_change_request_without_updating_entity(): void
    {
        $wajibPajak = $this->createApprovedWajibPajak();
        $petugas = $this->createAdminPanelUser('petugas');
        $verifikator = $this->createAdminPanelUser('verifikator');
        $catatanReview = 'Perubahan ditolak karena dokumen pendukung belum tersedia.';

        $this->actingAs($petugas);

        $request = DataChangeRequest::createRequest(
            entity: $wajibPajak,
            fieldChanges: [
                'nama_lengkap' => 'Budi Ditolak',
                'alamat' => 'Jl. Hayam Wuruk No. 21',
            ],
            alasanPerubahan: 'Koreksi identitas wajib pajak.',
        );

        $this->actingAs($verifikator);

        Livewire::test(ListDataChangeRequests::class)
            ->assertCanSeeTableRecords([$request])
            ->mountTableAction('reject', $request)
            ->set('mountedActions.0.data.catatan_review', $catatanReview)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $request->refresh();
        $wajibPajak->refresh();

        $this->assertSame('rejected', $request->status);
        $this->assertSame($catatanReview, $request->catatan_review);
        $this->assertSame($verifikator->id, $request->reviewed_by);
        $this->assertNotNull($request->reviewed_at);
        $this->assertSame('Budi Lama', $wajibPajak->nama_lengkap);
        $this->assertSame('Jl. Veteran No. 12', $wajibPajak->alamat);
    }

    public function test_petugas_can_submit_tax_object_change_request_and_verifikator_can_approve_it(): void
    {
        $taxObject = $this->createTaxObject();
        $petugas = $this->createAdminPanelUser('petugas');
        $verifikator = $this->createAdminPanelUser('verifikator');
        $namaBaru = 'Objek Hotel Baru';
        $alamatBaru = 'Jl. Diponegoro No. 77';

        $this->actingAs($petugas);

        Livewire::test(EditTaxObject::class, ['record' => $taxObject->getRouteKey()])
            ->fillForm([
                'npwpd' => $taxObject->npwpd,
                'jenis_pajak_id' => $taxObject->jenis_pajak_id,
                'sub_jenis_pajak_id' => $taxObject->sub_jenis_pajak_id,
                'nama_objek_pajak' => $namaBaru,
                'alamat_objek' => $alamatBaru,
                'kecamatan' => $taxObject->kecamatan,
                'kelurahan' => $taxObject->kelurahan,
                'tarif_persen' => (float) $taxObject->tarif_persen,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $taxObject->refresh();
        $request = DataChangeRequest::where('entity_type', 'tax_objects')->firstOrFail();

        $this->assertSame('Objek Hotel Lama', $taxObject->nama_objek_pajak);
        $this->assertSame('Jl. Ahmad Yani No. 1', $taxObject->alamat_objek);
        $this->assertSame('pending', $request->status);
        $this->assertSame('tax_objects', $request->entity_type);
        $this->assertSame($taxObject->id, $request->entity_id);
        $this->assertSame($petugas->id, $request->requested_by);
        $this->assertSame('Objek Hotel Lama', $request->field_changes['nama_objek_pajak']['old']);
        $this->assertSame($namaBaru, $request->field_changes['nama_objek_pajak']['new']);
        $this->assertSame('Jl. Ahmad Yani No. 1', $request->field_changes['alamat_objek']['old']);
        $this->assertSame($alamatBaru, $request->field_changes['alamat_objek']['new']);

        $this->actingAs($verifikator);

        Livewire::test(ListDataChangeRequests::class)
            ->assertCanSeeTableRecords([$request])
            ->callTableAction('approve', $request, [
                'catatan_review' => 'Perubahan objek pajak valid.',
            ])
            ->assertHasNoTableActionErrors();

        $request->refresh();
        $taxObject->refresh();

        $this->assertSame('approved', $request->status);
        $this->assertSame('Perubahan objek pajak valid.', $request->catatan_review);
        $this->assertSame($verifikator->id, $request->reviewed_by);
        $this->assertNotNull($request->reviewed_at);
        $this->assertSame($namaBaru, $taxObject->nama_objek_pajak);
        $this->assertSame($alamatBaru, $taxObject->alamat_objek);
    }

    public function test_verifikator_can_reject_pending_tax_object_change_request_without_updating_entity(): void
    {
        $taxObject = $this->createTaxObject();
        $petugas = $this->createAdminPanelUser('petugas');
        $verifikator = $this->createAdminPanelUser('verifikator');
        $catatanReview = 'Perubahan objek pajak ditolak karena bukti lapangan belum lengkap.';

        $this->actingAs($petugas);

        $request = DataChangeRequest::createRequest(
            entity: $taxObject,
            fieldChanges: [
                'nama_objek_pajak' => 'Objek Hotel Ditolak',
                'alamat_objek' => 'Jl. Teuku Umar No. 11',
            ],
            alasanPerubahan: 'Koreksi alamat objek pajak.',
        );

        $this->actingAs($verifikator);

        Livewire::test(ListDataChangeRequests::class)
            ->assertCanSeeTableRecords([$request])
            ->mountTableAction('reject', $request)
            ->set('mountedActions.0.data.catatan_review', $catatanReview)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $request->refresh();
        $taxObject->refresh();

        $this->assertSame('rejected', $request->status);
        $this->assertSame($catatanReview, $request->catatan_review);
        $this->assertSame($verifikator->id, $request->reviewed_by);
        $this->assertNotNull($request->reviewed_at);
        $this->assertSame('Objek Hotel Lama', $taxObject->nama_objek_pajak);
        $this->assertSame('Jl. Ahmad Yani No. 1', $taxObject->alamat_objek);
    }

    private function createApprovedWajibPajak(): WajibPajak
    {
        $user = User::create([
            'name' => 'Portal User ' . Str::random(6),
            'email' => sprintf('portal-%s@example.test', Str::random(8)),
            'password' => Hash::make('password'),
            'nik' => '3522011234567890',
            'nama_lengkap' => 'Budi Lama',
            'alamat' => 'Jl. Veteran No. 12',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        return WajibPajak::create([
            'user_id' => $user->id,
            'nik' => '3522011234567890',
            'nama_lengkap' => 'Budi Lama',
            'alamat' => 'Jl. Veteran No. 12',
            'asal_wilayah' => 'bojonegoro',
            'district_code' => null,
            'village_code' => null,
            'tipe_wajib_pajak' => 'perorangan',
            'status' => 'disetujui',
            'npwpd' => 'P100000000001',
            'tanggal_daftar' => now()->subDays(5),
            'tanggal_verifikasi' => now()->subDays(4),
        ]);
    }

    private function createTaxObject(): TaxObject
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $wajibPajak = $this->createApprovedWajibPajak();
        $jenisPajak = JenisPajak::where('kode', '41101')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();
        $province = Province::create([
            'code' => '35',
            'name' => 'Jawa Timur',
        ]);
        Regency::create([
            'province_code' => $province->code,
            'code' => '35.22',
            'name' => 'Kabupaten Bojonegoro',
        ]);
        $district = District::create([
            'regency_code' => '35.22',
            'code' => '35.22.01',
            'name' => 'Bojonegoro',
        ]);
        $village = Village::create([
            'district_code' => $district->code,
            'code' => '35.22.01.2001',
            'name' => 'Kadipaten',
            'postal_code' => '62111',
        ]);

        return TaxObject::create([
            'nik' => '3522011234567890',
            'nama_objek_pajak' => 'Objek Hotel Lama',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => $wajibPajak->npwpd,
            'nopd' => 1101,
            'alamat_objek' => 'Jl. Ahmad Yani No. 1',
            'kelurahan' => $village->name,
            'kecamatan' => $district->name,
            'tarif_persen' => 10,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'is_insidentil' => false,
        ]);
    }

    private function createAdminPanelUser(string $role): User
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
}