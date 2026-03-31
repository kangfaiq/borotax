<?php

namespace Database\Seeders;

use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndonesiaVillageSeeder extends Seeder
{
    /**
     * Seed kelurahan/desa seluruh Indonesia dari file JSON.
     * Kelurahan Bojonegoro (district_code 3522xxx) di-skip karena sudah ada di VillageSeeder.
     * Kelurahan yang district-nya tidak ada di tabel districts juga di-skip.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/villages.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("File {$jsonPath} tidak ditemukan! Jalankan download script terlebih dahulu.");
            return;
        }

        // Load valid district codes
        $validDistricts = District::pluck('code')->flip()->toArray();

        $villages = json_decode(file_get_contents($jsonPath), true);
        $count = 0;
        $skipped = 0;
        $noDistrict = 0;
        $batch = [];

        foreach ($villages as $village) {
            // Skip kelurahan Bojonegoro karena sudah ada di VillageSeeder
            if (str_starts_with($village['district_code'], '3522')) {
                $skipped++;
                continue;
            }

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

        $this->command->info("Seeded {$count} villages (skipped {$skipped} Bojonegoro, {$noDistrict} no matching district).");
    }
}
