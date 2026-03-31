<?php

namespace App\Filament\Resources\PimpinanResource\Pages;

use App\Filament\Resources\PimpinanResource;
use App\Domain\Shared\Models\ActivityLog;
use Filament\Resources\Pages\CreateRecord;

class CreatePimpinan extends CreateRecord
{
    protected static string $resource = PimpinanResource::class;

    protected function afterCreate(): void
    {
        ActivityLog::log(
            action: 'CREATE_PIMPINAN',
            targetTable: 'pimpinan',
            targetId: $this->record->id,
            description: "Membuat pimpinan: {$this->record->nama} ({$this->record->jabatan})"
        );
    }
}
