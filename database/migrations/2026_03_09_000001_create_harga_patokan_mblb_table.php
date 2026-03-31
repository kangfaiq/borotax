<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('harga_patokan_mblb', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sub_jenis_pajak_id')->nullable()->constrained('sub_jenis_pajak')->nullOnDelete();
            $table->string('nama_mineral', 150);
            $table->json('nama_alternatif')->nullable();
            $table->text('harga_patokan'); // Encrypted
            $table->string('satuan', 20)->default('m3');
            $table->string('dasar_hukum', 255)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_patokan_mblb');
    }
};
