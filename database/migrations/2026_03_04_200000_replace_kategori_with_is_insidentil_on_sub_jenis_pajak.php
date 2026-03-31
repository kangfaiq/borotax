<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add is_insidentil boolean column
        Schema::table('sub_jenis_pajak', function (Blueprint $table) {
            $table->boolean('is_insidentil')->default(false)
                ->comment('Apakah sub jenis pajak bersifat insidentil')
                ->after('tarif_persen');
        });

        // 2. Migrate data: kategori 'insidentil' → is_insidentil = true
        DB::table('sub_jenis_pajak')
            ->where('kategori', 'insidentil')
            ->update(['is_insidentil' => true]);

        // 3. Drop kategori column
        Schema::table('sub_jenis_pajak', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }

    public function down(): void
    {
        // 1. Re-add kategori column
        Schema::table('sub_jenis_pajak', function (Blueprint $table) {
            $table->string('kategori', 50)->nullable()
                ->comment('Kategori tambahan (e.g., tetap, insidentil)')
                ->after('tarif_persen');
        });

        // 2. Migrate data back
        DB::table('sub_jenis_pajak')
            ->where('is_insidentil', true)
            ->update(['kategori' => 'insidentil']);

        DB::table('sub_jenis_pajak')
            ->where('is_insidentil', false)
            ->update(['kategori' => 'tetap']);

        // 3. Drop is_insidentil column
        Schema::table('sub_jenis_pajak', function (Blueprint $table) {
            $table->dropColumn('is_insidentil');
        });
    }
};
