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
        Schema::create('data_change_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Polymorphic: entity yang diminta perubahannya (wajib_pajak / tax_objects)
            $table->string('entity_type', 100)->comment('Tabel: wajib_pajak, tax_objects');
            $table->uuid('entity_id');

            // Detail perubahan (encrypted JSON)
            $table->text('field_changes')->comment('JSON encrypted: {field: {old, new}}');

            // Metadata permintaan
            $table->text('alasan_perubahan');
            $table->string('dokumen_pendukung')->nullable()->comment('Path file pendukung');

            // Status workflow
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_review')->nullable()->comment('Catatan dari reviewer');

            // Aktor
            $table->uuid('requested_by');
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('reviewed_by')->references('id')->on('users');

            // Indexes
            $table->index('entity_type');
            $table->index('entity_id');
            $table->index('status');
            $table->index('requested_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_change_requests');
    }
};
