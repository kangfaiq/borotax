<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->uuid('tax_object_id')->nullable()->after('sub_jenis_pajak_id');
            $table->unsignedTinyInteger('masa_pajak_bulan')->nullable()->after('payment_expired_at');
            $table->unsignedSmallInteger('masa_pajak_tahun')->nullable()->after('masa_pajak_bulan');

            $table->foreign('tax_object_id')->references('id')->on('tax_objects')->nullOnDelete();
            $table->unique(['tax_object_id', 'masa_pajak_bulan', 'masa_pajak_tahun'], 'taxes_objek_masa_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropUnique('taxes_objek_masa_unique');
            $table->dropForeign(['tax_object_id']);
            $table->dropColumn(['tax_object_id', 'masa_pajak_bulan', 'masa_pajak_tahun']);
        });
    }
};
