<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom NSPR dan NJOPR ke tabel reklame_tariffs.
     *
     * NSPR = Nilai Sewa Pajak Reklame
     * NJOPR = Nilai Jual Objek Pajak Reklame
     *
     * Rumus: tarif_pokok (Pajak) = (NSPR + NJOPR) × 25%
     */
    public function up(): void
    {
        Schema::table('reklame_tariffs', function (Blueprint $table) {
            $table->decimal('nspr', 15, 2)->nullable()
                ->after('satuan_label')
                ->comment('Nilai Sewa Pajak Reklame per satuan');
            $table->decimal('njopr', 15, 2)->nullable()
                ->after('nspr')
                ->comment('Nilai Jual Objek Pajak Reklame per satuan');
        });
    }

    public function down(): void
    {
        Schema::table('reklame_tariffs', function (Blueprint $table) {
            $table->dropColumn(['nspr', 'njopr']);
        });
    }
};
