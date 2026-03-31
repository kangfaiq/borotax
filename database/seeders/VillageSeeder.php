<?php

namespace Database\Seeders;

use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VillageSeeder extends Seeder
{
    /**
     * Seed kelurahan/desa seluruh Indonesia dari file JSON (cahyadsn/wilayah format).
     * Format kode: dot-separated, e.g. 35.22.01.2001
     */
    public function run(): void
    {
        $jsonPath = database_path('data/villages.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("File {$jsonPath} tidak ditemukan! Jalankan: php database/scripts/download_wilayah.php");
            return;
        }

        // Load valid district codes
        $validDistricts = District::pluck('code')->flip()->toArray();

        $villages = json_decode(file_get_contents($jsonPath), true);
        $count = 0;
        $noDistrict = 0;
        $batch = [];

        foreach ($villages as $village) {
            // Skip jika district tidak ada di database
            if (!isset($validDistricts[$village['district_code']])) {
                $noDistrict++;
                continue;
            }

            $batch[] = [
                'district_code' => $village['district_code'],
                'code' => $village['code'],
                'name' => $village['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $count++;

            // Insert in batches of 500 for performance
            if (count($batch) >= 500) {
                DB::table('villages')->upsert(
                    $batch,
                    ['code'],
                    ['name', 'district_code', 'updated_at']
                );
                $batch = [];
            }
        }

        // Insert remaining
        if (!empty($batch)) {
            DB::table('villages')->upsert(
                $batch,
                ['code'],
                ['name', 'district_code', 'updated_at']
            );
        }

        $this->command->info("Seeded {$count} villages (skipped {$noDistrict} with no matching district).");
    }
}
