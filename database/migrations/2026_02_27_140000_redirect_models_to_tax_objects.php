<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi konsolidasi: tambahkan kolom reklame & air tanah ke tax_objects,
 * dan rename FK di tabel relasi agar mengarah ke tax_objects.
 *
 * Setelah migrasi ini:
 * - Model ReklameObject & WaterObject → $table = 'tax_objects'
 * - reklame_requests.reklame_id → reklame_requests.tax_object_id
 * - skpd_reklame.reklame_id → skpd_reklame.tax_object_id
 * - meter_reports.water_object_id → meter_reports.tax_object_id
 * - skpd_air_tanah.water_object_id → skpd_air_tanah.tax_object_id
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Tambah kolom reklame-specific ke tax_objects ──
        Schema::table('tax_objects', function (Blueprint $table) {
            // Reklame
            $table->decimal('panjang', 8, 2)->nullable()->after('bobot_sda')
                ->comment('Panjang reklame (m)');
            $table->decimal('lebar', 8, 2)->nullable()->after('panjang')
                ->comment('Lebar reklame (m)');
            $table->decimal('luas_m2', 10, 2)->nullable()->after('lebar')
                ->comment('Luas reklame (m²) = panjang × lebar');
            $table->unsignedInteger('jumlah_muka')->nullable()->after('luas_m2')
                ->comment('Jumlah muka reklame');
            $table->date('tanggal_pasang')->nullable()->after('jumlah_muka')
                ->comment('Tanggal pemasangan reklame');
            $table->date('masa_berlaku_sampai')->nullable()->after('tanggal_pasang')
                ->comment('Batas masa berlaku reklame');
            $table->string('status', 20)->nullable()->after('masa_berlaku_sampai')
                ->comment('Status reklame: aktif/kadaluarsa');

            // Air Tanah
            $table->string('jenis_sumber', 20)->nullable()->after('status')
                ->comment('Jenis sumber air: sumurBor/sumurGali/matAir/springWell');
            $table->unsignedInteger('last_meter_reading')->nullable()->after('jenis_sumber')
                ->comment('Angka meter terakhir');
            $table->date('last_report_date')->nullable()->after('last_meter_reading')
                ->comment('Tanggal laporan meter terakhir');
        });

        // ── 2. Rename FK di tabel relasi ──

        // reklame_requests: reklame_id → tax_object_id
        if (Schema::hasColumn('reklame_requests', 'reklame_id')) {
            Schema::table('reklame_requests', function (Blueprint $table) {
                $table->renameColumn('reklame_id', 'tax_object_id');
            });
        }

        // skpd_reklame: reklame_id → tax_object_id
        if (Schema::hasColumn('skpd_reklame', 'reklame_id')) {
            Schema::table('skpd_reklame', function (Blueprint $table) {
                $table->renameColumn('reklame_id', 'tax_object_id');
            });
        }

        // meter_reports: water_object_id → tax_object_id
        if (Schema::hasColumn('meter_reports', 'water_object_id')) {
            Schema::table('meter_reports', function (Blueprint $table) {
                $table->renameColumn('water_object_id', 'tax_object_id');
            });
        }

        // skpd_air_tanah: water_object_id → tax_object_id
        if (Schema::hasColumn('skpd_air_tanah', 'water_object_id')) {
            Schema::table('skpd_air_tanah', function (Blueprint $table) {
                $table->renameColumn('water_object_id', 'tax_object_id');
            });
        }
    }

    public function down(): void
    {
        // Rollback FK renames
        if (Schema::hasColumn('reklame_requests', 'tax_object_id')) {
            Schema::table('reklame_requests', function (Blueprint $table) {
                $table->renameColumn('tax_object_id', 'reklame_id');
            });
        }
        if (Schema::hasColumn('skpd_reklame', 'tax_object_id')) {
            Schema::table('skpd_reklame', function (Blueprint $table) {
                $table->renameColumn('tax_object_id', 'reklame_id');
            });
        }
        if (Schema::hasColumn('meter_reports', 'tax_object_id')) {
            Schema::table('meter_reports', function (Blueprint $table) {
                $table->renameColumn('tax_object_id', 'water_object_id');
            });
        }
        if (Schema::hasColumn('skpd_air_tanah', 'tax_object_id')) {
            Schema::table('skpd_air_tanah', function (Blueprint $table) {
                $table->renameColumn('tax_object_id', 'water_object_id');
            });
        }

        // Rollback kolom baru
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->dropColumn([
                'panjang', 'lebar', 'luas_m2', 'jumlah_muka',
                'tanggal_pasang', 'masa_berlaku_sampai', 'status',
                'jenis_sumber', 'last_meter_reading', 'last_report_date',
            ]);
        });
    }
};
