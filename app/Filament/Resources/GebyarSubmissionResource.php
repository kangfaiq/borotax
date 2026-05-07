<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use App\Filament\Resources\GebyarSubmissionResource\Pages\ListGebyarSubmissions;
use App\Filament\Resources\GebyarSubmissionResource\Pages;
use App\Domain\Gebyar\Models\GebyarSubmission;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use App\Domain\Shared\Services\NotificationService;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

class GebyarSubmissionResource extends Resource
{
    protected static ?string $model = GebyarSubmission::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    protected static string | \UnitEnum | null $navigationGroup = 'Program';

    protected static ?string $navigationLabel = 'Gebyar Sadar Pajak';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Submission')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('user_name')
                            ->label('Nama Pengirim')
                            ->disabled(),
                        TextInput::make('user_nik')
                            ->label('NIK')
                            ->disabled(),
                        TextInput::make('period_year')
                            ->label('Periode Tahunan')
                            ->disabled(),
                        TextInput::make('status')
                            ->label('Status')
                            ->disabled(),
                    ])->columns(2),

                Section::make('Detail Transaksi')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('place_name')
                            ->label('Nama Tempat')
                            ->disabled(),
                        // Jenis Pajak via relation (tidak terenkripsi)
                        TextInput::make('jenisPajak.nama')
                            ->label('Jenis Pajak')
                            ->disabled(),
                        DatePicker::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->disabled(),
                        TextInput::make('transaction_amount')
                            ->label('Nominal (Rp)')
                            ->prefix('Rp')
                            ->disabled(),

                        // Image Preview
                        Placeholder::make('image_preview')
                            ->label('Foto Struk / Nota')
                            ->content(fn($record) => $record?->image_url
                                ? view('filament.components.image-preview', ['url' => $record->image_url])
                                : 'Tidak ada foto'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu Submit')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('user_name')
                    ->label('Nama Pengirim')
                    ->searchable(),
                TextColumn::make('place_name')
                    ->label('Tempat')
                    ->searchable(),
                TextColumn::make('transaction_amount')
                    ->label('Nominal')
                    ->money('IDR'),
                ImageColumn::make('image_url')
                    ->label('Foto Struk')
                    ->disk('public')
                    ->visibility('public')
                    ->checkFileExistence(false) // Disable existence check for encrypted paths logic if complex
                    ->extraImgAttributes(['class' => 'object-cover w-16 h-16 rounded-lg']), // Thumbnail
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Verifikasi',
                        'approved' => 'Sah (Valid)',
                        'rejected' => 'Ditolak (Invalid)',
                    ]),
            
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Detail Pengajuan Gebyar')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('4xl')
                    ->visible(fn(GebyarSubmission $record) => auth()->user()?->can('view', $record) ?? false)
                    ->modalContent(fn(GebyarSubmission $record) => view('filament.components.gebyar-submission-detail', [
                        'record' => $record->loadMissing(['user', 'jenisPajak', 'verificationStatusHistories.actor']),
                    ])),
                Action::make('verify')
                    ->label('Sah')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn(GebyarSubmission $record) => $record->status === 'pending' && auth()->user()->can('update', $record))
                    ->requiresConfirmation()
                    ->action(function (GebyarSubmission $record) {
                        $record->update([
                            'status' => 'approved',
                            'verified_at' => now(),
                            // 'verified_by' => auth()->id(), // Kolom tidak ada di schema
                        ]);

                        // Tambah kupon ke user
                        $record->user->increment('total_kupon_undian', $record->kupon_count);

                        // Notify WP: pengajuan disetujui
                        if ($record->user) {
                            NotificationService::notifyUserBoth(
                                $record->user,
                                'Pengajuan Gebyar Disetujui',
                                'Pengajuan gebyar sadar pajak Anda telah diverifikasi dan disetujui. Kupon undian berhasil ditambahkan.',
                                'verification',
                                actionUrl: route('portal.dashboard'),
                            );
                        }

                        Notification::make()
                            ->title('Submission Valid')
                            ->body('Kupon berhasil ditambahkan ke user.')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn(GebyarSubmission $record) => $record->status === 'pending' && auth()->user()->can('update', $record))
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (GebyarSubmission $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'verified_at' => now(),
                        ]);

                        // Notify WP: pengajuan ditolak
                        if ($record->user) {
                            NotificationService::notifyUserBoth(
                                $record->user,
                                'Pengajuan Gebyar Ditolak',
                                'Pengajuan gebyar sadar pajak Anda ditolak. Alasan: ' . $data['rejection_reason'],
                                'verification',
                                actionUrl: route('portal.dashboard'),
                            );
                        }

                        Notification::make()
                            ->title('Submission Ditolak')
                            ->danger()
                            ->send();
                    }),
            ])
            ->toolbarActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGebyarSubmissions::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
