<?php

namespace App\Filament\Resources\InstansiResource\Pages;

use App\Domain\Shared\Models\ActivityLog;
use App\Filament\Resources\InstansiResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInstansi extends CreateRecord
{
    protected static string $resource = InstansiResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return InstansiResource::mutateRegionFormData($data);
    }

    protected function afterCreate(): void
    {
        ActivityLog::log(
            action: 'CREATE_INSTANSI',
            targetTable: 'instansi',
            targetId: $this->record->id,
            description: "Membuat instansi: {$this->record->nama}"
        );
    }
}