<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sub_jenis_pajak', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('jenis_pajak_id');
            $table->string('kode', 50)->unique()->comment('Kode unik sub-jenis pajak');
            $table->string('nama', 100)->comment('Nama sub-jenis pajak');
            $table->string('nama_lengkap', 255)->nullable()->comment('Nama lengkap resmi');
            $table->text('deskripsi')->nullable()->comment('Deskripsi sub-jenis');
            $table->string('icon', 50)->nullable()->comment('Icon emoji atau nama ikon');
            $table->decimal('tarif_persen', 5, 2)->comment('Tarif pajak (%)');
            $table->string('kategori', 50)->nullable()->comment('Kategori tambahan (e.g., tetap, insidentil)');
            $table->boolean('is_active')->default(true)->comment('Status aktif');
            $table->integer('urutan')->default(0)->comment('Urutan tampilan');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('jenis_pajak_id')->references('id')->on('jenis_pajak')->onDelete('cascade');
            $table->index('jenis_pajak_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_jenis_pajak');
    }
};
