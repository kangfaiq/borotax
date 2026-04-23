<?php

use App\Domain\AirTanah\Models\MeterReport;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Filament\Resources\MeterReportResource\Pages\ListMeterReports;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('processes submitted meter report from comma decimal input', function () {
    $this->seed([
        JenisPajakSeeder::class,
        SubJenisPajakSeeder::class,
    ]);

    $jenisPajak = JenisPajak::where('kode', '41108')->firstOrFail();
    $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();
    $petugas = createMeterReportDecimalPetugasFixture();

    $wajibPajak = User::create([
        'name' => 'Portal Meter User',
        'email' => sprintf('portal-meter-%s@example.test', Str::random(6)),
        'password' => Hash::make('password'),
        'nik' => '3522011234567891',
        'nama_lengkap' => 'Portal Meter User',
        'alamat' => 'Jl. Sudirman No. 4',
        'role' => 'wajibPajak',
        'status' => 'verified',
        'email_verified_at' => now(),
    ]);

    $waterObject = WaterObject::create([
        'nik' => '3522011234567891',
        'nama_objek_pajak' => 'Sumur Cadangan',
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'jenis_sumber' => 'sumurBor',
        'npwpd' => 'P100000000011',
        'nopd' => 2002,
        'alamat_objek' => 'Jl. Rajawali No. 2',
        'kelurahan' => 'Kadipaten',
        'kecamatan' => 'Bojonegoro',
        'last_meter_reading' => 80,
        'tanggal_daftar' => now()->toDateString(),
        'is_active' => true,
        'is_opd' => false,
        'tarif_persen' => 20,
        'kelompok_pemakaian' => '1',
        'kriteria_sda' => '1',
        'uses_meter' => true,
    ]);

    $report = MeterReport::create([
        'tax_object_id' => $waterObject->id,
        'user_id' => $wajibPajak->id,
        'user_nik' => '3522011234567891',
        'user_name' => 'Portal Meter User',
        'meter_reading_before' => 80,
        'meter_reading_after' => 110,
        'usage' => 30,
        'photo_url' => 'meter-reports/sample-2.jpg',
        'latitude' => '-7.1500000',
        'longitude' => '111.8800000',
        'location_verified' => true,
        'status' => 'submitted',
        'reported_at' => now()->subDay(),
    ]);

    $this->actingAs($petugas);

    Livewire::test(ListMeterReports::class)
        ->assertCanSeeTableRecords([$report])
        ->callTableAction('process', $report, [
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'meter_reading_before' => '80,50',
            'meter_reading_after' => '110,75',
            'tarif_per_m3' => '1.000,00',
            'tarif_persen' => '20,00',
        ])
        ->assertHasNoTableActionErrors();

    $report->refresh();
    $draft = SkpdAirTanah::where('meter_report_id', $report->id)->firstOrFail();

    expect($report->status)->toBe('processing')
        ->and((float) $report->meter_reading_before)->toBe(80.5)
        ->and((float) $report->meter_reading_after)->toBe(110.75)
        ->and((float) $report->usage)->toBe(30.25)
        ->and((float) $draft->meter_reading_before)->toBe(80.5)
        ->and((float) $draft->meter_reading_after)->toBe(110.75)
        ->and((float) $draft->usage)->toBe(30.25)
        ->and((float) $draft->dasar_pengenaan)->toBe(30250.0)
        ->and((float) $draft->jumlah_pajak)->toBe(6050.0)
        ->and($draft->status)->toBe('draft');
});

function createMeterReportDecimalPetugasFixture(): User
{
    return User::create([
        'name' => 'Petugas Meter Decimal',
        'nama_lengkap' => 'Petugas Meter Decimal',
        'email' => sprintf('petugas-meter-%s@example.test', Str::random(8)),
        'password' => Hash::make('password'),
        'role' => 'petugas',
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}