<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tax_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tax_id')->index();
            $table->string('external_ref')->nullable()->index(); // Ref Bank/Channel

            // Encrypted amounts
            $table->text('amount_paid'); // Total bayar
            $table->text('principal_paid'); // Alokasi pokok
            $table->text('penalty_paid'); // Alokasi sanksi

            $table->string('payment_channel'); // BANK_JATIM, QRIS, dll
            $table->dateTime('paid_at');
            $table->text('raw_response')->nullable(); // JSON response dari payment gateway
            $table->string('description')->nullable(); // Keterangan tambahan (misal: "Pokok Only")

            $table->timestamps();

            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_payments');
    }
};
