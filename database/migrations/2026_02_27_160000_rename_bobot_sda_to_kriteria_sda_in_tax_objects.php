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
            $table->renameColumn('bobot_sda', 'kriteria_sda');
        });

        // Update comment & constraint after rename
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->string('kriteria_sda', 5)->nullable()->comment('Kriteria SDA air tanah (1, 2, 3, 4)')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->renameColumn('kriteria_sda', 'bobot_sda');
        });

        Schema::table('tax_objects', function (Blueprint $table) {
            $table->string('bobot_sda', 5)->nullable()->comment('Bobot SDA air tanah (A, B, C, D)')->change();
        });
    }
};
