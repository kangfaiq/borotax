<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aset_reklame_pemkab', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_aset', 10)->unique()->comment('NB001, BB001, dst');
            $table->string('nama', 150)->comment('Nama/deskripsi aset');
            $table->enum('jenis', ['neon_box', 'billboard']);

            // Lokasi
            $table->text('lokasi')->comment('🔐 Alamat/ruas jalan (terenkripsi)');
            $table->text('keterangan')->nullable()->comment('Deskripsi tambahan');
            $table->string('kawasan', 100)->nullable()->comment('Kawasan Terminal, Perbatasan, dst');
            $table->string('traffic', 50)->nullable()->comment('Sangat Tinggi, Tinggi, dst');
            $table->string('kelompok_lokasi', 5)->nullable()->comment('A, A1, A2, A3, B, C');

            // Dimensi
            $table->decimal('panjang', 8, 2);
            $table->decimal('lebar', 8, 2);
            $table->decimal('luas_m2', 10, 2);
            $table->integer('jumlah_muka')->default(1);

            // Koordinat
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Harga sewa referensi (terenkripsi)
            $table->text('harga_sewa_per_tahun')->nullable()->comment('🔐 Harga referensi per tahun (terenkripsi)');
            $table->text('harga_sewa_per_bulan')->nullable()->comment('🔐 Harga referensi per bulan (terenkripsi)');
            $table->text('harga_sewa_per_minggu')->nullable()->comment('🔐 Harga referensi per minggu (terenkripsi)');

            // Foto
            $table->text('foto_path')->nullable()->comment('🔐 Path foto aset (terenkripsi)');

            // Status
            $table->enum('status_ketersediaan', ['tersedia', 'disewa', 'maintenance', 'tidak_aktif', 'dipinjam_opd'])->default('tersedia');
            $table->text('catatan_status')->nullable()->comment('Alasan manual override status');
            $table->boolean('is_active')->default(true);

            // Peminjaman OPD/Dinas
            $table->string('peminjam_opd', 150)->nullable()->comment('Nama OPD/Dinas peminjam');
            $table->string('materi_pinjam', 255)->nullable()->comment('Isi materi yang ditayangkan');
            $table->date('pinjam_mulai')->nullable();
            $table->date('pinjam_selesai')->nullable();
            $table->text('catatan_pinjam')->nullable();

            $table->timestamps();

            $table->index('jenis');
            $table->index('status_ketersediaan');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aset_reklame_pemkab');
    }
};
