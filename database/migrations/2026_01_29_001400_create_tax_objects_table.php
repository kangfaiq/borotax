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
        Schema::create('tax_objects', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Pemilik
            $table->text('nik')->comment('🔐 NIK pemilik (terenkripsi)');
            $table->string('nik_hash', 64)->index()->comment('Hash NIK untuk pencarian');

            // Data objek
            $table->text('nama_objek_pajak')->comment('🔐 Nama usaha (terenkripsi)');
            $table->uuid('jenis_pajak_id');
            $table->uuid('sub_jenis_pajak_id');
            $table->string('npwpd', 13)->index();
            $table->integer('nopd')->comment('NOPD (nomor urut)');

            // Alamat
            $table->text('alamat_objek')->comment('🔐 Alamat objek pajak (terenkripsi)');
            $table->string('kelurahan', 50);
            $table->string('kecamatan', 50);

            // Tarif dan status
            $table->decimal('tarif_persen', 5, 2);
            $table->date('tanggal_daftar');
            $table->boolean('is_active')->default(true);

            // Kontak (terenkripsi)
            $table->text('nomor_telp')->nullable()->comment('🔐 Nomor telepon (terenkripsi)');
            $table->text('email')->nullable()->comment('🔐 Email (terenkripsi)');
            $table->text('foto_objek_path')->nullable()->comment('🔐 Path foto objek (terenkripsi)');

            $table->timestamps();

            $table->foreign('jenis_pajak_id')->references('id')->on('jenis_pajak');
            $table->foreign('sub_jenis_pajak_id')->references('id')->on('sub_jenis_pajak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_objects');
    }
};
