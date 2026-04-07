<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SkpdAirTanahResource\Pages;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\Tax\Models\Tax;
use App\Enums\TaxStatus;
use App\Domain\Master\Models\Pimpinan;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

class SkpdAirTanahResource extends Resource
{
    protected static ?string $model = SkpdAirTanah::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Verifikasi';

    protected static ?string $navigationLabel = 'Verifikasi SKPD ABT';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'draft')->count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Data SKPD ABT')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\TextInput::make('nomor_skpd')->disabled(),
                        Forms\Components\TextInput::make('nama_wajib_pajak')->disabled(),
                        Forms\Components\TextInput::make('nama_objek')->label('Lokasi')->disabled(),
                        Forms\Components\TextInput::make('periode_bulan')->disabled(),

                        Forms\Components\TextInput::make('meter_reading_before')->label('Meter Awal')->disabled(),
                        Forms\Components\TextInput::make('meter_reading_after')->label('Meter Akhir')->disabled(),
                        Forms\Components\TextInput::make('usage')->label('Pakai (m3)')->disabled(),

                        Forms\Components\TextInput::make('tarif_per_m3')
                            ->label('NPA (Rp/m3)')
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('jumlah_pajak')
                            ->label('Pajak Terutang')
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\DatePicker::make('jatuh_tempo')
                            ->label('Tanggal Jatuh Tempo')
                            ->disabled()
                            ->helperText('Otomatis dihitung: akhir bulan berikutnya dari periode'),
                    ])->columns(2),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi SKPD ABT')
                ->columnSpanFull()
                ->schema([
                    Infolists\Components\TextEntry::make('nomor_skpd')->label('Nomor SKPD'),
                    Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn(string $state): string => match ($state) {
                            'draft' => 'Draft',
                            'disetujui' => 'Disetujui',
                            'ditolak' => 'Ditolak',
                            default => str($state)->headline()->toString(),
                        })
                        ->color(fn(string $state): string => match ($state) {
                            'draft' => 'warning',
                            'disetujui' => 'success',
                            'ditolak' => 'danger',
                            default => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('periode_bulan')->label('Periode'),
                    Infolists\Components\TextEntry::make('jumlah_pajak')->label('Jumlah Pajak')->money('IDR'),
                ])->columns(4),
            Section::make('Data Objek Air Tanah')
                ->columnSpanFull()
                ->schema([
                    Infolists\Components\TextEntry::make('nama_objek')->label('Nama Objek'),
                    Infolists\Components\TextEntry::make('nopd')->label('NOPD'),
                    Infolists\Components\TextEntry::make('waterObject.uses_meter')
                        ->label('Jenis Objek Air Tanah')
                        ->formatStateUsing(fn($state): string => $state ? 'Objek Meter Air' : 'Objek Non Meter Air')
                        ->badge()
                        ->color(fn($state): string => $state ? 'info' : 'warning'),
                    Infolists\Components\TextEntry::make('alamat_objek')->label('Alamat Objek')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('kecamatan')->label('Kecamatan'),
                    Infolists\Components\TextEntry::make('kelurahan')->label('Kelurahan'),
                ])->columns(3),
            Section::make('Data Penggunaan')
                ->columnSpanFull()
                ->schema([
                    Infolists\Components\TextEntry::make('meter_reading_before')
                        ->label('Meter Awal')
                        ->suffix(' m3')
                        ->hidden(fn(SkpdAirTanah $record): bool => !($record->waterObject?->uses_meter ?? false)),
                    Infolists\Components\TextEntry::make('meter_reading_after')
                        ->label('Meter Akhir')
                        ->suffix(' m3')
                        ->hidden(fn(SkpdAirTanah $record): bool => !($record->waterObject?->uses_meter ?? false)),
                    Infolists\Components\TextEntry::make('usage')
                        ->label(fn(SkpdAirTanah $record): string => ($record->waterObject?->uses_meter ?? false) ? 'Pemakaian' : 'Penggunaan Langsung')
                        ->suffix(' m3'),
                ])->columns(3),
            Section::make('Dokumen Pendukung')
                ->columnSpanFull()
                ->schema([
                    Infolists\Components\TextEntry::make('lampiran_path')
                        ->label('Lampiran Pendukung')
                        ->formatStateUsing(fn(?string $state): string => $state ? 'Lihat Lampiran' : 'Tidak ada (opsional)')
                        ->url(fn(SkpdAirTanah $record): ?string => $record->lampiran_url)
                        ->openUrlInNewTab(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_buat')
                    ->label('Tgl Buat')
                    ->dateTime('d/m/Y'),
                Tables\Columns\TextColumn::make('nomor_skpd')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_wajib_pajak')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jumlah_pajak')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lampiran_path')
                    ->label('Lampiran')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => $state ? 'Ada Lampiran' : 'Tidak Ada')
                    ->url(fn(SkpdAirTanah $record): ?string => $record->lampiran_url)
                    ->openUrlInNewTab()
                    ->color(fn(?string $state): string => $state ? 'info' : 'gray'),
                Tables\Columns\TextColumn::make('jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn(?string $state): string => $state && now()->gt($state) ? 'danger' : 'success'),
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
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft (Perlu Verifikasi)',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),
                Tables\Filters\TernaryFilter::make('lampiran_path')
                    ->label('Ada Lampiran')
                    ->nullable()
                    ->queries(
                        true: fn($query) => $query->whereNotNull('lampiran_path'),
                        false: fn($query) => $query->whereNull('lampiran_path'),
                        blank: fn($query) => $query,
                    ),
            
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                ActionGroup::make([
                    \Filament\Actions\Action::make('cetak_skpd')
                        ->label('Cetak SKPD')
                        ->icon('heroicon-o-printer')
                        ->url(fn(SkpdAirTanah $record) => route('skpd-air-tanah.show', $record->id))
                        ->openUrlInNewTab(),
                    \Filament\Actions\Action::make('unduh_skpd')
                        ->label('Unduh SKPD')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (SkpdAirTanah $record) {
                            $record->load(['waterObject', 'jenisPajak', 'subJenisPajak']);

                            $pimpinan = $record->pimpinan_id
                                ? Pimpinan::find($record->pimpinan_id)
                                : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

                            $pdf = Pdf::loadView('documents.skpd-air-tanah', [
                                'skpd' => $record,
                                'pimpinan' => $pimpinan,
                                'isPdf' => true,
                            ]);

                            $pdf->setPaper([0, 0, 609.449, 935.433], 'portrait');

                            $filename = 'SKPD_Air_Tanah_' . str_replace([' ', '/'], '_', $record->nomor_skpd) . '.pdf';

                            return response()->streamDownload(fn () => print($pdf->output()), $filename, [
                                'Content-Type' => 'application/pdf',
                            ]);
                        }),
                ])
                    ->icon('heroicon-m-document-text')
                    ->visible(fn(SkpdAirTanah $record) => in_array($record->status, ['draft', 'disetujui'])),
                \Filament\Actions\Action::make('approve')
                    ->label('Setujui & Terbitkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(SkpdAirTanah $record) => $record->status === 'draft' && auth()->user()->can('verify', $record))
                    ->authorize(fn(SkpdAirTanah $record) => auth()->user()?->can('verify', $record) ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Terbitkan SKPD ABT?')
                    ->modalDescription('System akan generate Billing ID.')
                    ->action(function (SkpdAirTanah $record): void {
                        DB::transaction(function () use ($record) {
                            $kodeJenisPajak = $record->jenisPajak->kode ?? '41108';
                            $billing = Tax::generateBillingCode($kodeJenisPajak);
                            $noSkpd = SkpdAirTanah::generateNomorSkpd();

                            // Hitung jatuh tempo: akhir bulan berikutnya dari periode
                            $jatuhTempo = SkpdAirTanah::hitungJatuhTempoAirTanah($record->periode_bulan);

                            // Resolve pimpinan: dari verifikator yang login, fallback ke pimpinan utama
                            $pimpinan = auth()->user()->pimpinan_id
                                ? Pimpinan::find(auth()->user()->pimpinan_id)
                                : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

                            $record->update([
                                'status' => 'disetujui',
                                'nomor_skpd' => $noSkpd,
                                'kode_billing' => $billing,
                                'jatuh_tempo' => $jatuhTempo,
                                'tanggal_verifikasi' => now(),
                                'verifikator_id' => auth()->id(),
                                'verifikator_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                                'pimpinan_id' => $pimpinan?->id,
                            ]);

                            // Update Request juga (jika ada meter report terkait)
                            if ($record->meter_report_id) {
                                $record->meterReport()->update([
                                    'status' => 'approved',
                                ]);
                            }

                            // Sync data ke WaterObject
                            if ($record->tax_object_id) {
                                WaterObject::where('id', $record->tax_object_id)
                                    ->update([
                                        'last_meter_reading' => $record->meter_reading_after,
                                        'nama_objek_pajak' => $record->nama_objek,
                                        'alamat_objek' => $record->alamat_objek,
                                        'kecamatan' => $record->kecamatan,
                                        'kelurahan' => $record->kelurahan,
                                    ]);
                            }

                            // Insert ke tabel Taxes (Tagihan)
                            Tax::create([
                                'jenis_pajak_id' => $record->jenis_pajak_id,
                                'sub_jenis_pajak_id' => $record->sub_jenis_pajak_id ?: null,
                                'tax_object_id' => $record->tax_object_id,
                                'user_id' => $record->meterReport?->user_id,
                                'amount' => $record->jumlah_pajak,
                                'omzet' => $record->dasar_pengenaan,
                                'tarif_persentase' => $record->tarif_persen,
                                'status' => TaxStatus::Verified,
                                'billing_code' => $billing,
                                'skpd_number' => $noSkpd,
                                'payment_expired_at' => $jatuhTempo,
                                'verified_at' => now(),
                                'verified_by' => auth()->id(),
                                'meter_reading' => $record->meter_reading_after,
                                'previous_meter_reading' => $record->meter_reading_before,
                                'meter_photo_url' => $record->meterReport?->photo_url,
                                'dasar_hukum' => $record->dasar_hukum,
                            ]);
                        });

                        Notification::make()
                            ->title('SKPD Diterbitkan')
                            ->body("Nomor: {$record->nomor_skpd}, Billing: {$record->kode_billing}")
                            ->success()
                            ->send();
                    }),
                \Filament\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(SkpdAirTanah $record) => $record->status === 'draft' && auth()->user()->can('verify', $record))
                    ->authorize(fn(SkpdAirTanah $record) => auth()->user()?->can('verify', $record) ?? false)
                    ->schema([
                        Forms\Components\Textarea::make('catatan_verifikasi')->required(),
                    ])
                    ->action(function (SkpdAirTanah $record, array $data): void {
                        $record->update([
                            'status' => 'ditolak',
                            'catatan_verifikasi' => $data['catatan_verifikasi'],
                            'verifikator_id' => auth()->id(),
                            'verifikator_nama' => auth()->user()->nama_lengkap,
                        ]);

                        // Update Meter Report -> Rejected
                        $record->meterReport()->update([
                            'status' => 'rejected',
                            // Add rejection note logic if needed to MeterReport table or just keep in SKPD
                        ]);

                        Notification::make()
                            ->title('SKPD Ditolak')
                            ->danger()
                            ->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkAction::make('bulk_approve')
                    ->label('Setujui Terpilih')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn () => auth()->user()?->hasRole(['admin', 'verifikator']) ?? false)
                    ->authorize(fn () => auth()->user()?->hasRole(['admin', 'verifikator']) ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Setujui SKPD ABT Terpilih?')
                    ->modalDescription('Semua SKPD ABT draft yang dipilih akan diterbitkan dengan Kode Pembayaran Aktif masing-masing.')
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records): void {
                        $draftRecords = $records
                            ->where('status', 'draft')
                            ->filter(fn (SkpdAirTanah $record) => auth()->user()->can('verify', $record));

                        if ($draftRecords->isEmpty()) {
                            Notification::make()
                                ->title('Tidak ada SKPD draft yang dapat diverifikasi')
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

                            // Base count untuk sequential nomor SKPD (hindari duplikat)
                            $tahun = date('Y');
                            $bulan = date('m');
                            $baseCount = SkpdAirTanah::whereYear('tanggal_buat', $tahun)
                                ->whereMonth('tanggal_buat', $bulan)
                                ->count();

                            $seq = 0;
                            foreach ($draftRecords as $record) {
                                $seq++;
                                $kodeJenisPajak = $record->jenisPajak->kode ?? '41108';
                                $billing = Tax::generateBillingCode($kodeJenisPajak);

                                $number = str_pad($baseCount + $seq, 6, '0', STR_PAD_LEFT);
                                $noSkpd = "SKPD-ABT/{$tahun}/{$bulan}/{$number}";

                                $jatuhTempo = SkpdAirTanah::hitungJatuhTempoAirTanah($record->periode_bulan);

                                $record->update([
                                    'status' => 'disetujui',
                                    'nomor_skpd' => $noSkpd,
                                    'kode_billing' => $billing,
                                    'jatuh_tempo' => $jatuhTempo,
                                    'tanggal_verifikasi' => now(),
                                    'verifikator_id' => auth()->id(),
                                    'verifikator_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                                    'pimpinan_id' => $pimpinan?->id,
                                ]);

                                if ($record->meter_report_id) {
                                    $record->meterReport()->update([
                                        'status' => 'approved',
                                    ]);
                                }

                                // Sync data ke WaterObject
                                if ($record->tax_object_id) {
                                    WaterObject::where('id', $record->tax_object_id)
                                        ->update([
                                            'last_meter_reading' => $record->meter_reading_after,
                                            'nama_objek_pajak' => $record->nama_objek,
                                            'alamat_objek' => $record->alamat_objek,
                                            'kecamatan' => $record->kecamatan,
                                            'kelurahan' => $record->kelurahan,
                                        ]);
                                }

                                Tax::create([
                                    'jenis_pajak_id' => $record->jenis_pajak_id,
                                    'sub_jenis_pajak_id' => $record->sub_jenis_pajak_id ?: null,
                                    'tax_object_id' => $record->tax_object_id,
                                    'user_id' => $record->meterReport?->user_id,
                                    'amount' => $record->jumlah_pajak,
                                    'omzet' => $record->dasar_pengenaan,
                                    'tarif_persentase' => $record->tarif_persen,
                                    'status' => TaxStatus::Verified,
                                    'billing_code' => $billing,
                                    'skpd_number' => $noSkpd,
                                    'payment_expired_at' => $jatuhTempo,
                                    'verified_at' => now(),
                                    'verified_by' => auth()->id(),
                                    'meter_reading' => $record->meter_reading_after,
                                    'previous_meter_reading' => $record->meter_reading_before,
                                    'meter_photo_url' => $record->meterReport?->photo_url,
                                    'dasar_hukum' => $record->dasar_hukum,
                                ]);
                            }
                        });

                        Notification::make()
                            ->title("Berhasil menyetujui {$count} SKPD ABT")
                            ->success()
                            ->send();
                    }),
                \Filament\Actions\BulkAction::make('bulk_reject')
                    ->label('Tolak Terpilih')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn () => auth()->user()?->hasRole(['admin', 'verifikator']) ?? false)
                    ->authorize(fn () => auth()->user()?->hasRole(['admin', 'verifikator']) ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Tolak SKPD ABT Terpilih?')
                    ->schema([
                        Forms\Components\Textarea::make('catatan_verifikasi')
                            ->label('Catatan Penolakan')
                            ->required(),
                    ])
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, array $data): void {
                        $draftRecords = $records
                            ->where('status', 'draft')
                            ->filter(fn (SkpdAirTanah $record) => auth()->user()->can('verify', $record));

                        if ($draftRecords->isEmpty()) {
                            Notification::make()
                                ->title('Tidak ada SKPD draft yang dapat diverifikasi')
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
                                    'verifikator_id' => auth()->id(),
                                    'verifikator_nama' => auth()->user()->nama_lengkap,
                                ]);

                                $record->meterReport()->update([
                                    'status' => 'rejected',
                                ]);
                            }
                        });

                        Notification::make()
                            ->title("Berhasil menolak {$count} SKPD ABT")
                            ->danger()
                            ->send();
                    }),
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
            'index' => Pages\ListSkpdAirTanahs::route('/'),
            'view' => Pages\ViewSkpdAirTanah::route('/{record}'),
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
