<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom NSPR, NJOPR, dan satuan_label snapshot ke tabel skpd_reklame.
     *
     * Snapshot saat SKPD diterbitkan agar dokumen lama tetap akurat
     * meskipun tarif di reklame_tariffs berubah kemudian.
     */
    public function up(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->decimal('nspr', 15, 2)->nullable()
                ->after('tarif_pokok')
                ->comment('Snapshot NSPR saat SKPD diterbitkan');
            $table->decimal('njopr', 15, 2)->nullable()
                ->after('nspr')
                ->comment('Snapshot NJOPR saat SKPD diterbitkan');
            $table->string('satuan_label', 30)->nullable()
                ->after('satuan_waktu')
                ->comment('Snapshot label satuan: Th/m², Bln/m², dll');
        });
    }

    public function down(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->dropColumn(['nspr', 'njopr', 'satuan_label']);
        });
    }
};
