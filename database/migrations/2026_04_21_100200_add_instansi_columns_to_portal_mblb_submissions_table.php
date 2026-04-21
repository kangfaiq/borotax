<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portal_mblb_submissions', function (Blueprint $table) {
            $table->foreignUuid('instansi_id')
                ->nullable()
                ->after('user_id')
                ->constrained('instansi')
                ->nullOnDelete();
            $table->string('instansi_nama')->nullable()->after('instansi_id');
            $table->string('instansi_kategori', 50)->nullable()->after('instansi_nama');
        });
    }

    public function down(): void
    {
        Schema::table('portal_mblb_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('instansi_id');
            $table->dropColumn(['instansi_nama', 'instansi_kategori']);
        });
    }
};