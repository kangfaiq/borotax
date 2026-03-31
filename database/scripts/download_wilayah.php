<?php

/**
 * Download dan transformasi data wilayah dari cahyadsn/wilayah repository.
 *
 * Menghasilkan:
 * - database/data/districts.json   (kecamatan seluruh Indonesia)
 * - database/data/villages.json    (kelurahan/desa seluruh Indonesia)
 *
 * Format output sesuai format dot-separated cahyadsn/wilayah:
 * - Province:  11
 * - Regency:   11.01
 * - District:  11.01.01
 * - Village:   11.01.01.2001
 *
 * Usage: php database/scripts/download_wilayah.php
 */

$sqlUrl = 'https://raw.githubusercontent.com/cahyadsn/wilayah/master/db/wilayah.sql';

// Resolve paths relative to project root
$projectRoot = dirname(__DIR__, 2);
$dataDir = $projectRoot . '/database/data';

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

echo "Downloading wilayah.sql from cahyadsn/wilayah...\n";

$context = stream_context_create([
    'http' => [
        'timeout' => 120,
        'user_agent' => 'PHP/Borotax-Wilayah-Downloader',
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
]);

$sql = @file_get_contents($sqlUrl, false, $context);

if ($sql === false) {
    echo "ERROR: Gagal mengunduh file SQL. Pastikan koneksi internet tersedia.\n";
    exit(1);
}

echo "Downloaded " . number_format(strlen($sql)) . " bytes.\n";
echo "Parsing SQL data...\n";

$districts = [];
$villages = [];
$provinces = [];
$regencies = [];

// Parse INSERT statements
// Format: ('kode','nama'),
$pattern = "/\('([^']+)','([^']+)'\)/";

if (preg_match_all($pattern, $sql, $matches, PREG_SET_ORDER)) {
    echo "Found " . number_format(count($matches)) . " records.\n";

    foreach ($matches as $match) {
        $kode = $match[1];
        $nama = $match[2];

        // Count dots to determine level
        $dotCount = substr_count($kode, '.');

        switch ($dotCount) {
            case 0:
                // Province: "11"
                $provinces[] = ['code' => $kode, 'name' => $nama];
                break;

            case 1:
                // Regency: "11.01"
                $provinceCode = explode('.', $kode)[0];
                $regencies[] = [
                    'province_code' => $provinceCode,
                    'code' => $kode,
                    'name' => $nama,
                ];
                break;

            case 2:
                // District: "11.01.01"
                $parts = explode('.', $kode);
                $regencyCode = $parts[0] . '.' . $parts[1];
                $districts[] = [
                    'regency_code' => $regencyCode,
                    'code' => $kode,
                    'name' => $nama,
                ];
                break;

            case 3:
                // Village: "11.01.01.2001"
                $parts = explode('.', $kode);
                $districtCode = $parts[0] . '.' . $parts[1] . '.' . $parts[2];
                $villages[] = [
                    'district_code' => $districtCode,
                    'code' => $kode,
                    'name' => $nama,
                ];
                break;
        }
    }
}

echo "\nParsed data:\n";
echo "  Provinces:  " . number_format(count($provinces)) . "\n";
echo "  Regencies:  " . number_format(count($regencies)) . "\n";
echo "  Districts:  " . number_format(count($districts)) . "\n";
echo "  Villages:   " . number_format(count($villages)) . "\n";

// Save districts.json
$districtsFile = $dataDir . '/districts.json';
file_put_contents($districtsFile, json_encode($districts, JSON_UNESCAPED_UNICODE));
echo "\nSaved districts to: {$districtsFile}\n";

// Save villages.json
$villagesFile = $dataDir . '/villages.json';
file_put_contents($villagesFile, json_encode($villages, JSON_UNESCAPED_UNICODE));
echo "Saved villages to: {$villagesFile}\n";

// Also save provinces and regencies for reference
$provincesFile = $dataDir . '/provinces.json';
file_put_contents($provincesFile, json_encode($provinces, JSON_UNESCAPED_UNICODE));
echo "Saved provinces to: {$provincesFile}\n";

$regenciesFile = $dataDir . '/regencies.json';
file_put_contents($regenciesFile, json_encode($regencies, JSON_UNESCAPED_UNICODE));
echo "Saved regencies to: {$regenciesFile}\n";

echo "\nDone! Data wilayah berhasil diunduh dan disimpan.\n";
