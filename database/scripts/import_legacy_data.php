<?php

/**
 * Script: Import Legacy Data
 *
 * Digunakan untuk mengimport data pajak dari sistem/regulasi lama
 * ke dalam sistem baru dengan penandaan is_legacy = true.
 *
 * Usage:
 *   php artisan tinker database/scripts/import_legacy_data.php
 *
 * Atau modifikasi dan jalankan sebagai artisan command.
 */

use App\Domain\Tax\Models\Tax;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LegacyImporter
{
    protected int $imported = 0;
    protected int $skipped = 0;
    protected array $errors = [];

    /**
     * Import data dari CSV atau array.
     *
     * Format data:
     * [
     *     'jenis_pajak_kode' => '41108',
     *     'billing_code'     => 'OLD-123456',
     *     'amount'           => 150000,
     *     'paid_at'          => '2025-06-15',
     *     'dasar_hukum'      => 'Perda No. X Tahun 2020',
     *     ...
     * ]
     */
    public function importFromArray(array $records): void
    {
        DB::beginTransaction();

        try {
            foreach ($records as $index => $record) {
                try {
                    $this->importRecord($record, $index);
                    $this->imported++;
                } catch (\Exception $e) {
                    $this->skipped++;
                    $this->errors[] = "Row {$index}: {$e->getMessage()}";
                    Log::warning("Legacy import skipped row {$index}", [
                        'error' => $e->getMessage(),
                        'record' => $record,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $this->printSummary();
    }

    protected function importRecord(array $record, int $index): void
    {
        // Validate required fields
        $required = ['jenis_pajak_id', 'amount'];
        foreach ($required as $field) {
            if (empty($record[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Check for duplicates by legacy_billing_code
        if (!empty($record['billing_code'])) {
            $exists = Tax::where('legacy_billing_code', $record['billing_code'])->exists();
            if ($exists) {
                throw new \InvalidArgumentException("Duplicate legacy_billing_code: {$record['billing_code']}");
            }
        }

        Tax::create([
            'jenis_pajak_id'      => $record['jenis_pajak_id'],
            'sub_jenis_pajak_id'  => $record['sub_jenis_pajak_id'] ?? null,
            'tax_object_id'       => $record['tax_object_id'] ?? null,
            'user_id'             => $record['user_id'] ?? null,
            'amount'              => $record['amount'],
            'status'              => $record['status'] ?? 'paid',
            'billing_code'        => $record['billing_code'] ?? null,
            'paid_at'             => $record['paid_at'] ?? null,
            'dasar_hukum'         => $record['dasar_hukum'] ?? 'Regulasi Lama',
            'is_legacy'           => true,
            'legacy_billing_code' => $record['billing_code'] ?? null,
            'notes'               => $record['notes'] ?? 'Imported from legacy system',
        ]);
    }

    protected function printSummary(): void
    {
        echo "\n=== Legacy Import Summary ===\n";
        echo "Imported: {$this->imported}\n";
        echo "Skipped:  {$this->skipped}\n";

        if (!empty($this->errors)) {
            echo "\nErrors:\n";
            foreach ($this->errors as $error) {
                echo "  - {$error}\n";
            }
        }

        echo "=============================\n";
    }
}

// --- Contoh penggunaan ---
// $importer = new LegacyImporter();
// $importer->importFromArray([
//     [
//         'jenis_pajak_id' => 'uuid-of-air-tanah',
//         'amount'         => 150000,
//         'billing_code'   => 'OLD-ABT-2024-001',
//         'paid_at'        => '2024-06-15',
//         'dasar_hukum'    => 'Perda No. X Tahun 2020',
//     ],
// ]);
