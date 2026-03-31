<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->foreignUuid('harga_patokan_reklame_id')->nullable()->after('sub_jenis_pajak_id')->constrained('harga_patokan_reklame')->nullOnDelete();
            $table->foreignUuid('lokasi_jalan_id')->nullable()->after('kelompok_lokasi')->constrained('kelompok_lokasi_jalan')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->dropForeign(['harga_patokan_reklame_id']);
            $table->dropForeign(['lokasi_jalan_id']);
            $table->dropColumn(['harga_patokan_reklame_id', 'lokasi_jalan_id']);
        });
    }
};