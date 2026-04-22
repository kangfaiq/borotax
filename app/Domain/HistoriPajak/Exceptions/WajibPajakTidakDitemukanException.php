<?php

namespace App\Domain\HistoriPajak\Exceptions;

use RuntimeException;

class WajibPajakTidakDitemukanException extends RuntimeException
{
    public function __construct(string $npwpd)
    {
        parent::__construct("Wajib Pajak dengan NPWPD {$npwpd} tidak ditemukan.");
    }
}
