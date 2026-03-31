<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->string('stpd_payment_code', 18)->nullable()->unique()->after('stpd_number');
        });
    }

    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropUnique(['stpd_payment_code']);
            $table->dropColumn('stpd_payment_code');
        });
    }
};