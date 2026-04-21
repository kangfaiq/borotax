<?php

namespace App\Filament\Resources;

use App\Domain\Master\Models\Instansi;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Region\Models\Village;
use App\Enums\InstansiKategori;
use App\Filament\Resources\InstansiResource\Pages\CreateInstansi;
use App\Filament\Resources\InstansiResource\Pages\EditInstansi;
use App\Filament\Resources\InstansiResource\Pages\ListInstansis;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InstansiResource extends Resource
{
    private const BOJONEGORO_PROVINCE_CODE = '35';
    private const BOJONEGORO_REGENCY_CODE = '35.22';

    protected static ?string $model = Instansi::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Instansi';

    protected static ?string $modelLabel = 'Instansi';

    protected static ?string $pluralModelLabel = 'Instansi';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', Instansi::class) ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', Instansi::class) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Instansi')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('kode')
                            ->label('Kode')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('nama')
                            ->label('Nama Instansi')
                            ->required()
                            ->maxLength(255),
                        Select::make('kategori')
                            ->label('Kategori')
                            ->options(collect(InstansiKategori::cases())
                                ->mapWithKeys(fn (InstansiKategori $kategori) => [$kategori->value => $kategori->getLabel()])
                                ->all())
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->inline(false),
                        Textarea::make('alamat')
                            ->label('Alamat / Lokasi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Wilayah Instansi')
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
                            ->afterStateHydrated(function (Set $set, ?string $state): void {
                                if (blank($state)) {
                                    $set('asal_wilayah', 'bojonegoro');
                                }
                            })
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                if ($state === 'bojonegoro') {
                                    $set('province_code', self::BOJONEGORO_PROVINCE_CODE);
                                    $set('regency_code', self::BOJONEGORO_REGENCY_CODE);

                                    return;
                                }

                                $set('province_code', null);
                                $set('regency_code', null);
                                $set('district_code', null);
                                $set('village_code', null);
                            })
                            ->columnSpanFull(),
                        Select::make('province_code')
                            ->label('Provinsi')
                            ->options(fn () => Province::query()->orderBy('name')->pluck('name', 'code'))
                            ->searchable()
                            ->required(fn (Get $get): bool => $get('asal_wilayah') === 'luar_bojonegoro')
                            ->visible(fn (Get $get): bool => $get('asal_wilayah') === 'luar_bojonegoro')
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('regency_code', null);
                                $set('district_code', null);
                                $set('village_code', null);
                            }),
                        Select::make('regency_code')
                            ->label('Kabupaten / Kota')
                            ->options(function (Get $get) {
                                $provinceCode = $get('province_code');

                                if (! filled($provinceCode)) {
                                    return [];
                                }

                                return Regency::query()
                                    ->where('province_code', $provinceCode)
                                    ->orderBy('name')
                                    ->pluck('name', 'code');
                            })
                            ->searchable()
                            ->required(fn (Get $get): bool => $get('asal_wilayah') === 'luar_bojonegoro')
                            ->visible(fn (Get $get): bool => $get('asal_wilayah') === 'luar_bojonegoro')
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('district_code', null);
                                $set('village_code', null);
                            }),
                        Select::make('district_code')
                            ->label('Kecamatan')
                            ->options(function (Get $get) {
                                if (($get('asal_wilayah') ?? 'bojonegoro') === 'bojonegoro') {
                                    return District::query()
                                        ->where('regency_code', self::BOJONEGORO_REGENCY_CODE)
                                        ->orderBy('name')
                                        ->pluck('name', 'code');
                                }

                                $regencyCode = $get('regency_code');

                                if (! filled($regencyCode)) {
                                    return [];
                                }

                                return District::query()
                                    ->where('regency_code', $regencyCode)
                                    ->orderBy('name')
                                    ->pluck('name', 'code');
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('village_code', null);
                            }),
                        Select::make('village_code')
                            ->label('Kelurahan / Desa')
                            ->options(function (Get $get) {
                                $districtCode = $get('district_code');

                                if (! filled($districtCode)) {
                                    return [];
                                }

                                return Village::query()
                                    ->where('district_code', $districtCode)
                                    ->orderBy('name')
                                    ->pluck('name', 'code');
                            })
                            ->searchable()
                            ->required(),
                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function mutateRegionFormData(array $data): array
    {
        if (($data['asal_wilayah'] ?? 'bojonegoro') === 'bojonegoro') {
            $data['province_code'] = self::BOJONEGORO_PROVINCE_CODE;
            $data['regency_code'] = self::BOJONEGORO_REGENCY_CODE;
        }

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('nama')
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama')
                    ->label('Nama Instansi')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (?InstansiKategori $state): string => $state?->getLabel() ?? '-'),
                TextColumn::make('alamat')
                    ->label('Alamat / Lokasi')
                    ->limit(60)
                    ->wrap()
                    ->placeholder('-'),
                TextColumn::make('district_name')
                    ->label('Kecamatan')
                    ->state(fn (Instansi $record): string => $record->district?->name ?? '-'),
                TextColumn::make('village_name')
                    ->label('Kelurahan / Desa')
                    ->state(fn (Instansi $record): string => $record->village?->name ?? '-')
                    ->wrap(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options(collect(InstansiKategori::cases())
                        ->mapWithKeys(fn (InstansiKategori $kategori) => [$kategori->value => $kategori->getLabel()])
                        ->all()),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Nonaktif',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstansis::route('/'),
            'create' => CreateInstansi::route('/create'),
            'edit' => EditInstansi::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['province', 'regency', 'district', 'village'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}