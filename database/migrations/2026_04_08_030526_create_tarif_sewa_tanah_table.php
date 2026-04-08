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
        Schema::create('tarif_sewa_tanah', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sub_jenis_pajak_id');
            $table->decimal('tarif_nominal', 15, 2);
            $table->string('satuan_waktu', 20); // perTahun / perBulan
            $table->date('berlaku_mulai');
            $table->date('berlaku_sampai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sub_jenis_pajak_id')->references('id')->on('sub_jenis_pajak');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarif_sewa_tanah');
    }
};
