<?php

namespace App\Filament\Resources;

use App\Domain\Master\Models\Pimpinan;
use App\Domain\Retribusi\Models\SkrdSewaRetribusi;
use App\Domain\Tax\Models\Tax;
use App\Enums\TaxStatus;
use App\Filament\Resources\SkrdSewaRetribusiResource\Pages;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class SkrdSewaRetribusiResource extends Resource
{
    protected static ?string $model = SkrdSewaRetribusi::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static string | \UnitEnum | null $navigationGroup = 'Verifikasi';

    protected static ?string $navigationLabel = 'Verifikasi SKRD Sewa Tanah';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'draft')->count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Data SKRD')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\TextInput::make('nomor_skrd')->disabled(),
                        Forms\Components\TextInput::make('nama_wajib_pajak')->disabled(),
                        Forms\Components\TextInput::make('nama_objek')->disabled(),
                        Forms\Components\TextInput::make('alamat_objek')->disabled(),
                        Forms\Components\TextInput::make('luas_m2')->label('Luas (m²)')->disabled(),
                        Forms\Components\TextInput::make('jumlah_reklame')->label('Jumlah Reklame')->disabled(),
                        Forms\Components\TextInput::make('tarif_nominal')
                            ->label('Harga Sub Jenis')
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('tarif_pajak_persen')->label('Tarif Retribusi (%)')->disabled(),
                        Forms\Components\TextInput::make('durasi')->label('Durasi')->disabled(),
                        Forms\Components\TextInput::make('satuan_label')->label('Satuan Waktu')->disabled(),
                        Forms\Components\TextInput::make('jumlah_retribusi')
                            ->label('Total Retribusi')
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\DatePicker::make('jatuh_tempo')
                            ->label('Tanggal Jatuh Tempo')
                            ->disabled()
                            ->helperText('Otomatis dihitung: masa_berlaku_mulai + 1 bulan - 1 hari'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_buat')
                    ->label('Tgl Buat')
                    ->dateTime('d/m/Y'),
                Tables\Columns\TextColumn::make('nomor_skrd')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_wajib_pajak')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subJenisPajak.nama')
                    ->label('Sub Jenis'),
                Tables\Columns\TextColumn::make('luas_m2')
                    ->label('Luas (m²)')
                    ->numeric(2)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('jumlah_reklame')
                    ->label('Jml Reklame')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('jumlah_retribusi')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (?string $state): string => $state && now()->gt($state) ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('petugas_nama')
                    ->label('Petugas'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft (Perlu Verifikasi)',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),
                TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\Action::make('cetak_skrd')
                        ->label('Cetak SKRD')
                        ->icon('heroicon-o-printer')
                        ->url(fn (SkrdSewaRetribusi $record) => route('skrd-sewa.show', $record->id))
                        ->openUrlInNewTab(),
                    Actions\Action::make('unduh_skrd')
                        ->label('Unduh SKRD')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (SkrdSewaRetribusi $record) {
                            $record->load(['jenisPajak', 'subJenisPajak']);

                            $pimpinan = $record->pimpinan_id
                                ? Pimpinan::find($record->pimpinan_id)
                                : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

                            $pdf = Pdf::loadView('documents.skrd-sewa-tanah', [
                                'skrd' => $record,
                                'pimpinan' => $pimpinan,
                                'isPdf' => true,
                            ]);

                            $pdf->setPaper([0, 0, 609.449, 935.433], 'portrait');

                            $filename = 'SKRD_SewaTanah_' . str_replace([' ', '/'], '_', $record->nomor_skrd) . '.pdf';

                            return response()->streamDownload(fn () => print($pdf->output()), $filename, [
                                'Content-Type' => 'application/pdf',
                            ]);
                        }),
                ])
                    ->icon('heroicon-m-document-text')
                    ->visible(fn (SkrdSewaRetribusi $record) => in_array($record->status, ['draft', 'disetujui'])),
                Actions\Action::make('approve')
                    ->label('Setujui & Terbitkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (SkrdSewaRetribusi $record) => $record->status === 'draft' && static::canVerify($record))
                    ->requiresConfirmation()
                    ->modalHeading('Terbitkan SKRD?')
                    ->modalDescription('Aksi ini akan menerbitkan Kode Pembayaran Aktif dan mengubah status menjadi Disetujui.')
                    ->action(function (SkrdSewaRetribusi $record): void {
                        $record->loadMissing('jenisPajak');
                        $billingKode = $record->jenisPajak->getBillingKode();
                        $billing = Tax::generateBillingCode($billingKode);
                        $noSkrd = SkrdSewaRetribusi::generateNomorSkrd();

                        $jatuhTempo = SkrdSewaRetribusi::hitungJatuhTempoReklame($record->masa_berlaku_mulai);

                        $pimpinan = auth()->user()->pimpinan_id
                            ? Pimpinan::find(auth()->user()->pimpinan_id)
                            : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

                        $record->update([
                            'status' => 'disetujui',
                            'nomor_skrd' => $noSkrd,
                            'kode_billing' => $billing,
                            'jatuh_tempo' => $jatuhTempo,
                            'tanggal_verifikasi' => now(),
                            'verifikator_id' => auth()->id(),
                            'verifikator_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                            'pimpinan_id' => $pimpinan?->id,
                        ]);

                        Tax::create([
                            'jenis_pajak_id' => $record->jenis_pajak_id,
                            'sub_jenis_pajak_id' => $record->sub_jenis_pajak_id,
                            'user_id' => $record->petugas_id,
                            'amount' => $record->jumlah_retribusi,
                            'omzet' => $record->jumlah_retribusi,
                            'tarif_persentase' => 0,
                            'status' => TaxStatus::Verified,
                            'billing_code' => $billing,
                            'skpd_number' => $noSkrd,
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('SKRD Diterbitkan')
                            ->body("Nomor: {$noSkrd}, Kode Pembayaran Aktif: {$billing}")
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (SkrdSewaRetribusi $record) => $record->status === 'draft' && static::canVerify($record))
                    ->schema([
                        Forms\Components\Textarea::make('catatan_verifikasi')->required(),
                    ])
                    ->action(function (SkrdSewaRetribusi $record, array $data): void {
                        $record->update([
                            'status' => 'ditolak',
                            'catatan_verifikasi' => $data['catatan_verifikasi'],
                            'verifikator_id' => auth()->id(),
                            'verifikator_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                        ]);

                        Notification::make()
                            ->title('SKRD Ditolak')
                            ->danger()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Actions\BulkAction::make('bulk_approve')
                    ->label('Setujui Terpilih')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn () => auth()->user()?->hasRole(['admin', 'verifikator']) ?? false)
                    ->authorize(fn () => auth()->user()?->hasRole(['admin', 'verifikator']) ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Setujui SKRD Terpilih?')
                    ->modalDescription('Semua SKRD draft yang dipilih akan diterbitkan dengan Kode Pembayaran Aktif masing-masing.')
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records): void {
                        $draftRecords = $records
                            ->where('status', 'draft')
                            ->filter(fn (SkrdSewaRetribusi $record) => static::canVerify($record));

                        if ($draftRecords->isEmpty()) {
                            Notification::make()
                                ->title('Tidak ada SKRD draft yang dapat diverifikasi')
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

                            $tahun = date('Y');
                            $bulan = date('m');
                            $baseCount = SkrdSewaRetribusi::whereYear('tanggal_buat', $tahun)
                                ->whereMonth('tanggal_buat', $bulan)
                                ->count();

                            $seq = 0;
                            foreach ($draftRecords as $record) {
                                $seq++;
                                $record->loadMissing('jenisPajak');
                                $billingKode = $record->jenisPajak->getBillingKode();
                                $billing = Tax::generateBillingCode($billingKode);

                                $number = str_pad($baseCount + $seq, 6, '0', STR_PAD_LEFT);
                                $noSkrd = "SKRD/{$tahun}/{$bulan}/{$number}";

                                $jatuhTempo = SkrdSewaRetribusi::hitungJatuhTempoReklame($record->masa_berlaku_mulai);

                                $record->update([
                                    'status' => 'disetujui',
                                    'nomor_skrd' => $noSkrd,
                                    'kode_billing' => $billing,
                                    'jatuh_tempo' => $jatuhTempo,
                                    'tanggal_verifikasi' => now(),
                                    'verifikator_id' => auth()->id(),
                                    'verifikator_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                                    'pimpinan_id' => $pimpinan?->id,
                                ]);

                                Tax::create([
                                    'jenis_pajak_id' => $record->jenis_pajak_id,
                                    'sub_jenis_pajak_id' => $record->sub_jenis_pajak_id,
                                    'user_id' => $record->petugas_id,
                                    'amount' => $record->jumlah_retribusi,
                                    'omzet' => $record->jumlah_retribusi,
                                    'tarif_persentase' => 0,
                                    'status' => TaxStatus::Verified,
                                    'billing_code' => $billing,
                                    'skpd_number' => $noSkrd,
                                    'verified_at' => now(),
                                    'verified_by' => auth()->id(),
                                ]);
                            }
                        });

                        Notification::make()
                            ->title("Berhasil menyetujui {$count} SKRD")
                            ->success()
                            ->send();
                    }),
                Actions\BulkAction::make('bulk_reject')
                    ->label('Tolak Terpilih')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn () => auth()->user()?->hasRole(['admin', 'verifikator']) ?? false)
                    ->authorize(fn () => auth()->user()?->hasRole(['admin', 'verifikator']) ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Tolak SKRD Terpilih?')
                    ->schema([
                        Forms\Components\Textarea::make('catatan_verifikasi')
                            ->label('Catatan Penolakan')
                            ->required(),
                    ])
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, array $data): void {
                        $draftRecords = $records
                            ->where('status', 'draft')
                            ->filter(fn (SkrdSewaRetribusi $record) => static::canVerify($record));

                        if ($draftRecords->isEmpty()) {
                            Notification::make()
                                ->title('Tidak ada SKRD draft yang dapat diverifikasi')
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
                                    'verifikator_id' => auth()->id(),
                                    'verifikator_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                                ]);
                            }
                        });

                        Notification::make()
                            ->title("Berhasil menolak {$count} SKRD")
                            ->danger()
                            ->send();
                    }),
            ]);
    }

    protected static function canVerify(SkrdSewaRetribusi $record): bool
    {
        return auth()->user()?->hasRole(['admin', 'verifikator'])
            && $record->status === 'draft'
            && $record->petugas_id !== auth()->id();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSkrdSewaRetribusi::route('/'),
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
