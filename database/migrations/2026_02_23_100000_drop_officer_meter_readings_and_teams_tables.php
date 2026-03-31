<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus tabel officer_meter_readings dan teams.
     * Kedua tabel ini tidak lagi digunakan (fitur Cek Meter & Tim Lapangan dihapus).
     * Tabel meter_reports tetap dipertahankan karena masih digunakan oleh SKPD Air Tanah.
     */
    public function up(): void
    {
        // Drop officer_meter_readings dulu (memiliki FK ke teams)
        Schema::dropIfExists('officer_meter_readings');
        Schema::dropIfExists('teams');
    }

    public function down(): void
    {
        // Recreate teams
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Recreate officer_meter_readings
        Schema::create('officer_meter_readings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nop')->comment('NOP objek pajak air tanah');
            $table->string('nama_objek');
            $table->string('alamat_objek')->nullable();
            $table->unsignedBigInteger('officer_id')->comment('ID petugas yang melakukan cek');
            $table->foreignId('tim_id')->constrained('teams')->comment('Tim yang ditugaskan');
            $table->decimal('meter_sebelumnya', 12, 2)->default(0);
            $table->decimal('meter_sekarang', 12, 2)->default(0);
            $table->decimal('pemakaian', 12, 2)->default(0);
            $table->date('masa_pajak_terakhir')->nullable();
            $table->date('tanggal_lapor_terakhir')->nullable();
            $table->date('tanggal_cek')->nullable();
            $table->string('foto_meter_path')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('nop');
            $table->index('officer_id');
        });
    }
};
