<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add diameter2 column for ellipse shape and update bentuk comment.
     * Elips: π × (d1/2) × (d2/2)
     */
    public function up(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->decimal('diameter2', 8, 2)->nullable()->after('diameter')
                ->comment('Untuk elips - diameter kedua (meter)');
        });

        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->decimal('diameter2', 8, 2)->nullable()->after('diameter')
                ->comment('Untuk elips - diameter kedua (meter)');
        });
    }

    public function down(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->dropColumn('diameter2');
        });

        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->dropColumn('diameter2');
        });
    }
};
