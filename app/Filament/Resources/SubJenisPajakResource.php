<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\FilamentDecimalInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\SubJenisPajakResource\Pages\ListSubJenisPajaks;
use App\Filament\Resources\SubJenisPajakResource\Pages\CreateSubJenisPajak;
use App\Filament\Resources\SubJenisPajakResource\Pages\EditSubJenisPajak;
use App\Filament\Resources\SubJenisPajakResource\Pages;
use App\Domain\Master\Models\SubJenisPajak;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubJenisPajakResource extends Resource
{
    protected static ?string $model = SubJenisPajak::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Sub Jenis Pajak';

    protected static ?string $modelLabel = 'Sub Jenis Pajak';

    protected static ?string $pluralModelLabel = 'Sub Jenis Pajak';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Sub Jenis Pajak')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('jenis_pajak_id')
                            ->label('Jenis Pajak')
                            ->relationship('jenisPajak', 'nama')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('kode')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('HOTEL_BINTANG'),
                        TextInput::make('nama')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Hotel Bintang'),
                        TextInput::make('nama_lengkap')
                            ->maxLength(255)
                            ->placeholder('Pajak Hotel Bintang'),
                        Textarea::make('deskripsi')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        TextInput::make('icon')
                            ->maxLength(50)
                            ->placeholder('⭐'),
                    ])->columns(2),

                Section::make('Konfigurasi Tarif')
                    ->columnSpanFull()
                    ->schema([
                        FilamentDecimalInput::configure(TextInput::make('tarif_persen')
                            ->required()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(10)),
                        Toggle::make('is_insidentil')
                            ->label('Insidentil')
                            ->helperText('Aktifkan jika sub jenis pajak ini bersifat insidentil')
                            ->default(false),
                        TextInput::make('urutan')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jenisPajak.nama_singkat')
                    ->label('Jenis Pajak')
                    ->badge()
                    ->sortable(),
                TextColumn::make('kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('icon')
                    ->label('Icon'),
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('tarif_persen')
                    ->label('Tarif')
                    ->suffix('%')
                    ->sortable(),
                IconColumn::make('is_insidentil')
                    ->label('Insidentil')
                    ->boolean()
                    ->trueIcon('heroicon-o-clock')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('urutan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('urutan')
            ->filters([
                SelectFilter::make('jenis_pajak_id')
                    ->label('Jenis Pajak')
                    ->relationship('jenisPajak', 'nama'),
                TernaryFilter::make('is_insidentil')
                    ->label('Insidentil'),
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
                ]),
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
            'index' => ListSubJenisPajaks::route('/'),
            'create' => CreateSubJenisPajak::route('/create'),
            'edit' => EditSubJenisPajak::route('/{record}/edit'),
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
