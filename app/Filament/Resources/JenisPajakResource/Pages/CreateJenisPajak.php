<?php

namespace App\Filament\Resources\JenisPajakResource\Pages;

use App\Filament\Resources\JenisPajakResource;
use App\Domain\Shared\Models\ActivityLog;
use Filament\Resources\Pages\CreateRecord;

class CreateJenisPajak extends CreateRecord
{
    protected static string $resource = JenisPajakResource::class;

    protected function afterCreate(): void
    {
        ActivityLog::log(
            action: 'CREATE_JENIS_PAJAK',
            targetTable: 'jenis_pajak',
            targetId: $this->record->id,
            description: "Membuat jenis pajak: {$this->record->nama}"
        );
    }
}
