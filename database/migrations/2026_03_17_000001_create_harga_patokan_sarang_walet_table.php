<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('harga_patokan_sarang_walet', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_jenis', 100); // Mangkuk, Sudut, Patahan, Bubuk
            $table->text('harga_patokan'); // Encrypted, harga per kg
            $table->string('satuan', 20)->default('kg');
            $table->string('dasar_hukum', 255)->nullable();
            $table->date('berlaku_mulai')->nullable()->comment('Tanggal mulai berlaku harga');
            $table->date('berlaku_sampai')->nullable()->comment('Tanggal akhir berlaku (null = masih berlaku)');
            $table->boolean('is_active')->default(true)->index();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_patokan_sarang_walet');
    }
};
