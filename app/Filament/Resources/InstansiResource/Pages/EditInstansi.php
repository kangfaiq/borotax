<?php

namespace App\Filament\Resources\InstansiResource\Pages;

use App\Domain\Shared\Models\ActivityLog;
use App\Filament\Resources\InstansiResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditInstansi extends EditRecord
{
    protected static string $resource = InstansiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        ActivityLog::log(
            action: 'UPDATE_INSTANSI',
            targetTable: 'instansi',
            targetId: $this->record->id,
            description: "Mengubah instansi: {$this->record->nama}"
        );
    }
}