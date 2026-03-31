<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->uuid('request_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->uuid('request_id')->nullable(false)->change();
        });
    }
};
