<?php

namespace Database\Seeders;

use App\Domain\Region\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/provinces.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("File {$jsonPath} tidak ditemukan! Jalankan: php database/scripts/download_wilayah.php");
            return;
        }

        $provinces = json_decode(file_get_contents($jsonPath), true);

        foreach ($provinces as $province) {
            Province::updateOrCreate(
                ['code' => $province['code']],
                ['name' => $province['name']]
            );
        }

        $this->command->info('Seeded ' . count($provinces) . ' provinces.');
    }
}
