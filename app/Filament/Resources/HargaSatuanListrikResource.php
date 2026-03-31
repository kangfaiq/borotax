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
use App\Filament\Resources\HargaSatuanListrikResource\Pages\ListHargaSatuanListriks;
use App\Filament\Resources\HargaSatuanListrikResource\Pages\CreateHargaSatuanListrik;
use App\Filament\Resources\HargaSatuanListrikResource\Pages\EditHargaSatuanListrik;
use App\Domain\Tax\Models\HargaSatuanListrik;
use App\Filament\Resources\HargaSatuanListrikResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HargaSatuanListrikResource extends Resource
{
    protected static ?string $model = HargaSatuanListrik::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bolt';
    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Harga Satuan Listrik';
    protected static ?string $modelLabel = 'Harga Satuan Listrik';
    protected static ?string $pluralModelLabel = 'Harga Satuan Listrik';
    protected static ?int $navigationSort = 7;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', HargaSatuanListrik::class) ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', HargaSatuanListrik::class) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Wilayah & Harga')
                ->columnSpanFull()->schema([
                TextInput::make('nama_wilayah')
                    ->label('Nama Wilayah')
                    ->required()
                    ->maxLength(150)
                    ->placeholder('Kab. Bojonegoro'),
                TextInput::make('harga_per_kwh')
                    ->label('Harga Satuan (Rp/kWh)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->placeholder('1500'),
            ])->columns(2),
            Section::make('Referensi & Status')->schema([
                TextInput::make('dasar_hukum')
                    ->label('Dasar Hukum')
                    ->maxLength(255)
                    ->placeholder('Perda Kab. Bojonegoro No ...'),
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
                TextColumn::make('nama_wilayah')
                    ->label('Wilayah')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('harga_per_kwh')
                    ->label('Harga per kWh')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable(),
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
            ->defaultSort('nama_wilayah')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
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
            'index' => ListHargaSatuanListriks::route('/'),
            'create' => CreateHargaSatuanListrik::route('/create'),
            'edit' => EditHargaSatuanListrik::route('/{record}/edit'),
        ];
    }
}
