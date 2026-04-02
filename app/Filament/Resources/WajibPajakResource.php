<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Filament\Resources\WajibPajakResource\Pages\ListWajibPajaks;
use App\Filament\Resources\WajibPajakResource\Pages\ViewWajibPajak;
use App\Filament\Resources\WajibPajakResource\Pages\EditWajibPajak;
use App\Filament\Resources\WajibPajakResource\Pages;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Shared\Models\ActivityLog;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WajibPajakResource extends Resource
{
    protected static ?string $model = WajibPajak::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static string | \UnitEnum | null $navigationGroup = 'Verifikasi';

    protected static ?string $navigationLabel = 'Wajib Pajak';

    protected static ?string $modelLabel = 'Wajib Pajak';

    protected static ?string $pluralModelLabel = 'Wajib Pajak';

    protected static ?int $navigationSort = 1;

    public static function canEditApprovedRecord(WajibPajak $record): bool
    {
        return $record->status === 'disetujui' && (auth()->user()?->can('update', $record) ?? false);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'menungguVerifikasi')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Wajib Pajak')
                    ->columnSpanFull()
                    ->description('Data dienkripsi saat disimpan')
                    ->schema([
                        TextInput::make('nik')
                            ->label('NIK')
                            ->disabled(fn (string $operation): bool => $operation === 'view')
                            ->inputMode('numeric')
                            ->maxLength(16)
                            ->minLength(16)
                            ->regex('/^[0-9]{16}$/'),
                        TextInput::make('nama_lengkap')
                            ->disabled(fn (string $operation): bool => $operation === 'view')
                            ->maxLength(255),
                        Textarea::make('alamat')
                            ->disabled(fn (string $operation): bool => $operation === 'view')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Kontak')
                    ->columnSpanFull()
                    ->description('Data kontak dari akun pengguna')
                    ->schema([
                        Placeholder::make('wa_display')
                            ->label('No. WhatsApp')
                            ->content(fn ($record) => $record?->user?->no_whatsapp ?: '-'),
                        Placeholder::make('telp_display')
                            ->label('No. Telepon')
                            ->content(fn ($record) => $record?->user?->no_telp ?: '-'),
                        Placeholder::make('email_display')
                            ->label('Email')
                            ->content(fn ($record) => $record?->user?->email ?: '-'),
                    ])->columns(3),

                Section::make('Asal Wilayah')
                    ->columnSpanFull()
                    ->schema([
                        Placeholder::make('asal_wilayah_display')
                            ->label('Asal Wilayah')
                            ->content(fn($record) => $record?->asal_wilayah === 'luar_bojonegoro'
                                ? 'Luar Kabupaten Bojonegoro'
                                : 'Kabupaten Bojonegoro'),
                        Placeholder::make('provinsi_display')
                            ->label('Provinsi')
                            ->content(fn($record) => $record?->province?->name ?? '-')
                            ->visible(fn($record) => $record?->asal_wilayah === 'luar_bojonegoro'),
                        Placeholder::make('kabupaten_display')
                            ->label('Kabupaten / Kota')
                            ->content(fn($record) => $record?->regency?->name ?? '-')
                            ->visible(fn($record) => $record?->asal_wilayah === 'luar_bojonegoro'),
                        Placeholder::make('kecamatan_display')
                            ->label('Kecamatan')
                            ->content(fn($record) => $record?->district?->name ?? '-'),
                        Placeholder::make('kelurahan_display')
                            ->label('Kelurahan / Desa')
                            ->content(fn($record) => $record?->village?->name ?? '-'),
                    ])->columns(2),

                Section::make('Tipe Wajib Pajak')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('tipe_wajib_pajak')
                            ->label('Tipe')
                            ->options([
                                'perorangan' => 'Perorangan',
                                'perusahaan' => 'Perusahaan',
                            ])
                            ->disabled(fn (string $operation): bool => $operation === 'view'),
                        TextInput::make('nama_perusahaan')
                            ->visible(fn($record) => $record?->tipe_wajib_pajak === 'perusahaan')
                            ->disabled(fn (string $operation): bool => $operation === 'view'),
                        TextInput::make('nib')
                            ->label('NIB')
                            ->visible(fn($record) => $record?->tipe_wajib_pajak === 'perusahaan')
                            ->disabled(fn (string $operation): bool => $operation === 'view'),
                        TextInput::make('npwp_pusat')
                            ->label('NPWP Pusat')
                            ->visible(fn($record) => $record?->tipe_wajib_pajak === 'perusahaan')
                            ->disabled(fn (string $operation): bool => $operation === 'view'),
                    ])->columns(2),

                Section::make('Dokumen')
                    ->columnSpanFull()
                    ->schema([
                        Placeholder::make('ktp_preview')
                            ->label('Foto KTP')
                            ->content(fn($record) => $record?->ktp_image_path
                                ? view('filament.components.image-preview', ['url' => $record->ktp_image_path])
                                : 'Tidak ada'),
                        Placeholder::make('selfie_preview')
                            ->label('Foto Selfie')
                            ->content(fn($record) => $record?->selfie_image_path
                                ? view('filament.components.image-preview', ['url' => $record->selfie_image_path])
                                : 'Tidak ada'),
                    ])->columns(2),

                Section::make('Status & Verifikasi')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('status')
                            ->options([
                                'menungguVerifikasi' => 'Menunggu Verifikasi',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                                'perluPerbaikan' => 'Perlu Perbaikan',
                            ])
                            ->disabled(),
                        TextInput::make('npwpd')
                            ->label('NPWPD')
                            ->disabled(),
                        DateTimePicker::make('tanggal_daftar')
                            ->disabled(),
                        DateTimePicker::make('tanggal_verifikasi')
                            ->disabled(),
                        TextInput::make('petugas_nama')
                            ->label('Verifikator')
                            ->disabled(),
                        Textarea::make('catatan_verifikasi')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_daftar')
                    ->label('Tgl Daftar')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('nama_lengkap')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $matchingIds = WajibPajak::all(['id', 'nama_lengkap'])
                            ->filter(fn ($wp) => str_contains(strtolower($wp->nama_lengkap), strtolower($search)))
                            ->pluck('id');

                        return $query->whereIn('id', $matchingIds);
                    })
                    ->wrap(),
                TextColumn::make('tipe_wajib_pajak')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'perorangan' ? 'info' : 'warning')
                    ->formatStateUsing(fn(string $state): string => $state === 'perorangan' ? 'P1' : 'P2'),
                TextColumn::make('npwpd')
                    ->label('NPWPD')
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'menungguVerifikasi' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        'perluPerbaikan' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'menungguVerifikasi' => 'Menunggu',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'perluPerbaikan' => 'Perbaiki',
                        default => $state,
                    }),
                TextColumn::make('petugas_nama')
                    ->label('Verifikator')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_daftar', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'menungguVerifikasi' => 'Menunggu Verifikasi',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'perluPerbaikan' => 'Perlu Perbaikan',
                    ]),
                SelectFilter::make('tipe_wajib_pajak')
                    ->label('Tipe')
                    ->options([
                        'perorangan' => 'Perorangan (P1)',
                        'perusahaan' => 'Perusahaan (P2)',
                    ]),
            
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn(WajibPajak $record): bool => static::canEditApprovedRecord($record)),
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(WajibPajak $record): bool => $record->status === 'menungguVerifikasi' && auth()->user()->can('verify', $record))
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pendaftaran Wajib Pajak')
                    ->modalDescription('Anda akan menyetujui pendaftaran ini dan generate NPWPD.')
                    ->action(function (WajibPajak $record): void {
                        $npwpd = WajibPajak::generateNpwpd($record->tipe_wajib_pajak ?? 'perorangan');

                        $record->update([
                            'status' => 'disetujui',
                            'npwpd' => $npwpd,
                            'tanggal_verifikasi' => now(),
                            'petugas_id' => auth()->id(),
                            'petugas_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                        ]);

                        ActivityLog::log(
                            action: 'APPROVE_WAJIB_PAJAK',
                            targetTable: 'wajib_pajak',
                            targetId: $record->id,
                            description: "Menyetujui WP: {$record->nama_lengkap}, NPWPD: {$npwpd}"
                        );

                        Notification::make()
                            ->title('Berhasil Disetujui')
                            ->body("NPWPD: {$npwpd}")
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(WajibPajak $record): bool => $record->status === 'menungguVerifikasi' && auth()->user()->can('verify', $record))
                    ->modalHeading('Tolak Pendaftaran Wajib Pajak')
                    ->schema([
                        Textarea::make('catatan_verifikasi')
                            ->label('Alasan Penolakan')
                            ->default('')
                            ->placeholder('Jelaskan alasan penolakan...'),
                    ])
                    ->action(function (WajibPajak $record, array $data): void {
                        validator($data, [
                            'catatan_verifikasi' => ['required', 'string'],
                        ])->validate();

                        $record->update([
                            'status' => 'ditolak',
                            'tanggal_verifikasi' => now(),
                            'petugas_id' => auth()->id(),
                            'petugas_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                            'catatan_verifikasi' => $data['catatan_verifikasi'],
                        ]);

                        ActivityLog::log(
                            action: 'REJECT_WAJIB_PAJAK',
                            targetTable: 'wajib_pajak',
                            targetId: $record->id,
                            description: "Menolak WP: {$record->nama_lengkap}. Alasan: {$data['catatan_verifikasi']}"
                        );

                        Notification::make()
                            ->title('Pendaftaran Ditolak')
                            ->warning()
                            ->send();
                    }),
                Action::make('requestRevision')
                    ->label('Perlu Perbaikan')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->visible(fn(WajibPajak $record): bool => $record->status === 'menungguVerifikasi' && auth()->user()->can('verify', $record))
                    ->modalHeading('Minta Perbaikan Pendaftaran Wajib Pajak')
                    ->schema([
                        Textarea::make('catatan_verifikasi')
                            ->label('Catatan Perbaikan')
                            ->default('')
                            ->placeholder('Jelaskan data yang harus diperbaiki...'),
                    ])
                    ->action(function (WajibPajak $record, array $data): void {
                        validator($data, [
                            'catatan_verifikasi' => ['required', 'string'],
                        ])->validate();

                        $record->update([
                            'status' => 'perluPerbaikan',
                            'tanggal_verifikasi' => now(),
                            'petugas_id' => auth()->id(),
                            'petugas_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                            'catatan_verifikasi' => $data['catatan_verifikasi'],
                        ]);

                        ActivityLog::log(
                            action: 'REQUEST_REVISION_WAJIB_PAJAK',
                            targetTable: 'wajib_pajak',
                            targetId: $record->id,
                            description: "Meminta perbaikan WP: {$record->nama_lengkap}. Catatan: {$data['catatan_verifikasi']}"
                        );

                        Notification::make()
                            ->title('Perbaikan Diminta')
                            ->body('Catatan perbaikan telah dikirim ke wajib pajak.')
                            ->info()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                //
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
            'index' => ListWajibPajaks::route('/'),
            'view' => ViewWajibPajak::route('/{record}'),
            'edit' => EditWajibPajak::route('/{record}/edit'),
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
