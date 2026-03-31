<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_ppj_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tax_id')->constrained('taxes')->cascadeOnDelete();
            $table->foreignUuid('harga_satuan_listrik_id')->nullable()->constrained('harga_satuan_listrik')->nullOnDelete();
            $table->decimal('kapasitas_kva', 12, 2);
            $table->decimal('tingkat_penggunaan_persen', 5, 2);
            $table->decimal('jangka_waktu_jam', 10, 2);
            $table->text('harga_satuan'); // encrypted
            $table->text('njtl'); // encrypted — Nilai Jual Tenaga Listrik
            $table->text('subtotal_dpp'); // encrypted
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_ppj_details');
    }
};
