<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelompok_lokasi_jalan', function (Blueprint $table) {
            $table->date('berlaku_mulai')->nullable()->after('is_active');
            $table->date('berlaku_sampai')->nullable()->after('berlaku_mulai');
        });

        DB::table('kelompok_lokasi_jalan')
            ->whereNull('berlaku_mulai')
            ->update([
                'berlaku_mulai' => '2026-01-01',
                'berlaku_sampai' => null,
            ]);
    }

    public function down(): void
    {
        Schema::table('kelompok_lokasi_jalan', function (Blueprint $table) {
            $table->dropColumn(['berlaku_mulai', 'berlaku_sampai']);
        });
    }
};