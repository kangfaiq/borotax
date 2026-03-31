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
        Schema::table('npa_air_tanah', function (Blueprint $table) {
            $table->json('npa_tiers')->nullable()->after('npa_per_m3')->comment('Tarif struktur progresif (min_vol, max_vol, npa) -> encrypted array');
            $table->text('npa_per_m3')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npa_air_tanah', function (Blueprint $table) {
            $table->dropColumn('npa_tiers');
            $table->text('npa_per_m3')->nullable(false)->change();
        });
    }
};
