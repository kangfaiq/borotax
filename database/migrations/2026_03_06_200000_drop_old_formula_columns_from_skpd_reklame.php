<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->dropColumn([
                'durasi_hari',
                'nilai_sewa_per_m2_per_hari',
                'tarif_persen',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->integer('durasi_hari')->nullable();
            $table->text('nilai_sewa_per_m2_per_hari')->nullable();
            $table->decimal('tarif_persen', 5, 2)->nullable();
        });
    }
};
