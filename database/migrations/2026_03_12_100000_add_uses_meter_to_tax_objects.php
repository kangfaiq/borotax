<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->boolean('uses_meter')->default(true)->after('kriteria_sda')
                ->comment('Apakah objek menggunakan meteran air');
        });
    }

    public function down(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->dropColumn('uses_meter');
        });
    }
};
