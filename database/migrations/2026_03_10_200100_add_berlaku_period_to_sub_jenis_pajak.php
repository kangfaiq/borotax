<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sub_jenis_pajak', function (Blueprint $table) {
            $table->date('berlaku_mulai')->nullable()->after('urutan')->comment('Tanggal mulai berlaku tarif');
            $table->date('berlaku_sampai')->nullable()->after('berlaku_mulai')->comment('Tanggal akhir berlaku tarif (null = masih berlaku)');
            $table->string('dasar_hukum', 255)->nullable()->after('berlaku_sampai')->comment('Referensi peraturan (Perda/Pergub)');
        });
    }

    public function down(): void
    {
        Schema::table('sub_jenis_pajak', function (Blueprint $table) {
            $table->dropColumn(['berlaku_mulai', 'berlaku_sampai', 'dasar_hukum']);
        });
    }
};
