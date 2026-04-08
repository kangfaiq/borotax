<?php

namespace App\Filament\Resources;

use App\Domain\Auth\Support\GeneratedLoginEmail;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use App\Filament\Resources\DaftarWajibPajakResource\Pages\ListDaftarWajibPajaks;
use App\Filament\Resources\DaftarWajibPajakResource\Pages\CreateDaftarWajibPajak;
use App\Filament\Resources\DaftarWajibPajakResource\Pages\ViewDaftarWajibPajak;
use App\Filament\Resources\DaftarWajibPajakResource\Pages\EditDaftarWajibPajak;
use App\Filament\Resources\DaftarWajibPajakResource\Pages;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Village;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class DaftarWajibPajakResource extends Resource
{
    protected static ?string $model = WajibPajak::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-plus';

    protected static string | \UnitEnum | null $navigationGroup = 'Pendaftaran';

    protected static ?string $navigationLabel = 'Daftar Wajib Pajak';

    protected static ?string $modelLabel = 'Pendaftaran Wajib Pajak';

    protected static ?string $pluralModelLabel = 'Pendaftaran Wajib Pajak';

    protected static ?string $slug = 'pendaftaran/wajib-pajak';

    protected static ?int $navigationSort = 1;

    // Kode wilayah Bojonegoro
    private const BOJONEGORO_PROVINCE_CODE = '35';
    private const BOJONEGORO_REGENCY_CODE = '35.22';

    /**
     * Modul pendaftaran dipakai admin/petugas untuk input awal.
     * Verifikator menangani approval/verification di modul lain meskipun policy WajibPajak
     * lebih luas untuk akses data wajib pajak secara umum.
     */
    private static function canManageRegistrationFlow(): bool
    {
        return auth()->user()?->hasRole(['admin', 'petugas']) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canManageRegistrationFlow();
    }

    public static function canAccess(): bool
    {
        return static::canManageRegistrationFlow();
    }

    /**
     * Bypass policy checks — this resource manages its own access via canAccess()
     */
    public static function canViewAny(): bool
    {
        return static::canManageRegistrationFlow();
    }

    public static function canCreate(): bool
    {
        return static::canManageRegistrationFlow();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Wajib Pajak')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nik')
                            ->label('NIK')
                            ->required()
                            ->inputMode('numeric')
                            ->maxLength(16)
                            ->minLength(16)
                            ->regex('/^[0-9]{16}$/')
                            ->validationMessages([
                                'regex' => 'NIK harus terdiri dari 16 digit angka.',
                            ]),
                        TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('alamat')
                            ->label('Alamat')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Kontak')
                    ->columnSpanFull()
                    ->description('Kontak akan disimpan di akun pengguna (terenkripsi)')
                    ->schema([
                        TextInput::make('no_whatsapp')
                            ->label('No. WhatsApp')
                            ->required()
                            ->placeholder('08xxxxxxxxxx')
                            ->helperText('Format: 08xxxxxxxxxx')
                            ->regex('/^08\d{8,12}$/')
                            ->validationMessages([
                                'regex' => 'Format No. WhatsApp harus diawali 08 diikuti 8-12 digit angka.',
                            ]),
                        TextInput::make('no_telp')
                            ->label('No. Telepon')
                            ->placeholder('(0353) 881826')
                            ->helperText('Format: (kode area) nomor, contoh: (0353) 881826')
                            ->regex('/^\(\d{3,5}\)\s?\d{4,8}$/')
                            ->validationMessages([
                                'regex' => 'Format No. Telepon harus (kode area) nomor, contoh: (0353) 881826',
                            ]),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(fn(string $operation): bool => $operation === 'edit')
                            ->helperText(function (?string $state, $record, string $operation): string {
                                if ($operation === 'create') {
                                    return 'Kosongkan jika wajib pajak tidak memiliki email, sistem akan generate email login otomatis.';
                                }

                                return GeneratedLoginEmail::isGenerated($record?->user?->email ?? $state)
                                    ? 'Label UI: Username login otomatis. Sampaikan username ini ke wajib pajak.'
                                    : 'Label UI: Email login wajib pajak.';
                            })
                            ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? str($state)->trim()->lower()->value() : null),
                        Placeholder::make('email_login_status')
                            ->label('Status Username Login')
                            ->content(function ($record) {
                                $email = $record?->user?->email;
                                $isGenerated = GeneratedLoginEmail::isGenerated($email);
                                $badgeClasses = $isGenerated
                                    ? 'bg-amber-100 text-amber-800 ring-amber-600/20'
                                    : 'bg-emerald-100 text-emerald-800 ring-emerald-600/20';
                                $description = $isGenerated
                                    ? 'Gunakan username login ini saat menyampaikan akun ke wajib pajak.'
                                    : 'Akun memakai email asli wajib pajak.';

                                return new HtmlString(
                                    '<span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ' . $badgeClasses . '">' . GeneratedLoginEmail::sourceLabel($email) . '</span>'
                                    . '<div class="mt-2 text-sm text-gray-600">' . $description . '</div>'
                                );
                            })
                            ->visible(fn($record, string $operation): bool => $operation !== 'create' && filled($record?->user?->email))
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Asal Wilayah')
                    ->columnSpanFull()
                    ->schema([
                        Radio::make('asal_wilayah')
                            ->label('Asal Wilayah')
                            ->options([
                                'bojonegoro' => 'Kabupaten Bojonegoro',
                                'luar_bojonegoro' => 'Luar Kabupaten Bojonegoro',
                            ])
                            ->default('bojonegoro')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('province_code', null);
                                $set('regency_code', null);
                                $set('district_code', null);
                                $set('village_code', null);
                            })
                            ->columnSpanFull(),

                        // === LUAR BOJONEGORO: Provinsi & Kabupaten (hanya tampil utk luar) ===
                        Select::make('province_code')
                            ->label('Provinsi')
                            ->options(fn() => Province::orderBy('name')->pluck('name', 'code'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->visible(fn(Get $get) => $get('asal_wilayah') === 'luar_bojonegoro')
                            ->afterStateUpdated(function (Set $set) {
                                $set('regency_code', null);
                                $set('district_code', null);
                                $set('village_code', null);
                            }),
                        Select::make('regency_code')
                            ->label('Kabupaten / Kota')
                            ->options(function (Get $get) {
                                $provinceCode = $get('province_code');
                                if (!$provinceCode)
                                    return [];
                                return Regency::where('province_code', $provinceCode)
                                    ->orderBy('name')
                                    ->pluck('name', 'code');
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->visible(fn(Get $get) => $get('asal_wilayah') === 'luar_bojonegoro')
                            ->afterStateUpdated(function (Set $set) {
                                $set('district_code', null);
                                $set('village_code', null);
                            }),

                        // === KECAMATAN: satu field, opsi dinamis ===
                        Select::make('district_code')
                            ->label('Kecamatan')
                            ->options(function (Get $get) {
                                $asal = $get('asal_wilayah');
                                if ($asal === 'bojonegoro') {
                                    return District::where('regency_code', self::BOJONEGORO_REGENCY_CODE)
                                        ->orderBy('name')
                                        ->pluck('name', 'code');
                                }
                                // Luar Bojonegoro: filter by selected regency
                                $regencyCode = $get('regency_code');
                                if (!$regencyCode)
                                    return [];
                                return District::where('regency_code', $regencyCode)
                                    ->orderBy('name')
                                    ->pluck('name', 'code');
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('village_code', null);
                            }),

                        // === KELURAHAN / DESA: satu field, opsi dinamis ===
                        Select::make('village_code')
                            ->label('Kelurahan / Desa')
                            ->options(function (Get $get) {
                                $districtCode = $get('district_code');
                                if (!$districtCode)
                                    return [];
                                return Village::where('district_code', $districtCode)
                                    ->orderBy('name')
                                    ->pluck('name', 'code');
                            })
                            ->searchable()
                            ->required(),
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
                            ->required()
                            ->live()
                            ->default('perorangan'),
                        TextInput::make('nama_perusahaan')
                            ->label('Nama Perusahaan')
                            ->visible(fn(Get $get) => $get('tipe_wajib_pajak') === 'perusahaan')
                            ->required(fn(Get $get) => $get('tipe_wajib_pajak') === 'perusahaan'),
                        TextInput::make('nib')
                            ->label('NIB')
                            ->visible(fn(Get $get) => $get('tipe_wajib_pajak') === 'perusahaan'),
                        TextInput::make('npwp_pusat')
                            ->label('NPWP Pusat')
                            ->visible(fn(Get $get) => $get('tipe_wajib_pajak') === 'perusahaan'),
                    ])->columns(2),
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
                    ->label('Nama Lengkap')
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
                    ->formatStateUsing(fn(string $state): string => $state === 'perorangan' ? 'Perorangan' : 'Perusahaan'),
                TextColumn::make('npwpd')
                    ->label('NPWPD')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('user.email')
                    ->label('Sumber Login')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => GeneratedLoginEmail::sourceLabel($state))
                    ->color(fn(?string $state): string => GeneratedLoginEmail::sourceColor($state)),
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
                    ->label('Petugas')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_daftar', 'desc')
            ->filters([
                SelectFilter::make('tipe_wajib_pajak')
                    ->label('Tipe')
                    ->options([
                        'perorangan' => 'Perorangan',
                        'perusahaan' => 'Perusahaan',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'menungguVerifikasi' => 'Menunggu Verifikasi',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'perluPerbaikan' => 'Perlu Perbaikan',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDaftarWajibPajaks::route('/'),
            'create' => CreateDaftarWajibPajak::route('/create'),
            'view' => ViewDaftarWajibPajak::route('/{record}'),
            'edit' => EditDaftarWajibPajak::route('/{record}/edit'),
        ];
    }
}
