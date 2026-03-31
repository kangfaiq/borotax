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
        Schema::create('pimpinan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kab')->nullable();
            $table->string('opd')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('bidang')->nullable();
            $table->string('sub_bidang')->nullable();
            $table->string('nama');
            $table->string('pangkat')->nullable();
            $table->string('nip')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pimpinan');
    }
};
