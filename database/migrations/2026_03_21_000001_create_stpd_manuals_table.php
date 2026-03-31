<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stpd_manuals', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relasi ke billing (taxes)
            $table->uuid('tax_id');
            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('cascade');

            // Tipe STPD: pokok_sanksi = billing belum dibayar, sanksi_saja = pokok lunas sanksi belum
            $table->enum('tipe', ['pokok_sanksi', 'sanksi_saja']);

            // Nomor STPD resmi (diisi saat disetujui verifikator)
            $table->string('nomor_stpd', 50)->nullable()->unique();

            // Status workflow: draft → disetujui/ditolak
            $table->enum('status', ['draft', 'disetujui', 'ditolak'])->default('draft');

            // Proyeksi tanggal bayar (untuk tipe pokok_sanksi)
            $table->date('proyeksi_tanggal_bayar')->nullable();

            // Perhitungan sanksi
            $table->integer('bulan_terlambat')->default(0);
            $table->text('sanksi_dihitung')->comment('🔐 Nominal sanksi dihitung (terenkripsi)');
            $table->text('pokok_belum_dibayar')->nullable()->comment('🔐 Nominal pokok belum dibayar (terenkripsi)');

            // Catatan
            $table->text('catatan_petugas')->nullable();
            $table->text('catatan_verifikasi')->nullable();

            // Petugas (pembuat)
            $table->uuid('petugas_id');
            $table->string('petugas_nama', 100)->nullable();
            $table->timestamp('tanggal_buat');

            // Verifikator (penyetuju/penolak)
            $table->uuid('verifikator_id')->nullable();
            $table->string('verifikator_nama', 100)->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();

            // Pimpinan penandatangan
            $table->uuid('pimpinan_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('tipe');
            $table->index('tax_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stpd_manuals');
    }
};
