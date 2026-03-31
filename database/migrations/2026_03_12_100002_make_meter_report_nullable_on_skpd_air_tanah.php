<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('skpd_air_tanah', function (Blueprint $table) {
            $table->uuid('meter_report_id')->nullable()->change();
            $table->uuid('sub_jenis_pajak_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('skpd_air_tanah', function (Blueprint $table) {
            $table->uuid('meter_report_id')->nullable(false)->change();
            $table->uuid('sub_jenis_pajak_id')->nullable(false)->change();
        });
    }
};
