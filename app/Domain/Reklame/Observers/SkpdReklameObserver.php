<?php

namespace App\Domain\Reklame\Observers;

use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\SkpdReklame;

class SkpdReklameObserver
{
    public function updated(SkpdReklame $skpd): void
    {
        // Hanya proses jika status berubah dan terkait aset pemkab
        if (!$skpd->wasChanged('status') || !$skpd->aset_reklame_pemkab_id) {
            return;
        }

        $aset = AsetReklamePemkab::find($skpd->aset_reklame_pemkab_id);
        if (!$aset) {
            return;
        }

        // Sinkronisasi status ketersediaan aset
        $aset->syncKetersediaan();

        // Update permohonan sewa jika ada
        if ($skpd->permohonan_sewa_id && $skpd->status === 'disetujui') {
            $skpd->permohonanSewa()->update([
                'status'          => 'disetujui',
                'tanggal_selesai' => now(),
                'skpd_id'         => $skpd->id,
            ]);
        }

        if ($skpd->permohonan_sewa_id && $skpd->status === 'ditolak') {
            $skpd->permohonanSewa()->update([
                'status'         => 'ditolak',
                'catatan_petugas' => 'Ditolak Verifikator: ' . ($skpd->catatan_verifikasi ?? '-'),
            ]);
        }
    }
}
