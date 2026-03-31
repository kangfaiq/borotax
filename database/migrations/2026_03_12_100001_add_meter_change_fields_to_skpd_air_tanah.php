<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('skpd_air_tanah', function (Blueprint $table) {
            $table->boolean('is_meter_change')->default(false)->after('usage')
                ->comment('Apakah ada pergantian meteran di bulan ini');
            $table->decimal('meter_old_end', 12, 2)->nullable()->after('is_meter_change')
                ->comment('Angka meter akhir meteran lama (sebelum rusak)');
            $table->decimal('meter_new_start', 12, 2)->nullable()->after('meter_old_end')
                ->comment('Angka meter awal meteran baru');
            $table->decimal('meter_new_end', 12, 2)->nullable()->after('meter_new_start')
                ->comment('Angka meter akhir meteran baru');
            $table->text('catatan_meter')->nullable()->after('meter_new_end')
                ->comment('Catatan pergantian meteran');
        });
    }

    public function down(): void
    {
        Schema::table('skpd_air_tanah', function (Blueprint $table) {
            $table->dropColumn([
                'is_meter_change',
                'meter_old_end',
                'meter_new_start',
                'meter_new_end',
                'catatan_meter',
            ]);
        });
    }
};
