<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataChangeRequestResource\Pages;
use App\Domain\Shared\Models\DataChangeRequest;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;
use App\Domain\Shared\Services\NotificationService;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

class DataChangeRequestResource extends Resource
{
    protected static ?string $model = DataChangeRequest::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Verifikasi';

    protected static ?string $navigationLabel = 'Perubahan Data';

    protected static ?string $modelLabel = 'Permintaan Perubahan';

    protected static ?string $pluralModelLabel = 'Permintaan Perubahan Data';

    protected static ?string $slug = 'verifikasi/perubahan-data';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::pending()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    /**
     * Label field yang ramah pengguna
     */
    private static function getFieldLabel(string $field): string
    {
        return match ($field) {
            'nik' => 'NIK',
            'nama_lengkap' => 'Nama Lengkap',
            'alamat' => 'Alamat',
            'tipe_wajib_pajak' => 'Tipe Wajib Pajak',
            'nama_perusahaan' => 'Nama Perusahaan',
            'nib' => 'NIB',
            'npwp_pusat' => 'NPWP Pusat',
            'asal_wilayah' => 'Asal Wilayah',
            'nama_objek_pajak' => 'Nama Objek Pajak',
            'alamat_objek' => 'Alamat Objek',
            'kelurahan' => 'Kelurahan',
            'kecamatan' => 'Kecamatan',
            'panjang' => 'Panjang (m)',
            'lebar' => 'Lebar (m)',
            'luas_m2' => 'Luas (m²)',
            'jumlah_muka' => 'Jumlah Muka',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'kelompok_lokasi' => 'Kelompok Lokasi',
            'tarif_persen' => 'Tarif (%)',
            default => str_replace('_', ' ', ucfirst($field)),
        };
    }

    /**
     * Render tabel perubahan field sebagai HTML
     */
    private static function renderFieldChangesTable(DataChangeRequest $record): HtmlString
    {
        $changes = $record->field_changes;
        if (!$changes) return new HtmlString('<p class="text-gray-500">Tidak ada perubahan</p>');

        $html = '<table class="w-full text-sm border-collapse">';
        $html .= '<thead><tr class="bg-gray-100 dark:bg-gray-700">';
        $html .= '<th class="border p-2 text-left">Field</th>';
        $html .= '<th class="border p-2 text-left">Nilai Lama</th>';
        $html .= '<th class="border p-2 text-left">Nilai Baru</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($changes as $field => $change) {
            $label = self::getFieldLabel($field);
            $old = e($change['old'] ?? '-');
            $new = e($change['new'] ?? '-');
            $html .= "<tr>";
            $html .= "<td class=\"border p-2 font-medium\">{$label}</td>";
            $html .= "<td class=\"border p-2 text-red-600 dark:text-red-400\">{$old}</td>";
            $html .= "<td class=\"border p-2 text-green-600 dark:text-green-400\">{$new}</td>";
            $html .= "</tr>";
        }

