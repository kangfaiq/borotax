<?php

namespace App\Filament\Resources\SubJenisPajakResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use App\Filament\Resources\SubJenisPajakResource;
use App\Domain\Shared\Models\ActivityLog;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubJenisPajak extends EditRecord
{
    protected static string $resource = SubJenisPajakResource::class;

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
            action: 'UPDATE_SUB_JENIS_PAJAK',
            targetTable: 'sub_jenis_pajak',
            targetId: $this->record->id,
            description: "Mengupdate sub jenis pajak: {$this->record->nama}"
        );
    }
}
