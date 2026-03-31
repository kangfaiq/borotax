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
            $table->dropForeign(['tax_object_id']);
            $table->uuid('tax_object_id')->nullable()->change();
            $table->foreign('tax_object_id')->references('id')->on('tax_objects')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->dropForeign(['tax_object_id']);
            $table->uuid('tax_object_id')->nullable(false)->change();
            $table->foreign('tax_object_id')->references('id')->on('tax_objects');
        });
    }
};
