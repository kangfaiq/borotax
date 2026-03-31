<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom berlaku_mulai & berlaku_sampai untuk versioning tarif.
     *
     * Memungkinkan:
     * - Penjadwalan tarif baru di masa depan
     * - Riwayat tarif lama tetap tersimpan
     * - Lookup tarif berdasarkan tanggal SKPD dibuat
     */
    public function up(): void
    {
        // Step 1: Add columns (skip if already exist from partial migration)
        if (!Schema::hasColumn('reklame_tariffs', 'berlaku_mulai')) {
            Schema::table('reklame_tariffs', function (Blueprint $table) {
                $table->date('berlaku_mulai')->default('2026-01-01')
                    ->after('is_active')
                    ->comment('Tanggal mulai berlaku tarif');
                $table->date('berlaku_sampai')->nullable()
                    ->after('berlaku_mulai')
                    ->comment('Tanggal akhir berlaku (null = masih berlaku)');
            });
        }

        // Step 2: Drop FK, update unique, re-add FK
        Schema::table('reklame_tariffs', function (Blueprint $table) {
            $table->dropForeign(['sub_jenis_pajak_id']);
            $table->dropUnique('reklame_tariffs_unique');

            $table->unique(
                ['sub_jenis_pajak_id', 'kelompok_lokasi', 'satuan_waktu', 'berlaku_mulai'],
                'reklame_tariffs_unique'
            );

            $table->foreign('sub_jenis_pajak_id')
                ->references('id')
                ->on('sub_jenis_pajak')
                ->cascadeOnDelete();

            $table->index('berlaku_mulai', 'reklame_tariffs_berlaku_idx');
        });
    }

    public function down(): void
    {
        Schema::table('reklame_tariffs', function (Blueprint $table) {
            $table->dropIndex('reklame_tariffs_berlaku_idx');
            $table->dropForeign(['sub_jenis_pajak_id']);
            $table->dropUnique('reklame_tariffs_unique');

            $table->unique(
                ['sub_jenis_pajak_id', 'kelompok_lokasi', 'satuan_waktu'],
                'reklame_tariffs_unique'
            );

            $table->foreign('sub_jenis_pajak_id')
                ->references('id')
                ->on('sub_jenis_pajak')
                ->cascadeOnDelete();

            $table->dropColumn(['berlaku_mulai', 'berlaku_sampai']);
        });
    }
};
