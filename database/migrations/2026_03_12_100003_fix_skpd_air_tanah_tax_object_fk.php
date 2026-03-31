<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->dropForeignIfExists('skpd_air_tanah', 'skpd_air_tanah_water_object_id_foreign');
        $this->dropForeignIfExists('skpd_air_tanah', 'skpd_air_tanah_tax_object_id_foreign');

        if (! $this->foreignKeyExists('skpd_air_tanah', 'skpd_air_tanah_tax_object_id_foreign')) {
            Schema::table('skpd_air_tanah', function (Blueprint $table) {
                $table->foreign('tax_object_id')->references('id')->on('tax_objects');
            });
        }
    }

    public function down(): void
    {
        $this->dropForeignIfExists('skpd_air_tanah', 'skpd_air_tanah_tax_object_id_foreign');
        $this->dropForeignIfExists('skpd_air_tanah', 'skpd_air_tanah_water_object_id_foreign');

        if (! $this->foreignKeyExists('skpd_air_tanah', 'skpd_air_tanah_water_object_id_foreign')) {
            Schema::table('skpd_air_tanah', function (Blueprint $table) {
                $table->foreign('tax_object_id', 'skpd_air_tanah_water_object_id_foreign')
                    ->references('id')->on('water_objects');
            });
        }
    }

    private function dropForeignIfExists(string $table, string $constraint): void
    {
        if (! $this->foreignKeyExists($table, $constraint)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($constraint) {
            $blueprint->dropForeign($constraint);
        });
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};
