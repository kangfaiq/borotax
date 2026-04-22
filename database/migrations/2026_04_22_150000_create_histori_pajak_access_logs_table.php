<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('histori_pajak_access_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('npwpd', 13)->nullable()->index();
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->string('ip', 45)->nullable()->index();
            $table->string('user_agent', 512)->nullable();
            $table->string('status', 50)->index();
            $table->unsignedInteger('jumlah_dokumen')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['npwpd', 'created_at']);
            $table->index(['ip', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('histori_pajak_access_logs');
    }
};
