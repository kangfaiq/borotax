<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot dimension columns from tax_objects into skpd_reklame
     * so that old SKPD documents remain consistent when reklame object dimensions change.
     */
    public function up(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->decimal('panjang', 8, 2)->nullable()->after('bentuk');
            $table->decimal('lebar', 8, 2)->nullable()->after('panjang');
            $table->decimal('tinggi', 8, 2)->nullable()->after('lebar');
            $table->decimal('sisi_atas', 8, 2)->nullable()->after('tinggi');
            $table->decimal('sisi_bawah', 8, 2)->nullable()->after('sisi_atas');
            $table->decimal('diameter', 8, 2)->nullable()->after('sisi_bawah');
            $table->decimal('alas', 8, 2)->nullable()->after('diameter');
        });

        // Backfill existing records from tax_objects
        DB::statement("
            UPDATE skpd_reklame sr
            JOIN tax_objects t ON sr.tax_object_id = t.id
            SET sr.panjang = t.panjang,
                sr.lebar = t.lebar,
                sr.tinggi = t.tinggi,
                sr.sisi_atas = t.sisi_atas,
                sr.sisi_bawah = t.sisi_bawah,
                sr.diameter = t.diameter,
                sr.alas = t.alas
        ");
    }

    public function down(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->dropColumn([
                'panjang', 'lebar', 'tinggi',
                'sisi_atas', 'sisi_bawah', 'diameter', 'alas',
            ]);
        });
    }
};
