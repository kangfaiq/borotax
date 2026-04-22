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
}
