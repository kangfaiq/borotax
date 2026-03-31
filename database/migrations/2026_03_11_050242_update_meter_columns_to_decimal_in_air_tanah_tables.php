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
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->decimal('last_meter_reading', 10, 2)->nullable()->change();
        });
        Schema::table('meter_reports', function (Blueprint $table) {
            $table->decimal('meter_reading_before', 10, 2)->change();
            $table->decimal('meter_reading_after', 10, 2)->change();
            $table->decimal('usage', 10, 2)->change();
        });
        Schema::table('skpd_air_tanah', function (Blueprint $table) {
            $table->decimal('meter_reading_before', 10, 2)->change();
            $table->decimal('meter_reading_after', 10, 2)->change();
            $table->decimal('usage', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->integer('last_meter_reading')->nullable()->change();
        });
        Schema::table('meter_reports', function (Blueprint $table) {
            $table->integer('meter_reading_before')->change();
            $table->integer('meter_reading_after')->change();
            $table->integer('usage')->change();
        });
        Schema::table('skpd_air_tanah', function (Blueprint $table) {
            $table->integer('meter_reading_before')->change();
            $table->integer('meter_reading_after')->change();
            $table->integer('usage')->change();
        });
    }
};
