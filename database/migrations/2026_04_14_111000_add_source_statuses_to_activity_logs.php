<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('source_statuses', 255)->nullable()->after('summary_count')
                ->comment('Daftar status asal plain untuk event batch seperti auto-expire, format: ,pending,verified,');

            $table->index(['action', 'source_statuses'], 'activity_logs_action_source_statuses_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_action_source_statuses_index');
            $table->dropColumn('source_statuses');
        });
    }
};