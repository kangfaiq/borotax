<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembetulanRequestResource\Pages;
use App\Domain\Tax\Models\PembetulanRequest;
use App\Domain\Tax\Models\Tax;
use App\Enums\TaxStatus;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Domain\Shared\Services\NotificationService;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

class PembetulanRequestResource extends Resource
{
    protected static ?string $model = PembetulanRequest::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-pencil-square';

    protected static string | \UnitEnum | null $navigationGroup = 'Verifikasi';

    protected static ?string $navigationLabel = 'Permintaan Pembetulan';

    protected static ?string $modelLabel = 'Permintaan Pembetulan';

    protected static ?string $pluralModelLabel = 'Permintaan Pembetulan';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['admin', 'petugas']) ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['pending', 'diproses'])->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.nama_lengkap')
                    ->label('Wajib Pajak')
                    ->searchable()
                    ->description(fn(PembetulanRequest $record) => $record->user?->nik ?? ''),
                Tables\Columns\TextColumn::make('tax.billing_code')
                    ->label('Billing Sumber')
                    ->fontFamily('mono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tax.taxObject.nama_objek_pajak')
                    ->label('Objek Pajak')
                    ->limit(30),
                Tables\Columns\TextColumn::make('tax_period')
                    ->label('Masa Pajak')
                    ->state(
                        fn(PembetulanRequest $record) =>
                        $record->tax
                        ? Carbon::create($record->tax->masa_pajak_tahun, $record->tax->masa_pajak_bulan, 1)->translatedFormat('F Y')
                        : '-'
                    ),
                Tables\Columns\TextColumn::make('tax.status')
                    ->label('Status Billing Sumber')
                    ->badge()
                    ->color(fn(TaxStatus|string|null $state): string => match ($state instanceof TaxStatus ? $state->value : $state) {
                        'pending' => 'warning',
                        'paid', 'verified' => 'success',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(TaxStatus|string|null $state): string => match ($state instanceof TaxStatus ? $state->value : $state) {
                        'pending' => 'Belum Dibayar',
                        'paid' => 'Sudah Dibayar',
                        'verified' => 'Terverifikasi',
                        'cancelled' => 'Dibatalkan',
                        default => ($state instanceof TaxStatus ? $state->value : $state) ?? '-',
                    }),
                Tables\Columns\TextColumn::make('omzet_baru')
                    ->label('Omzet Koreksi')
                    ->money('IDR', 0)
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'diproses' => 'info',
                        'selesai' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'pending' => 'Menunggu',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
                    ]),
            
                TrashedFilter::make(),
            ])
            ->actions([
                // Lihat detail alasan
                \Filament\Actions\Action::make('lihatAlasan')
                    ->label('Lihat Alasan')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Detail Permohonan Pembetulan')
                    ->modalContent(
                        fn(PembetulanRequest $record) =>
                        view('filament.components.pembetulan-detail', ['record' => $record])
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                // Proses — tandai sedang diproses
                \Filament\Actions\Action::make('proses')
                    ->label('Proses')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn(PembetulanRequest $record) => $record->status === 'pending' && auth()->user()->can('review', $record))
                    ->requiresConfirmation()
                    ->modalHeading('Proses Permohonan')
                    ->modalDescription('Tandai permohonan ini sedang diproses oleh Anda.')
                    ->action(function (PembetulanRequest $record): void {
                        $record->update([
                            'status' => 'diproses',
                            'processed_by' => auth()->id(),
                            'processed_at' => now(),
                        ]);

                        // Notify WP: permintaan sedang diproses
                        if ($record->user) {
                            NotificationService::notifyUserBoth(
                                $record->user,
                                'Pembetulan Billing Sedang Diproses',
                                'Permohonan pembetulan billing Anda sedang diproses oleh petugas.',
                                'info'
                            );
                        }

                        Notification::make()
                            ->title('Permohonan ditandai sedang diproses')
                            ->success()
                            ->send();
                    }),

                // Selesai — otomatis buat billing pembetulan
                \Filament\Actions\Action::make('selesai')
                    ->label('Setujui & Buat Billing')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(PembetulanRequest $record) => in_array($record->status, ['pending', 'diproses']) && auth()->user()->can('review', $record))
                    ->schema([
                        Forms\Components\Textarea::make('catatan_petugas')
                            ->label('Catatan Petugas')
                            ->placeholder('Isi catatan atas pembetulan ini (opsional)')
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Setujui & Buat Billing Pembetulan')
                    ->modalDescription('Permohonan akan disetujui dan billing pembetulan otomatis dibuat.')
                    ->action(function (PembetulanRequest $record, array $data): void {
                        $tax = $record->tax()->with(['jenisPajak', 'taxObject'])->first();

                        if (!$tax) {
                            Notification::make()
                                ->title('Gagal: Data billing original tidak ditemukan')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            DB::transaction(function () use ($record, $tax, $data) {
                                $petugasName = auth()->user()->nama_lengkap ?? auth()->user()->name;
                                $omzet = $record->omzet_baru ? (float) $record->omzet_baru : (float) $tax->omzet;
                                $tarifPersen = (float) $tax->tarif_persentase;
                                $taxAmount = $omzet * ($tarifPersen / 100);

                                $pembetulanKe = 0;
                                $parentTaxId = null;
                                $notesPrefix = '';

                                if ($tax->status === TaxStatus::Paid) {
                                    // Billing sudah dibayar → buat billing tambahan
                                    $pembetulanKe = (int) $tax->pembetulan_ke + 1;
                                    $parentTaxId = $tax->id;
                                    $notesPrefix = "Pembetulan ke-{$pembetulanKe} atas billing {$tax->billing_code}. ";
                                } else {
                                    // Billing belum dibayar → cancel lama, buat pengganti
                                    $tax->update([
                                        'status' => TaxStatus::Cancelled,
                                        'notes' => ($tax->notes ? $tax->notes . ' | ' : '')
                                            . "Dibatalkan untuk pembetulan oleh petugas: {$petugasName} pada " . now()->format('d/m/Y H:i'),
                                    ]);
                                    $notesPrefix = "Pengganti billing {$tax->billing_code}. ";
                                }

                                $jenisPajak = $tax->jenisPajak;
                                $billingCode = Tax::generateBillingCode($jenisPajak->kode ?? '41102');

                                $newTax = Tax::create([
                                    'jenis_pajak_id' => $tax->jenis_pajak_id,
                                    'sub_jenis_pajak_id' => $tax->sub_jenis_pajak_id,
                                    'tax_object_id' => $tax->tax_object_id,
                                    'user_id' => $tax->user_id,
                                    'amount' => $taxAmount,
                                    'omzet' => $omzet,
                                    'tarif_persentase' => $tarifPersen,
                                    'status' => TaxStatus::Pending,
                                    'billing_code' => $billingCode,
                                    'payment_expired_at' => Tax::hitungJatuhTempoSelfAssessment(
                                        $tax->masa_pajak_bulan,
                                        $tax->masa_pajak_tahun
                                    ),
                                    'masa_pajak_bulan' => $tax->masa_pajak_bulan,
                                    'masa_pajak_tahun' => $tax->masa_pajak_tahun,
                                    'pembetulan_ke' => $pembetulanKe,
                                    'parent_tax_id' => $parentTaxId,
                                    'notes' => $notesPrefix
                                        . "Dari permohonan pembetulan WP. Alasan: {$record->alasan}. "
                                        . "Disetujui oleh: {$petugasName}",
                                ]);

                                // Update request status
                                $record->update([
                                    'status' => 'selesai',
                                    'catatan_petugas' => ($data['catatan_petugas'] ?? '')
                                        . ($data['catatan_petugas'] ? ' | ' : '')
                                        . "Billing pembetulan dibuat: {$billingCode}",
                                    'processed_by' => auth()->id(),
                                    'processed_at' => now(),
                                ]);
                            });

                            Notification::make()
                                ->title('Permohonan disetujui & billing pembetulan berhasil dibuat')
                                ->success()
                                ->send();

                            // Notify WP: pembetulan selesai, billing baru dibuat
                            $wpUser = $record->user;
                            if ($wpUser) {
                                NotificationService::notifyUserBoth(
                                    $wpUser,
                                    'Pembetulan Billing Selesai',
                                    'Permohonan pembetulan billing Anda telah disetujui. Billing baru telah dibuat, silakan cek di riwayat transaksi.',
                                    'payment'
                                );
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal membuat billing pembetulan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Tolak
                \Filament\Actions\Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(PembetulanRequest $record) => in_array($record->status, ['pending', 'diproses']) && auth()->user()->can('review', $record))
                    ->schema([
                        Forms\Components\Textarea::make('catatan_petugas')
                            ->label('Alasan Penolakan')
                            ->placeholder('Jelaskan mengapa permohonan ditolak')
                            ->required()
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Permohonan')
                    ->modalDescription('Permohonan pembetulan akan ditolak.')
                    ->action(function (PembetulanRequest $record, array $data): void {
                        $record->update([
                            'status' => 'ditolak',
                            'catatan_petugas' => $data['catatan_petugas'],
                            'processed_by' => auth()->id(),
                            'processed_at' => now(),
                        ]);

                        // Notify WP: pembetulan ditolak
                        if ($record->user) {
                            NotificationService::notifyUserBoth(
                                $record->user,
                                'Pembetulan Billing Ditolak',
                                'Permohonan pembetulan billing Anda ditolak. Alasan: ' . $data['catatan_petugas'],
                                'verification'
                            );
                        }

                        Notification::make()
                            ->title('Permohonan ditolak')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembetulanRequests::route('/'),
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
