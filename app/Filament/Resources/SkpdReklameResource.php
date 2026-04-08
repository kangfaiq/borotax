<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SkpdReklameResource\Pages;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Shared\Models\ActivityLog;
use App\Domain\Tax\Models\Tax;
use App\Enums\TaxStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

class SkpdReklameResource extends Resource
{
    protected const REKLAME_OBJECT_SYNC_ACTION = 'UPDATE_TAX_OBJECT_FROM_SKPD_REKLAME_APPROVAL';

    protected const REKLAME_OBJECT_SYNC_MAP = [
        'nama_objek_pajak' => 'nama_reklame',
        'alamat_objek' => 'alamat_reklame',
        'sub_jenis_pajak_id' => 'sub_jenis_pajak_id',
        'kelompok_lokasi' => 'kelompok_lokasi',
        'bentuk' => 'bentuk',
        'panjang' => 'panjang',
        'lebar' => 'lebar',
        'tinggi' => 'tinggi',
        'sisi_atas' => 'sisi_atas',
        'sisi_bawah' => 'sisi_bawah',
        'diameter' => 'diameter',
        'diameter2' => 'diameter2',
        'alas' => 'alas',
        'luas_m2' => 'luas_m2',
        'jumlah_muka' => 'jumlah_muka',
    ];

    protected static ?string $model = SkpdReklame::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static string | \UnitEnum | null $navigationGroup = 'Verifikasi';

