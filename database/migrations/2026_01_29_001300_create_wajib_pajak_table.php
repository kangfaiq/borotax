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
        Schema::create('wajib_pajak', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');

            // Data terenkripsi
            $table->text('nik')->comment('🔐 NIK wajib pajak (terenkripsi)');
            $table->string('nik_hash', 64)->index()->comment('Hash NIK untuk pencarian');
            $table->text('nama_lengkap')->comment('🔐 Nama lengkap (terenkripsi)');
            $table->text('alamat')->comment('🔐 Alamat (terenkripsi)');
            $table->text('no_telp')->comment('🔐 Nomor telepon (terenkripsi)');
            $table->text('email')->comment('🔐 Email (terenkripsi)');

            // Tipe wajib pajak
            $table->enum('tipe_wajib_pajak', ['perorangan', 'perusahaan'])->default('perorangan')->comment('P1 = perorangan, P2 = perusahaan');

            // Data perusahaan (terenkripsi, nullable)
            $table->text('nib')->nullable()->comment('🔐 NIB (terenkripsi, wajib untuk P2)');
            $table->text('npwp_pusat')->nullable()->comment('🔐 NPWP Pusat (terenkripsi, wajib untuk P2)');
            $table->text('nama_perusahaan')->nullable()->comment('🔐 Nama perusahaan (terenkripsi)');

            // Dokumen (terenkripsi)
            $table->text('ktp_image_path')->nullable()->comment('🔐 Path foto KTP (terenkripsi)');
            $table->text('selfie_image_path')->nullable()->comment('🔐 Path foto selfie (terenkripsi)');

            // Status dan verifikasi
            $table->enum('status', ['menungguVerifikasi', 'disetujui', 'ditolak', 'perluPerbaikan'])->default('menungguVerifikasi');
            $table->timestamp('tanggal_daftar');
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->uuid('petugas_id')->nullable()->comment('ID petugas yang verifikasi');
            $table->string('petugas_nama', 100)->nullable();
            $table->text('catatan_verifikasi')->nullable();

            // NPWPD
            $table->string('npwpd', 13)->nullable()->unique()->comment('NPWPD (tidak dienkripsi - untuk relasi)');
            $table->integer('nopd')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wajib_pajak');
    }
};
