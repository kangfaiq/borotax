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
        Schema::create('gebyar_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');

            // User info (terenkripsi)
            $table->text('user_nik')->comment('🔐 NIK pengirim (terenkripsi)');
            $table->text('user_name')->comment('🔐 Nama pengirim (terenkripsi)');

            // Transaksi
            $table->uuid('jenis_pajak_id');
            $table->text('place_name')->comment('🔐 Nama tempat (terenkripsi)');
            $table->date('transaction_date');
            $table->text('transaction_amount')->comment('🔐 Nominal transaksi (terenkripsi)');
            $table->string('transaction_amount_hash', 64)->index()->comment('Hash nominal untuk deteksi duplikat');

            // Gambar (terenkripsi)
            $table->text('image_url')->comment('🔐 URL gambar nota (terenkripsi)');
            $table->text('original_image_url')->nullable()->comment('🔐 URL gambar asli (terenkripsi)');

            // Status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('period_year');
            $table->integer('kupon_count')->default(1);
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->timestamp('verified_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('jenis_pajak_id')->references('id')->on('jenis_pajak');
            $table->index('status');
            $table->index('period_year');
            $table->index(['transaction_date', 'transaction_amount_hash'], 'gebyar_txn_date_amount_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gebyar_submissions');
    }
};
