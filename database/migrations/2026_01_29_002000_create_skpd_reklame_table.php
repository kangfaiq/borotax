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
        Schema::create('skpd_reklame', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_skpd', 50)->unique();
            $table->uuid('reklame_id');
            $table->uuid('request_id');
            $table->uuid('jenis_pajak_id');
            $table->uuid('sub_jenis_pajak_id');

            // Data WP (terenkripsi)
            $table->text('nik_wajib_pajak')->comment('🔐 NIK wajib pajak (terenkripsi)');
            $table->text('nama_wajib_pajak')->comment('🔐 Nama wajib pajak (terenkripsi)');
            $table->text('alamat_wajib_pajak')->comment('🔐 Alamat wajib pajak (terenkripsi)');

            // Data reklame
            $table->text('nama_reklame')->comment('🔐 Nama reklame (terenkripsi)');
            $table->string('jenis_reklame', 50);
            $table->text('alamat_reklame')->comment('🔐 Alamat reklame (terenkripsi)');
            $table->decimal('luas_m2', 10, 2);
            $table->integer('jumlah_muka');

            // Masa berlaku
            $table->date('masa_berlaku_mulai');
            $table->date('masa_berlaku_sampai');
            $table->integer('durasi_hari');

            // Perhitungan (terenkripsi)
            $table->text('nilai_sewa_per_m2_per_hari')->comment('🔐 Nilai sewa (terenkripsi)');
            $table->text('dasar_pengenaan')->comment('🔐 Dasar pengenaan pajak (terenkripsi)');
            $table->decimal('tarif_persen', 5, 2)->default(25);
            $table->text('jumlah_pajak')->comment('🔐 Jumlah pajak terutang (terenkripsi)');

            // Status
            $table->enum('status', ['draft', 'menungguVerifikasi', 'disetujui', 'ditolak'])->default('draft');

            // Petugas
            $table->timestamp('tanggal_buat');
            $table->uuid('petugas_id');
            $table->string('petugas_nama', 100)->nullable();

            // Verifikator
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->uuid('verifikator_id')->nullable();
            $table->string('verifikator_nama', 100)->nullable();
            $table->text('catatan_verifikasi')->nullable();

            // Dokumen (terenkripsi)
            $table->text('ttd_elektronik_url')->nullable()->comment('🔐 URL TTD elektronik (terenkripsi)');
            $table->text('qr_code_url')->nullable()->comment('🔐 URL QR code (terenkripsi)');
            $table->string('nomor_seri_dokumen', 50)->nullable();
            $table->string('kode_billing', 18)->nullable();

            $table->timestamps();

            $table->foreign('reklame_id')->references('id')->on('reklame_objects');
            $table->foreign('request_id')->references('id')->on('reklame_requests');
            $table->foreign('jenis_pajak_id')->references('id')->on('jenis_pajak');
            $table->foreign('sub_jenis_pajak_id')->references('id')->on('sub_jenis_pajak');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skpd_reklame');
    }
};
