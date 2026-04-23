<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\FilamentDecimalInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\HargaPatokanMblbResource\Pages\ListHargaPatokanMblbs;
use App\Filament\Resources\HargaPatokanMblbResource\Pages\CreateHargaPatokanMblb;
use App\Filament\Resources\HargaPatokanMblbResource\Pages\EditHargaPatokanMblb;
use App\Domain\Tax\Models\HargaPatokanMblb;
use App\Filament\Resources\HargaPatokanMblbResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

class HargaPatokanMblbResource extends Resource
{
    protected static ?string $model = HargaPatokanMblb::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Harga Patokan MBLB';
    protected static ?string $modelLabel = 'Harga Patokan MBLB';
    protected static ?string $pluralModelLabel = 'Harga Patokan MBLB';
    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', HargaPatokanMblb::class) ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', HargaPatokanMblb::class) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Mineral')
                ->columnSpanFull()->schema([
                TextInput::make('nama_mineral')
                    ->label('Nama Mineral')
                    ->required()
                    ->maxLength(150)
                    ->placeholder('Pasir Pasang'),
                TagsInput::make('nama_alternatif')
                    ->label('Nama Alternatif')
                    ->placeholder('Tambah nama alternatif...')
                    ->helperText('Nama mineral lain yang kena tarif sama'),
                FilamentDecimalInput::configure(TextInput::make('harga_patokan')
                    ->label('Harga Patokan (Rp)')
                    ->required()
                    ->minValue(0)
                    ->placeholder('100000')),
                TextInput::make('satuan')
                    ->label('Satuan')
                    ->default('m3')
                    ->maxLength(20),
            ])->columns(2),
            Section::make('Referensi & Status')->columnSpanFull()->schema([
                TextInput::make('dasar_hukum')
                    ->label('Dasar Hukum')
                    ->maxLength(255)
                    ->placeholder('Kepgub Jatim No ...'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_mineral')
                    ->label('Nama Mineral')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_alternatif')
                    ->label('Nama Alternatif')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->nama_alternatif ?? []),
                TextColumn::make('harga_patokan')
                    ->label('Harga Patokan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('satuan')
                    ->label('Satuan'),
                TextColumn::make('dasar_hukum')
                    ->label('Dasar Hukum')
                    ->limit(40)
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nama_mineral')
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
            'index' => ListHargaPatokanMblbs::route('/'),
            'create' => CreateHargaPatokanMblb::route('/create'),
            'edit' => EditHargaPatokanMblb::route('/{record}/edit'),
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
