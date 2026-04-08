<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('skrd_sewa_retribusi', function (Blueprint $table) {
            $table->uuid('objek_retribusi_id')->nullable()->after('sub_jenis_pajak_id');
            $table->decimal('luas_m2', 10, 2)->nullable()->after('alamat_objek');
            $table->integer('jumlah_reklame')->default(1)->after('luas_m2');
            $table->decimal('tarif_pajak_persen', 5, 2)->default(25.00)->after('jumlah_reklame');

            $table->foreign('objek_retribusi_id')->references('id')->on('objek_retribusi_sewa_tanah');
        });
    }

    public function down(): void
    {
        Schema::table('skrd_sewa_retribusi', function (Blueprint $table) {
            $table->dropForeign(['objek_retribusi_id']);
            $table->dropColumn(['objek_retribusi_id', 'luas_m2', 'jumlah_reklame', 'tarif_pajak_persen']);
        });
    }
};
