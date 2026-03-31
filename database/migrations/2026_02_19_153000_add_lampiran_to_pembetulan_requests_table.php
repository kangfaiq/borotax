<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pembetulan_requests', function (Blueprint $table) {
            $table->string('lampiran')->nullable()->after('omzet_baru')
                ->comment('Path file lampiran pendukung (foto/dokumen, max 1MB)');
        });
    }

    public function down(): void
    {
        Schema::table('pembetulan_requests', function (Blueprint $table) {
            $table->dropColumn('lampiran');
        });
    }
};
