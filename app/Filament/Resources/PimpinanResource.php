<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\PimpinanResource\Pages\ListPimpinans;
use App\Filament\Resources\PimpinanResource\Pages\CreatePimpinan;
use App\Filament\Resources\PimpinanResource\Pages\EditPimpinan;
use App\Domain\Master\Models\Pimpinan;
use App\Filament\Resources\PimpinanResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PimpinanResource extends Resource
{
    protected static ?string $model = Pimpinan::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-circle';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Pimpinan';

    protected static ?string $modelLabel = 'Pimpinan';

    protected static ?string $pluralModelLabel = 'Pimpinan';

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', Pimpinan::class) ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', Pimpinan::class) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pimpinan')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('nip')
                            ->label('NIP')
                            ->maxLength(50),
                        TextInput::make('pangkat')
                            ->label('Pangkat / Golongan')
                            ->maxLength(255),
                        TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Informasi Organisasi')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('kab')
                            ->label('Kabupaten / Kota')
                            ->maxLength(255),
                        TextInput::make('opd')
                            ->label('OPD')
                            ->maxLength(255),
                        TextInput::make('bidang')
                            ->label('Bidang')
                            ->maxLength(255),
                        TextInput::make('sub_bidang')
                            ->label('Sub Bidang')
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('bidang')
                    ->label('Bidang')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('sub_bidang')
                    ->label('Sub Bidang')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pangkat')
                    ->label('Pangkat')
                    ->searchable(),
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('jabatan')
                    ->label('Jabatan')
                    ->options(fn () => Pimpinan::whereNotNull('jabatan')
                        ->distinct()
                        ->pluck('jabatan', 'jabatan')
                        ->toArray()
                    ),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPimpinans::route('/'),
            'create' => CreatePimpinan::route('/create'),
            'edit' => EditPimpinan::route('/{record}/edit'),
        ];
    }
}
