<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_aset_reklame', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('aset_reklame_pemkab_id')
                  ->constrained('aset_reklame_pemkab')
                  ->cascadeOnDelete();
            $table->string('peminjam_opd', 150);
            $table->string('materi_pinjam', 255);
            $table->date('pinjam_mulai');
            $table->date('pinjam_selesai');
            $table->text('catatan_pinjam')->nullable();
            $table->string('file_bukti_dukung', 255)->nullable()->comment('Surat OPD ke Bapenda');
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
            $table->uuid('petugas_id')->nullable();
            $table->string('petugas_nama', 100)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('aset_reklame_pemkab_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_aset_reklame');
    }
};
