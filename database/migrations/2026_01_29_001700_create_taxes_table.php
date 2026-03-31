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
        Schema::create('taxes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relasi
            $table->uuid('jenis_pajak_id');
            $table->uuid('sub_jenis_pajak_id')->nullable();
            $table->uuid('user_id');

            // Data pajak (terenkripsi)
            $table->text('amount')->comment('🔐 Jumlah pajak (terenkripsi)');
            $table->text('omzet')->nullable()->comment('🔐 Omzet (terenkripsi)');
            $table->decimal('tarif_persentase', 5, 2)->nullable();

            // Status dan billing
            $table->enum('status', ['draft', 'pending', 'paid', 'verified', 'rejected', 'expired'])->default('draft');
            $table->string('billing_code', 18)->nullable()->index();
            $table->string('skpd_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->text('attachment_url')->nullable()->comment('🔐 URL lampiran (terenkripsi)');

            // Timestamp
            $table->timestamps();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->uuid('verified_by')->nullable();

            // Meter (untuk Air Tanah)
            $table->integer('meter_reading')->nullable();
            $table->integer('previous_meter_reading')->nullable();
            $table->text('meter_photo_url')->nullable()->comment('🔐 URL foto meter (terenkripsi)');

            // Lokasi (terenkripsi)
            $table->text('latitude')->nullable()->comment('🔐 Koordinat latitude (terenkripsi)');
            $table->text('longitude')->nullable()->comment('🔐 Koordinat longitude (terenkripsi)');

            // Rejection
            $table->text('rejection_reason')->nullable();

            // Payment
            $table->string('payment_channel', 50)->nullable()->comment('Channel: QRIS, VA_BCA, VA_MANDIRI, E_WALLET');
            $table->string('payment_ref', 100)->nullable();
            $table->decimal('payment_fee', 15, 2)->nullable();
            $table->timestamp('payment_expired_at')->nullable();

            $table->foreign('jenis_pajak_id')->references('id')->on('jenis_pajak');
            $table->foreign('sub_jenis_pajak_id')->references('id')->on('sub_jenis_pajak');
            $table->foreign('user_id')->references('id')->on('users');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
