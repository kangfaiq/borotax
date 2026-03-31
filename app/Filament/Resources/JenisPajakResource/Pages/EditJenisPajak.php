<?php

namespace App\Filament\Resources\JenisPajakResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use App\Filament\Resources\JenisPajakResource;
use App\Domain\Shared\Models\ActivityLog;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJenisPajak extends EditRecord
{
    protected static string $resource = JenisPajakResource::class;

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
            action: 'UPDATE_JENIS_PAJAK',
            targetTable: 'jenis_pajak',
            targetId: $this->record->id,
            description: "Mengupdate jenis pajak: {$this->record->nama}"
        );
    }
}
