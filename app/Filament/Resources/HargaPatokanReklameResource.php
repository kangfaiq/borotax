<?php

namespace App\Filament\Resources;

use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Filament\Resources\HargaPatokanReklameResource\Pages\CreateHargaPatokanReklame;
use App\Filament\Resources\HargaPatokanReklameResource\Pages\EditHargaPatokanReklame;
use App\Filament\Resources\HargaPatokanReklameResource\Pages\ListHargaPatokanReklames;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

class HargaPatokanReklameResource extends Resource
{
    protected static ?string $model = HargaPatokanReklame::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static string | \UnitEnum | null $navigationGroup = 'Reklame';

    protected static ?string $navigationLabel = 'Harga Patokan Reklame';

    protected static ?string $modelLabel = 'Harga Patokan Reklame';

    protected static ?string $pluralModelLabel = 'Harga Patokan Reklame';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', HargaPatokanReklame::class) ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', HargaPatokanReklame::class) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Master Detail Reklame')
                ->columnSpanFull()
                ->schema([
                    Select::make('sub_jenis_pajak_id')
                        ->label('Sub Jenis Pajak Reklame')
                        ->options(SubJenisPajak::whereIn('kode', ['REKLAME_TETAP', 'REKLAME_KAIN'])->orderBy('urutan')->pluck('nama', 'id'))
                        ->required()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (?string $state, Set $set): void {
                            $isInsidentil = (bool) SubJenisPajak::where('id', $state)->value('is_insidentil');
                            $set('is_insidentil', $isInsidentil);
                        }),
                    TextInput::make('kode')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('nama')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('nama_lengkap')
                        ->maxLength(255),
                    TextInput::make('urutan')
                        ->numeric()
                        ->default(1)
                        ->required(),
                    Toggle::make('is_insidentil')
                        ->disabled()
                        ->dehydrated()
                        ->label('Insidentil'),
                    Toggle::make('is_active')
                        ->default(true)
                        ->label('Aktif'),
                ])
                ->columns(2),
            Section::make('Tarif Berlaku')
                ->columnSpanFull()
                ->schema([
                    Repeater::make('reklameTariffs')
                        ->relationship()
                        ->label('Tarif Reklame')
                        ->defaultItems(0)
                        ->orderColumn(false)
                        ->schema([
                            Select::make('kelompok_lokasi')
                                ->label('Kelompok Lokasi')
                                ->options([
                                    'A' => 'A',
                                    'A1' => 'A1',
                                    'A2' => 'A2',
                                    'A3' => 'A3',
                                    'B' => 'B',
                                    'C' => 'C',
                                ])
                                ->nullable(),
                            Select::make('satuan_waktu')
                                ->label('Satuan Waktu')
                                ->options([
                                    'perTahun' => 'Per Tahun',
                                    'perBulan' => 'Per Bulan',
                                    'perMinggu' => 'Per Minggu',
                                    'perHari' => 'Per Hari',
                                    'perLembar' => 'Per Lembar',
                                    'perMingguPerBuah' => 'Per Minggu/Buah',
                                    'perHariPerBuah' => 'Per Hari/Buah',
                                ])
                                ->required(),
                            TextInput::make('satuan_label')
                                ->required()
                                ->maxLength(100),
                            TextInput::make('nspr')
                                ->numeric()
                                ->default(0)
                                ->required(),
                            TextInput::make('njopr')
                                ->numeric()
                                ->default(0)
                                ->required(),
                            TextInput::make('tarif_pokok')
                                ->numeric()
                                ->required(),
                            DatePicker::make('berlaku_mulai')
                                ->required()
                                ->default('2026-01-01'),
                            DatePicker::make('berlaku_sampai'),
                            Toggle::make('is_active')
                                ->default(true),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subJenisPajak.nama')
                    ->label('Sub Jenis')
                    ->badge(),
                TextColumn::make('reklame_tariffs_count')
                    ->counts('reklameTariffs')
                    ->label('Jumlah Tarif'),
                IconColumn::make('is_insidentil')
                    ->label('Insidentil')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('urutan')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            
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

    public static function getPages(): array
    {
        return [
            'index' => ListHargaPatokanReklames::route('/'),
            'create' => CreateHargaPatokanReklame::route('/create'),
            'edit' => EditHargaPatokanReklame::route('/{record}/edit'),
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