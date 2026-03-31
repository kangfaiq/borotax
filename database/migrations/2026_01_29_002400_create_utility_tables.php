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
        // Tabel Kode Verifikasi (OTP)
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

        // Tabel Notifikasi Aplikasi (Renamed from notifications to avoid conflict with Filament)
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('title', 255);
            $table->text('body');
            $table->string('type', 50)->comment('Jenis: info, payment, verification, promo');
            $table->json('data_payload')->nullable()->comment('Data navigasi');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->index('user_id');
            $table->index('is_read');
            $table->index('created_at');
        });

        // Tabel Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('actor_id')->nullable();
            $table->string('actor_type', 50)->comment('Tipe: user, admin, system');
            $table->string('action', 100)->comment('Aksi: LOGIN, CREATE_TAX, VERIFY_WP, dll');
            $table->string('target_table', 100)->nullable();
            $table->uuid('target_id')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('actor_id');
            $table->index('action');
            $table->index('created_at');
        });

        // Tabel App Versions
        Schema::create('app_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('platform', ['android', 'ios'])->unique();
            $table->string('min_version', 20)->comment('Versi minimal yang diizinkan');
            $table->string('latest_version', 20)->comment('Versi terbaru');
            $table->boolean('force_update')->default(false);
            $table->boolean('maintenance_mode')->default(false);
            $table->text('message')->nullable();
            $table->string('store_url', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('verification_codes');
    }
};
