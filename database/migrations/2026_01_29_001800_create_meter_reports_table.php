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
        Schema::create('meter_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('water_object_id');
            $table->uuid('user_id');

            // User info (terenkripsi)
            $table->text('user_nik')->comment('🔐 NIK pelapor (terenkripsi)');
            $table->text('user_name')->comment('🔐 Nama pelapor (terenkripsi)');

            // Meter reading
            $table->integer('meter_reading_before');
            $table->integer('meter_reading_after');
            $table->integer('usage')->comment('Penggunaan (m³)');

            // Foto (terenkripsi)
            $table->text('photo_url')->nullable()->comment('🔐 URL foto meter (terenkripsi)');

            // Lokasi (terenkripsi)
            $table->text('latitude')->nullable()->comment('🔐 Koordinat latitude (terenkripsi)');
            $table->text('longitude')->nullable()->comment('🔐 Koordinat longitude (terenkripsi)');
            $table->boolean('location_verified')->default(false);

            // Status
            $table->enum('status', ['submitted', 'processing', 'approved', 'rejected'])->default('submitted');
            $table->timestamp('reported_at');
            $table->uuid('skpd_id')->nullable()->comment('ID SKPD yang dibuat');

            $table->timestamps();

            $table->foreign('water_object_id')->references('id')->on('water_objects');
            $table->foreign('user_id')->references('id')->on('users');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_reports');
    }
};
