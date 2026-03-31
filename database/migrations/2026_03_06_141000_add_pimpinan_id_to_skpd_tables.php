<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->uuid('pimpinan_id')->nullable()->after('verifikator_nama');
            $table->foreign('pimpinan_id')->references('id')->on('pimpinan')->nullOnDelete();
        });

        Schema::table('skpd_air_tanah', function (Blueprint $table) {
            $table->uuid('pimpinan_id')->nullable()->after('verifikator_nama');
            $table->foreign('pimpinan_id')->references('id')->on('pimpinan')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->dropForeign(['pimpinan_id']);
            $table->dropColumn('pimpinan_id');
        });

        Schema::table('skpd_air_tanah', function (Blueprint $table) {
            $table->dropForeign(['pimpinan_id']);
            $table->dropColumn('pimpinan_id');
        });
    }
};
