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
        Schema::create('jenis_pajak', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 10)->unique()->comment('Kode jenis pajak, e.g., 41101, 41102');
            $table->string('nama', 100)->comment('Nama resmi jenis pajak');
            $table->string('nama_singkat', 50)->comment('Nama singkat untuk display');
            $table->text('deskripsi')->nullable()->comment('Deskripsi jenis pajak');
            $table->string('icon', 50)->nullable()->comment('Icon emoji atau nama ikon');
            $table->decimal('tarif_default', 5, 2)->comment('Tarif default (%)');
            $table->enum('tipe_assessment', ['self_assessment', 'official_assessment'])->comment('Tipe assessment pajak');
            $table->boolean('is_active')->default(true)->comment('Status aktif');
            $table->integer('urutan')->default(0)->comment('Urutan tampilan');
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('urutan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_pajak');
    }
};
