<?php

namespace Database\Seeders;

use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Regency;
use Illuminate\Database\Seeder;

class IndonesiaDistrictSeeder extends Seeder
{
    /**
     * Seed kecamatan seluruh Indonesia dari file JSON.
     * Kecamatan Bojonegoro (regency_code 35.22) di-skip karena sudah ada di DistrictSeeder.
     * Kecamatan yang regency-nya tidak ada di tabel regencies juga di-skip.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/districts.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("File {$jsonPath} tidak ditemukan! Jalankan download script terlebih dahulu.");
            return;
        }

        // Load valid regency codes
        $validRegencies = Regency::pluck('code')->flip()->toArray();

        $districts = json_decode(file_get_contents($jsonPath), true);
        $count = 0;
        $skipped = 0;
        $noRegency = 0;

        foreach ($districts as $district) {
            // Skip kecamatan Bojonegoro karena sudah ada di DistrictSeeder
            if ($district['regency_code'] === '35.22') {
                $skipped++;
                continue;
            }

            // Skip jika regency tidak ada di database
            if (!isset($validRegencies[$district['regency_code']])) {
                $noRegency++;
                continue;
            }

            District::updateOrCreate(
                ['code' => $district['code']],
                [
                    'name' => $district['name'],
                    'regency_code' => $district['regency_code'],
                ]
            );
            $count++;
        }

        $this->command->info("Seeded {$count} districts (skipped {$skipped} Bojonegoro, {$noRegency} no matching regency).");
    }
}
