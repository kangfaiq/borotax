<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\FilamentDecimalInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\AsetReklamePemkabResource\Pages\ListAsetReklamePemkab;
use App\Filament\Resources\AsetReklamePemkabResource\Pages\CreateAsetReklamePemkab;
use App\Filament\Resources\AsetReklamePemkabResource\Pages\EditAsetReklamePemkab;
use App\Filament\Resources\AsetReklamePemkabResource\Pages;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\PeminjamanAsetReklame;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

class AsetReklamePemkabResource extends Resource
{
    protected static ?string $model = AsetReklamePemkab::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';

    protected static string | \UnitEnum | null $navigationGroup = 'Reklame';

    protected static ?string $navigationLabel = 'Aset Reklame Pemkab';

    protected static ?string $modelLabel = 'Aset Reklame Pemkab';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Aset')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('kode_aset')
                            ->label('Kode Aset')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(10)
                            ->placeholder('NB001 / BB001'),
                        TextInput::make('nama')
                            ->required()
                            ->maxLength(150),
                        Select::make('jenis')
                            ->required()
                            ->options([
                                'neon_box'  => 'Neon Box',
                                'billboard' => 'Billboard',
                            ]),
                        TextInput::make('lokasi')
                            ->label('Lokasi / Ruas Jalan')
                            ->required(),
                        Textarea::make('keterangan')
                            ->rows(2),
                        TextInput::make('kawasan')
                            ->placeholder('Kawasan Terminal, Perbatasan, dst'),
                        TextInput::make('traffic')
                            ->label('Kepadatan Traffic')
                            ->placeholder('Sangat Tinggi, Tinggi'),
                        Select::make('kelompok_lokasi')
                            ->label('Kelompok Lokasi')
                            ->options([
                                'A'  => 'A',
                                'A1' => 'A1',
                                'A2' => 'A2',
                                'A3' => 'A3',
                                'B'  => 'B',
                                'C'  => 'C',
                            ]),
                    ])->columns(2),

                Section::make('Dimensi & Koordinat')
                    ->columnSpanFull()
                    ->schema([
                        FilamentDecimalInput::configure(TextInput::make('panjang')
                            ->label('Panjang (m)')
                            ->required()
                            ->step(0.01)),
                        FilamentDecimalInput::configure(TextInput::make('lebar')
                            ->label('Lebar (m)')
                            ->required()
                            ->step(0.01)),
                        FilamentDecimalInput::configure(TextInput::make('luas_m2')
                            ->label('Luas (m²)')
                            ->step(0.01)
                            ->helperText('Otomatis: panjang × lebar')),
                        TextInput::make('jumlah_muka')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1),
                        FilamentDecimalInput::configure(TextInput::make('latitude')
                            ->step(0.0000001)),
                        FilamentDecimalInput::configure(TextInput::make('longitude')
                            ->step(0.0000001)),
                    ])->columns(3),

                Section::make('Harga Sewa Referensi')
                    ->columnSpanFull()
                    ->schema([
                        FilamentDecimalInput::configure(TextInput::make('harga_sewa_per_tahun')
                            ->label('Per Tahun (Rp)')
                            ->minValue(0)),
                        FilamentDecimalInput::configure(TextInput::make('harga_sewa_per_bulan')
                            ->label('Per Bulan (Rp)')
                            ->minValue(0)),
                        FilamentDecimalInput::configure(TextInput::make('harga_sewa_per_minggu')
                            ->label('Per Minggu (Rp)')
                            ->minValue(0)),
                    ])->columns(3),

                Section::make('Status')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('status_ketersediaan')
                            ->options([
                                'tersedia'     => 'Tersedia',
                                'disewa'       => 'Disewa',
                                'maintenance'  => 'Maintenance',
                                'tidak_aktif'  => 'Tidak Aktif',
                                'dipinjam_opd' => 'Dipinjam OPD',
                            ])
                            ->default('tersedia')
                            ->required(),
                        Textarea::make('catatan_status')
                            ->label('Catatan Status')
                            ->rows(2),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_aset')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('jenis')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'neon_box' ? 'info' : 'primary')
                    ->formatStateUsing(fn(string $state): string => $state === 'neon_box' ? 'Neon Box' : 'Billboard'),
                TextColumn::make('lokasi')
                    ->limit(25),
                TextColumn::make('luas_m2')
                    ->label('Luas (m²)')
                    ->sortable(),
                TextColumn::make('jumlah_muka')
                    ->label('Muka'),
                TextColumn::make('harga_sewa_per_tahun')
                    ->label('Sewa/Tahun')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status_ketersediaan')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'tersedia'     => 'success',
                        'disewa'       => 'danger',
                        'maintenance'  => 'warning',
                        'tidak_aktif'  => 'gray',
                        'dipinjam_opd' => 'info',
                        default        => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'tersedia'     => 'Tersedia',
                        'disewa'       => 'Disewa',
                        'maintenance'  => 'Maintenance',
                        'tidak_aktif'  => 'Tidak Aktif',
                        'dipinjam_opd' => 'Dipinjam OPD',
                        default        => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('jenis')
                    ->options([
                        'neon_box'  => 'Neon Box',
                        'billboard' => 'Billboard',
                    ]),
                SelectFilter::make('status_ketersediaan')
                    ->label('Status')
                    ->options([
                        'tersedia'     => 'Tersedia',
                        'disewa'       => 'Disewa',
                        'maintenance'  => 'Maintenance',
                        'tidak_aktif'  => 'Tidak Aktif',
                        'dipinjam_opd' => 'Dipinjam OPD',
                    ]),
            
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (AsetReklamePemkab $record): bool => auth()->user()?->can('update', $record) ?? false)
                    ->authorize(fn (AsetReklamePemkab $record): bool => auth()->user()?->can('update', $record) ?? false),
                Action::make('set_maintenance')
                    ->label('Maintenance')
                    ->icon('heroicon-o-wrench')
                    ->color('warning')
                    ->visible(fn (AsetReklamePemkab $record): bool => $record->status_ketersediaan !== 'maintenance' && static::canManageMaintenanceAndPinjam())
                    ->authorize(fn (): bool => static::canManageMaintenanceAndPinjam())
                    ->schema([
                        Textarea::make('catatan_status')
                            ->label('Catatan / Alasan')
                            ->required(),
                    ])
                    ->action(function (AsetReklamePemkab $record, array $data): void {
                        $record->update([
                            'status_ketersediaan' => 'maintenance',
                            'catatan_status'      => $data['catatan_status'],
                        ]);
                        Notification::make()->success()->title('Status diubah ke Maintenance')->send();
                    }),
                Action::make('set_tersedia')
                    ->label('Set Tersedia')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (AsetReklamePemkab $record): bool => in_array($record->status_ketersediaan, ['maintenance', 'tidak_aktif', 'dipinjam_opd']) && static::canManageAdminOnlyOperationalActions())
                    ->authorize(fn (): bool => static::canManageAdminOnlyOperationalActions())
                    ->requiresConfirmation()
                    ->action(function (AsetReklamePemkab $record): void {
                        // Close any active peminjaman records
                        $record->peminjamanAktif()->update(['status' => 'selesai']);

                        $record->update([
                            'status_ketersediaan' => 'tersedia',
                            'catatan_status'      => 'Manual: dikembalikan ke tersedia oleh ' . auth()->user()->nama_lengkap,
                            'peminjam_opd'        => null,
                            'materi_pinjam'       => null,
                            'pinjam_mulai'        => null,
                            'pinjam_selesai'      => null,
                            'catatan_pinjam'      => null,
                        ]);
                        Notification::make()->success()->title('Status diubah ke Tersedia')->send();
                    }),
                Action::make('pinjam_opd')
                    ->label('Pinjam OPD')
                    ->icon('heroicon-o-building-library')
                    ->color('info')
                    ->visible(fn (AsetReklamePemkab $record): bool => $record->status_ketersediaan === 'tersedia' && static::canManageMaintenanceAndPinjam())
                    ->authorize(fn (): bool => static::canManageMaintenanceAndPinjam())
                    ->schema([
                        TextInput::make('peminjam_opd')
                            ->label('Nama OPD / Dinas Peminjam')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('materi_pinjam')
                            ->label('Materi / Konten Reklame')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('pinjam_mulai')
                            ->label('Tanggal Mulai Pinjam')
                            ->required(),
                        DatePicker::make('pinjam_selesai')
                            ->label('Tanggal Selesai Pinjam')
                            ->required()
                            ->after('pinjam_mulai'),
                        Textarea::make('catatan_pinjam')
                            ->label('Catatan')
                            ->rows(2),
                        FileUpload::make('file_bukti_dukung')
                            ->label('Surat OPD ke Bapenda (opsional)')
                            ->disk('local')
                            ->directory('peminjaman-aset-reklame')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->maxSize(2048),
                    ])
                    ->action(function (AsetReklamePemkab $record, array $data): void {
                        PeminjamanAsetReklame::create([
                            'aset_reklame_pemkab_id' => $record->id,
                            'peminjam_opd'           => $data['peminjam_opd'],
                            'materi_pinjam'          => $data['materi_pinjam'],
                            'pinjam_mulai'           => $data['pinjam_mulai'],
                            'pinjam_selesai'         => $data['pinjam_selesai'],
                            'catatan_pinjam'         => $data['catatan_pinjam'] ?? null,
                            'file_bukti_dukung'      => $data['file_bukti_dukung'] ?? null,
                            'status'                 => 'aktif',
                            'petugas_id'             => auth()->id(),
                            'petugas_nama'           => auth()->user()->nama_lengkap ?? auth()->user()->name,
                        ]);

                        $record->update([
                            'status_ketersediaan' => 'dipinjam_opd',
                            'catatan_status'      => 'Dipinjam oleh ' . $data['peminjam_opd'],
                            'peminjam_opd'        => $data['peminjam_opd'],
                            'materi_pinjam'       => $data['materi_pinjam'],
                            'pinjam_mulai'        => $data['pinjam_mulai'],
                            'pinjam_selesai'      => $data['pinjam_selesai'],
                            'catatan_pinjam'      => $data['catatan_pinjam'] ?? null,
                        ]);
                        Notification::make()->success()->title('Aset dipinjamkan ke OPD')->send();
                    }),
                Action::make('selesai_pinjam')
                    ->label('Selesai Pinjam')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn (AsetReklamePemkab $record): bool => $record->status_ketersediaan === 'dipinjam_opd' && static::canManageAdminOnlyOperationalActions())
                    ->authorize(fn (): bool => static::canManageAdminOnlyOperationalActions())
                    ->requiresConfirmation()
                    ->modalHeading('Selesaikan Peminjaman OPD?')
                    ->action(function (AsetReklamePemkab $record): void {
                        // Update riwayat peminjaman aktif → selesai
                        $record->peminjamanAktif()->update(['status' => 'selesai']);

                        $record->update([
                            'status_ketersediaan' => 'tersedia',
                            'catatan_status'      => 'Peminjaman OPD selesai (' . $record->peminjam_opd . ')',
                            'peminjam_opd'        => null,
                            'materi_pinjam'       => null,
                            'pinjam_mulai'        => null,
                            'pinjam_selesai'      => null,
                            'catatan_pinjam'      => null,
                        ]);
                        Notification::make()->success()->title('Peminjaman selesai, aset kembali tersedia')->send();
                    }),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('kode_aset');
    }

    public static function getRelations(): array
    {
        return [];
    }

    protected static function canManageMaintenanceAndPinjam(): bool
    {
        return auth()->user()?->hasRole(['admin', 'verifikator', 'petugas']) ?? false;
    }

    protected static function canManageAdminOnlyOperationalActions(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAsetReklamePemkab::route('/'),
            'create' => CreateAsetReklamePemkab::route('/create'),
            'edit'   => EditAsetReklamePemkab::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        AsetReklamePemkab::syncExpiredOpdBorrowings();

        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
