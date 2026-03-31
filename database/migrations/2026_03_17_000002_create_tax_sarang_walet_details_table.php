<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tax_sarang_walet_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tax_id')->constrained('taxes')->cascadeOnDelete();
            $table->foreignUuid('harga_patokan_sarang_walet_id')->nullable()->constrained('harga_patokan_sarang_walet')->nullOnDelete();
            $table->string('jenis_sarang', 100); // Snapshot nama jenis sarang
            $table->decimal('volume_kg', 12, 2); // Volume dalam kg
            $table->text('harga_patokan'); // Encrypted, snapshot harga per kg
            $table->text('subtotal_dpp'); // Encrypted, volume × harga_patokan
            $table->timestamps();

            $table->index('tax_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_sarang_walet_details');
    }
};
