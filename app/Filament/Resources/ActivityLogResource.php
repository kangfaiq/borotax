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
use App\Filament\Resources\ActivityLogResource\Pages\ListAutoExpireActivityLogs;
use App\Filament\Resources\ActivityLogResource\Pages\ViewActivityLog;
use App\Enums\TaxStatus;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\ActivityLogResource\Pages;
use App\Domain\Shared\Models\ActivityLog;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    public const AUTO_EXPIRE_FILTER_VALUE = 'auto_expire';

    public const QUICK_DATE_TODAY = 'today';

    public const QUICK_DATE_LAST_7_DAYS = 'last_7_days';

    public const QUICK_DATE_LAST_30_DAYS = 'last_30_days';

    public const SOURCE_STATUS_PENDING = 'pending';

    public const SOURCE_STATUS_VERIFIED = 'verified';

    public const SOURCE_STATUS_PARTIALLY_PAID = 'partially_paid';

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
                    ->formatStateUsing(fn (ActivityLog $record): string => $record->action_label)
                    ->badge()
                    ->searchable(),
                TextColumn::make('summary_count')
                    ->label('Jumlah Billing')
                    ->badge()
                    ->placeholder('-')
                    ->state(fn (ActivityLog $record): ?int => $record->auto_expire_count)
                    ->color('warning')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('target_table')
                    ->label('Target')
                    ->placeholder('-'),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('auto_expire_billing_summary')
                    ->label('Batch Billing')
                    ->state(fn (ActivityLog $record): ?string => $record->auto_expire_billing_summary)
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('auto_expire_source_status_summary')
                    ->label('Status Asal')
                    ->state(fn (ActivityLog $record): ?string => $record->auto_expire_source_status_summary)
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('auto_expire_jenis_pajak_summary')
                    ->label('Ringkasan Jenis Pajak')
                    ->state(fn (ActivityLog $record): ?string => $record->auto_expire_jenis_pajak_summary)
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('history_scope')
                    ->label('Riwayat Otomatis')
                    ->options([
                        self::AUTO_EXPIRE_FILTER_VALUE => 'Auto-Expire Billing',
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            ($data['value'] ?? null) === self::AUTO_EXPIRE_FILTER_VALUE,
                            fn ($query) => $query->where('action', ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES)
                        );
                    }),
                SelectFilter::make('source_status')
                    ->label('Status Asal')
                    ->options([
                        self::SOURCE_STATUS_PENDING => TaxStatus::Pending->getLabel(),
                        self::SOURCE_STATUS_VERIFIED => TaxStatus::Verified->getLabel(),
                        self::SOURCE_STATUS_PARTIALLY_PAID => TaxStatus::PartiallyPaid->getLabel(),
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        return $query->when(
                            filled($value),
                            fn ($query) => $query
                                ->where('action', ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES)
                                ->where('source_statuses', 'like', '%,' . $value . ',%')
                        );
                    }),
                SelectFilter::make('quick_date_range')
                    ->label('Tanggal Cepat')
                    ->options([
                        self::QUICK_DATE_TODAY => 'Hari ini',
                        self::QUICK_DATE_LAST_7_DAYS => '7 hari terakhir',
                        self::QUICK_DATE_LAST_30_DAYS => '30 hari terakhir',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            self::QUICK_DATE_TODAY => $query->whereDate('created_at', today()),
                            self::QUICK_DATE_LAST_7_DAYS => $query->where('created_at', '>=', now()->startOfDay()->subDays(6)),
                            self::QUICK_DATE_LAST_30_DAYS => $query->where('created_at', '>=', now()->startOfDay()->subDays(29)),
                            default => $query,
                        };
                    }),
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
            'auto-expire-history' => ListAutoExpireActivityLogs::route('/auto-expire-history'),
            'view' => ViewActivityLog::route('/{record}'),
        ];
    }

    public static function getAutoExpireHistoryUrl(): string
    {
        return static::getUrl('auto-expire-history');
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
