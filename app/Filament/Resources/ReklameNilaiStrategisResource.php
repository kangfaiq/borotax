<?php

namespace App\Filament\Resources;

use App\Domain\Reklame\Models\ReklameNilaiStrategis;
use App\Filament\Forms\Components\FilamentDecimalInput;
use App\Filament\Resources\ReklameNilaiStrategisResource\Pages\CreateReklameNilaiStrategis;
use App\Filament\Resources\ReklameNilaiStrategisResource\Pages\EditReklameNilaiStrategis;
use App\Filament\Resources\ReklameNilaiStrategisResource\Pages\ListReklameNilaiStrategis;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReklameNilaiStrategisResource extends Resource
{
    protected static ?string $model = ReklameNilaiStrategis::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Nilai Strategis Reklame';

    protected static ?string $modelLabel = 'Nilai Strategis Reklame';

    protected static ?string $pluralModelLabel = 'Nilai Strategis Reklame';

    protected static ?int $navigationSort = 6;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', ReklameNilaiStrategis::class) ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', ReklameNilaiStrategis::class) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tarif Nilai Strategis Reklame')
                ->schema([
                    Select::make('kelas_kelompok')
                        ->label('Kelas Kelompok')
                        ->options([
                            'A' => 'A',
                            'B' => 'B',
                            'C' => 'C',
                        ])
                        ->required(),
                    FilamentDecimalInput::configure(TextInput::make('luas_min')
                        ->label('Luas Minimum (m2)')
                        ->required()
                        ->minValue(0)
                        ->step(0.01)),
                    FilamentDecimalInput::configure(TextInput::make('luas_max')
                        ->label('Luas Maksimum (m2)')
                        ->minValue(0)
                        ->step(0.01)
                        ->nullable()
                        ->helperText('Kosongkan jika batas atas tidak terbatas.')),
                    FilamentDecimalInput::configure(TextInput::make('tarif_per_tahun')
                        ->label('Tarif per Tahun')
                        ->required()
                        ->minValue(0)
                        ->step(0.01)),
                    FilamentDecimalInput::configure(TextInput::make('tarif_per_bulan')
                        ->label('Tarif per Bulan')
                        ->required()
                        ->minValue(0)
                        ->step(0.01)),
                    DatePicker::make('berlaku_mulai')
                        ->required()
                        ->default('2026-01-01'),
                    DatePicker::make('berlaku_sampai'),
                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kelas_kelompok')
                    ->label('Kelas')
                    ->badge()
                    ->sortable(),
                TextColumn::make('luas_min')
                    ->label('Luas Min')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('luas_max')
                    ->label('Luas Max')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('Tanpa batas')
                    ->sortable(),
                TextColumn::make('tarif_per_tahun')
                    ->label('Tarif/Tahun')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('tarif_per_bulan')
                    ->label('Tarif/Bulan')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
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
            ->defaultSort('kelas_kelompok')
            ->filters([
                SelectFilter::make('kelas_kelompok')
                    ->label('Kelas Kelompok')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'C' => 'C',
                    ]),
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
            'index' => ListReklameNilaiStrategis::route('/'),
            'create' => CreateReklameNilaiStrategis::route('/create'),
            'edit' => EditReklameNilaiStrategis::route('/{record}/edit'),
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