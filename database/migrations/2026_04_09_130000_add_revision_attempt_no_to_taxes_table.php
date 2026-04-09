<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('taxes', 'revision_attempt_no')) {
            Schema::table('taxes', function (Blueprint $table) {
                $table->unsignedSmallInteger('revision_attempt_no')->default(0)->after('pembetulan_ke')
                    ->comment('Nomor internal attempt billing per masa pajak untuk audit dan unique key');
            });
        }

        DB::table('taxes')->update([
            'revision_attempt_no' => DB::raw('pembetulan_ke'),
        ]);

        Schema::table('taxes', function (Blueprint $table) {
            $table->dropForeign(['tax_object_id']);
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->dropUnique('taxes_objek_masa_pembetulan_seq_unique');
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->unique(
                ['tax_object_id', 'masa_pajak_bulan', 'masa_pajak_tahun', 'revision_attempt_no', 'billing_sequence'],
                'taxes_objek_masa_revision_attempt_seq_unique'
            );
            $table->foreign('tax_object_id')->references('id')->on('tax_objects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropForeign(['tax_object_id']);
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->dropUnique('taxes_objek_masa_revision_attempt_seq_unique');
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->unique(
                ['tax_object_id', 'masa_pajak_bulan', 'masa_pajak_tahun', 'pembetulan_ke', 'billing_sequence'],
                'taxes_objek_masa_pembetulan_seq_unique'
            );
            $table->foreign('tax_object_id')->references('id')->on('tax_objects')->nullOnDelete();
        });

        if (Schema::hasColumn('taxes', 'revision_attempt_no')) {
            Schema::table('taxes', function (Blueprint $table) {
                $table->dropColumn('revision_attempt_no');
            });
        }
    }
};