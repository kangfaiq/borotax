<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->string('npwpd', 13)->nullable()->after('sub_jenis_pajak_id');
        });

        Schema::table('permohonan_sewa_reklame', function (Blueprint $table) {
            $table->string('npwpd', 13)->nullable()->after('skpd_id');
        });
    }

    public function down(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->dropColumn('npwpd');
        });

        Schema::table('permohonan_sewa_reklame', function (Blueprint $table) {
            $table->dropColumn('npwpd');
        });
    }
};
