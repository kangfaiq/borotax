<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
            ReklameSubJenisPajakSeeder::class,
            KelompokLokasiJalanSeeder::class,
            ReklameTariffSeeder::class,
            AsetReklamePemkabSeeder::class,
            AppVersionSeeder::class,
            ProvinceSeeder::class,
            RegencySeeder::class,
            DistrictSeeder::class,
            VillageSeeder::class,
        ]);
    }
}
