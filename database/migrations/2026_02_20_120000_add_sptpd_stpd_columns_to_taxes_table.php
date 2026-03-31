<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->string('sptpd_number')->nullable()->after('skpd_number');
            $table->string('stpd_number')->nullable()->after('sptpd_number');
            $table->uuid('parent_tax_id')->nullable()->after('id')->index();
            // pembetulan_ke already exists in 2026_02_19 migration

            // Foreign key to itself
            $table->foreign('parent_tax_id')->references('id')->on('taxes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropForeign(['parent_tax_id']);
            $table->dropColumn(['sptpd_number', 'stpd_number', 'parent_tax_id']);
            // pembetulan_ke might have existed in some previous schema check? 
            // The file view of Tax.php showed 'pembetulan_ke' in fillable and casts.
            // Let me double check usage of 'pembetulan_ke'.
        });
    }
};
