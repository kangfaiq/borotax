<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permohonan_sewa_reklame', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_tiket', 20)->unique()->comment('SEWA-YYYYMMDD-XXXX');
            $table->uuid('aset_reklame_pemkab_id');
            $table->uuid('user_id')->nullable();

            // Data pemohon (terenkripsi)
            $table->text('nik')->comment('🔐 NIK pemohon (terenkripsi)');
            $table->text('nama')->comment('🔐 Nama pemohon (terenkripsi)');
            $table->text('alamat')->comment('🔐 Alamat pemohon (terenkripsi)');
            $table->text('no_telepon')->comment('🔐 Nomor telepon (terenkripsi)');
            $table->text('nama_usaha')->nullable()->comment('🔐 Nama usaha/badan (terenkripsi)');
            $table->string('email', 100)->nullable();

            // Registrasi izin
            $table->string('nomor_registrasi_izin', 100)->comment('Nomor registrasi izin dari DPMPTSP Kab Bojonegoro');

            // Dokumen upload
            $table->string('file_ktp', 255)->nullable()->comment('Path file KTP');
            $table->string('file_npwp', 255)->nullable()->comment('Path file NPWP (opsional untuk perusahaan)');
            $table->string('file_desain_reklame', 255)->nullable()->comment('Path file desain/materi reklame');

            // Detail sewa
            $table->string('jenis_reklame_dipasang', 100)->comment('Jenis iklan yang akan dipasang');
            $table->integer('durasi_sewa_hari')->comment('30/90/180/365');
            $table->date('tanggal_mulai_diinginkan');
            $table->text('catatan')->nullable();

            // Status workflow
            $table->enum('status', ['diajukan', 'perlu_revisi', 'diproses', 'disetujui', 'ditolak'])->default('diajukan');
            $table->timestamp('tanggal_pengajuan');

            // Proses
            $table->uuid('petugas_id')->nullable();
            $table->string('petugas_nama', 100)->nullable();
            $table->text('catatan_petugas')->nullable()->comment('Catatan petugas / alasan revisi / alasan tolak');
            $table->timestamp('tanggal_diproses')->nullable();
            $table->timestamp('tanggal_selesai')->nullable();

            // Link ke SKPD
            $table->uuid('skpd_id')->nullable()->comment('ID SKPD yang dihasilkan');

            $table->timestamps();

            $table->foreign('aset_reklame_pemkab_id')->references('id')->on('aset_reklame_pemkab');
            $table->foreign('user_id')->references('id')->on('users');
            $table->index('status');
            $table->index('nomor_tiket');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_sewa_reklame');
    }
};
