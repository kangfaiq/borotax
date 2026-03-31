<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('harga_patokan_mblb', function (Blueprint $table) {
            $table->date('berlaku_mulai')->nullable()->after('dasar_hukum')->comment('Tanggal mulai berlaku harga');
            $table->date('berlaku_sampai')->nullable()->after('berlaku_mulai')->comment('Tanggal akhir berlaku (null = masih berlaku)');
        });
    }

    public function down(): void
    {
        Schema::table('harga_patokan_mblb', function (Blueprint $table) {
            $table->dropColumn(['berlaku_mulai', 'berlaku_sampai']);
        });
    }
};
