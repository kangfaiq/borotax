<?php

namespace App\Console\Commands;

use App\Domain\Reklame\Models\AsetReklamePemkab;
use Illuminate\Console\Command;

class SyncKetersediaanAsetReklame extends Command
{
    protected $signature = 'reklame:sync-ketersediaan';

    protected $description = 'Sinkronisasi status ketersediaan aset reklame pemkab berdasarkan SKPD aktif';

    public function handle(): int
    {
        $asets = AsetReklamePemkab::where('is_active', true)
            ->where('status_ketersediaan', 'disewa')
            ->get();

        $updated = 0;

        foreach ($asets as $aset) {
            $statusBefore = $aset->status_ketersediaan;
            $aset->syncKetersediaan();

            if ($aset->fresh()->status_ketersediaan !== $statusBefore) {
                $updated++;
                $this->info("Aset {$aset->kode_aset}: {$statusBefore} → {$aset->fresh()->status_ketersediaan}");
            }
        }

        $this->info("Selesai. {$updated} aset diperbarui dari total {$asets->count()} aset disewa.");

        return self::SUCCESS;
    }
}
