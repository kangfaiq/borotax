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
        Schema::create('skpd_air_tanah', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_skpd', 50)->unique();
            $table->uuid('meter_report_id');
            $table->uuid('water_object_id');
            $table->uuid('jenis_pajak_id');
            $table->uuid('sub_jenis_pajak_id');

            // Data WP (terenkripsi)
            $table->text('nik_wajib_pajak')->comment('🔐 NIK wajib pajak (terenkripsi)');
            $table->text('nama_wajib_pajak')->comment('🔐 Nama wajib pajak (terenkripsi)');
            $table->text('alamat_wajib_pajak')->comment('🔐 Alamat wajib pajak (terenkripsi)');

            // Data objek
            $table->text('nama_objek')->comment('🔐 Nama objek (terenkripsi)');
            $table->text('alamat_objek')->comment('🔐 Alamat objek (terenkripsi)');
            $table->string('nopd', 20)->nullable();
            $table->string('kecamatan', 50);
            $table->string('kelurahan', 50);

            // Meter reading
            $table->integer('meter_reading_before');
            $table->integer('meter_reading_after');
            $table->integer('usage')->comment('Penggunaan (m³)');
            $table->string('periode_bulan', 20);

            // Perhitungan (terenkripsi)
            $table->text('tarif_per_m3')->comment('🔐 Tarif per m³ (terenkripsi)');
            $table->text('dasar_pengenaan')->comment('🔐 Dasar pengenaan (terenkripsi)');
            $table->decimal('tarif_persen', 5, 2)->default(20);
            $table->text('jumlah_pajak')->comment('🔐 Jumlah pajak (terenkripsi)');

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

            $table->foreign('meter_report_id')->references('id')->on('meter_reports');
            $table->foreign('water_object_id')->references('id')->on('water_objects');
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
        Schema::dropIfExists('skpd_air_tanah');
    }
};
