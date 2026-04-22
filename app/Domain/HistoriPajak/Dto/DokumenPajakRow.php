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
        public string $status,
        public string $statusLabel,
    ) {
    }

    public function jumlahSisa(): float
    {
        return max(0, $this->jumlahTagihan - $this->jumlahTerbayar);
    }

    public function isLewatJatuhTempo(): bool
    {
        if ($this->jatuhTempo === null) {
            return false;
        }

        if ($this->jumlahSisa() <= 0) {
            return false;
        }

        return $this->jatuhTempo->isPast();
    }

    public function effectiveStatus(): string
    {
        return $this->isLewatJatuhTempo() ? 'lewat_jatuh_tempo' : $this->status;
    }

    public function effectiveStatusLabel(): string
    {
        return $this->isLewatJatuhTempo() ? 'Lewat Jatuh Tempo' : $this->statusLabel;
    }
}
