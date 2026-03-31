<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Domain\Shared\Models\ActivityLog;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        ActivityLog::log(
            action: 'CREATE_USER',
            targetTable: 'users',
            targetId: $this->record->id,
            description: "Membuat user: {$this->record->name} ({$this->record->role})"
        );
    }
}
