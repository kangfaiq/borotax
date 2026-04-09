<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('active_session_id')
                ->nullable()
                ->after('remember_token')
                ->comment('Single session marker for web and API access');
            $table->string('active_session_channel')
                ->nullable()
                ->after('active_session_id')
                ->comment('Current active session channel');
            $table->string('active_session_ip', 45)
                ->nullable()
                ->after('active_session_channel')
                ->comment('Last active session IP address');
            $table->text('active_session_user_agent')
                ->nullable()
                ->after('active_session_ip')
                ->comment('Last active session user agent');
            $table->timestamp('active_session_started_at')
                ->nullable()
                ->after('active_session_user_agent')
                ->comment('Last active session issued at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'active_session_id',
                'active_session_channel',
                'active_session_ip',
                'active_session_user_agent',
                'active_session_started_at',
            ]);
        });
    }
};