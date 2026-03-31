<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pembetulan_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tax_id')->comment('Billing yang ingin dikoreksi');
            $table->uuid('user_id')->comment('WP yang mengajukan');
            $table->text('alasan')->comment('Alasan pembetulan dari WP');
            $table->decimal('omzet_baru', 15, 2)->nullable()->comment('Omzet koreksi (saran WP)');
            $table->enum('status', ['pending', 'diproses', 'selesai', 'ditolak'])->default('pending');
            $table->text('catatan_petugas')->nullable();
            $table->uuid('processed_by')->nullable()->comment('Petugas yang menangani');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('tax_id')->references('id')->on('taxes')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembetulan_requests');
    }
};
