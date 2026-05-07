<?php

namespace App\Filament\Resources;

use App\Domain\Reklame\Models\ReklameRequest;
use App\Domain\Shared\Services\NotificationService;
use App\Filament\Pages\BuatSkpdReklame;
use App\Filament\Resources\ReklameRequestResource\Pages;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReklameRequestResource extends Resource
{
    protected static ?string $model = ReklameRequest::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';

    protected static string | \UnitEnum | null $navigationGroup = 'Reklame';

    protected static ?string $navigationLabel = 'Pengajuan Reklame Portal';

    protected static ?string $modelLabel = 'Pengajuan Reklame Portal';

    protected static ?string $pluralModelLabel = 'Pengajuan Reklame Portal';

    protected static ?int $navigationSort = 6;

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['admin', 'petugas']) ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereIn('status', ['diajukan', 'menungguVerifikasi', 'diproses'])->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal_pengajuan', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reklameObject.nama_objek_pajak')
                    ->label('Objek Reklame')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('reklameObject.npwpd')
                    ->label('NPWPD')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('user_name')
                    ->label('Pemohon')
                    ->formatStateUsing(fn (?string $state): string => $state ?: '-')
                    ->wrap(),
                Tables\Columns\TextColumn::make('durasi_perpanjangan_hari')
                    ->label('Durasi')
                    ->formatStateUsing(fn (?int $state): string => $state ? $state . ' hari' : '-'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'diajukan' => 'warning',
                        'menungguVerifikasi' => 'info',
                        'diproses' => 'primary',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'diajukan' => 'Diajukan',
                        'menungguVerifikasi' => 'Menunggu Verifikasi',
                        'diproses' => 'Diproses',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('petugas_nama')
                    ->label('Petugas')
                    ->placeholder('-')
                    ->wrap(),
                Tables\Columns\TextColumn::make('tanggal_diproses')
                    ->label('Diproses')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'diajukan' => 'Diajukan',
                        'menungguVerifikasi' => 'Menunggu Verifikasi',
                        'diproses' => 'Diproses',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Detail Pengajuan Reklame')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('4xl')
                    ->visible(fn (ReklameRequest $record): bool => auth()->user()?->can('view', $record) ?? false)
                    ->modalContent(fn (ReklameRequest $record) => view('filament.components.reklame-request-detail', [
                        'record' => $record->loadMissing(['reklameObject', 'user', 'verificationStatusHistories.actor']),
                    ])),
                Action::make('proses')
                    ->label('Proses')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn (ReklameRequest $record): bool => in_array($record->status, ['diajukan', 'menungguVerifikasi']) && (auth()->user()?->can('update', $record) ?? false))
                    ->requiresConfirmation()
                    ->modalHeading('Proses Pengajuan Reklame?')
                    ->modalDescription('Pengajuan akan ditandai sedang diproses oleh Anda.')
                    ->action(function (ReklameRequest $record): void {
                        $record->update([
                            'status' => 'diproses',
                            'tanggal_diproses' => now(),
                            'petugas_id' => auth()->id(),
                            'petugas_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                        ]);

                        if ($record->user) {
                            NotificationService::notifyUserBoth(
                                $record->user,
                                'Pengajuan Reklame Sedang Diproses',
                                'Pengajuan reklame Anda sedang diproses oleh petugas.',
                                'info',
                                actionUrl: route('portal.reklame.index'),
                            );
                        }

                        Notification::make()
                            ->title('Pengajuan reklame ditandai sedang diproses')
                            ->success()
                            ->send();
                    }),
                Action::make('buat_skpd')
                    ->label('Buat SKPD')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn (ReklameRequest $record): bool => in_array($record->status, ['diajukan', 'menungguVerifikasi', 'diproses']) && (auth()->user()?->can('update', $record) ?? false))
                    ->url(fn (ReklameRequest $record): string => BuatSkpdReklame::getUrl() . '?request_id=' . $record->id),
                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ReklameRequest $record): bool => in_array($record->status, ['diajukan', 'menungguVerifikasi', 'diproses']) && (auth()->user()?->can('update', $record) ?? false))
                    ->schema([
                        Textarea::make('catatan_petugas')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pengajuan Reklame?')
                    ->action(function (ReklameRequest $record, array $data): void {
                        $record->update([
                            'status' => 'ditolak',
                            'tanggal_selesai' => now(),
                            'catatan_petugas' => $data['catatan_petugas'],
                            'petugas_id' => auth()->id(),
                            'petugas_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                        ]);

                        if ($record->user) {
                            NotificationService::notifyUserBoth(
                                $record->user,
                                'Pengajuan Reklame Ditolak',
                                'Pengajuan reklame Anda ditolak. Alasan: ' . $data['catatan_petugas'],
                                'verification',
                                actionUrl: route('portal.reklame.index'),
                            );
                        }

                        Notification::make()
                            ->title('Pengajuan reklame ditolak')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReklameRequests::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['reklameObject', 'user'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}