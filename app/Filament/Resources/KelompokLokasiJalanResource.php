<?php

namespace App\Filament\Resources;

use App\Domain\Reklame\Models\KelompokLokasiJalan;
use App\Filament\Resources\KelompokLokasiJalanResource\Pages\CreateKelompokLokasiJalan;
use App\Filament\Resources\KelompokLokasiJalanResource\Pages\EditKelompokLokasiJalan;
use App\Filament\Resources\KelompokLokasiJalanResource\Pages\ListKelompokLokasiJalans;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KelompokLokasiJalanResource extends Resource
{
    protected static ?string $model = KelompokLokasiJalan::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map';

    protected static string | \UnitEnum | null $navigationGroup = 'Reklame';

    protected static ?string $navigationLabel = 'Kelompok Lokasi Jalan';

    protected static ?string $modelLabel = 'Kelompok Lokasi Jalan';

    protected static ?string $pluralModelLabel = 'Kelompok Lokasi Jalan';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', KelompokLokasiJalan::class) ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', KelompokLokasiJalan::class) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kelompok Lokasi Jalan')
                ->schema([
                    Select::make('kelompok')
                        ->options(KelompokLokasiJalan::getKelompokOptions())
                        ->required(),
                    TextInput::make('nama_jalan')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('deskripsi')
                        ->maxLength(255),
                    DatePicker::make('berlaku_mulai')
                        ->required()
                        ->default('2026-01-01'),
                    DatePicker::make('berlaku_sampai'),
                    Toggle::make('is_active')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kelompok')
                    ->badge()
                    ->sortable(),
                TextColumn::make('nama_jalan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('deskripsi')
                    ->limit(40),
                TextColumn::make('berlaku_mulai')
                    ->label('Berlaku Mulai')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('berlaku_sampai')
                    ->label('Berlaku Sampai')
                    ->date('d/m/Y')
                    ->placeholder('Seterusnya')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('kelompok')
            ->filters([
                SelectFilter::make('kelompok')
                    ->options(KelompokLokasiJalan::getKelompokOptions()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKelompokLokasiJalans::route('/'),
            'create' => CreateKelompokLokasiJalan::route('/create'),
            'edit' => EditKelompokLokasiJalan::route('/{record}/edit'),
        ];
    }
}