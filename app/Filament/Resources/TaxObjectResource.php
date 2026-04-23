<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\FilamentDecimalInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use App\Filament\Resources\TaxObjectResource\Pages\ListTaxObjects;
use App\Filament\Resources\TaxObjectResource\Pages\CreateTaxObject;
use App\Filament\Resources\TaxObjectResource\Pages\ViewTaxObject;
use App\Filament\Resources\TaxObjectResource\Pages\EditTaxObject;
use App\Filament\Resources\TaxObjectResource\Pages;
use App\Filament\Forms\Components\MapPicker;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Domain\Reklame\Models\KelompokLokasiJalan;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Village;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaxObjectResource extends Resource
{
    protected static ?string $model = TaxObject::class;

    private const BOJONEGORO_REGENCY_CODE = '35.22';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string | \UnitEnum | null $navigationGroup = 'Pendaftaran';

    protected static ?string $navigationLabel = 'Daftar Objek Pajak';

    protected static ?string $modelLabel = 'Objek Pajak';

    protected static ?string $pluralModelLabel = 'Objek Pajak';

    protected static ?string $slug = 'pendaftaran/objek-pajak';

    protected static ?int $navigationSort = 2;

    /**
     * Pendaftaran objek pajak adalah alur operasional admin/petugas.
     * Verifikator tetap boleh melihat data objek pajak di modul lain via policy,
     * tetapi tidak masuk ke modul pendaftaran ini.
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
     * Helper: Cek apakah jenis pajak terpilih bertipe reklame (official_assessment + kode 41104)
     */
    private static function isReklame(Get $get): bool
    {
        $jenisPajakId = $get('jenis_pajak_id');
        if (!$jenisPajakId) return false;
        $jp = JenisPajak::find($jenisPajakId);
        return $jp && $jp->tipe_assessment === 'official_assessment' && $jp->kode === '41104';
    }

    /**
     * Helper: Cek apakah jenis pajak terpilih bertipe 'air_tanah'
     */
    private static function isAirTanah(Get $get): bool
    {
        $jenisPajakId = $get('jenis_pajak_id');
        if (!$jenisPajakId) return false;
        $jp = JenisPajak::find($jenisPajakId);
        return $jp && $jp->kode === '41108';
    }

    /**
     * Helper: Cek apakah jenis pajak terpilih bertipe 'self_assessment'
     */
    private static function isSelfAssessment(Get $get): bool
    {
        $jenisPajakId = $get('jenis_pajak_id');
        if (!$jenisPajakId) return false;
        $jp = JenisPajak::find($jenisPajakId);
        return $jp && $jp->tipe_assessment === 'self_assessment';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // === Section: Data Wajib Pajak ===
                Section::make('Data Wajib Pajak')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('npwpd')
                            ->label('NPWPD')
                            ->options(fn () => WajibPajak::where('status', 'disetujui')
                                ->whereNotNull('npwpd')
                                ->get()
                                ->mapWithKeys(fn ($wp) => [$wp->npwpd => $wp->npwpd . ' - ' . $wp->nama_lengkap]))
                            ->searchable()
                            ->required()
                            ->live(),

                        // === Daftar Objek Pajak per NPWPD ===
                        ViewField::make('daftar_objek')
                            ->view('filament.forms.components.daftar-objek-pajak')
                            ->visible(fn (Get $get) => !empty($get('npwpd')))
                            ->dehydrated(false),
                    ]),

                // === Section: Jenis Pajak ===
                Section::make('Jenis Pajak')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('jenis_pajak_id')
                            ->label('Jenis Pajak')
                            ->options(fn () => JenisPajak::where('is_active', true)
                                ->pluck('nama', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $set('sub_jenis_pajak_id', null);
                                $set('harga_patokan_reklame_id', null);
                                $set('lokasi_jalan_id', null);
                                $set('kelompok_lokasi', null);
                                $set('kelompok_pemakaian', null);
                                $set('kriteria_sda', null);
                                $set('is_opd', false);
                                $set('is_insidentil', false);

                                // Auto-select when only 1 sub jenis pajak exists
                                if ($state) {
                                    $subs = SubJenisPajak::where('jenis_pajak_id', $state)
                                        ->where('is_active', true)
                                        ->get();
                                    if ($subs->count() === 1) {
                                        $set('sub_jenis_pajak_id', $subs->first()->id);
                                        $set('tarif_persen', $subs->first()->tarif_persen);
                                    }
                                }
                            }),
                        Select::make('sub_jenis_pajak_id')
                            ->label('Sub Jenis Pajak')
                            ->options(fn (Get $get) => $get('jenis_pajak_id')
                                ? SubJenisPajak::where('jenis_pajak_id', $get('jenis_pajak_id'))
                                    ->where('is_active', true)
                                    ->pluck('nama', 'id')
                                : [])
                            ->searchable()
                            ->required()
                            ->live()
                            ->disabled(fn (Get $get) => $get('jenis_pajak_id')
                                && SubJenisPajak::where('jenis_pajak_id', $get('jenis_pajak_id'))
                                    ->where('is_active', true)
                                    ->count() <= 1)
                            ->dehydrated()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if ($get('sub_jenis_pajak_id')) {
                                    $sub = SubJenisPajak::find($get('sub_jenis_pajak_id'));
                                    if ($sub) {
                                        $set('tarif_persen', $sub->tarif_persen);
                                    }
                                }
                                $set('harga_patokan_reklame_id', null);
                                $set('is_opd', false);
                                $set('is_insidentil', false);
                            }),
                        // Toggle OPD: hanya untuk Self Assessment sub jenis KATERING
                        Toggle::make('is_opd')
                            ->label('Untuk OPD (Organisasi Perangkat Daerah)')
                            ->helperText('Aktifkan jika objek pajak ini melayani Jasa Boga/Katering untuk OPD (1 masa pajak bisa beberapa billing, tidak kena denda, wajib keterangan)')
                            ->default(false)
                            ->visible(function (Get $get) {
                                $subId = $get('sub_jenis_pajak_id');
                                if (!$subId) return false;
                                $sub = SubJenisPajak::find($subId);
                                return $sub && $sub->kode === 'PBJT_KATERING';
                            })
                            ->columnSpanFull(),
                        // Toggle Insidentil: untuk PBJT Hiburan (41103) dan Parkir (41107)
                        Toggle::make('is_insidentil')
                            ->label('Objek Insidentil')
                            ->helperText('Aktifkan jika objek pajak ini bersifat insidentil (1 masa pajak bisa beberapa billing, tidak kena denda, wajib keterangan)')
                            ->default(false)
                            ->visible(function (Get $get) {
                                $jenisPajakId = $get('jenis_pajak_id');
                                if (!$jenisPajakId) return false;
                                $jp = JenisPajak::find($jenisPajakId);
                                return $jp && in_array($jp->kode, ['41103', '41107']);
                            })
                            ->columnSpanFull(),
                    ])->columns(2),

                // === Section: Data Objek Pajak ===
                Section::make('Data Objek Pajak')
                    ->columnSpanFull()
                    ->schema([
                        Hidden::make('tanggal_daftar')
                            ->default(now()->toDateString()),
                        TextInput::make('nama_objek_pajak')
                            ->label('Nama Objek Pajak')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('alamat_objek')
                            ->label('Alamat Objek')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('kecamatan')
                            ->label('Kecamatan')
                            ->options(fn () => District::where('regency_code', self::BOJONEGORO_REGENCY_CODE)
                                ->orderBy('name')->pluck('name', 'name'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('kelurahan', null)),
                        Select::make('kelurahan')
                            ->label('Kelurahan / Desa')
                            ->options(function (Get $get) {
                                if (!$get('kecamatan')) return [];
                                $district = District::where('regency_code', self::BOJONEGORO_REGENCY_CODE)
                                    ->where('name', $get('kecamatan'))->first();
                                if (!$district) return [];
                                return Village::where('district_code', $district->code)
                                    ->orderBy('name')
                                    ->pluck('name', 'name');
                            })
                            ->searchable()
                            ->required(),
                        FilamentDecimalInput::configure(TextInput::make('tarif_persen')
                            ->label('Tarif (%)')
                            ->suffix('%')
                            ->required()),
                    ])->columns(2),

                // === Section: Field Kondisional Reklame ===
                Section::make('Data Reklame')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('bentuk')
                            ->label('Bentuk Reklame')
                            ->options([
                                'persegi' => 'Persegi / Persegi Panjang',
                                'trapesium' => 'Trapesium',
                                'lingkaran' => 'Lingkaran',
                                'elips' => 'Elips',
                                'segitiga' => 'Segitiga',
                            ])
                            ->required()
                            ->live()
                            ->default('persegi'),

                        // Persegi: panjang × lebar
                        FilamentDecimalInput::configure(TextInput::make('panjang')
                            ->label('Panjang (m)')
                            ->step(0.01)
                            ->suffix('m')
                            ->visible(fn (Get $get) => ($get('bentuk') ?? 'persegi') === 'persegi')
                            ->required(fn (Get $get) => ($get('bentuk') ?? 'persegi') === 'persegi')),
                        FilamentDecimalInput::configure(TextInput::make('lebar')
                            ->label('Lebar (m)')
                            ->step(0.01)
                            ->suffix('m')
                            ->visible(fn (Get $get) => ($get('bentuk') ?? 'persegi') === 'persegi')
                            ->required(fn (Get $get) => ($get('bentuk') ?? 'persegi') === 'persegi')),

                        // Trapesium: sisi atas, sisi bawah, tinggi
                        FilamentDecimalInput::configure(TextInput::make('sisi_atas')
                            ->label('Sisi Atas (m)')
                            ->step(0.01)
                            ->suffix('m')
                            ->visible(fn (Get $get) => $get('bentuk') === 'trapesium')
                            ->required(fn (Get $get) => $get('bentuk') === 'trapesium')),
                        FilamentDecimalInput::configure(TextInput::make('sisi_bawah')
                            ->label('Sisi Bawah (m)')
                            ->step(0.01)
                            ->suffix('m')
                            ->visible(fn (Get $get) => $get('bentuk') === 'trapesium')
                            ->required(fn (Get $get) => $get('bentuk') === 'trapesium')),
                        FilamentDecimalInput::configure(TextInput::make('tinggi')
                            ->label('Tinggi (m)')
                            ->step(0.01)
                            ->suffix('m')
                            ->visible(fn (Get $get) => in_array($get('bentuk'), ['trapesium', 'segitiga']))
                            ->required(fn (Get $get) => in_array($get('bentuk'), ['trapesium', 'segitiga']))),

                        // Lingkaran & Elips: diameter
                        FilamentDecimalInput::configure(TextInput::make('diameter')
                            ->label(fn (Get $get) => $get('bentuk') === 'elips' ? 'Diameter 1 (m)' : 'Diameter (m)')
                            ->step(0.01)
                            ->suffix('m')
                            ->visible(fn (Get $get) => in_array($get('bentuk'), ['lingkaran', 'elips']))
                            ->required(fn (Get $get) => in_array($get('bentuk'), ['lingkaran', 'elips']))),
                        FilamentDecimalInput::configure(TextInput::make('diameter2')
                            ->label('Diameter 2 (m)')
                            ->step(0.01)
                            ->suffix('m')
                            ->visible(fn (Get $get) => $get('bentuk') === 'elips')
                            ->required(fn (Get $get) => $get('bentuk') === 'elips')),

                        // Segitiga: alas
                        FilamentDecimalInput::configure(TextInput::make('alas')
                            ->label('Alas (m)')
                            ->step(0.01)
                            ->suffix('m')
                            ->visible(fn (Get $get) => $get('bentuk') === 'segitiga')
                            ->required(fn (Get $get) => $get('bentuk') === 'segitiga')),

                        TextInput::make('jumlah_muka')
                            ->label('Jumlah Muka / Sisi')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),

                        Select::make('harga_patokan_reklame_id')
                            ->label('Rincian Harga Patokan Reklame')
                            ->options(fn (Get $get) => HargaPatokanReklame::query()
                                ->forSubJenisPajak($get('sub_jenis_pajak_id'))
                                ->active()
                                ->orderBy('urutan')
                                ->pluck('nama', 'id')
                                ->toArray())
                            ->searchable()
                            ->required()
                            ->visible(fn (Get $get) => self::isReklame($get) && filled($get('sub_jenis_pajak_id')))
                            ->helperText('Pilih rincian reklame sesuai sub jenis reklame yang dipilih.'),

                        Select::make('lokasi_jalan_id')
                            ->label('Lokasi / Jalan Penempatan')
                            ->options(fn (Get $get) => KelompokLokasiJalan::getActiveOptions(
                                $get('tanggal_daftar') ?: now()->toDateString(),
                                $get('lokasi_jalan_id')
                            ))
                            ->searchable()
                            ->required()
                            ->live()
                            ->placeholder('Cari nama jalan atau kawasan...')
                            ->helperText('Pilih jalan/kawasan yang berlaku pada tanggal referensi objek, kelompok lokasi akan terisi otomatis')
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (!$state) {
                                    $set('kelompok_lokasi', null);
                                    return;
                                }
                                $record = KelompokLokasiJalan::find($state);
                                if ($record) {
                                    $set('kelompok_lokasi', $record->kelompok);
                                }
                            })
                            ->columnSpanFull(),

                        Select::make('kelompok_lokasi')
                            ->label('Kelompok Lokasi')
                            ->options(KelompokLokasiJalan::getKelompokOptions())
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Terisi otomatis berdasarkan jalan yang dipilih'),
                    ])->columns(2)
                    ->visible(fn (Get $get) => self::isReklame($get)),

                // === Section: Field Kondisional Air Tanah ===
                Section::make('Data Air Tanah')
                    ->columnSpanFull()
                    ->description('Sesuai Pergub Jawa Timur No. 35 Tahun 2025 tentang Penetapan Nilai Perolehan Air Tanah')
                    ->schema([
                        Select::make('kelompok_pemakaian')
                            ->label('Kelompok Pemakaian')
                            ->options([
                                '1' => '1 — Produk berupa Air',
                                '2' => '2 — Produk bukan Air, risiko tinggi',
                                '3' => '3 — Produk bukan Air, risiko menengah',
                                '4' => '4 — Produk bukan Air, risiko rendah',
                                '5' => '5 — Sosial/Pendidikan/Kesehatan/Pemerintahan/BUMD Air Minum',
                            ])
                            ->helperText('Pasal 6 — Komponen peruntukan dan pengelolaan Air Tanah')
                            ->required(),
                        Select::make('kriteria_sda')
                            ->label('Kriteria Sumber Daya Air')
                            ->options([
                                '1' => '1 — Kualitas baik, ada sumber Air alternatif',
                                '2' => '2 — Kualitas baik, tidak ada sumber Air alternatif',
                                '3' => '3 — Kualitas tidak baik, ada sumber Air alternatif',
                                '4' => '4 — Kualitas tidak baik, tidak ada sumber Air alternatif',
                            ])
                            ->helperText('Pasal 5 ayat 2 huruf a — Komponen sumber daya alam (bobot eksponensial)')
                            ->required(),
                    ])->columns(2)
                    ->visible(fn (Get $get) => self::isAirTanah($get)),

                // === Section: Status Aktif (hanya tampil saat edit) ===
                Section::make('Status Objek')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Objek Pajak Aktif')
                            ->helperText('Non-aktifkan jika objek pajak ini sudah tidak beroperasi')
                            ->default(true),
                    ])
                    ->visible(fn (?TaxObject $record): bool => $record !== null),

                // === Section: Foto Objek Pajak ===
                Section::make('Foto Objek Pajak')
                    ->columnSpanFull()
                    ->schema([
                        Hidden::make('foto_objek_path'),
                        ViewField::make('foto_upload_ui')
                            ->view('filament.forms.components.foto-objek-upload')
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),

                // === Section: Koordinat Lokasi ===
                Section::make('Koordinat Lokasi')
                    ->columnSpanFull()
                    ->description('Klik pada peta untuk menandai lokasi objek pajak')
                    ->schema([
                        MapPicker::make('map')
                            ->defaultLocation(-7.1507, 111.8828)
                            ->defaultZoom(13)
                            ->columnSpanFull(),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->readOnly(),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->readOnly(),
                    ])->columns(2)
                    ->collapsed(fn (?TaxObject $record): bool => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('npwpd')
                    ->label('NPWPD')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_objek_pajak')
                    ->label('Nama Objek')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $matchingIds = TaxObject::all(['id', 'nama_objek_pajak'])
                            ->filter(fn ($obj) => str_contains(strtolower($obj->nama_objek_pajak), strtolower($search)))
                            ->pluck('id');

                        return $query->whereIn('id', $matchingIds);
                    })
                    ->wrap(),
                TextColumn::make('jenisPajak.nama')
                    ->label('Jenis Pajak')
                    ->sortable(),
                TextColumn::make('subJenisPajak.nama')
                    ->label('Sub Jenis')
                    ->toggleable(),
                TextColumn::make('kecamatan')
                    ->label('Kecamatan')
                    ->sortable(),
                TextColumn::make('kelurahan')
                    ->label('Kelurahan')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tarif_persen')
                    ->label('Tarif')
                    ->suffix('%')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('tanggal_daftar')
                    ->label('Tgl Daftar')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('jenis_pajak_id')
                    ->label('Jenis Pajak')
                    ->options(fn () => JenisPajak::where('is_active', true)
                        ->pluck('nama', 'id')),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            
                TrashedFilter::make(),
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
            'index' => ListTaxObjects::route('/'),
            'create' => CreateTaxObject::route('/create'),
            'view' => ViewTaxObject::route('/{record}'),
            'edit' => EditTaxObject::route('/{record}/edit'),
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
