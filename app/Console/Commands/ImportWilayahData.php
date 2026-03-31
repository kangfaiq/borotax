<?php

namespace App\Console\Commands;

use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Region\Models\Village;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportWilayahData extends Command
{
    protected $signature = 'wilayah:import
        {--provinces : Import provinces only}
        {--regencies : Import regencies only}
        {--districts : Import districts only}
        {--villages : Import villages only}
        {--all : Import all levels}
        {--download : Download fresh data from cahyadsn/wilayah first}';

    protected $description = 'Import data wilayah seluruh Indonesia dari cahyadsn/wilayah (format kode dot-separated)';

    private string $sqlUrl = 'https://raw.githubusercontent.com/cahyadsn/wilayah/master/db/wilayah.sql';

    public function handle(): int
    {
        // Determine what to import
        $importAll = $this->option('all') || (!$this->option('provinces') && !$this->option('regencies') && !$this->option('districts') && !$this->option('villages'));

        // Optionally download fresh data
        if ($this->option('download') || !$this->jsonFilesExist()) {
            $this->downloadAndParseData();
        }

        if ($importAll || $this->option('provinces')) {
            $this->importProvinces();
        }

        if ($importAll || $this->option('regencies')) {
            $this->importRegencies();
        }

        if ($importAll || $this->option('districts')) {
            $this->importDistricts();
        }

        if ($importAll || $this->option('villages')) {
            $this->importVillages();
        }

        $this->info('Import selesai!');
        return 0;
    }

    private function jsonFilesExist(): bool
    {
        return file_exists(database_path('data/provinces.json'))
            && file_exists(database_path('data/regencies.json'))
            && file_exists(database_path('data/districts.json'))
            && file_exists(database_path('data/villages.json'));
    }

    private function downloadAndParseData(): void
    {
        $this->info('Mengunduh data dari cahyadsn/wilayah...');

        $response = Http::timeout(120)->get($this->sqlUrl);

        if (!$response->successful()) {
            $this->error('Gagal mengunduh data wilayah');
            return;
        }

        $sql = $response->body();
        $this->info('Downloaded ' . number_format(strlen($sql)) . ' bytes.');

        $provinces = [];
        $regencies = [];
        $districts = [];
        $villages = [];

        // Parse INSERT statements: ('kode','nama')
        preg_match_all("/\('([^']+)','([^']+)'\)/", $sql, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $kode = $match[1];
            $nama = $match[2];
            $dotCount = substr_count($kode, '.');

            match ($dotCount) {
                0 => $provinces[] = ['code' => $kode, 'name' => $nama],
                1 => $regencies[] = ['province_code' => explode('.', $kode)[0], 'code' => $kode, 'name' => $nama],
                2 => $districts[] = ['regency_code' => implode('.', array_slice(explode('.', $kode), 0, 2)), 'code' => $kode, 'name' => $nama],
                3 => $villages[] = ['district_code' => implode('.', array_slice(explode('.', $kode), 0, 3)), 'code' => $kode, 'name' => $nama],
                default => null,
            };
        }

        $dataDir = database_path('data');
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        file_put_contents($dataDir . '/provinces.json', json_encode($provinces, JSON_UNESCAPED_UNICODE));
        file_put_contents($dataDir . '/regencies.json', json_encode($regencies, JSON_UNESCAPED_UNICODE));
        file_put_contents($dataDir . '/districts.json', json_encode($districts, JSON_UNESCAPED_UNICODE));
        file_put_contents($dataDir . '/villages.json', json_encode($villages, JSON_UNESCAPED_UNICODE));

        $this->info("Parsed: " . count($provinces) . " provinces, " . count($regencies) . " regencies, "
            . count($districts) . " districts, " . count($villages) . " villages.");
    }

    private function importProvinces(): void
    {
        $data = json_decode(file_get_contents(database_path('data/provinces.json')), true);
        $bar = $this->output->createProgressBar(count($data));
        $bar->start();

        foreach ($data as $row) {
            Province::updateOrCreate(['code' => $row['code']], ['name' => $row['name']]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Imported " . count($data) . " provinces.");
    }

    private function importRegencies(): void
    {
        $data = json_decode(file_get_contents(database_path('data/regencies.json')), true);
        $bar = $this->output->createProgressBar(count($data));
        $bar->start();
        $batch = [];

        foreach ($data as $row) {
            $batch[] = [
                'code' => $row['code'],
                'province_code' => $row['province_code'],
                'name' => $row['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('regencies')->upsert($batch, ['code'], ['province_code', 'name', 'updated_at']);
                $batch = [];
            }
            $bar->advance();
        }

        if (!empty($batch)) {
            DB::table('regencies')->upsert($batch, ['code'], ['province_code', 'name', 'updated_at']);
        }

        $bar->finish();
        $this->newLine();
        $this->info("Imported " . count($data) . " regencies.");
    }

    private function importDistricts(): void
    {
        $data = json_decode(file_get_contents(database_path('data/districts.json')), true);
        $bar = $this->output->createProgressBar(count($data));
        $bar->start();
        $batch = [];
        $count = 0;

        foreach ($data as $row) {
            $batch[] = [
                'code' => $row['code'],
                'regency_code' => $row['regency_code'],
                'name' => $row['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $count++;

            if (count($batch) >= 500) {
                DB::table('districts')->upsert($batch, ['code'], ['regency_code', 'name', 'updated_at']);
                $batch = [];
            }
            $bar->advance();
        }

        if (!empty($batch)) {
            DB::table('districts')->upsert($batch, ['code'], ['regency_code', 'name', 'updated_at']);
        }

        $bar->finish();
        $this->newLine();
        $this->info("Imported {$count} districts.");
    }

    private function importVillages(): void
    {
        $data = json_decode(file_get_contents(database_path('data/villages.json')), true);
        $bar = $this->output->createProgressBar(count($data));
        $bar->start();
        $batch = [];
        $count = 0;

        foreach ($data as $row) {
            $batch[] = [
                'code' => $row['code'],
                'district_code' => $row['district_code'],
                'name' => $row['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $count++;

            if (count($batch) >= 500) {
                DB::table('villages')->upsert($batch, ['code'], ['district_code', 'name', 'updated_at']);
                $batch = [];
            }
            $bar->advance();
        }

        if (!empty($batch)) {
            DB::table('villages')->upsert($batch, ['code'], ['district_code', 'name', 'updated_at']);
        }

        $bar->finish();
        $this->newLine();
        $this->info("Imported {$count} villages.");
    }
}
