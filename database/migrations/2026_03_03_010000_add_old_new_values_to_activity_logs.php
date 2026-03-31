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
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->text('old_values')->nullable()->after('description')
                ->comment('JSON encrypted: nilai sebelum perubahan');
            $table->text('new_values')->nullable()->after('old_values')
                ->comment('JSON encrypted: nilai setelah perubahan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['old_values', 'new_values']);
        });
    }
};
