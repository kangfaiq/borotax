<?php

namespace App\Filament\Resources;

use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Village;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Retribusi\Models\ObjekRetribusiSewaTanah;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Filament\Resources\ObjekRetribusiSewaTanahResource\Pages;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\ValidationException;

class ObjekRetribusiSewaTanahResource extends Resource
{
    protected static ?string $model = ObjekRetribusiSewaTanah::class;

    private const BOJONEGORO_REGENCY_CODE = '35.22';
    private const REKLAME_TO_RETRIBUSI_SUB_JENIS = [
        'REKLAME_TETAP' => ['SEWA_TANAH_PERMANEN', 'SEWA_TANAH_RUMIJA'],
        'REKLAME_KAIN' => ['SEWA_TANAH_KAIN'],
    ];

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';

    protected static string | \UnitEnum | null $navigationGroup = 'Pendaftaran';

    protected static ?string $navigationLabel = 'Objek Retribusi Sewa Tanah';

    protected static ?string $modelLabel = 'Objek Retribusi Sewa Tanah';

    protected static ?string $pluralModelLabel = 'Objek Retribusi Sewa Tanah';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'petugas']);
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'petugas']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('1. Pilih NPWPD')
                    ->schema([
                        Forms\Components\Select::make('npwpd')
                            ->label('NPWPD')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => static::searchApprovedWajibPajakOptions($search))
                            ->getOptionLabelUsing(function (?string $value): ?string {
                                $wp = static::resolveApprovedWajibPajakByNpwpd($value);

                                return $wp ? "{$wp->npwpd} - {$wp->nama_lengkap}" : $value;
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                $set('tax_object_id', null);
                                $set('luas_m2', null);
                                $set('nama_objek', null);
                                $set('alamat_objek', null);
                                $set('kecamatan', null);
                                $set('kelurahan', null);

                                if (! $state) {
                                    $set('nik', null);
                                    $set('nik_hash', null);
                                    $set('nama_pemilik', null);
                                    $set('alamat_pemilik', null);

                                    return;
                                }

                                $wajibPajak = static::resolveApprovedWajibPajakByNpwpd($state);

                                $set('nik', $wajibPajak?->nik);
                                $set('nik_hash', $wajibPajak?->nik_hash);
                                $set('nama_pemilik', $wajibPajak?->nama_lengkap);
                                $set('alamat_pemilik', $wajibPajak?->alamat);
                            })
                            ->helperText('Pilih NPWPD wajib pajak terlebih dahulu.'),
                    ]),
                Section::make('2. Pilih Objek Reklame')
                    ->schema([
                        Forms\Components\Select::make('tax_object_id')
                            ->label('Objek Reklame')
                            ->options(function (Get $get): array {
                                $npwpd = $get('npwpd');

                                if (! $npwpd) {
                                    return [];
                                }

                                return ReklameObject::where('npwpd', $npwpd)
                                    ->orderBy('nama_objek_pajak')
                                    ->get()
                                    ->mapWithKeys(fn (ReklameObject $obj) => [
                                        $obj->id => static::formatReklameObjectLabel($obj),
                                    ])
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function (?string $value): ?string {
                                $obj = ReklameObject::find($value);

                                return $obj ? static::formatReklameObjectLabel($obj) : null;
                            })
                            ->disabled(fn (Get $get): bool => blank($get('npwpd')))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                if (! $state) {
                                    $set('luas_m2', null);
                                    $set('nama_objek', null);
                                    $set('alamat_objek', null);
                                    $set('kecamatan', null);
                                    $set('kelurahan', null);
                                    $set('sub_jenis_pajak_id', null);

                                    return;
                                }

                                $obj = ReklameObject::find($state);

                                if (! $obj) {
                                    return;
                                }

                                $wajibPajak = static::resolveApprovedWajibPajakByNpwpd($obj->npwpd);

                                $set('npwpd', $obj->npwpd);
                                $set('luas_m2', (float) $obj->luas_m2);
                                $set('nik', $wajibPajak?->nik);
                                $set('nik_hash', $wajibPajak?->nik_hash);
                                $set('nama_pemilik', $wajibPajak?->nama_lengkap);
                                $set('alamat_pemilik', $wajibPajak?->alamat);
                                $set('nama_objek', $obj->nama_objek_pajak);
                                $set('alamat_objek', $obj->alamat_objek);
                                $set('kecamatan', $obj->kecamatan);
                                $set('kelurahan', $obj->kelurahan);

                                $allowedSubJenisOptions = static::getRetribusiSubJenisOptionsForTaxObject($state);
                                $currentSubJenisId = $get('sub_jenis_pajak_id');

                                if (count($allowedSubJenisOptions) === 1) {
                                    $set('sub_jenis_pajak_id', array_key_first($allowedSubJenisOptions));
                                } elseif (! array_key_exists((string) $currentSubJenisId, $allowedSubJenisOptions)) {
                                    $set('sub_jenis_pajak_id', null);
                                }
                            })
                            ->helperText('Daftar objek reklame hanya menampilkan objek milik NPWPD yang dipilih.'),
                        Forms\Components\TextInput::make('luas_m2')
                            ->label('Luas m² (dari objek reklame)')
                            ->numeric()
                            ->readOnly()
                            ->dehydrated(),
                    ]),
                Section::make('3. Informasi Wajib Pajak')
                    ->schema([
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->readOnly()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\Hidden::make('nik_hash'),
                        Forms\Components\TextInput::make('nama_pemilik')
                            ->label('Nama Pemilik')
                            ->readOnly()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\Textarea::make('alamat_pemilik')
                            ->label('Alamat Pemilik')
                            ->readOnly()
                            ->dehydrated()
                            ->rows(2),
                    ])
                    ->columns(2),
                Section::make('4. Pilih Sub Jenis Retribusi')
                    ->schema([
                        Forms\Components\Select::make('sub_jenis_pajak_id')
                            ->label('Sub Jenis Retribusi')
                            ->options(fn (Get $get): array => static::getRetribusiSubJenisOptionsForTaxObject($get('tax_object_id')))
                                ->helperText('Objek reklame kategori tetap hanya dapat dipasangkan dengan sewa tanah permanen atau RUMIJA, sedangkan objek reklame insidentil hanya dipasangkan dengan sewa tanah kain.')
                            ->required(),
                    ]),
                Section::make('5. Data Objek Retribusi')
                    ->schema([
                        Forms\Components\TextInput::make('nama_objek')
                            ->label('Nama Objek Retribusi')
                            ->required(),
                        Forms\Components\Textarea::make('alamat_objek')
                            ->label('Alamat Objek')
                            ->rows(2)
                            ->required(),
                        Forms\Components\Select::make('kecamatan')
                            ->label('Kecamatan')
                            ->options(fn (): array => District::where('regency_code', self::BOJONEGORO_REGENCY_CODE)
                                ->orderBy('name')
                                ->pluck('name', 'name')
                                ->toArray())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('kelurahan', null))
                            ->required(),
                        Forms\Components\Select::make('kelurahan')
                            ->label('Kelurahan / Desa')
                            ->options(function (Get $get): array {
                                if (! $get('kecamatan')) {
                                    return [];
                                }

                                $district = District::where('regency_code', self::BOJONEGORO_REGENCY_CODE)
                                    ->where('name', $get('kecamatan'))
                                    ->first();

                                if (! $district) {
                                    return [];
                                }

                                return Village::where('district_code', $district->code)
                                    ->orderBy('name')
                                    ->pluck('name', 'name')
                                    ->toArray();
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nopd')
                    ->label('NOPD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('npwpd')
                    ->label('NPWPD')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_pemilik')
                    ->label('Pemilik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_objek')
                    ->label('Objek')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subJenisPajak.nama')
                    ->label('Sub Jenis'),
                Tables\Columns\TextColumn::make('kecamatan')
                    ->label('Kecamatan')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('kelurahan')
                    ->label('Kelurahan')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('luas_m2')
                    ->label('Luas m²')
                    ->suffix(' m²')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sub_jenis_pajak_id')
                    ->label('Sub Jenis')
                    ->relationship('subJenisPajak', 'nama'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                TrashedFilter::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
                Actions\RestoreBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListObjekRetribusiSewaTanah::route('/'),
            'create' => Pages\CreateObjekRetribusiSewaTanah::route('/create'),
            'edit' => Pages\EditObjekRetribusiSewaTanah::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function syncOwnerData(array $data): array
    {
        if (empty($data['tax_object_id'])) {
            return $data;
        }

        $objekReklame = ReklameObject::find($data['tax_object_id']);

        if (! $objekReklame) {
            return $data;
        }

        if (! empty($data['npwpd']) && $data['npwpd'] !== $objekReklame->npwpd) {
            throw ValidationException::withMessages([
                'tax_object_id' => 'Objek reklame yang dipilih tidak sesuai dengan NPWPD.',
            ]);
        }

        $wajibPajak = static::resolveApprovedWajibPajakByNpwpd($objekReklame->npwpd);

        if (! $wajibPajak) {
            throw ValidationException::withMessages([
                'npwpd' => 'NPWPD pada objek reklame belum terhubung ke wajib pajak yang disetujui.',
            ]);
        }

        $data['npwpd'] = $wajibPajak->npwpd;
        $data['luas_m2'] = (float) $objekReklame->luas_m2;
        $data['nik'] = $wajibPajak->nik;
        $data['nik_hash'] = $wajibPajak->nik_hash;
        $data['nama_pemilik'] = $wajibPajak->nama_lengkap;
        $data['alamat_pemilik'] = $wajibPajak->alamat;
        $data['nama_objek'] = filled($data['nama_objek'] ?? null) ? $data['nama_objek'] : $objekReklame->nama_objek_pajak;
        $data['alamat_objek'] = filled($data['alamat_objek'] ?? null) ? $data['alamat_objek'] : $objekReklame->alamat_objek;
        $data['kecamatan'] = filled($data['kecamatan'] ?? null) ? $data['kecamatan'] : $objekReklame->kecamatan;
        $data['kelurahan'] = filled($data['kelurahan'] ?? null) ? $data['kelurahan'] : $objekReklame->kelurahan;

        return $data;
    }

    public static function resolveApprovedWajibPajakByNpwpd(?string $npwpd): ?WajibPajak
    {
        if (! $npwpd) {
            return null;
        }

        return WajibPajak::where('npwpd', $npwpd)
            ->where('status', 'disetujui')
            ->first();
    }

    public static function searchApprovedWajibPajakOptions(string $search): array
    {
        $keyword = trim($search);

        if ($keyword === '') {
            return [];
        }

        $results = collect();

        if (ctype_digit($keyword) && strlen($keyword) >= 5) {
            $nikHash = WajibPajak::generateHash($keyword);

            $results = WajibPajak::query()
                ->where('status', 'disetujui')
                ->where('nik_hash', $nikHash)
                ->limit(20)
                ->get();
        }

        if ($results->isEmpty()) {
            $keyword = str($keyword)->lower()->toString();

            $results = WajibPajak::query()
                ->where('status', 'disetujui')
                ->get()
                ->filter(fn (WajibPajak $wp): bool =>
                    str_contains(strtolower((string) $wp->npwpd), $keyword)
                    || str_contains(strtolower((string) $wp->nik), $keyword)
                    || str_contains(strtolower((string) $wp->nama_lengkap), $keyword)
                )
                ->take(20)
                ->values();
        }

        return $results
            ->mapWithKeys(fn (WajibPajak $wp) => [
                $wp->npwpd => "{$wp->npwpd} - {$wp->nama_lengkap}",
            ])
            ->toArray();
    }

    protected static function formatReklameObjectLabel(ReklameObject $objek): string
    {
        return "{$objek->nopd} - {$objek->nama_objek_pajak} ({$objek->luas_m2} m²)";
    }

    public static function getRetribusiSubJenisOptionsForTaxObject(?string $taxObjectId): array
    {
        $jenisPajak = JenisPajak::where('kode', '42101')->first();

        if (! $jenisPajak) {
            return [];
        }

        $allowedCodes = static::resolveAllowedRetribusiSubJenisCodes($taxObjectId);

        return SubJenisPajak::query()
            ->where('jenis_pajak_id', $jenisPajak->id)
            ->active()
            ->ordered()
            ->when($allowedCodes !== null, fn (Builder $query) => $query->whereIn('kode', $allowedCodes))
            ->pluck('nama', 'id')
            ->toArray();
    }

    protected static function resolveAllowedRetribusiSubJenisCodes(?string $taxObjectId): ?array
    {
        if (! $taxObjectId) {
            return null;
        }

        $objekReklame = ReklameObject::query()
            ->with('subJenisPajak:id,kode')
            ->find($taxObjectId);

        $kodeReklame = $objekReklame?->subJenisPajak?->kode;

        return static::REKLAME_TO_RETRIBUSI_SUB_JENIS[$kodeReklame] ?? null;
    }
}
