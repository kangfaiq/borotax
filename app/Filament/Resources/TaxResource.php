<?php

namespace App\Filament\Resources;

use Filament\Tables\Columns\TextColumn;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use App\Filament\Resources\TaxResource\Pages\ListTaxes;
use App\Filament\Resources\TaxResource\Pages;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\Tax\Models\TaxPayment;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\Master\Models\Instansi;
use App\Domain\Master\Models\JenisPajak;
use App\Enums\InstansiKategori;
use App\Enums\TaxStatus;
use Filament\Notifications\Notification;
use Filament\Forms; // Perlu form untuk filter? Filament filter pakai form builder
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use League\Csv\Writer;
use SplTempFileObject;
use Filament\Tables\Filters\TrashedFilter;

class TaxResource extends Resource
{
    protected static ?string $model = Tax::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static string | \UnitEnum | null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Pendapatan';

    protected static ?string $modelLabel = 'Transaksi Pajak';

    protected static ?string $pluralModelLabel = 'Laporan Pendapatan';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = false;

    /**
     * Check if current view is for official assessment type (Reklame, Air Tanah).
     * Falls back to request query for non-Livewire contexts.
     */
    protected static function isOfficialAssessment(): bool
    {
        $jenisPajakId = request()->query('jenisPajakId');
        if (!$jenisPajakId) return false;
        $jp = JenisPajak::find($jenisPajakId);
        return $jp && $jp->tipe_assessment === 'official_assessment';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['parent', 'children', 'taxObject']))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('skpd_number')
                    ->label('Nomor SKPD')
                    ->searchable()
                    ->visible(fn($livewire): bool => method_exists($livewire, 'isOfficialAssessment') ? $livewire->isOfficialAssessment() : static::isOfficialAssessment())
                    ->toggleable(),
                TextColumn::make('billing_code')
                    ->label('Kode Pembayaran Aktif')
                    ->state(fn(Tax $record): string => $record->getPreferredPaymentCode())
                    ->description(fn(Tax $record): ?string => $record->stpd_payment_code ? 'Billing Sumber: ' . $record->billing_code : null)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $query) use ($search): Builder {
                            return $query
                                ->where('billing_code', 'like', "%{$search}%")
                                ->orWhere('stpd_payment_code', 'like', "%{$search}%");
                        });
                    })
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('pembetulan_ke')
                    ->label('Pembetulan')
                    ->badge()
                    ->formatStateUsing(function (Tax $record): string {
                        if ($record->pembetulan_ke > 0) {
                            return 'Pembetulan Ke-' . $record->pembetulan_ke;
                        }
                        if ($record->children->count() > 0) {
                            return 'Sudah Dipembetulan';
                        }
                        return 'Original';
                    })
                    ->color(function (Tax $record): string {
                        if ($record->pembetulan_ke > 0) {
                            return 'info';
                        }
                        if ($record->children->count() > 0) {
                            return 'warning';
                        }
                        return 'gray';
                    })
                    ->description(function (Tax $record): ?string {
                        if ($record->pembetulan_ke > 0 && $record->parent) {
                            return 'Billing Sumber: ' . $record->parent->billing_code;
                        }
                        if ($record->children->count() > 0) {
                            $latest = $record->children->sortByDesc('revision_attempt_no')->first();
                            return 'Pembetulan: ' . $latest->billing_code;
                        }
                        return null;
                    })
                    ->visible(fn($livewire): bool => method_exists($livewire, 'isOfficialAssessment') ? !$livewire->isOfficialAssessment() : !static::isOfficialAssessment())
                    ->toggleable(),
                TextColumn::make('taxObject.nama_objek_pajak')
                    ->label('Objek Pajak')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $matchingIds = TaxObject::all()
                            ->filter(fn($obj) => str_contains(
                                strtolower($obj->nama_objek_pajak ?? ''),
                                strtolower($search)
                            ))
                            ->pluck('id')
                            ->toArray();

                        return $query->whereIn('tax_object_id', $matchingIds);
                    })
                    ->sortable()
                    ->visible(fn($livewire): bool => method_exists($livewire, 'isOfficialAssessment') ? !$livewire->isOfficialAssessment() : !static::isOfficialAssessment())
                    ->toggleable(),
                TextColumn::make('instansi_nama')
                    ->label('Instansi')
                    ->searchable()
                    ->placeholder('-')
                    ->description(fn(Tax $record): ?string => $record->instansi_kategori?->getLabel())
                    ->visible(fn($livewire): bool => method_exists($livewire, 'isOfficialAssessment') ? !$livewire->isOfficialAssessment() : !static::isOfficialAssessment())
                    ->toggleable(),
                TextColumn::make('masa_pajak_bulan')
                    ->label('Masa Pajak')
                    ->formatStateUsing(
                        fn($state, Tax $record) =>
                        $state ? Carbon::create()->month((int) $state)->translatedFormat('F') . ' ' . $record->masa_pajak_tahun : 'Tahun ' . $record->masa_pajak_tahun
                    )
                    ->sortable()
                    ->visible(fn($livewire): bool => method_exists($livewire, 'isOfficialAssessment') ? !$livewire->isOfficialAssessment() : !static::isOfficialAssessment())
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('Jumlah Pajak')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->state(fn (Tax $record): string => $record->display_status->value)
                    ->formatStateUsing(fn (string $state): string => TaxStatus::from($state)->getLabel() ?? $state)
                    ->color(fn (string $state): string|array|null => TaxStatus::from($state)->getColor())
                    ->badge()
                    ->toggleable(),
                TextColumn::make('statusBayar')
                    ->label('Status Bayar')
                    ->badge()
                    ->state(function (Tax $record): string {
                        return TaxPayment::where('tax_id', $record->id)->exists() ? 'Lunas' : 'Belum Bayar';
                    })
                    ->color(fn(string $state): string => $state === 'Lunas' ? 'success' : 'warning')
                    ->toggleable(),
                TextColumn::make('payment_channel')
                    ->label('Metode Bayar')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('paid_at')
                    ->label('Tanggal Bayar')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('payment_expired_at')
                    ->label('Jatuh Tempo')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('notes')
                    ->label('Keterangan')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('tanggal_transaksi')
                    ->label('Tanggal Transaksi')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->columns(2)
                    ->columnSpanFull()
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Dari ' . Carbon::parse($data['from'])->format('d/m/Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Sampai ' . Carbon::parse($data['until'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
                Tables\Filters\SelectFilter::make('instansi_id')
                    ->label('Instansi')
                    ->options(fn () => Instansi::withTrashed()
                        ->orderBy('nama')
                        ->pluck('nama', 'id')
                        ->toArray()),
                Tables\Filters\SelectFilter::make('instansi_kategori')
                    ->label('Kategori Instansi')
                    ->options(collect(InstansiKategori::cases())
                        ->mapWithKeys(fn (InstansiKategori $kategori) => [$kategori->value => $kategori->getLabel()])
                        ->all()),
            
                TrashedFilter::make(),
            ])
            ->recordActions([
                // — SKPD Document (untuk official_assessment: Reklame, Air Tanah) —
                ActionGroup::make([
                    Action::make('cetak_skpd')
                        ->label('Cetak SKPD')
                        ->icon('heroicon-o-printer')
                        ->url(function (Tax $record) {
                            $skpd = SkpdReklame::where('kode_billing', $record->billing_code)->first();
                            if ($skpd) {
                                return route('skpd-reklame.show', $skpd->id);
                            }
                            $skpd = SkpdAirTanah::where('kode_billing', $record->billing_code)->first();
                            return $skpd ? route('skpd-air-tanah.show', $skpd->id) : null;
                        })
                        ->openUrlInNewTab(),
                    Action::make('unduh_skpd')
                        ->label('Unduh SKPD')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(function (Tax $record) {
                            $skpd = SkpdReklame::where('kode_billing', $record->billing_code)->first();
                            if ($skpd) {
                                return route('skpd-reklame.download', $skpd->id);
                            }
                            $skpd = SkpdAirTanah::where('kode_billing', $record->billing_code)->first();
                            return $skpd ? route('skpd-air-tanah.download', $skpd->id) : null;
                        }),
                ])
                    ->label('Dokumen')
                    ->icon('heroicon-m-document-text')
                    ->visible(fn(): bool => static::isOfficialAssessment()),

                // — Billing / SPTPD / STPD (untuk self_assessment) —
                ActionGroup::make([
                    Action::make('cetak_billing')
                        ->label(fn(Tax $record): string => $record->getBillingDocumentActionLabel())
                        ->icon('heroicon-o-printer')
                        ->tooltip(fn(Tax $record): string => $record->getBillingDocumentActionTitle())
                        ->url(fn(Tax $record) => route('portal.billing.document.show', $record->id))
                        ->openUrlInNewTab(),
                    Action::make('unduh_billing')
                        ->label(fn(Tax $record): string => $record->getBillingDownloadActionLabel())
                        ->icon('heroicon-o-arrow-down-tray')
                        ->tooltip(fn(Tax $record): string => $record->getBillingDownloadActionTitle())
                        ->url(fn(Tax $record) => route('portal.billing.document.download', $record->id)),

                    Action::make('cetak_sptpd')
                        ->label('Cetak SPTPD')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->visible(fn(Tax $record) => $record->status === TaxStatus::Paid && $record->sptpd_number)
                        ->url(fn(Tax $record) => route('portal.sptpd.show', $record->id))
                        ->openUrlInNewTab(),
                    Action::make('unduh_sptpd')
                        ->label('Unduh SPTPD')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->visible(fn(Tax $record) => $record->status === TaxStatus::Paid && $record->sptpd_number)
                        ->url(fn(Tax $record) => route('portal.sptpd.download', $record->id)),

                    Action::make('cetak_stpd')
                        ->label('Cetak STPD (Sanksi)')
                        ->icon('heroicon-o-printer')
                        ->color('warning')
                        ->visible(fn(Tax $record) => !empty($record->stpd_number) && !$record->isMultiBilling())
                        ->url(fn(Tax $record) => route('portal.stpd.show', $record->id))
                        ->openUrlInNewTab(),
                    Action::make('unduh_stpd')
                        ->label('Unduh STPD (Sanksi)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('warning')
                        ->visible(fn(Tax $record) => !empty($record->stpd_number) && !$record->isMultiBilling())
                        ->url(fn(Tax $record) => route('portal.stpd.download', $record->id)),
                ])
                    ->label('Dokumen')
                    ->icon('heroicon-m-document-text')
                    ->visible(fn(): bool => !static::isOfficialAssessment()),
            ])
            ->headerActions([
                Action::make('copy')
                    ->label('Copy Data')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('gray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $records = $query->get();

                        $lines = [];
                        $lines[] = "Tanggal Transaksi\tKode Pembayaran Aktif\tPembetulan\tObjek Pajak\tInstansi\tMasa Pajak\tJumlah Pajak\tStatus\tMetode Bayar\tTanggal Bayar\tJatuh Tempo";

                        foreach ($records as $record) {
                            $masaPajak = $record->masa_pajak_bulan ? Carbon::create()->month((int) $record->masa_pajak_bulan)->translatedFormat('F') . ' ' . $record->masa_pajak_tahun : 'Tahun ' . $record->masa_pajak_tahun;
                            $lines[] = implode("\t", [
                                $record->created_at->format('d/m/Y H:i'),
                                $record->getPreferredPaymentCode(),
                                $record->pembetulan_ke > 0 ? 'Pembetulan Ke-' . $record->pembetulan_ke : 'Original',
                                $record->taxObject->nama_objek_pajak ?? '-',
                                $record->instansi_nama ?? '-',
                                $masaPajak,
                                number_format((float) $record->amount, 0, ',', '.'),
                                static::formatReportStatus($record),
                                $record->payment_channel ?? '-',
                                $record->paid_at ? $record->paid_at->format('d/m/Y H:i') : '-',
                                $record->payment_expired_at ? Carbon::parse($record->payment_expired_at)->format('d/m/Y') : '-',
                            ]);
                        }

                        $text = implode("\n", $lines);
                        $escaped = json_encode($text);

                        $livewire->js(<<<JS
                            (async function() {
                                var textData = {$escaped};
                                if (navigator.clipboard && window.isSecureContext) {
                                    await navigator.clipboard.writeText(textData);
                                    return;
                                }

                                var ta = document.createElement('textarea');
                                ta.value = textData;
                                ta.setAttribute('readonly', 'readonly');
                                ta.style.position = 'fixed';
                                ta.style.left = '-9999px';
                                document.body.appendChild(ta);
                                ta.select();
                                document.execCommand('copy');
                                document.body.removeChild(ta);
                            })()
                        JS);

                        Notification::make()
                            ->title('Data berhasil disalin ke clipboard')
                            ->success()
                            ->send();
                    }),
                Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $records = $query->get();

                        $csv = Writer::createFromFileObject(new SplTempFileObject());
                        $csv->insertOne(['Tanggal Transaksi', 'Kode Pembayaran Aktif', 'Pembetulan', 'Objek Pajak', 'Instansi', 'Masa Pajak', 'Jumlah Pajak', 'Status', 'Metode Bayar', 'Tanggal Bayar', 'Jatuh Tempo']);

                        foreach ($records as $record) {
                            $masaPajak = $record->masa_pajak_bulan ? Carbon::create()->month((int) $record->masa_pajak_bulan)->translatedFormat('F') . ' ' . $record->masa_pajak_tahun : 'Tahun ' . $record->masa_pajak_tahun;
                            $csv->insertOne([
                                $record->created_at->format('Y-m-d H:i:s'),
                                $record->getPreferredPaymentCode(),
                                $record->pembetulan_ke > 0 ? 'Pembetulan Ke-' . $record->pembetulan_ke : 'Original',
                                $record->taxObject->nama_objek_pajak ?? '-',
                                $record->instansi_nama ?? '',
                                $masaPajak,
                                (string) $record->amount,
                                static::formatReportStatus($record),
                                $record->payment_channel ?? '-',
                                $record->paid_at ? $record->paid_at->format('Y-m-d H:i:s') : '',
                                $record->payment_expired_at ? Carbon::parse($record->payment_expired_at)->format('Y-m-d') : '',
                            ]);
                        }

                        return response()->streamDownload(function () use ($csv) {
                            echo $csv->toString();
                        }, 'laporan-pendapatan-' . date('Y-m-d') . '.csv');
                    })
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
            'index' => ListTaxes::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        Tax::syncExpiredStatuses();

        return parent::getEloquentQuery();
    }

    protected static function formatReportStatus(Tax $record): string
    {
        return $record->display_status_label;
    }
}
