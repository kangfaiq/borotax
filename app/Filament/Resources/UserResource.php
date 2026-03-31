<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages;
use App\Domain\Auth\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'User Management';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Username')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->label('Konfirmasi Password')
                            ->requiredWith('password')
                            ->dehydrated(false),
                    ])->columns(2),

                Section::make('Data Personal')
                    ->columnSpanFull()
                    ->description('Data ini akan dienkripsi saat disimpan')
                    ->schema([
                        TextInput::make('nama_lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('nik')
                            ->label('NIK')
                            ->inputMode('numeric')
                            ->maxLength(16)
                            ->minLength(16)
                            ->regex('/^[0-9]{16}$/')
                            ->unique(table: 'users', column: 'nik_hash', ignoreRecord: true),
                        TextInput::make('no_whatsapp')
                            ->label('No. WhatsApp')
                            ->tel(),
                        TextInput::make('tempat_lahir')
                            ->maxLength(100),
                        DatePicker::make('tanggal_lahir'),
                        Textarea::make('alamat')
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Role & Status')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('role')
                            ->options([
                                'admin' => 'Admin',
                                'verifikator' => 'Verifikator',
                                'petugas' => 'Petugas',
                                'wajibPajak' => 'Wajib Pajak',
                            ])
                            ->required()
                            ->default('petugas')
                            ->live(),
                        Select::make('pimpinan_id')
                            ->label('Pimpinan')
                            ->relationship('pimpinan', 'nama')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih Pimpinan')
                            ->visible(fn (Get $get) => $get('role') === 'verifikator'),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('verified'),
                        Toggle::make('must_change_password')
                            ->label('Wajib Ganti Password')
                            ->default(false),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'danger',
                        'verifikator' => 'warning',
                        'petugas' => 'info',
                        'wajibPajak' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('pimpinan.nama')
                    ->label('Pimpinan')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'verifikator' => 'Verifikator',
                        'petugas' => 'Petugas',
                        'wajibPajak' => 'Wajib Pajak',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('resetPassword')
                        ->label('Reset Password')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->schema([
                            TextInput::make('new_password')
                                ->label('Password Baru')
                                ->password()
                                ->required()
                                ->minLength(8)
                                ->confirmed(),
                            TextInput::make('new_password_confirmation')
                                ->label('Konfirmasi Password')
                                ->password()
                                ->required(),
                        ])
                        ->action(function (User $record, array $data): void {
                            $record->update([
                                'password' => Hash::make($data['new_password']),
                                'must_change_password' => true,
                                'password_changed_at' => now(),
                            ]);
                        }),
                    Action::make('toggleStatus')
                        ->label(fn(User $record): string => $record->status === 'verified' ? 'Blokir User' : 'Aktifkan User')
                        ->icon(fn(User $record): string => $record->status === 'verified' ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                        ->color(fn(User $record): string => $record->status === 'verified' ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->action(function (User $record): void {
                            $record->update([
                                'status' => $record->status === 'verified' ? 'rejected' : 'verified',
                            ]);
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('role', ['admin', 'verifikator', 'petugas'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
