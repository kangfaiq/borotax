<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\HargaPatokanSarangWaletResource\Pages\ListHargaPatokanSarangWalets;
use App\Filament\Resources\HargaPatokanSarangWaletResource\Pages\CreateHargaPatokanSarangWalet;
use App\Filament\Resources\HargaPatokanSarangWaletResource\Pages\EditHargaPatokanSarangWalet;
use App\Domain\Tax\Models\HargaPatokanSarangWalet;
use App\Filament\Resources\HargaPatokanSarangWaletResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

class HargaPatokanSarangWaletResource extends Resource
{
    protected static ?string $model = HargaPatokanSarangWalet::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Harga Patokan Sarang Walet';
    protected static ?string $modelLabel = 'Harga Patokan Sarang Walet';
    protected static ?string $pluralModelLabel = 'Harga Patokan Sarang Walet';
    protected static ?int $navigationSort = 6;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', HargaPatokanSarangWalet::class) ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', HargaPatokanSarangWalet::class) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Jenis Sarang')
                ->columnSpanFull()->schema([
                TextInput::make('nama_jenis')
                    ->label('Nama Jenis Sarang')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Mangkuk'),
                TextInput::make('harga_patokan')
                    ->label('Harga Patokan (Rp/kg)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->placeholder('6000000'),
                TextInput::make('satuan')
                    ->label('Satuan')
                    ->default('kg')
                    ->maxLength(20),
            ])->columns(2),
            Section::make('Referensi & Status')->schema([
                TextInput::make('dasar_hukum')
                    ->label('Dasar Hukum')
                    ->maxLength(255)
                    ->placeholder('Perda Kab. Bojonegoro No 8 Tahun 2025'),
                DatePicker::make('berlaku_mulai')
                    ->label('Berlaku Mulai')
                    ->required(),
                DatePicker::make('berlaku_sampai')
                    ->label('Berlaku Sampai')
                    ->helperText('Kosongkan jika masih berlaku'),
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
                TextColumn::make('nama_jenis')
                    ->label('Jenis Sarang')
                    ->searchable()
                    ->sortable(),
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
                TextColumn::make('berlaku_mulai')
                    ->label('Berlaku Mulai')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('berlaku_sampai')
                    ->label('Berlaku Sampai')
                    ->date('d/m/Y')
                    ->placeholder('Sekarang')
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
            ->defaultSort('nama_jenis')
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
            'index' => ListHargaPatokanSarangWalets::route('/'),
            'create' => CreateHargaPatokanSarangWalet::route('/create'),
            'edit' => EditHargaPatokanSarangWalet::route('/{record}/edit'),
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
