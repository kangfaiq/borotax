<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BOJONEGORO_PROVINCE_CODE = '35';
    private const BOJONEGORO_REGENCY_CODE = '35.22';

    public function up(): void
    {
        Schema::table('instansi', function (Blueprint $table) {
            $table->enum('asal_wilayah', ['bojonegoro', 'luar_bojonegoro'])
                ->nullable()
                ->after('alamat');
            $table->string('province_code')->nullable()->after('asal_wilayah');
            $table->string('regency_code')->nullable()->after('province_code');
            $table->string('district_code')->nullable()->after('regency_code');
            $table->string('village_code')->nullable()->after('district_code');

            $table->foreign('province_code')->references('code')->on('provinces')->nullOnDelete();
            $table->foreign('regency_code')->references('code')->on('regencies')->nullOnDelete();
            $table->foreign('district_code')->references('code')->on('districts')->nullOnDelete();
            $table->foreign('village_code')->references('code')->on('villages')->nullOnDelete();
        });

        $this->backfillSeededInstansiRegions();
    }

    public function down(): void
    {
        Schema::table('instansi', function (Blueprint $table) {
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

    private function backfillSeededInstansiRegions(): void
    {
        if (! Schema::hasTable('districts') || ! Schema::hasTable('villages')) {
            return;
        }

        $districtMap = [];

        foreach (DB::table('districts')
            ->where('regency_code', self::BOJONEGORO_REGENCY_CODE)
            ->get(['code', 'name']) as $district) {
            $districtMap[$this->normalizeDistrictName($district->name)] = $district->code;
        }

        $villageMap = [];

        foreach (DB::table('villages')
            ->whereIn('district_code', array_values($districtMap))
            ->get(['code', 'district_code', 'name']) as $village) {
            $villageMap[$village->district_code . '|' . $this->normalizeVillageName($village->name)] = $village->code;
        }

        foreach (DB::table('instansi')->select(['id', 'keterangan'])->get() as $instansi) {
            $location = $this->parseLegacyLocation($instansi->keterangan);

            if ($location === null) {
                continue;
            }

            $districtCode = $districtMap[$this->normalizeDistrictName($location['district'])] ?? null;
            $villageCode = $districtCode !== null
                ? ($villageMap[$districtCode . '|' . $this->normalizeVillageName($location['village'])] ?? null)
                : null;

            $updates = [
                'asal_wilayah' => 'bojonegoro',
                'province_code' => self::BOJONEGORO_PROVINCE_CODE,
                'regency_code' => self::BOJONEGORO_REGENCY_CODE,
                'district_code' => $districtCode,
                'village_code' => $villageCode,
            ];

            if ($districtCode !== null && $villageCode !== null) {
                $updates['keterangan'] = null;
            }

            DB::table('instansi')
                ->where('id', $instansi->id)
                ->update($updates);
        }
    }

    private function parseLegacyLocation(?string $note): ?array
    {
        if (! is_string($note) || trim($note) === '') {
            return null;
        }

        $matches = [];

        if (! preg_match('/Desa\/Kelurahan:\s*(.+?)\s*;\s*Kecamatan:\s*(.+)$/i', $note, $matches)) {
            return null;
        }

        return [
            'village' => $matches[1],
            'district' => $matches[2],
        ];
    }

    private function normalizeDistrictName(?string $value): string
    {
        $normalized = strtoupper(trim((string) $value));
        $normalized = preg_replace('/^(KEC(?:AMATAN)?\.?\s+)/', '', $normalized) ?? $normalized;

        return preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
    }

    private function normalizeVillageName(?string $value): string
    {
        $normalized = strtoupper(trim((string) $value));
        $normalized = preg_replace('/^(DESA|KELURAHAN|KEL\.?|DS\.?)(\s+)/', '', $normalized) ?? $normalized;

        return preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
    }
};