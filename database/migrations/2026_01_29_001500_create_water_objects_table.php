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
        Schema::create('water_objects', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Pemilik
            $table->text('nik')->comment('🔐 NIK pemilik (terenkripsi)');
            $table->string('nik_hash', 64)->index()->comment('Hash NIK untuk pencarian');

            // Data objek
            $table->text('nama_objek')->comment('🔐 Nama objek (terenkripsi)');
            $table->uuid('jenis_pajak_id');
            $table->uuid('sub_jenis_pajak_id');
            $table->enum('jenis_sumber', ['sumurBor', 'sumurGali', 'matAir', 'springWell']);
            $table->string('npwpd', 13)->index();
            $table->integer('nopd');

            // Alamat
            $table->text('alamat_objek')->comment('🔐 Alamat objek (terenkripsi)');
            $table->string('kelurahan', 50);
            $table->string('kecamatan', 50);

            // Koordinat (terenkripsi)
            $table->text('latitude')->nullable()->comment('🔐 Koordinat latitude (terenkripsi)');
            $table->text('longitude')->nullable()->comment('🔐 Koordinat longitude (terenkripsi)');

            // Meter
            $table->integer('last_meter_reading')->nullable();
            $table->date('last_report_date')->nullable();

            // Status
            $table->date('tanggal_daftar');
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('water_objects');
    }
};
