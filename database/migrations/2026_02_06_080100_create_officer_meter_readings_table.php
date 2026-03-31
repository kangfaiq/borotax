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
        Schema::create('officer_meter_readings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Referensi ke simpadu
            $table->string('nop', 50)->index()->comment('NOP dari dat_objek_pajak simpadu');

            // Petugas & Tim
            $table->uuid('officer_id')->comment('Petugas yang melakukan cek');
            $table->foreignId('tim_id')->constrained('teams')->comment('Tim yang ditugaskan');

            // Snapshot data dari simpadu (untuk historical record)
            $table->string('nama_objek', 255)->nullable()->comment('Nama objek saat dicek');
            $table->text('alamat_objek')->nullable()->comment('Alamat objek saat dicek');

            // Meter reading
            $table->decimal('meter_sebelumnya', 12, 2)->nullable()->comment('Meter terakhir dari simpadu');
            $table->decimal('meter_sekarang', 12, 2)->comment('Meter saat ini (input petugas)');
            $table->decimal('pemakaian', 12, 2)->nullable()->comment('Selisih meter');

            // Masa pajak info
            $table->date('masa_pajak_terakhir')->nullable()->comment('Masa pajak terakhir dari simpadu');
            $table->date('tanggal_lapor_terakhir')->nullable()->comment('Tanggal lapor terakhir dari simpadu');
            $table->date('tanggal_cek')->comment('Tanggal petugas melakukan cek');

            // Foto & catatan
            $table->string('foto_meter_path', 255)->nullable()->comment('Path foto meter');
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->foreign('officer_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('officer_meter_readings');
    }
};
