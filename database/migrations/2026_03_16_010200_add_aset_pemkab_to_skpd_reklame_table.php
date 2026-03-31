<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->uuid('aset_reklame_pemkab_id')->nullable()->after('request_id')
                ->comment('Link ke aset pemkab (jika sewa aset pemkab)');
            $table->uuid('permohonan_sewa_id')->nullable()->after('aset_reklame_pemkab_id')
                ->comment('Link ke permohonan sewa online (jika dari online)');

            $table->foreign('aset_reklame_pemkab_id')->references('id')->on('aset_reklame_pemkab')->nullOnDelete();
            $table->foreign('permohonan_sewa_id')->references('id')->on('permohonan_sewa_reklame')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->dropForeign(['aset_reklame_pemkab_id']);
            $table->dropForeign(['permohonan_sewa_id']);
            $table->dropColumn(['aset_reklame_pemkab_id', 'permohonan_sewa_id']);
        });
    }
};
