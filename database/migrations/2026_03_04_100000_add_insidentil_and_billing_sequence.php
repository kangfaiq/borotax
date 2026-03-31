<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tambah is_insidentil pada tax_objects dan billing_sequence pada taxes.
     *
     * is_insidentil: untuk objek pajak Hiburan yang bersifat insidentil
     *   (multi-billing per masa pajak, tidak kena denda, wajib keterangan)
     *
     * billing_sequence: mendukung multi-billing per masa pajak untuk objek
     *   insidentil dan OPD. Default 0 untuk objek reguler (tetap terproteksi
     *   oleh unique constraint), auto-increment (1, 2, 3…) untuk insidentil/OPD.
     */
    public function up(): void
    {
        // 1. Tambah is_insidentil pada tax_objects
        if (!Schema::hasColumn('tax_objects', 'is_insidentil')) {
            Schema::table('tax_objects', function (Blueprint $table) {
                $table->boolean('is_insidentil')->default(false)->after('is_opd')
                    ->comment('Objek insidentil: multi-billing per masa pajak, tidak kena denda');
            });
        }

        // 2. Tambah billing_sequence pada taxes
        if (!Schema::hasColumn('taxes', 'billing_sequence')) {
            Schema::table('taxes', function (Blueprint $table) {
                $table->unsignedSmallInteger('billing_sequence')->default(0)->after('pembetulan_ke')
                    ->comment('Urutan billing dalam 1 masa pajak: 0 = reguler, 1+ = multi-billing (OPD/insidentil)');
            });
        }

        // 3. Replace unique constraint to include billing_sequence
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropForeign(['tax_object_id']);
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->dropUnique('taxes_objek_masa_pembetulan_unique');
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->unique(
                ['tax_object_id', 'masa_pajak_bulan', 'masa_pajak_tahun', 'pembetulan_ke', 'billing_sequence'],
                'taxes_objek_masa_pembetulan_seq_unique'
            );
            $table->foreign('tax_object_id')->references('id')->on('tax_objects')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original unique constraint
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropForeign(['tax_object_id']);
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->dropUnique('taxes_objek_masa_pembetulan_seq_unique');
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->unique(
                ['tax_object_id', 'masa_pajak_bulan', 'masa_pajak_tahun', 'pembetulan_ke'],
                'taxes_objek_masa_pembetulan_unique'
            );
            $table->foreign('tax_object_id')->references('id')->on('tax_objects')->nullOnDelete();
        });

        if (Schema::hasColumn('taxes', 'billing_sequence')) {
            Schema::table('taxes', function (Blueprint $table) {
                $table->dropColumn('billing_sequence');
            });
        }

        if (Schema::hasColumn('tax_objects', 'is_insidentil')) {
            Schema::table('tax_objects', function (Blueprint $table) {
                $table->dropColumn('is_insidentil');
            });
        }
    }
};
