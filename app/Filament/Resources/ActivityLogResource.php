<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\ViewAction;
use App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs;
use App\Filament\Resources\ActivityLogResource\Pages\ViewActivityLog;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\ActivityLogResource\Pages;
use App\Domain\Shared\Models\ActivityLog;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string | \UnitEnum | null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Activity Log';

    protected static ?string $modelLabel = 'Activity Log';

    protected static ?string $pluralModelLabel = 'Activity Logs';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Aktivitas')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('action')
                            ->label('Aksi')
                            ->disabled(),
                        TextInput::make('actor_type')
                            ->label('Tipe Aktor')
                            ->disabled(),
                        TextInput::make('actor.name')
                            ->label('Aktor')
                            ->disabled(),
                        TextInput::make('target_table')
                            ->label('Target Tabel')
                            ->disabled(),
                        TextInput::make('target_id')
                            ->label('Target ID')
                            ->disabled(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->disabled()
                            ->columnSpanFull(),
                        TextInput::make('ip_address')
                            ->label('IP Address')
                            ->disabled(),
                        Textarea::make('user_agent')
                            ->label('User Agent')
                            ->disabled()
                            ->columnSpanFull(),
                        DateTimePicker::make('created_at')
                            ->label('Waktu')
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('actor.name')
                    ->label('Aktor')
                    ->placeholder('Sistem')
                    ->searchable(),
                TextColumn::make('actor_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'danger',
                        'user' => 'info',
                        'system' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->searchable(),
                TextColumn::make('target_table')
                    ->label('Target')
                    ->placeholder('-'),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('actor_type')
                    ->label('Tipe Aktor')
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'User',
                        'system' => 'System',
                    ]),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Dari'),
                        DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($query) => $query->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($query) => $query->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
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
            'index' => ListActivityLogs::route('/'),
            'view' => ViewActivityLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
