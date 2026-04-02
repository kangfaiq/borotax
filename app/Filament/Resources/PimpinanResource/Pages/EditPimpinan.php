<?php

namespace App\Filament\Resources\PimpinanResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use App\Filament\Resources\PimpinanResource;
use App\Domain\Shared\Models\ActivityLog;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPimpinan extends EditRecord
{
    protected static string $resource = PimpinanResource::class;

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
            action: 'UPDATE_PIMPINAN',
            targetTable: 'pimpinan',
            targetId: $this->record->id,
            description: "Mengubah pimpinan: {$this->record->nama} ({$this->record->jabatan})"
        );
    }
}
