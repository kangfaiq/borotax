<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drop tabel reklame_objects dan water_objects.
 *
 * Data objek pajak sudah dikonsolidasi ke tabel tax_objects.
 * FK constraints dari tabel lain (skpd_reklame, skpd_air_tanah,
 * meter_reports, reklame_requests) perlu di-drop terlebih dahulu.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop FK constraints yang merujuk ke reklame_objects (jika tabel masih ada)
        if (Schema::hasTable('reklame_requests') && Schema::hasTable('reklame_objects')) {
            try {
                Schema::table('reklame_requests', fn ($table) => $table->dropForeign(['reklame_id']));
            } catch (\Exception $e) { /* FK mungkin sudah tidak ada */ }
        }
        if (Schema::hasTable('skpd_reklame') && Schema::hasTable('reklame_objects')) {
            try {
                Schema::table('skpd_reklame', fn ($table) => $table->dropForeign(['reklame_id']));
            } catch (\Exception $e) { /* FK mungkin sudah tidak ada */ }
        }

        // Drop FK constraints yang merujuk ke water_objects (jika tabel masih ada)
        if (Schema::hasTable('meter_reports') && Schema::hasTable('water_objects')) {
            try {
                Schema::table('meter_reports', fn ($table) => $table->dropForeign(['water_object_id']));
            } catch (\Exception $e) { /* FK mungkin sudah tidak ada */ }
        }
        if (Schema::hasTable('skpd_air_tanah') && Schema::hasTable('water_objects')) {
            try {
                Schema::table('skpd_air_tanah', fn ($table) => $table->dropForeign(['water_object_id']));
            } catch (\Exception $e) { /* FK mungkin sudah tidak ada */ }
        }

        // Drop tabel (aman meskipun sudah tidak ada)
        Schema::dropIfExists('reklame_objects');
        Schema::dropIfExists('water_objects');
    }

    public function down(): void
    {
        // Tabel tidak di-recreate di rollback — gunakan migration asli untuk restore
    }
};
