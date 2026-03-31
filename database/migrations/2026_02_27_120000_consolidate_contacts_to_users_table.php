<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom no_telp ke tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->text('no_telp')->nullable()->after('no_whatsapp')
                ->comment('🔐 No telepon rumah/kantor (terenkripsi), format: (0353) 881826');
        });

        // 2. Drop kolom kontak dari tabel wajib_pajak
        Schema::table('wajib_pajak', function (Blueprint $table) {
            $table->dropColumn(['no_telp', 'email']);
        });

        // 3. Drop kolom kontak dari tabel tax_objects
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->dropColumn(['nomor_telp', 'email']);
        });
    }

    public function down(): void
    {
        // Restore kolom kontak ke tax_objects
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->text('nomor_telp')->nullable()->comment('🔐 Nomor telepon (terenkripsi)');
            $table->text('email')->nullable()->comment('🔐 Email (terenkripsi)');
        });

        // Restore kolom kontak ke wajib_pajak
        Schema::table('wajib_pajak', function (Blueprint $table) {
            $table->text('no_telp')->comment('🔐 encrypted');
            $table->text('email')->comment('🔐 encrypted');
        });

        // Drop no_telp dari users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('no_telp');
        });
    }
};
