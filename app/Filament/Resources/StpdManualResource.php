<?php

namespace App\Filament\Resources;

use App\Domain\Master\Models\Pimpinan;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\Tax\Models\Tax;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Filament\Resources\StpdManualResource\Pages;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\ActionGroup;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StpdManualResource extends Resource
{
    protected static ?string $model = StpdManual::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-minus';
    protected static string | \UnitEnum | null $navigationGroup = 'Verifikasi';
    protected static ?string $navigationLabel = 'Verifikasi STPD';
    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'draft')->count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Data STPD')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_stpd')->disabled(),
                        Forms\Components\TextInput::make('tipe')
                            ->label('Tipe STPD')
                            ->formatStateUsing(fn(?string $state) => match ($state) {
                                'pokok_sanksi' => 'Pokok & Sanksi',
                                'sanksi_saja' => 'Sanksi Saja',
                                default => $state,
                            })
                            ->disabled(),
                        Forms\Components\TextInput::make('sanksi_dihitung')
                            ->label('Sanksi Dihitung')
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('pokok_belum_dibayar')
                            ->label('Pokok Belum Dibayar')
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('bulan_terlambat')
                            ->label('Bulan Terlambat')
                            ->disabled(),
                        Forms\Components\DatePicker::make('proyeksi_tanggal_bayar')
                            ->label('Proyeksi Tanggal Bayar')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_buat')
                    ->label('Tgl Buat')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax.billing_code')
                    ->label('Kode Pembayaran Aktif')
                    ->state(fn(StpdManual $record): string => $record->tax?->getPreferredPaymentCode() ?? '-')
                    ->description(fn(StpdManual $record): ?string => $record->tax?->stpd_payment_code ? 'Billing Sumber: ' . $record->tax->billing_code : null)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('tax', function (Builder $taxQuery) use ($search): Builder {
                            return $taxQuery
                                ->where('billing_code', 'like', "%{$search}%")
                                ->orWhere('stpd_payment_code', 'like', "%{$search}%");
                        });
                    })
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'pokok_sanksi' => 'Pokok & Sanksi',
                        'sanksi_saja' => 'Sanksi Saja',
                        default => $state,
                    })
                    ->color(fn(string $state) => match ($state) {
                        'pokok_sanksi' => 'danger',
                        'sanksi_saja' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('sanksi_dihitung')
                    ->label('Sanksi')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bulan_terlambat')
                    ->label('Bln Terlambat')
                    ->suffix(' bln'),
                Tables\Columns\TextColumn::make('proyeksi_tanggal_bayar')
                    ->label('Proyeksi Bayar')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('petugas_nama')
                    ->label('Petugas'),
            ])
            ->defaultSort('tanggal_buat', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft (Perlu Verifikasi)',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),
                Tables\Filters\SelectFilter::make('tipe')
                    ->options([
                        'pokok_sanksi' => 'Pokok & Sanksi',
                        'sanksi_saja' => 'Sanksi Saja',
                    ]),
            
                TrashedFilter::make(),
            ])
            ->actions([
                // Document actions
                ActionGroup::make([
                    \Filament\Actions\Action::make('cetak_stpd')
                        ->label('Cetak STPD')
                        ->icon('heroicon-o-printer')
                        ->url(fn(StpdManual $record) => route('stpd-manual.show', $record->id))
                        ->openUrlInNewTab()
                        ->visible(fn(StpdManual $record) => $record->status === 'disetujui'),
                    \Filament\Actions\Action::make('unduh_stpd')
                        ->label('Unduh STPD')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->visible(fn(StpdManual $record) => $record->status === 'disetujui')
                        ->action(function (StpdManual $record) {
                            return redirect()->route('stpd-manual.download', $record->id);
                        }),
                ])
                    ->icon('heroicon-m-document-text')
                    ->visible(fn(StpdManual $record) => $record->status === 'disetujui'),

                // Detail view
                \Filament\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Detail STPD')
                    ->modalContent(function (StpdManual $record) {
                        $record->load('tax.jenisPajak', 'tax.taxObject');
                        $tax = $record->tax;
                        $wp = $tax ? WajibPajak::where('user_id', $tax->user_id)->first() : null;

                        return view('filament.resources.stpd-manual.detail', [
                            'record' => $record,
                            'tax' => $tax,
                            'wp' => $wp,
                        ]);
                    })
                    ->modalWidth('2xl'),

                // Approve
                \Filament\Actions\Action::make('approve')
                    ->label('Setujui & Terbitkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(StpdManual $record) => $record->status === 'draft' && (auth()->user()?->can('verify', $record) ?? false))
                    ->authorize(fn(StpdManual $record) => auth()->user()?->can('verify', $record) ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Terbitkan STPD?')
                    ->modalDescription('Aksi ini akan men-generate nomor STPD resmi dan meng-update data billing terkait.')
                    ->action(function (StpdManual $record): void {
                        $nomorStpd = StpdManual::generateNomorStpd();

                        $pimpinan = auth()->user()->pimpinan_id
                            ? Pimpinan::find(auth()->user()->pimpinan_id)
                            : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

                        $record->update([
                            'status' => 'disetujui',
                            'nomor_stpd' => $nomorStpd,
                            'tanggal_verifikasi' => now(),
                            'verifikator_id' => auth()->id(),
                            'verifikator_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                            'pimpinan_id' => $pimpinan?->id,
                        ]);

                        // Opsi C: Sync stpd_number ke tabel taxes
                        $tax = $record->tax;
                        if ($tax) {
                            $tax->syncApprovedManualStpd($record);
                        }

                        Notification::make()
                            ->title('STPD Diterbitkan')
                            ->body("Nomor: {$nomorStpd}")
                            ->success()
                            ->send();
                    }),

                // Reject
                \Filament\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(StpdManual $record) => $record->status === 'draft' && (auth()->user()?->can('verify', $record) ?? false))
                    ->authorize(fn(StpdManual $record) => auth()->user()?->can('verify', $record) ?? false)
                    ->schema([
                        Forms\Components\Textarea::make('catatan_verifikasi')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (StpdManual $record, array $data): void {
                        $record->update([
                            'status' => 'ditolak',
                            'catatan_verifikasi' => $data['catatan_verifikasi'],
                            'tanggal_verifikasi' => now(),
                            'verifikator_id' => auth()->id(),
                            'verifikator_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                        ]);

                        Notification::make()
                            ->title('STPD Ditolak')
                            ->danger()
                            ->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkAction::make('bulk_approve')
                    ->label('Setujui Terpilih')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn() => auth()->user()?->hasRole(['admin', 'verifikator']) ?? false)
                    ->authorize(fn() => auth()->user()?->hasRole(['admin', 'verifikator']) ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Setujui STPD Terpilih?')
                    ->modalDescription('Semua STPD draft yang dipilih akan diterbitkan.')
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records): void {
                        $draftRecords = $records
                            ->where('status', 'draft')
                            ->filter(fn (StpdManual $record) => auth()->user()?->can('verify', $record) ?? false);

                        if ($draftRecords->isEmpty()) {
                            Notification::make()
                                ->title('Tidak ada STPD draft yang dapat diverifikasi')
                                ->body('Dokumen yang Anda buat sendiri tidak dapat diverifikasi oleh akun yang sama.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $count = $draftRecords->count();

                        DB::transaction(function () use ($draftRecords) {
                            $pimpinan = auth()->user()->pimpinan_id
                                ? Pimpinan::find(auth()->user()->pimpinan_id)
                                : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

                            foreach ($draftRecords as $record) {
                                $nomorStpd = StpdManual::generateNomorStpd();

                                $record->update([
                                    'status' => 'disetujui',
                                    'nomor_stpd' => $nomorStpd,
                                    'tanggal_verifikasi' => now(),
                                    'verifikator_id' => auth()->id(),
                                    'verifikator_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                                    'pimpinan_id' => $pimpinan?->id,
                                ]);

                                // Sync to taxes
                                $tax = $record->tax;
                                if ($tax) {
                                    $tax->syncApprovedManualStpd($record);
                                }
                            }
                        });

                        Notification::make()
                            ->title("Berhasil menyetujui {$count} STPD")
                            ->success()
                            ->send();
                    }),
                \Filament\Actions\BulkAction::make('bulk_reject')
                    ->label('Tolak Terpilih')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn() => auth()->user()?->hasRole(['admin', 'verifikator']) ?? false)
                    ->authorize(fn() => auth()->user()?->hasRole(['admin', 'verifikator']) ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Tolak STPD Terpilih?')
                    ->schema([
                        Forms\Components\Textarea::make('catatan_verifikasi')
                            ->label('Catatan Penolakan')
                            ->required(),
                    ])
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, array $data): void {
                        $draftRecords = $records
                            ->where('status', 'draft')
                            ->filter(fn (StpdManual $record) => auth()->user()?->can('verify', $record) ?? false);

                        if ($draftRecords->isEmpty()) {
                            Notification::make()
                                ->title('Tidak ada STPD draft yang dapat diverifikasi')
                                ->body('Dokumen yang Anda buat sendiri tidak dapat diverifikasi oleh akun yang sama.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $count = $draftRecords->count();

                        DB::transaction(function () use ($draftRecords, $data) {
                            foreach ($draftRecords as $record) {
                                $record->update([
                                    'status' => 'ditolak',
                                    'catatan_verifikasi' => $data['catatan_verifikasi'],
                                    'tanggal_verifikasi' => now(),
                                    'verifikator_id' => auth()->id(),
                                    'verifikator_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                                ]);
                            }
                        });

                        Notification::make()
                            ->title("Berhasil menolak {$count} STPD")
                            ->danger()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStpdManuals::route('/'),
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
