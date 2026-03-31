<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use App\Filament\Resources\UserResource;
use App\Domain\Shared\Models\ActivityLog;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

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
            action: 'UPDATE_USER',
            targetTable: 'users',
            targetId: $this->record->id,
            description: "Mengupdate user: {$this->record->name}"
        );
    }
}