    protected static ?string $navigationLabel = 'Verifikasi SKPD Reklame';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'draft')->count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Data SKPD')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\TextInput::make('nomor_skpd')->disabled(),
                        Forms\Components\TextInput::make('nama_wajib_pajak')->disabled(),
                        Forms\Components\TextInput::make('nama_reklame')->disabled(),
                        Forms\Components\TextInput::make('alamat_reklame')->disabled(),

                        Forms\Components\TextInput::make('luas_m2')->label('Luas (m²)')->disabled(),
                        Forms\Components\TextInput::make('jumlah_muka')->disabled(),
                        Forms\Components\TextInput::make('durasi')->label('Durasi')->disabled(),
                        Forms\Components\TextInput::make('satuan_waktu')->label('Satuan Waktu')->disabled(),
                        Forms\Components\TextInput::make('tarif_pokok')
                            ->label('Tarif Pokok')
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('jumlah_pajak')
                            ->label('Total Pajak Terutang')
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
                Tables\Columns\TextColumn::make('nomor_skpd')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_wajib_pajak')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_reklame'),
                Tables\Columns\TextColumn::make('jumlah_pajak')
                    ->money('IDR')
                    ->sortable(),
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
            
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    \Filament\Actions\Action::make('cetak_skpd')
                        ->label('Cetak SKPD')
                        ->icon('heroicon-o-printer')
                        ->url(fn(SkpdReklame $record) => route('skpd-reklame.show', $record->id))
                        ->openUrlInNewTab(),
                    \Filament\Actions\Action::make('unduh_skpd')
                        ->label('Unduh SKPD')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (SkpdReklame $record) {
                            $record->load(['reklameObject', 'jenisPajak', 'subJenisPajak']);

                            $pimpinan = $record->pimpinan_id
                                ? Pimpinan::find($record->pimpinan_id)
                                : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

                            $pdf = Pdf::loadView('documents.skpd-reklame', [
                                'skpd' => $record,
                                'pimpinan' => $pimpinan,
                                'isPdf' => true,
                            ]);

                            $pdf->setPaper([0, 0, 609.449, 935.433], 'portrait');

                            $filename = 'SKPD_Reklame_' . str_replace([' ', '/'], '_', $record->nomor_skpd) . '.pdf';

                            return response()->streamDownload(fn () => print($pdf->output()), $filename, [
                                'Content-Type' => 'application/pdf',
                            ]);
                        }),
                ])
                    ->icon('heroicon-m-document-text')
                    ->visible(fn(SkpdReklame $record) => in_array($record->status, ['draft', 'disetujui'])),
                \Filament\Actions\Action::make('approve')
                    ->label('Setujui & Terbitkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(SkpdReklame $record) => $record->status === 'draft' && auth()->user()->can('verify', $record))
                    ->authorize(fn(SkpdReklame $record) => auth()->user()?->can('verify', $record) ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Terbitkan SKPD?')
                    ->modalDescription('Aksi ini akan menerbitkan Kode Pembayaran Aktif dan mengubah status menjadi Disetujui.')
                    ->action(function (SkpdReklame $record): void {
                        $draftNomorSkpd = $record->nomor_skpd;
                        $kodeJenisPajak = $record->jenisPajak->kode ?? '41104';
                        $billing = Tax::generateBillingCode($kodeJenisPajak);
                        $noSkpd = SkpdReklame::generateNomorSkpd(); // Official Number

                        // Hitung jatuh tempo: masa_berlaku_mulai + 1 bulan - 1 hari
                        $jatuhTempo = SkpdReklame::hitungJatuhTempoReklame($record->masa_berlaku_mulai);
            
                        // Resolve pimpinan: gunakan pimpinan dari verifikator, fallback ke pimpinan utama
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

                        // Update Request juga (jika ada)
                        if ($record->request_id) {
                            $record->reklameRequest()->update([
                                'status' => 'disetujui', // Selesai
                                'tanggal_selesai' => now(),
                            ]);
                        }

                        static::syncApprovedSkpdToReklameObject($record, $draftNomorSkpd);

                        // Insert ke tabel Taxes (Tagihan)
                        // Agar muncul di menu Pembayaran / Laporan Pendapatan
                        Tax::create([
                            'jenis_pajak_id' => $record->jenis_pajak_id,
                            'sub_jenis_pajak_id' => $record->sub_jenis_pajak_id,
                            'user_id' => $record->reklameRequest?->user_id ?? $record->petugas_id,
                            'amount' => $record->jumlah_pajak, // Enkripsi
                            'omzet' => $record->dasar_pengenaan,
                            'tarif_persentase' => 25,
                            'status' => TaxStatus::Verified, // SKPD terbit, menunggu pembayaran
                            'billing_code' => $billing,
                            'skpd_number' => $noSkpd,
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('SKPD Diterbitkan')
                            ->body("Nomor: {$noSkpd}, Kode Pembayaran Aktif: {$billing}")
                            ->success()
                            ->send();
                    }),
                \Filament\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(SkpdReklame $record) => $record->status === 'draft' && auth()->user()->can('verify', $record))
                    ->authorize(fn(SkpdReklame $record) => auth()->user()?->can('verify', $record) ?? false)
                    ->schema([
                        Forms\Components\Textarea::make('catatan_verifikasi')->required(),
                    ])
                    ->action(function (SkpdReklame $record, array $data): void {
                        $record->update([
                            'status' => 'ditolak',
                            'catatan_verifikasi' => $data['catatan_verifikasi'],
                            'verifikator_id' => auth()->id(),
                            'verifikator_nama' => auth()->user()->nama_lengkap,
                        ]);

                        // Update Request -> Ditolak / Perlu Perbaikan?
                        // Kita set ditolak dulu
                        $record->reklameRequest()->update([
                            'status' => 'ditolak',
                            'catatan_petugas' => 'Ditolak Verifikator: ' . $data['catatan_verifikasi'],
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
                    ->modalHeading('Setujui SKPD Terpilih?')
                    ->modalDescription('Semua SKPD draft yang dipilih akan diterbitkan dengan Kode Pembayaran Aktif masing-masing.')
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records): void {
                        $draftRecords = $records
                            ->where('status', 'draft')
                            ->filter(fn (SkpdReklame $record) => auth()->user()->can('verify', $record));

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
                            $baseCount = SkpdReklame::whereYear('tanggal_buat', $tahun)
                                ->whereMonth('tanggal_buat', $bulan)
                                ->count();

                            $seq = 0;
                            foreach ($draftRecords as $record) {
                                $seq++;
                                $draftNomorSkpd = $record->nomor_skpd;
                                $kodeJenisPajak = $record->jenisPajak->kode ?? '41104';
                                $billing = Tax::generateBillingCode($kodeJenisPajak);

                                $number = str_pad($baseCount + $seq, 6, '0', STR_PAD_LEFT);
                                $noSkpd = "SKPD-RKL/{$tahun}/{$bulan}/{$number}";

                                $jatuhTempo = SkpdReklame::hitungJatuhTempoReklame($record->masa_berlaku_mulai);

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

                                if ($record->request_id) {
                                    $record->reklameRequest()->update([
                                        'status' => 'disetujui',
                                        'tanggal_selesai' => now(),
                                    ]);
                                }

                                static::syncApprovedSkpdToReklameObject($record, $draftNomorSkpd);

                                Tax::create([
                                    'jenis_pajak_id' => $record->jenis_pajak_id,
                                    'sub_jenis_pajak_id' => $record->sub_jenis_pajak_id,
                                    'user_id' => $record->reklameRequest?->user_id ?? $record->petugas_id,
                                    'amount' => $record->jumlah_pajak,
                                    'omzet' => $record->dasar_pengenaan,
                                    'tarif_persentase' => 25,
                                    'status' => TaxStatus::Verified,
                                    'billing_code' => $billing,
                                    'skpd_number' => $noSkpd,
                                    'verified_at' => now(),
                                    'verified_by' => auth()->id(),
                                ]);
                            }
                        });

                        Notification::make()
                            ->title("Berhasil menyetujui {$count} SKPD")
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
                    ->modalHeading('Tolak SKPD Terpilih?')
                    ->schema([
                        Forms\Components\Textarea::make('catatan_verifikasi')
                            ->label('Catatan Penolakan')
                            ->required(),
                    ])
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, array $data): void {
                        $draftRecords = $records
                            ->where('status', 'draft')
                            ->filter(fn (SkpdReklame $record) => auth()->user()->can('verify', $record));

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

                                $record->reklameRequest()->update([
                                    'status' => 'ditolak',
                                    'catatan_petugas' => 'Ditolak Verifikator: ' . $data['catatan_verifikasi'],
                                ]);
                            }
                        });

                        Notification::make()
                            ->title("Berhasil menolak {$count} SKPD")
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

    protected static function syncApprovedSkpdToReklameObject(SkpdReklame $record, ?string $draftNomorSkpd = null): void
    {
        if (! $record->tax_object_id) {
            return;
        }

        $record->loadMissing(['reklameObject', 'reklameRequest']);

        $reklameObject = $record->reklameObject;

        if (! $reklameObject) {
            return;
        }

        $syncData = [];
        $oldValues = [];
        $newValues = [];

        foreach (static::REKLAME_OBJECT_SYNC_MAP as $objectField => $skpdField) {
            $newValue = $record->getAttribute($skpdField);
            $currentValue = $reklameObject->getAttribute($objectField);

            $syncData[$objectField] = $newValue;

            if (! static::reklameObjectValuesDiffer($currentValue, $newValue)) {
                continue;
            }

            $oldValues[$objectField] = $currentValue;
            $newValues[$objectField] = $newValue;
        }

        if ($oldValues === []) {
            return;
        }

        $reklameObject->fill($syncData);
        $reklameObject->save();

        ActivityLog::log(
            action: static::REKLAME_OBJECT_SYNC_ACTION,
            actorId: auth()->id(),
            targetTable: $reklameObject->getTable(),
            targetId: $reklameObject->getKey(),
            description: static::buildReklameObjectSyncDescription($record, $draftNomorSkpd),
            oldValues: $oldValues,
            newValues: $newValues,
        );
    }

    protected static function buildReklameObjectSyncDescription(SkpdReklame $record, ?string $draftNomorSkpd = null): string
    {
        $description = [
            'Sinkronisasi objek reklame dari persetujuan SKPD Reklame.',
            'Nomor draft: ' . ($draftNomorSkpd ?: '-'),
            'Nomor final: ' . ($record->nomor_skpd ?: '-'),
        ];

        if ($record->request_id) {
            $description[] = 'Request ID: ' . $record->request_id;
        }

        $description[] = 'Petugas draft: ' . ($record->petugas_nama ?: '-');
        $description[] = 'Verifikator penyetuju: ' . ($record->verifikator_nama ?: '-');

        return implode(' ', $description);
    }

    protected static function reklameObjectValuesDiffer(mixed $currentValue, mixed $newValue): bool
    {
        if ($currentValue === null && $newValue === null) {
            return false;
        }

        if (is_numeric($currentValue) && is_numeric($newValue)) {
            return (float) $currentValue !== (float) $newValue;
        }

        return (string) $currentValue !== (string) $newValue;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSkpdReklames::route('/'),
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
