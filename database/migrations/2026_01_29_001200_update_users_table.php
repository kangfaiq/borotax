<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Update users table sesuai database_schema.md
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ganti kolom name dengan nama_lengkap (terenkripsi)
            $table->text('nama_lengkap')->after('id')->nullable()->comment('🔐 Nama lengkap (terenkripsi)');

            // NIK dan hash
            $table->text('nik')->after('nama_lengkap')->nullable()->comment('🔐 NIK (terenkripsi)');
            $table->string('nik_hash', 64)->after('nik')->nullable()->unique()->comment('Hash NIK untuk pencarian (SHA-256)');

            // Data personal (terenkripsi)
            $table->text('tempat_lahir')->nullable()->comment('🔐 Tempat lahir (terenkripsi)');
            $table->text('tanggal_lahir')->nullable()->comment('🔐 Tanggal lahir (terenkripsi)');
            $table->text('alamat')->nullable()->comment('🔐 Alamat lengkap (terenkripsi)');
            $table->text('no_whatsapp')->nullable()->comment('🔐 Nomor WhatsApp (terenkripsi)');

            // Email hash untuk pencarian
            $table->string('email_hash', 64)->after('email')->nullable()->unique()->comment('Hash email untuk pencarian (SHA-256)');

            // Rename password -> password_hash (Laravel default sudah hash)
            // $table->renameColumn('password', 'password_hash'); // Skip karena Laravel sudah handle

            // Security fields
            $table->integer('failed_login_attempts')->default(0)->comment('Jumlah percobaan login gagal');
            $table->timestamp('locked_until')->nullable()->comment('Waktu hingga akun terkunci');
            $table->timestamp('last_login_at')->nullable()->comment('Waktu login terakhir');
            $table->timestamp('password_changed_at')->nullable()->comment('Waktu password terakhir diubah');
            $table->boolean('must_change_password')->default(false)->comment('Wajib ganti password saat login');

            // Dokumen (terenkripsi)
            $table->text('foto_ktp_url')->nullable()->comment('🔐 URL foto KTP (terenkripsi)');
            $table->text('foto_selfie_url')->nullable()->comment('🔐 URL foto selfie (terenkripsi)');

            // Status dan role
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending')->comment('Status user');
            $table->enum('role', ['user', 'wajibPajak', 'petugas', 'verifikator', 'admin'])->default('user')->comment('Role user');

            // Gebyar
            $table->integer('total_kupon_undian')->default(0)->comment('Total kupon undian');

            // Verifikasi
            $table->timestamp('verified_at')->nullable()->comment('Waktu verifikasi akun');

            // Soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nama_lengkap',
                'nik',
                'nik_hash',
                'tempat_lahir',
                'tanggal_lahir',
                'alamat',
                'no_whatsapp',
                'email_hash',
                'failed_login_attempts',
                'locked_until',
                'last_login_at',
                'password_changed_at',
                'must_change_password',
                'foto_ktp_url',
                'foto_selfie_url',
                'status',
                'role',
                'total_kupon_undian',
                'verified_at',
                'deleted_at'
            ]);
        });
    }
};
