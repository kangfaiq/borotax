<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\NpaAirTanahResource\Pages\ListNpaAirTanahs;
use App\Filament\Resources\NpaAirTanahResource\Pages\CreateNpaAirTanah;
use App\Filament\Resources\NpaAirTanahResource\Pages\EditNpaAirTanah;
use App\Filament\Resources\NpaAirTanahResource\Pages;
use App\Domain\AirTanah\Models\NpaAirTanah;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

class NpaAirTanahResource extends Resource
{
    protected static ?string $model = NpaAirTanah::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Tarif NPA Air Tanah';
    protected static ?string $modelLabel = 'NPA Air Tanah';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make('Informasi Kategori')
                        ->columnSpanFull()->schema([
                        TextInput::make('kelompok_pemakaian')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('kriteria_sda')
                            ->required()
                            ->maxLength(100)
                            ->label('Kriteria Sumber Daya Alam'),
                    ])->columns(2),

                    Section::make('Masa Berlaku & Legalitas')
                        ->columnSpanFull()->schema([
                        DatePicker::make('berlaku_mulai')
                            ->required(),
                        DatePicker::make('berlaku_sampai'),
                        TextInput::make('dasar_hukum')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),
                ])->columnSpan(['lg' => 2]),

                Group::make()->schema([
                    Section::make('Tarif Progresif (Tiers)')
                        ->columnSpanFull()->schema([
                        Repeater::make('npa_tiers')
                            ->schema([
                                TextInput::make('min_vol')
                                    ->label('Batas Bawah (m³)')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('max_vol')
                                    ->label('Batas Atas (m³)')
                                    ->numeric()
                                    ->helperText('Kosongkan untuk tak terhingga.'),
                                TextInput::make('npa')
                                    ->label('Tarif NPA (Rp)')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->reorderable(true)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => isset($state['min_vol']) ? "Tier: {$state['min_vol']} - " . ($state['max_vol'] ?? '∞') : null),
                    ]),
                ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kelompok_pemakaian')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kriteria_sda')
                    ->label('Kriteria SDA')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('berlaku_mulai')
                    ->date()
                    ->sortable(),
                TextColumn::make('berlaku_sampai')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
            
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNpaAirTanahs::route('/'),
            'create' => CreateNpaAirTanah::route('/create'),
            'edit' => EditNpaAirTanah::route('/{record}/edit'),
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
