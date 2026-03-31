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
        Schema::table('wajib_pajak', function (Blueprint $table) {
            $table->enum('asal_wilayah', ['bojonegoro', 'luar_bojonegoro'])
                ->default('bojonegoro')
                ->after('kelurahan')
                ->comment('Asal wilayah WP');

            $table->string('province_code')->nullable()->after('asal_wilayah');
            $table->string('regency_code')->nullable()->after('province_code');
            $table->string('district_code')->nullable()->after('regency_code');
            $table->string('village_code')->nullable()->after('district_code');

            $table->foreign('province_code')->references('code')->on('provinces')->nullOnDelete();
            $table->foreign('regency_code')->references('code')->on('regencies')->nullOnDelete();
            $table->foreign('district_code')->references('code')->on('districts')->nullOnDelete();
            $table->foreign('village_code')->references('code')->on('villages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wajib_pajak', function (Blueprint $table) {
            $table->dropForeign(['province_code']);
            $table->dropForeign(['regency_code']);
            $table->dropForeign(['district_code']);
            $table->dropForeign(['village_code']);

            $table->dropColumn([
                'asal_wilayah',
                'province_code',
                'regency_code',
                'district_code',
                'village_code',
            ]);
        });
    }
};
