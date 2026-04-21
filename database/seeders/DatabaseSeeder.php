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
            // Sistem dasar
            AdminUserSeeder::class,

            // Referensi pajak utama
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
            ReklameSubJenisPajakSeeder::class,

            // Data master wilayah
            ProvinceSeeder::class,
            RegencySeeder::class,
            DistrictSeeder::class,
            VillageSeeder::class,

            // Data master reklame
            KelompokLokasiJalanSeeder::class,
            ReklameTariffSeeder::class,
            ReklameNilaiStrategisSeeder::class,
            AsetReklamePemkabSeeder::class,

            // Data master penetapan pajak
            NpaAirTanahProgresifSeeder::class,
            HargaPatokanMblbSeeder::class,
            HargaPatokanSarangWaletSeeder::class,
            InstansiSeeder::class,

            // Konten dan versi aplikasi
            DestinationSeeder::class,
            AppVersionSeeder::class,
        ]);
    }
}
