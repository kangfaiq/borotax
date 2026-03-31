<?php

namespace App\Filament\Pages;

use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Actions\ActionGroup;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\Master\Models\JenisPajak;
use App\Enums\TaxStatus;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Carbon\Carbon;

class PembatalanBilling extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon  = 'heroicon-o-x-circle';
    protected static string | \UnitEnum | null $navigationGroup = 'Laporan Petugas';
    protected static ?string $navigationLabel = 'Pembatalan Billing';
    protected static ?string $title           = 'Pembatalan Billing Self Assessment';
    protected static ?int    $navigationSort  = 6;
    protected string  $view            = 'filament.pages.pembatalan-billing';

    public string $activeTab = 'aktif';

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'petugas']);
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'petugas']);
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => $this->getTableQuery())
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
                    ->copyable(),
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
                    }),
                TextColumn::make('masa_pajak_bulan')
                    ->label('Masa Pajak')
                    ->formatStateUsing(
                        fn($state, Tax $record) =>
                        Carbon::create()->month((int) $state)->translatedFormat('F') . ' ' . $record->masa_pajak_tahun
                    )
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Jumlah Pajak')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('cancellation_reason')
                    ->label('Alasan Pembatalan')
                    ->limit(50)
                    ->visible(fn(): bool => $this->activeTab === 'dibatalkan'),
                TextColumn::make('cancelled_at')
                    ->label('Tanggal Dibatalkan')
                    ->dateTime('d/m/Y H:i')
                    ->visible(fn(): bool => $this->activeTab === 'dibatalkan'),
                TextColumn::make('cancelledByUser.name')
                    ->label('Dibatalkan Oleh')
                    ->visible(fn(): bool => $this->activeTab === 'dibatalkan'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('tanggal')
                    ->label('Tanggal')
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
                    ->columnSpanFull(),
            ])
            ->recordActions([
                // — Batalkan billing (tab aktif) —
                Action::make('batalkan')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(): bool => $this->activeTab === 'aktif')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Billing')
                        ->modalDescription(fn(Tax $record) => "Apakah Anda yakin ingin membatalkan Kode Pembayaran Aktif {$record->getPreferredPaymentCode()}?")
                    ->schema([
                        Textarea::make('cancellation_reason')
                            ->label('Alasan Pembatalan')
                            ->required()
                            ->minLength(5)
                            ->maxLength(500)
                            ->placeholder('Tuliskan alasan pembatalan Kode Pembayaran Aktif...'),
                    ])
                    ->action(function (Tax $record, array $data): void {
                        $record->update([
                            'status' => TaxStatus::Cancelled,
                            'cancelled_at' => now(),
                            'cancelled_by' => auth()->id(),
                            'cancellation_reason' => $data['cancellation_reason'],
                        ]);
                        $record->delete(); // soft-delete

                        Notification::make()
                            ->success()
                            ->title('Billing Dibatalkan')
                            ->body("Kode Pembayaran Aktif {$record->getPreferredPaymentCode()} berhasil dibatalkan.")
                            ->send();
                    }),

                // — Pulihkan billing (tab dibatalkan) —
                Action::make('pulihkan')
                    ->label('Pulihkan')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn(): bool => $this->activeTab === 'dibatalkan')
                    ->requiresConfirmation()
                    ->modalHeading('Pulihkan Billing')
                    ->modalDescription(fn(Tax $record) => "Apakah Anda yakin ingin memulihkan Kode Pembayaran Aktif {$record->getPreferredPaymentCode()}?")
                    ->action(function (Tax $record): void {
                        $record->restore(); // undo soft-delete

                        $record->update([
                            'status' => TaxStatus::Pending,
                            'cancelled_at' => null,
                            'cancelled_by' => null,
                            'cancellation_reason' => null,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Billing Dipulihkan')
                            ->body("Kode Pembayaran Aktif {$record->getPreferredPaymentCode()} berhasil dipulihkan.")
                            ->send();
                    }),

                // — Lihat detail dokumen —
                ActionGroup::make([
                    Action::make('cetak_billing')
                        ->label('Cetak Billing')
                        ->icon('heroicon-o-printer')
                        ->url(fn(Tax $record) => route('portal.billing.document.show', $record->id))
                        ->openUrlInNewTab(),
                ])->label('Dokumen')->icon('heroicon-m-document-text'),
            ])
            ->toolbarActions([]);
    }

    protected function getTableQuery(): Builder
    {
        // Hanya tampilkan billing self_assessment
        $selfAssessmentIds = JenisPajak::where('tipe_assessment', 'self_assessment')->pluck('id');

        if ($this->activeTab === 'dibatalkan') {
            return Tax::onlyTrashed()
                ->whereIn('jenis_pajak_id', $selfAssessmentIds)
                ->with(['taxObject', 'cancelledByUser']);
        }

        return Tax::query()
            ->whereIn('jenis_pajak_id', $selfAssessmentIds)
            ->whereIn('status', [TaxStatus::Pending, TaxStatus::Paid, TaxStatus::Verified, TaxStatus::Expired])
            ->with(['taxObject']);
    }
}
