<?php

namespace App\Domain\HistoriPajak\Dto;

use App\Enums\JenisDokumenPajak;
use Carbon\CarbonInterface;

final readonly class DokumenPajakRow
{
    public function __construct(
        public JenisDokumenPajak $jenisDokumen,
        public string $jenisPajak,
        public ?string $nopd,
        public ?string $namaObjekPajak,
        public string $nomor,
        public string $masa,
        public ?CarbonInterface $tanggalTerbit,
        public ?CarbonInterface $jatuhTempo,
        public float $jumlahTagihan,
        public float $jumlahTerbayar,
        public ?CarbonInterface $tanggalBayar,
        public string $status,
        public string $statusLabel,
    ) {
    }

    public function jumlahSisa(): float
    {
        return max(0, $this->jumlahTagihan - $this->jumlahTerbayar);
    }

    public function hasOpenPayment(): bool
    {
        return $this->jumlahSisa() > 0;
    }

    public function isLewatJatuhTempo(): bool
    {
        if ($this->jatuhTempo === null) {
            return false;
        }

        if (! $this->hasOpenPayment()) {
            return false;
        }

        return $this->jatuhTempo->isPast();
    }

    public function effectiveStatus(): string
    {
        if ($this->isLewatJatuhTempo()) {
            return 'lewat_jatuh_tempo';
        }

        if ($this->jatuhTempo !== null && $this->hasOpenPayment() && ! $this->isStatusTetap()) {
            return 'menunggu_pembayaran';
        }

        return $this->status;
    }

    public function effectiveStatusLabel(): string
    {
        return match ($this->effectiveStatus()) {
            'lewat_jatuh_tempo' => 'Lewat Jatuh Tempo',
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            default => $this->statusLabel,
        };
    }

    private function isStatusTetap(): bool
    {
        return in_array($this->status, [
            'draft',
            'paid',
            'verified',
            'rejected',
            'cancelled',
            'ditolak',
            'menungguVerifikasi',
        ], true);
    }
}
