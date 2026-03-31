<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jenis_pajak', function (Blueprint $table) {
            $table->decimal('opsen_persen', 5, 2)->nullable()->after('tarif_default');
        });
    }

    public function down(): void
    {
        Schema::table('jenis_pajak', function (Blueprint $table) {
            $table->dropColumn('opsen_persen');
        });
    }
};
