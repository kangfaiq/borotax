<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use App\Filament\Resources\JenisPajakResource\Pages\ListJenisPajaks;
use App\Filament\Resources\JenisPajakResource\Pages\CreateJenisPajak;
use App\Filament\Resources\JenisPajakResource\Pages\EditJenisPajak;
use App\Filament\Resources\JenisPajakResource\Pages;
use App\Domain\Master\Models\JenisPajak;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JenisPajakResource extends Resource
{
    protected static ?string $model = JenisPajak::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Jenis Pajak';

    protected static ?string $modelLabel = 'Jenis Pajak';

    protected static ?string $pluralModelLabel = 'Jenis Pajak';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Jenis Pajak')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('kode')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true)
                            ->placeholder('41101'),
                        TextInput::make('nama')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Pajak Hotel'),
                        TextInput::make('nama_singkat')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('Hotel'),
                        Textarea::make('deskripsi')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        TextInput::make('icon')
                            ->maxLength(50)
                            ->placeholder('🏨'),
                    ])->columns(3),

                Section::make('Konfigurasi Pajak')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('tarif_default')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(10),
                        Select::make('tipe_assessment')
                            ->required()
                            ->options([
                                'self_assessment' => 'Self Assessment',
                                'official_assessment' => 'Official Assessment',
                            ])
                            ->default('self_assessment'),
                        TextInput::make('opsen_persen')
                            ->label('Tarif Opsen (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->placeholder('25')
                            ->helperText('Kosongkan jika tidak ada opsen'),
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
                TextColumn::make('kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('icon')
                    ->label('Icon'),
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('nama_singkat')
                    ->label('Singkat')
                    ->searchable(),
                TextColumn::make('tarif_default')
                    ->label('Tarif')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('opsen_persen')
                    ->label('Opsen')
                    ->suffix('%')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('tipe_assessment')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => $state === 'self_assessment' ? 'Self' : 'Official')
                    ->color(fn(string $state): string => $state === 'self_assessment' ? 'success' : 'warning'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('urutan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('urutan')
            ->filters([
                SelectFilter::make('tipe_assessment')
                    ->options([
                        'self_assessment' => 'Self Assessment',
                        'official_assessment' => 'Official Assessment',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
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
            'index' => ListJenisPajaks::route('/'),
            'create' => CreateJenisPajak::route('/create'),
            'edit' => EditJenisPajak::route('/{record}/edit'),
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
