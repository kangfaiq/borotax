<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->boolean('is_opd')->default(false)->after('is_active')
                ->comment('Apakah objek pajak untuk OPD (khusus Jasa Boga/Katering)');
        });
    }

    public function down(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->dropColumn('is_opd');
        });
    }
};
