<?php

namespace App\Filament\Resources;

use App\Domain\Shared\Services\NotificationService;
use App\Domain\Tax\Models\PortalMblbSubmission;
use App\Domain\Tax\Services\PortalMblbSubmissionService;
use App\Filament\Resources\PortalMblbSubmissionResource\Pages;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

class PortalMblbSubmissionResource extends Resource
{
    protected static ?string $model = PortalMblbSubmission::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Verifikasi';

    protected static ?string $navigationLabel = 'Pengajuan MBLB Portal';

    protected static ?string $modelLabel = 'Pengajuan MBLB Portal';

    protected static ?string $pluralModelLabel = 'Pengajuan MBLB Portal';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.nama_lengkap')
                    ->label('Wajib Pajak')
                    ->searchable()
                    ->description(fn(PortalMblbSubmission $record) => $record->user?->nik ?? ''),
                Tables\Columns\TextColumn::make('taxObject.nama_objek_pajak')
                    ->label('Objek Pajak')
                    ->searchable()
                    ->description(fn(PortalMblbSubmission $record) => $record->taxObject?->npwpd ?? ''),
                Tables\Columns\TextColumn::make('instansi_nama')
                    ->label('Instansi')
                    ->placeholder('-')
                    ->searchable()
                    ->description(fn(PortalMblbSubmission $record) => $record->instansi_kategori?->getLabel() ?? null)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('masa_pajak_label')
                    ->label('Masa Pajak'),
                Tables\Columns\TextColumn::make('total_tagihan')
                    ->label('Estimasi Tagihan')
                    ->money('IDR', 0)
                    ->state(fn(PortalMblbSubmission $record) => $record->total_tagihan),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu Verifikasi',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('approvedTax.billing_code')
                    ->label('Kode Pembayaran Aktif')
                    ->fontFamily('mono')
                    ->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Verifikasi',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
            
                TrashedFilter::make(),
            ])
            ->actions([
                \Filament\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Detail Pengajuan MBLB Portal')
                    ->modalContent(fn(PortalMblbSubmission $record) => view('filament.components.portal-mblb-submission-detail', [
                        'record' => $record,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                \Filament\Actions\Action::make('approve')
                    ->label('Setujui & Terbitkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(PortalMblbSubmission $record) => $record->status === 'pending' && auth()->user()->can('review', $record))
                    ->schema([
                        Forms\Components\Textarea::make('review_notes')
                            ->label('Catatan Verifikasi')
                            ->placeholder('Opsional')
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Terbitkan Billing MBLB?')
                    ->modalDescription('Kode Pembayaran Aktif akan dibuat setelah pengajuan ini disetujui.')
                    ->action(function (PortalMblbSubmission $record, array $data): void {
                        try {
                            $tax = app(PortalMblbSubmissionService::class)->approveSubmission(
                                $record,
                                auth()->user(),
                                $data['review_notes'] ?? null,
                            );

                            if ($record->user) {
                                NotificationService::notifyUserBoth(
                                    $record->user,
                                    'Billing MBLB Telah Diterbitkan',
                                    'Pengajuan billing MBLB Anda disetujui. Kode Pembayaran Aktif: ' . $tax->billing_code,
                                    'payment',
                                    actionUrl: route('portal.history'),
                                );
                            }

                            Notification::make()
                                ->title('Billing berhasil diterbitkan')
                                ->body('Kode Pembayaran Aktif: ' . $tax->billing_code)
                                ->success()
                                ->send();
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title('Pengajuan tidak dapat disetujui')
                                ->body(collect($exception->errors())->flatten()->first())
                                ->danger()
                                ->send();
                        }
                    }),
                \Filament\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(PortalMblbSubmission $record) => $record->status === 'pending' && auth()->user()->can('review', $record))
                    ->schema([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pengajuan Billing MBLB')
                    ->action(function (PortalMblbSubmission $record, array $data): void {
                        app(PortalMblbSubmissionService::class)->rejectSubmission(
                            $record,
                            auth()->user(),
                            $data['rejection_reason'],
                        );

                        if ($record->user) {
                            NotificationService::notifyUserBoth(
                                $record->user,
                                'Pengajuan Billing MBLB Ditolak',
                                'Pengajuan billing MBLB Anda ditolak. Alasan: ' . $data['rejection_reason'],
                                'verification',
                                actionUrl: route('portal.history'),
                            );
                        }

                        Notification::make()
                            ->title('Pengajuan ditolak')
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortalMblbSubmissions::route('/'),
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