<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi kode wilayah dari format numerik ke format dot-separated (cahyadsn/wilayah).
 *
 * Perubahan format:
 * - District: 3522010 → 35.22.01
 * - Village:  3522010001 → 35.22.01.2001 (or similar)
 *
 * Strategi:
 * 1. Truncate tabel villages, districts (dan data terkait di users/wajib_pajak)
 * 2. Re-seed dengan data baru dari JSON cahyadsn/wilayah
 *
 * Catatan: Jika ada data user/wajib_pajak yang sudah menggunakan kode lama,
 * kode tersebut akan di-null-kan dan perlu diupdate manual.
 */
return new class extends Migration {
    public function up(): void
    {
        // Disable FK checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear village_code and district_code references in users and wajib_pajak
        if (Schema::hasColumn('users', 'district_code')) {
            DB::table('users')->update([
                'district_code' => null,
                'village_code' => null,
            ]);
        }

        if (Schema::hasColumn('wajib_pajak', 'district_code')) {
            DB::table('wajib_pajak')->update([
                'district_code' => null,
                'village_code' => null,
            ]);
        }

        // Truncate villages and districts to replace with new codes
        DB::table('villages')->truncate();
        DB::table('districts')->truncate();

        // Re-enable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Re-seed with new data
        $this->seedDistricts();
        $this->seedVillages();
    }

    public function down(): void
    {
        // Rollback: truncate and would need to re-seed with old format
        // Since the old format is no longer in seeders, we just truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('villages')->truncate();
        DB::table('districts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function seedDistricts(): void
    {
        $jsonPath = database_path('data/districts.json');
        if (!file_exists($jsonPath)) {
            return;
        }

        $validRegencies = DB::table('regencies')->pluck('code')->flip()->toArray();
        $districts = json_decode(file_get_contents($jsonPath), true);
        $batch = [];

        foreach ($districts as $district) {
            if (!isset($validRegencies[$district['regency_code']])) {
                continue;
            }

            $batch[] = [
                'regency_code' => $district['regency_code'],
                'code' => $district['code'],
                'name' => $district['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('districts')->insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('districts')->insert($batch);
        }
    }

    private function seedVillages(): void
    {
        $jsonPath = database_path('data/villages.json');
        if (!file_exists($jsonPath)) {
            return;
        }

        $validDistricts = DB::table('districts')->pluck('code')->flip()->toArray();
        $villages = json_decode(file_get_contents($jsonPath), true);
        $batch = [];

        foreach ($villages as $village) {
            if (!isset($validDistricts[$village['district_code']])) {
                continue;
            }

            $batch[] = [
                'district_code' => $village['district_code'],
                'code' => $village['code'],
                'name' => $village['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('villages')->insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('villages')->insert($batch);
        }
    }
};
