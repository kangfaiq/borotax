<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabel riwayat tarif persentase pajak per sub jenis pajak.
     *
     * Memungkinkan:
     * - Versioning tarif berdasarkan masa berlaku
     * - Lookup tarif berdasarkan tanggal masa pajak
     * - Riwayat tarif lama tetap tersimpan
     * - Penjadwalan tarif baru di masa depan
     */
    public function up(): void
    {
        Schema::create('tarif_pajak', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sub_jenis_pajak_id')
                ->constrained('sub_jenis_pajak')
                ->cascadeOnDelete();
            $table->decimal('tarif_persen', 5, 2)->comment('Tarif pajak (%)');
            $table->date('berlaku_mulai')->comment('Tanggal mulai berlaku');
            $table->date('berlaku_sampai')->nullable()->comment('Tanggal akhir berlaku (null = masih berlaku)');
            $table->string('dasar_hukum', 255)->nullable()->comment('Referensi peraturan (Perda/Pergub)');
            $table->boolean('is_active')->default(true)->index();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['sub_jenis_pajak_id', 'berlaku_mulai'], 'tarif_pajak_unique');
            $table->index('berlaku_mulai', 'tarif_pajak_berlaku_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarif_pajak');
    }
};
