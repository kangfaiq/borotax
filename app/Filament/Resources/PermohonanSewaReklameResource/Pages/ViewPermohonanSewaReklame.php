<?php

namespace App\Filament\Resources\PermohonanSewaReklameResource\Pages;

use Filament\Actions\Action;
use App\Filament\Resources\PermohonanSewaReklameResource;
use Filament\Resources\Pages\Page;
use Filament\Actions;
use App\Filament\Pages\BuatSkpdReklame;

class ViewPermohonanSewaReklame extends Page
{
    protected static string $resource = PermohonanSewaReklameResource::class;

    protected string $view = 'filament.pages.view-permohonan-sewa-reklame';

    protected static ?string $title = 'Detail Permohonan Sewa Reklame';

    public $record;

    public function mount($record): void
    {
        $this->record = static::getResource()::getModel()::with('asetReklame')->findOrFail($record);
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Action::make('kembali')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(static::getResource()::getUrl('index')),
        ];

        if (in_array($this->record->status, ['diajukan', 'perlu_revisi', 'diproses'])) {
            $actions[] = Action::make('buat_skpd')
                ->label('Buat SKPD')
                ->icon('heroicon-o-document-check')
                ->color('success')
                ->url(BuatSkpdReklame::getUrl(['permohonan_id' => $this->record->id]));
        }

        return $actions;
    }
}
