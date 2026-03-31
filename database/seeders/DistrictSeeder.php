<?php

namespace Database\Seeders;

use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Regency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    /**
     * Seed kecamatan seluruh Indonesia dari file JSON (cahyadsn/wilayah format).
     * Format kode: dot-separated, e.g. 35.22.01
     */
    public function run(): void
    {
        $jsonPath = database_path('data/districts.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("File {$jsonPath} tidak ditemukan! Jalankan: php database/scripts/download_wilayah.php");
            return;
        }

        // Load valid regency codes
        $validRegencies = Regency::pluck('code')->flip()->toArray();

        $districts = json_decode(file_get_contents($jsonPath), true);
        $count = 0;
        $noRegency = 0;
        $batch = [];

        foreach ($districts as $district) {
            // Skip jika regency tidak ada di database
            if (!isset($validRegencies[$district['regency_code']])) {
                $noRegency++;
                continue;
            }

            $batch[] = [
                'regency_code' => $district['regency_code'],
                'code' => $district['code'],
                'name' => $district['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $count++;

            // Insert in batches of 500 for performance
            if (count($batch) >= 500) {
                DB::table('districts')->upsert(
                    $batch,
                    ['code'],
                    ['name', 'regency_code', 'updated_at']
                );
                $batch = [];
            }
        }

        // Insert remaining
        if (!empty($batch)) {
            DB::table('districts')->upsert(
                $batch,
                ['code'],
                ['name', 'regency_code', 'updated_at']
            );
        }

        $this->command->info("Seeded {$count} districts (skipped {$noRegency} with no matching regency).");
    }
}
