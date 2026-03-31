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
        Schema::create('reklame_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('reklame_id');
            $table->uuid('user_id');

            // User info (terenkripsi)
            $table->text('user_nik')->comment('🔐 NIK pemohon (terenkripsi)');
            $table->text('user_name')->comment('🔐 Nama pemohon (terenkripsi)');

            // Pengajuan
            $table->timestamp('tanggal_pengajuan');
            $table->integer('durasi_perpanjangan_hari');
            $table->text('catatan_pengajuan')->nullable();

            // Status
            $table->enum('status', ['diajukan', 'menungguVerifikasi', 'diproses', 'disetujui', 'ditolak'])->default('diajukan');

            // Proses
            $table->timestamp('tanggal_diproses')->nullable();
            $table->uuid('petugas_id')->nullable();
            $table->string('petugas_nama', 100)->nullable();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->text('catatan_petugas')->nullable();
            $table->uuid('skpd_id')->nullable()->comment('ID SKPD yang dihasilkan');

            $table->timestamps();

            $table->foreign('reklame_id')->references('id')->on('reklame_objects');
            $table->foreign('user_id')->references('id')->on('users');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reklame_requests');
    }
};