        $html .= '</tbody></table>';
        return new HtmlString($html);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Permintaan')
                    ->columnSpanFull()
                    ->schema([
                        Infolists\Components\TextEntry::make('entity_type')
                            ->label('Tipe Data')
                            ->formatStateUsing(fn($state) => match ($state) {
                                'wajib_pajak' => 'Wajib Pajak',
                                'tax_objects' => 'Objek Pajak',
                                default => $state,
                            })
                            ->badge()
                            ->color(fn($state) => match ($state) {
                                'wajib_pajak' => 'info',
                                'tax_objects' => 'warning',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('entity_id')
                            ->label('ID Entity'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'pending' => 'Menunggu Review',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Tanggal Pengajuan')
                            ->dateTime('d/m/Y H:i'),
                    ])->columns(4),

                Section::make('Detail Perubahan')
                    ->columnSpanFull()
                    ->schema([
                        Infolists\Components\ViewEntry::make('field_changes_table')
                            ->hiddenLabel()
                            ->view('filament.components.field-changes-table'),
                    ]),

                Section::make('Alasan & Dokumen')
                    ->schema([
                        Infolists\Components\TextEntry::make('alasan_perubahan')
                            ->label('Alasan Perubahan')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('dokumen_pendukung')
                            ->label('Dokumen Pendukung')
                            ->placeholder('Tidak ada')
                            ->url(fn($state) => $state ? '/storage/' . ltrim($state, '/') : null)
                            ->openUrlInNewTab(),
                    ])->columns(2),

                Section::make('Pengaju & Reviewer')
                    ->schema([
                        Infolists\Components\TextEntry::make('requester.nama_lengkap')
                            ->label('Diajukan Oleh')
                            ->default(fn($record) => $record->requester?->name ?? '-'),
                        Infolists\Components\TextEntry::make('reviewer.nama_lengkap')
                            ->label('Direview Oleh')
                            ->default(fn($record) => $record->reviewer?->name ?? '-')
                            ->placeholder('Belum direview'),
                        Infolists\Components\TextEntry::make('reviewed_at')
                            ->label('Tanggal Review')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Belum direview'),
                        Infolists\Components\TextEntry::make('catatan_review')
                            ->label('Catatan Review')
                            ->placeholder('Tidak ada catatan')
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Riwayat Verifikasi')
                    ->columnSpanFull()
                    ->schema([
                        Infolists\Components\ViewEntry::make('verification_status_history')
                            ->hiddenLabel()
                            ->view('filament.components.verification-status-history-entry'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Pengajuan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('entity_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'wajib_pajak' => 'info',
                        'tax_objects' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'wajib_pajak' => 'Wajib Pajak',
                        'tax_objects' => 'Objek Pajak',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Pengaju'),
                Tables\Columns\TextColumn::make('field_count')
                    ->label('Jumlah Field')
                    ->getStateUsing(fn($record) => count($record->field_changes ?? [])),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Tgl Review')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Review',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\SelectFilter::make('entity_type')
                    ->label('Tipe Data')
                    ->options([
                        'wajib_pajak' => 'Wajib Pajak',
                        'tax_objects' => 'Objek Pajak',
                    ]),
            
                TrashedFilter::make(),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(DataChangeRequest $record): bool =>
                        $record->isPending() && auth()->user()->can('review', $record)
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Perubahan Data')
                    ->modalDescription(fn(DataChangeRequest $record): string =>
                        "Anda akan menyetujui perubahan " . count($record->field_changes ?? []) . " field pada {$record->getEntityTypeLabel()}."
                    )
                    ->schema([
                        Forms\Components\Textarea::make('catatan_review')
                            ->label('Catatan (opsional)')
                            ->default('')
                            ->placeholder('Tambahkan catatan jika perlu...'),
                    ])
                    ->action(function (DataChangeRequest $record, array $data): void {
                        $result = $record->approve($data['catatan_review'] ?? null);
                        if ($result) {
                            // Notify WP: perubahan data disetujui
                            if ($record->requester) {
                                NotificationService::notifyUserBoth(
                                    $record->requester,
                                    'Perubahan Data Disetujui',
                                    'Permintaan perubahan data ' . $record->getEntityTypeLabel() . ' Anda telah disetujui dan data berhasil diperbarui.',
                                    'verification',
                                    actionUrl: route('portal.dashboard'),
                                );
                            }

                            Notification::make()
                                ->title('Perubahan Disetujui')
                                ->body('Data telah diperbarui sesuai permintaan.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal')
                                ->body('Entity tidak ditemukan atau status tidak valid.')
                                ->danger()
                                ->send();
                        }
                    }),
                \Filament\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(DataChangeRequest $record): bool =>
                        $record->isPending() && auth()->user()->can('review', $record)
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Perubahan Data')
                    ->schema([
                        Forms\Components\Textarea::make('catatan_review')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->default('')
                            ->placeholder('Jelaskan alasan penolakan...'),
                    ])
                    ->action(function (DataChangeRequest $record, array $data): void {
                        $record->reject($data['catatan_review']);

                        // Notify WP: perubahan data ditolak
                        if ($record->requester) {
                            NotificationService::notifyUserBoth(
                                $record->requester,
                                'Perubahan Data Ditolak',
                                'Permintaan perubahan data ' . $record->getEntityTypeLabel() . ' Anda ditolak. Alasan: ' . $data['catatan_review'],
                                'verification',
                                actionUrl: route('portal.dashboard'),
                            );
                        }

                        Notification::make()
                            ->title('Perubahan Ditolak')
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
            'index' => Pages\ListDataChangeRequests::route('/'),
            'view' => Pages\ViewDataChangeRequest::route('/{record}'),
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
