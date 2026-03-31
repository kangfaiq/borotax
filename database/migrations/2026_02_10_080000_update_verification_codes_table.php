<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Memperbarui tabel verification_codes untuk mendukung OTP registrasi via email.
     */
    public function up(): void
    {
        Schema::dropIfExists('verification_codes');

        Schema::create('verification_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('identifier')->comment('Email tujuan (terenkripsi)');
            $table->string('identifier_hash', 64)->comment('Hash email untuk pencarian');
            $table->string('code', 10)->comment('Kode OTP 6 digit');
            $table->string('code_hash', 64)->comment('Hash kode untuk validasi');
            $table->enum('type', ['registration', 'password_reset', 'login_otp', 'email_change']);
            $table->unsignedTinyInteger('attempts')->default(0)->comment('Jumlah percobaan verifikasi');
            $table->unsignedTinyInteger('max_attempts')->default(3)->comment('Batas maksimal percobaan');
            $table->timestamp('expires_at')->comment('Waktu kedaluwarsa (30 detik)');
            $table->boolean('is_used')->default(false);
            $table->timestamp('sent_at')->nullable()->comment('Waktu OTP dikirim');
            $table->timestamp('verified_at')->nullable()->comment('Waktu OTP berhasil diverifikasi');
            $table->string('verification_token', 64)->nullable()->comment('Token untuk melanjutkan registrasi');
            $table->timestamp('token_expires_at')->nullable()->comment('Waktu token kedaluwarsa');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('identifier_hash');
            $table->index('code_hash');
            $table->index(['type', 'is_used']);
            $table->index('expires_at');
            $table->index('verification_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_codes');

        // Recreate original table
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('code', 10);
            $table->enum('type', ['email_verification', 'password_reset', 'login_otp']);
            $table->string('contact_target', 100)->comment('Email/No HP tujuan');
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->index('user_id');
            $table->index('code');
        });
    }
};
