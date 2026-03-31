<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Filament\Resources\WajibPajakResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PengajuanTerbaru extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Pengajuan Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                WajibPajak::query()
                    ->where('status', 'menungguVerifikasi')
                    ->latest('tanggal_daftar')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('tanggal_daftar')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('tipe_wajib_pajak')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'perorangan' ? 'info' : 'warning')
                    ->formatStateUsing(fn(string $state): string => $state === 'perorangan' ? 'P1' : 'P2'),
                TextColumn::make('status')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn() => 'Menunggu'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn(WajibPajak $record): string => WajibPajakResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('Tidak ada pengajuan')
            ->emptyStateDescription('Semua pengajuan sudah diproses')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
