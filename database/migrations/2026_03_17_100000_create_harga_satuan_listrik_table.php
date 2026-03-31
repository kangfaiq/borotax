<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harga_satuan_listrik', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_wilayah', 150);
            $table->text('harga_per_kwh'); // encrypted
            $table->string('dasar_hukum', 255)->nullable();
            $table->date('berlaku_mulai')->nullable();
            $table->date('berlaku_sampai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_satuan_listrik');
    }
};
