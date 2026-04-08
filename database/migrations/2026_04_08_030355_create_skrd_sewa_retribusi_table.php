<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skrd_sewa_retribusi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_skrd', 50)->unique();

            $table->uuid('jenis_pajak_id');
            $table->uuid('sub_jenis_pajak_id');

            // Data WP (terenkripsi)
            $table->string('npwpd', 30)->nullable();
            $table->text('nik_wajib_pajak')->comment('encrypted');
            $table->text('nama_wajib_pajak')->comment('encrypted');
            $table->text('alamat_wajib_pajak')->comment('encrypted');

            // Data objek retribusi
            $table->text('nama_objek')->comment('encrypted');
            $table->text('alamat_objek')->comment('encrypted');

            // Perhitungan
            $table->text('tarif_nominal')->comment('encrypted - tarif per periode');
            $table->string('satuan_waktu', 20); // perTahun / perBulan
            $table->string('satuan_label', 30);
            $table->integer('durasi')->default(1);
            $table->text('jumlah_retribusi')->comment('encrypted - tarif × durasi');

            // Masa berlaku
            $table->date('masa_berlaku_mulai');
            $table->date('masa_berlaku_sampai');
            $table->date('jatuh_tempo')->nullable();

            // Status
            $table->string('status', 20)->default('draft')->index();

            // Petugas
            $table->timestamp('tanggal_buat');
            $table->uuid('petugas_id');
            $table->string('petugas_nama', 100)->nullable();

            // Verifikator
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->uuid('verifikator_id')->nullable();
            $table->string('verifikator_nama', 100)->nullable();
            $table->text('catatan_verifikasi')->nullable();

            // Pimpinan
            $table->uuid('pimpinan_id')->nullable();

            // Billing
            $table->string('kode_billing', 18)->nullable();

            // Dasar hukum
            $table->text('dasar_hukum')->nullable();

            // Legacy
            $table->boolean('is_legacy')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('jenis_pajak_id')->references('id')->on('jenis_pajak');
            $table->foreign('sub_jenis_pajak_id')->references('id')->on('sub_jenis_pajak');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skrd_sewa_retribusi');
    }
};
