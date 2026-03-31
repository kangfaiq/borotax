<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Taxes table
        Schema::table('taxes', function (Blueprint $table) {
            $table->string('dasar_hukum', 255)->nullable()->after('opsen')->comment('Referensi peraturan saat billing dibuat');
            $table->boolean('is_legacy')->default(false)->after('dasar_hukum')->comment('Data dari regulasi lama');
            $table->string('legacy_billing_code', 100)->nullable()->after('is_legacy')->comment('Kode billing sistem lama');

            $table->index('is_legacy');
        });

        // SKPD Air Tanah
        Schema::table('skpd_air_tanah', function (Blueprint $table) {
            $table->string('dasar_hukum', 255)->nullable()->after('kode_billing')->comment('Referensi peraturan saat SKPD dibuat');
            $table->boolean('is_legacy')->default(false)->after('dasar_hukum')->comment('Data dari regulasi lama');
        });

        // SKPD Reklame
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->string('dasar_hukum', 255)->nullable()->after('kode_billing')->comment('Referensi peraturan saat SKPD dibuat');
            $table->boolean('is_legacy')->default(false)->after('dasar_hukum')->comment('Data dari regulasi lama');
        });
    }

    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropIndex(['is_legacy']);
            $table->dropColumn(['dasar_hukum', 'is_legacy', 'legacy_billing_code']);
        });

        Schema::table('skpd_air_tanah', function (Blueprint $table) {
            $table->dropColumn(['dasar_hukum', 'is_legacy']);
        });

        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->dropColumn(['dasar_hukum', 'is_legacy']);
        });
    }
};
