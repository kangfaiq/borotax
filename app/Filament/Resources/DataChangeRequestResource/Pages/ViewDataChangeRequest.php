<?php

namespace App\Filament\Resources\DataChangeRequestResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use App\Filament\Resources\DataChangeRequestResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Forms;

class ViewDataChangeRequest extends ViewRecord
{
    protected static string $resource = DataChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Setujui Perubahan')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn(): bool =>
                    $this->record->isPending() && auth()->user()->can('review', $this->record)
                )
                ->requiresConfirmation()
                ->modalHeading('Setujui Perubahan Data')
                ->schema([
                    Textarea::make('catatan_review')
                        ->label('Catatan (opsional)')
                        ->placeholder('Tambahkan catatan jika perlu...'),
                ])
                ->action(function (array $data): void {
                    $result = $this->record->approve($data['catatan_review'] ?? null);
                    if ($result) {
                        Notification::make()
                            ->title('Perubahan Disetujui')
                            ->body('Data telah diperbarui sesuai permintaan.')
                            ->success()
                            ->send();
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    } else {
                        Notification::make()->title('Gagal')->danger()->send();
                    }
                }),

            Action::make('reject')
                ->label('Tolak Perubahan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn(): bool =>
                    $this->record->isPending() && auth()->user()->can('review', $this->record)
                )
                ->requiresConfirmation()
                ->modalHeading('Tolak Perubahan Data')
                ->schema([
                    Textarea::make('catatan_review')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->placeholder('Jelaskan alasan penolakan...'),
                ])
                ->action(function (array $data): void {
                    $this->record->reject($data['catatan_review']);
                    Notification::make()
                        ->title('Perubahan Ditolak')
                        ->warning()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}
