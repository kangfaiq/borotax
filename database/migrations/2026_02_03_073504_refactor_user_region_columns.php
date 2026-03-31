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
        Schema::table('users', function (Blueprint $table) {
            // Drop old columns if they exist
            // $table->dropColumn(['kabupaten', 'kecamatan', 'kelurahan']); // We can do this if we are freshing

            // Add new code columns
            $table->string('regency_code')->nullable()->after('alamat');
            $table->string('district_code')->nullable()->after('regency_code');
            $table->string('village_code')->nullable()->after('district_code');
            $table->string('birth_regency_code')->nullable()->after('tanggal_lahir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['regency_code', 'district_code', 'village_code', 'birth_regency_code']);
        });
    }
};
