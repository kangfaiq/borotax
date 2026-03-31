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
        Schema::create('reklame_objects', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Pemilik
            $table->text('nik')->comment('🔐 NIK pemilik (terenkripsi)');
            $table->string('nik_hash', 64)->index();

            // Data reklame
            $table->text('nama_reklame')->comment('🔐 Nama reklame (terenkripsi)');
            $table->uuid('jenis_pajak_id');
            $table->uuid('sub_jenis_pajak_id');
            $table->string('npwpd', 13)->index();
            $table->integer('nopd');

            // Alamat
            $table->text('alamat_reklame')->comment('🔐 Alamat lokasi reklame (terenkripsi)');
            $table->string('kelurahan', 50);
            $table->string('kecamatan', 50);

            // Ukuran
            $table->decimal('panjang', 10, 2)->comment('Panjang (meter)');
            $table->decimal('lebar', 10, 2)->comment('Lebar (meter)');
            $table->decimal('luas_m2', 10, 2)->comment('Luas (m²)');
            $table->integer('jumlah_muka')->default(1)->comment('Jumlah muka (1 atau 2)');

            // Masa berlaku
            $table->date('tanggal_pasang');
            $table->date('masa_berlaku_sampai');
            $table->enum('status', ['aktif', 'kadaluarsa', 'pending'])->default('pending');

            // Tarif
            $table->enum('kelompok_lokasi', ['A', 'B', 'C', 'D'])->comment('Kelompok lokasi untuk tarif');

            // Koordinat (terenkripsi)
            $table->text('latitude')->nullable()->comment('🔐 Koordinat latitude (terenkripsi)');
            $table->text('longitude')->nullable()->comment('🔐 Koordinat longitude (terenkripsi)');
            $table->text('foto_url')->nullable()->comment('🔐 URL foto reklame (terenkripsi)');

            $table->timestamps();

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
        Schema::dropIfExists('reklame_objects');
    }
};
