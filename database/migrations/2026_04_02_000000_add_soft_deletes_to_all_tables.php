<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'pimpinan',
        'wajib_pajak',
        'tax_objects',
        'reklame_requests',
        'skpd_reklame',
        'skpd_air_tanah',
        'meter_reports',
        'aset_reklame_pemkab',
        'permohonan_sewa_reklame',
        'peminjaman_aset_reklame',
        'kelompok_lokasi_jalan',
        'reklame_tariffs',
        'reklame_nilai_strategis',
        'harga_patokan_mblb',
        'tax_mblb_details',
        'harga_patokan_sarang_walet',
        'tax_sarang_walet_details',
        'harga_satuan_listrik',
        'tax_ppj_details',
        'npa_air_tanah',
        'tarif_pajak',
        'harga_patokan_reklame',
        'portal_mblb_submissions',
        'tax_assessment_compensations',
        'pembetulan_requests',
        'gebyar_submissions',
        'destinations',
        'news',
        'app_notifications',
        'app_versions',
        'districts',
        'villages',
        'data_change_requests',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};
