<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tax_mblb_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tax_id')->constrained('taxes')->cascadeOnDelete();
            $table->foreignUuid('harga_patokan_mblb_id')->nullable()->constrained('harga_patokan_mblb')->nullOnDelete();
            $table->string('jenis_mblb', 150); // Snapshot nama mineral
            $table->decimal('volume', 12, 2); // Volume dalam m³
            $table->text('harga_patokan'); // Encrypted, snapshot harga per m³
            $table->text('subtotal_dpp'); // Encrypted, volume × harga_patokan
            $table->timestamps();

            $table->index('tax_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_mblb_details');
    }
};
