<?php

namespace App\Filament\Resources\SubJenisPajakResource\Pages;

use App\Filament\Resources\SubJenisPajakResource;
use App\Domain\Shared\Models\ActivityLog;
use Filament\Resources\Pages\CreateRecord;

class CreateSubJenisPajak extends CreateRecord
{
    protected static string $resource = SubJenisPajakResource::class;

    protected function afterCreate(): void
    {
        ActivityLog::log(
            action: 'CREATE_SUB_JENIS_PAJAK',
            targetTable: 'sub_jenis_pajak',
            targetId: $this->record->id,
            description: "Membuat sub jenis pajak: {$this->record->nama}"
        );
    }
}
