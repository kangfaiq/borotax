<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add 'cancelled' to status enum
        DB::statement("ALTER TABLE taxes MODIFY COLUMN status ENUM('draft','pending','paid','verified','rejected','expired','cancelled') DEFAULT 'draft'");

        // 2. Add pembetulan_ke column (skip if already exists from partial migration)
        if (!Schema::hasColumn('taxes', 'pembetulan_ke')) {
            Schema::table('taxes', function (Blueprint $table) {
                $table->unsignedTinyInteger('pembetulan_ke')->default(0)->after('masa_pajak_tahun')
                    ->comment('Nomor pembetulan: 0 = asli, 1 = pembetulan ke-1, dst');
            });
        }

        // 3. Drop foreign key on tax_object_id first (it relies on the unique index),
        //    then drop the old unique and create a new one, then re-add FK
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropForeign(['tax_object_id']);
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->dropUnique('taxes_objek_masa_unique');
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->unique(
                ['tax_object_id', 'masa_pajak_bulan', 'masa_pajak_tahun', 'pembetulan_ke'],
                'taxes_objek_masa_pembetulan_unique'
            );
            $table->foreign('tax_object_id')->references('id')->on('tax_objects')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropForeign(['tax_object_id']);
            $table->dropUnique('taxes_objek_masa_pembetulan_unique');
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->unique(
                ['tax_object_id', 'masa_pajak_bulan', 'masa_pajak_tahun'],
                'taxes_objek_masa_unique'
            );
            $table->foreign('tax_object_id')->references('id')->on('tax_objects')->nullOnDelete();
            $table->dropColumn('pembetulan_ke');
        });

        DB::statement("ALTER TABLE taxes MODIFY COLUMN status ENUM('draft','pending','paid','verified','rejected','expired') DEFAULT 'draft'");
    }
};
