<?php

namespace Database\Seeders;

use App\Domain\Region\Models\Regency;
use Illuminate\Database\Seeder;

class RegencySeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/regencies.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("File {$jsonPath} tidak ditemukan! Jalankan: php database/scripts/download_wilayah.php");
            return;
        }

        $regencies = json_decode(file_get_contents($jsonPath), true);
        $count = 0;

        foreach ($regencies as $regency) {
            Regency::updateOrCreate(
                ['code' => $regency['code']],
                [
                    'province_code' => $regency['province_code'],
                    'name' => $regency['name'],
                ]
            );
            $count++;
        }

        $this->command->info("Seeded {$count} regencies.");
    }
}
