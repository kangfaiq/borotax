<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wajib_pajak', function (Blueprint $table) {
            $table->dropColumn(['kecamatan', 'kelurahan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wajib_pajak', function (Blueprint $table) {
            $table->string('kecamatan', 50)->nullable()->after('alamat');
            $table->string('kelurahan', 50)->nullable()->after('kecamatan');
        });
    }
};
