<?php

namespace App\Filament\Pages;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use App\Domain\AirTanah\Models\NpaAirTanah;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\KelompokLokasiJalan;
use App\Domain\Reklame\Models\ReklameTariff;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Reklame\Services\ReklameService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DaftarSkpdSaya extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static string | \UnitEnum | null $navigationGroup = 'Laporan Petugas';
    protected static ?string $navigationLabel = 'Daftar SKPD Saya';
    protected static ?string $title           = 'Daftar SKPD Saya';
    protected static ?int    $navigationSort  = 6;
    protected string  $view            = 'filament.pages.daftar-skpd-saya';

    public string $jenisSkpd = 'air_tanah';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'petugas';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'petugas';
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function setJenisSkpd(string $jenis): void
    {
        $this->jenisSkpd = $jenis;
        $this->resetPage();
        $this->resetTableSearch();
        $this->resetTableFiltersForm();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->jenisSkpd === 'air_tanah'
                ? SkpdAirTanah::query()->where('petugas_id', auth()->id())
                : SkpdReklame::query()->where('petugas_id', auth()->id()))
            ->defaultSort('tanggal_buat', 'desc')
            ->columns([
                TextColumn::make('tanggal_buat')
                    ->label('Tgl Buat')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                TextColumn::make('nomor_skpd')
                    ->searchable(),
                TextColumn::make('nama_wajib_pajak')
                    ->searchable(),
                TextColumn::make('nama_objek')
                    ->label('Objek')
                    ->visible(fn () => $this->jenisSkpd === 'air_tanah'),
                TextColumn::make('nama_reklame')
                    ->label('Objek')
                    ->visible(fn () => $this->jenisSkpd === 'reklame'),
                TextColumn::make('jumlah_pajak')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (?string $state): string => $state && now()->gt($state) ? 'danger' : 'success'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('catatan_verifikasi')
                    ->label('Catatan')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->catatan_verifikasi)
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),
                Filter::make('tanggal_buat')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->default(now()->startOfYear()),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->default(now()->endOfYear()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('tanggal_buat', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('tanggal_buat', '<=', $date));
                    })
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
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('cetak_skpd')
                        ->label('Cetak SKPD')
                        ->icon('heroicon-o-printer')
                        ->url(fn ($record) => $this->jenisSkpd === 'air_tanah'
                            ? route('skpd-air-tanah.show', $record->id)
                            : route('skpd-reklame.show', $record->id))
                        ->openUrlInNewTab(),
                    Action::make('unduh_skpd')
                        ->label('Unduh SKPD')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($record) {
                            if ($this->jenisSkpd === 'air_tanah') {
                                $record->load(['waterObject', 'jenisPajak', 'subJenisPajak']);
                                $view = 'documents.skpd-air-tanah';
                                $prefix = 'SKPD_Air_Tanah_';
                            } else {
                                $record->load(['reklameObject', 'jenisPajak', 'subJenisPajak']);
                                $view = 'documents.skpd-reklame';
                                $prefix = 'SKPD_Reklame_';
                            }

                            $pimpinan = $record->pimpinan_id
                                ? Pimpinan::find($record->pimpinan_id)
                                : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

                            $pdf = Pdf::loadView($view, [
                                'skpd' => $record,
                                'pimpinan' => $pimpinan,
                                'isPdf' => true,
                            ]);

                            $pdf->setPaper([0, 0, 609.449, 935.433], 'portrait');

                            $filename = $prefix . str_replace([' ', '/'], '_', $record->nomor_skpd) . '.pdf';

                            return response()->streamDownload(fn () => print($pdf->output()), $filename, [
                                'Content-Type' => 'application/pdf',
                            ]);
                        }),
                ])
                    ->icon('heroicon-m-document-text')
                    ->visible(fn ($record) => in_array($record->status, ['draft', 'disetujui'])),
                Action::make('revisi')
                    ->label('Revisi & Ajukan Ulang')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'ditolak')
                    ->modalHeading('Revisi SKPD')
                    ->modalDescription(fn ($record) => 'Catatan penolakan: ' . ($record->catatan_verifikasi ?? '-'))
                    ->modalSubmitActionLabel('Simpan & Ajukan Ulang')
                    ->fillForm(fn ($record) => $this->jenisSkpd === 'reklame'
                        ? [
                            'nama_reklame' => $record->nama_reklame,
                            'alamat_reklame' => $record->alamat_reklame,
                            'sub_jenis_pajak_id' => $record->sub_jenis_pajak_id,
                            'kelompok_lokasi' => $record->kelompok_lokasi,
                            'bentuk' => $record->bentuk ?? 'persegi',
                            'panjang' => $record->panjang,
                            'lebar' => $record->lebar,
                            'tinggi' => $record->tinggi,
                            'sisi_atas' => $record->sisi_atas,
                            'sisi_bawah' => $record->sisi_bawah,
                            'diameter' => $record->diameter,
                            'diameter2' => $record->diameter2,
                            'alas' => $record->alas,
                            'jumlah_muka' => $record->jumlah_muka,
                            'satuan_waktu' => $record->satuan_waktu,
                            'durasi' => $record->durasi,
                            'jumlah_reklame' => $record->jumlah_reklame,
                            'lokasi_penempatan' => $record->lokasi_penempatan,
                            'jenis_produk' => $record->jenis_produk,
                            'masa_berlaku_mulai' => $record->masa_berlaku_mulai,
                        ]
                        : [
                            'nama_objek' => $record->nama_objek,
                            'alamat_objek' => $record->alamat_objek,
                            'kecamatan' => $record->kecamatan,
                            'kelurahan' => $record->kelurahan,
                            'meter_reading_before' => $record->meter_reading_before,
                            'meter_reading_after' => $record->meter_reading_after,
                            'periode_bulan' => $record->periode_bulan,
                        ]
                    )
                    ->schema(fn ($record) => $this->jenisSkpd === 'reklame'
                        ? [
                            Section::make('Data Reklame')
                                ->columnSpanFull()->schema([
                                TextInput::make('nama_reklame')->label('Nama Reklame')->required(),
                                Textarea::make('alamat_reklame')->label('Alamat Reklame')->required()->rows(2),
                                Select::make('sub_jenis_pajak_id')
                                    ->label('Jenis Reklame')
                                    ->options(fn () => SubJenisPajak::where('jenis_pajak_id', $record->jenis_pajak_id)
                                        ->where('is_active', true)
                                        ->orderBy('urutan')
                                        ->pluck('nama', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->reactive(),
                                Select::make('kelompok_lokasi')
                                    ->label('Kelompok Lokasi')
                                    ->options(KelompokLokasiJalan::getKelompokOptions())
                                    ->required()
                                    ->reactive(),
                            ])->columns(2),
                            Section::make('Dimensi Reklame')
                                ->columnSpanFull()->schema([
                                Select::make('bentuk')
                                    ->label('Bentuk')
                                    ->options([
                                        'persegi' => 'Persegi / Persegi Panjang',
                                        'trapesium' => 'Trapesium',
                                        'lingkaran' => 'Lingkaran',
                                        'elips' => 'Elips',
                                        'segitiga' => 'Segitiga',
                                    ])
                                    ->required()
                                    ->reactive(),
                                TextInput::make('panjang')->label('Panjang (m)')->numeric()
                                    ->visible(fn ($get) => ($get('bentuk') ?? 'persegi') === 'persegi')
                                    ->required(fn ($get) => ($get('bentuk') ?? 'persegi') === 'persegi'),
                                TextInput::make('lebar')->label('Lebar (m)')->numeric()
                                    ->visible(fn ($get) => ($get('bentuk') ?? 'persegi') === 'persegi')
                                    ->required(fn ($get) => ($get('bentuk') ?? 'persegi') === 'persegi'),
                                TextInput::make('sisi_atas')->label('Sisi Atas (m)')->numeric()
                                    ->visible(fn ($get) => $get('bentuk') === 'trapesium')
                                    ->required(fn ($get) => $get('bentuk') === 'trapesium'),
                                TextInput::make('sisi_bawah')->label('Sisi Bawah (m)')->numeric()
                                    ->visible(fn ($get) => $get('bentuk') === 'trapesium')
                                    ->required(fn ($get) => $get('bentuk') === 'trapesium'),
                                TextInput::make('tinggi')->label('Tinggi (m)')->numeric()
                                    ->visible(fn ($get) => in_array($get('bentuk'), ['trapesium', 'segitiga']))
                                    ->required(fn ($get) => in_array($get('bentuk'), ['trapesium', 'segitiga'])),
                                TextInput::make('diameter')
                                    ->label(fn ($get) => $get('bentuk') === 'elips' ? 'Diameter 1 (m)' : 'Diameter (m)')
                                    ->numeric()
                                    ->visible(fn ($get) => in_array($get('bentuk'), ['lingkaran', 'elips']))
                                    ->required(fn ($get) => in_array($get('bentuk'), ['lingkaran', 'elips'])),
                                TextInput::make('diameter2')->label('Diameter 2 (m)')->numeric()
                                    ->visible(fn ($get) => $get('bentuk') === 'elips')
                                    ->required(fn ($get) => $get('bentuk') === 'elips'),
                                TextInput::make('alas')->label('Alas (m)')->numeric()
                                    ->visible(fn ($get) => $get('bentuk') === 'segitiga')
                                    ->required(fn ($get) => $get('bentuk') === 'segitiga'),
                            ])->columns(2),
                            TextInput::make('jumlah_muka')->label('Jumlah Muka')->numeric()->integer()->required(),
                            Select::make('satuan_waktu')
                                ->label('Satuan Waktu')
                                ->options(fn ($get) => ReklameTariff::getAvailableSatuanWaktu(
                                    $get('sub_jenis_pajak_id') ?? $record->sub_jenis_pajak_id,
                                    $get('kelompok_lokasi') ?? $record->kelompok_lokasi
                                ))
                                ->required()
                                ->reactive(),
                            TextInput::make('durasi')->label('Durasi')->numeric()->integer()->required(),
                            TextInput::make('jumlah_reklame')->label('Jumlah Reklame')->numeric()->integer()->required(),
                            Select::make('lokasi_penempatan')
                                ->label('Lokasi Penempatan')
                                ->options([
                                    'luar_ruangan' => 'Luar Ruangan',
                                    'dalam_ruangan' => 'Dalam Ruangan',
                                ])->required(),
                            Select::make('jenis_produk')
                                ->label('Jenis Produk')
                                ->options([
                                    'non_rokok' => 'Non Rokok',
                                    'rokok' => 'Rokok',
                                ])->required(),
                            DatePicker::make('masa_berlaku_mulai')
                                ->label('Masa Berlaku Mulai')
                                ->required(),
                        ]
                        : [
                            Section::make('Data Objek')
                                ->columnSpanFull()->schema([
                                TextInput::make('nama_objek')->label('Nama Objek')->required(),
                                Textarea::make('alamat_objek')->label('Alamat Objek')->required()->rows(2),
                                TextInput::make('kecamatan')->label('Kecamatan')->required(),
                                TextInput::make('kelurahan')->label('Kelurahan')->required(),
                            ])->columns(2),
                            TextInput::make('meter_reading_before')->label('Meter Awal')->numeric(),
                            TextInput::make('meter_reading_after')->label('Meter Akhir')->numeric()->required(),
                            TextInput::make('periode_bulan')
                                ->label('Periode (YYYY-MM)')
                                ->required()
                                ->placeholder('2026-03'),
                        ]
                    )
                    ->action(function ($record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            if ($this->jenisSkpd === 'reklame') {
                                // Hitung luas dari dimensi berdasarkan bentuk
                                $bentuk = $data['bentuk'] ?? 'persegi';
                                $luas = match ($bentuk) {
                                    'trapesium' => (((float) ($data['sisi_atas'] ?? 0) + (float) ($data['sisi_bawah'] ?? 0)) / 2) * (float) ($data['tinggi'] ?? 0),
                                    'lingkaran' => M_PI * pow((float) ($data['diameter'] ?? 0) / 2, 2),
                                    'elips' => M_PI * ((float) ($data['diameter'] ?? 0) / 2) * ((float) ($data['diameter2'] ?? 0) / 2),
                                    'segitiga' => ((float) ($data['alas'] ?? 0) * (float) ($data['tinggi'] ?? 0)) / 2,
                                    default => (float) ($data['panjang'] ?? 0) * (float) ($data['lebar'] ?? 0),
                                };
                                $luas = round($luas, 2);

                                $calc = app(ReklameService::class)->calculateTax(
                                    $data['sub_jenis_pajak_id'],
                                    $data['kelompok_lokasi'],
                                    $data['satuan_waktu'],
                                    $luas,
                                    (int) $data['jumlah_muka'],
                                    (int) $data['durasi'],
                                    (int) $data['jumlah_reklame'],
                                    $data['lokasi_penempatan'],
                                    $data['jenis_produk']
                                );

                                $mulai = Carbon::parse($data['masa_berlaku_mulai']);
                                $durasi = max(1, (int) $data['durasi']);
                                $sampai = match ($data['satuan_waktu']) {
                                    'perTahun' => $mulai->copy()->addYears($durasi)->subDay(),
                                    'perBulan' => $mulai->copy()->addMonths($durasi)->subDay(),
                                    'perMinggu', 'perMingguPerBuah' => $mulai->copy()->addWeeks($durasi)->subDay(),
                                    'perHari', 'perHariPerBuah' => $mulai->copy()->addDays($durasi)->subDay(),
                                    default => $mulai->copy()->addYears($durasi)->subDay(),
                                };

                                $record->update([
                                    'nama_reklame' => $data['nama_reklame'],
                                    'alamat_reklame' => $data['alamat_reklame'],
                                    'sub_jenis_pajak_id' => $data['sub_jenis_pajak_id'],
                                    'kelompok_lokasi' => $data['kelompok_lokasi'],
                                    'bentuk' => $bentuk,
                                    'panjang' => $data['panjang'] ?? null,
                                    'lebar' => $data['lebar'] ?? null,
                                    'tinggi' => $data['tinggi'] ?? null,
                                    'sisi_atas' => $data['sisi_atas'] ?? null,
                                    'sisi_bawah' => $data['sisi_bawah'] ?? null,
                                    'diameter' => $data['diameter'] ?? null,
                                    'diameter2' => $data['diameter2'] ?? null,
                                    'alas' => $data['alas'] ?? null,
                                    'luas_m2' => $luas,
                                    'jumlah_muka' => $data['jumlah_muka'],
                                    'satuan_waktu' => $data['satuan_waktu'],
                                    'satuan_label' => $calc['satuan_label'],
                                    'durasi' => $data['durasi'],
                                    'jumlah_reklame' => $data['jumlah_reklame'],
                                    'lokasi_penempatan' => $data['lokasi_penempatan'],
                                    'jenis_produk' => $data['jenis_produk'],
                                    'masa_berlaku_mulai' => $data['masa_berlaku_mulai'],
                                    'masa_berlaku_sampai' => $sampai,
                                    'tarif_pokok' => $calc['tarif_pokok'],
                                    'nspr' => $calc['nspr'],
                                    'njopr' => $calc['njopr'],
                                    'penyesuaian_lokasi' => $calc['penyesuaian_lokasi'],
                                    'penyesuaian_produk' => $calc['penyesuaian_produk'],
                                    'nilai_strategis' => $calc['nilai_strategis'],
                                    'pokok_pajak_dasar' => $calc['pokok_pajak_dasar'],
                                    'dasar_pengenaan' => $calc['dasar_pengenaan'],
                                    'jumlah_pajak' => $calc['jumlah_pajak'],
                                    'status' => 'draft',
                                    'catatan_verifikasi' => null,
                                    'tanggal_verifikasi' => null,
                                    'verifikator_id' => null,
                                    'verifikator_nama' => null,
                                    'kode_billing' => null,
                                    'jatuh_tempo' => null,
                                    'pimpinan_id' => null,
                                ]);

                                // Sync ke objek pajak dilakukan saat verifikasi (approve), bukan saat revisi
                            } else {
                                // Air Tanah
                                $meterBefore = (float) ($data['meter_reading_before'] ?? 0);
                                $meterAfter = (float) $data['meter_reading_after'];
                                $usage = round(max(0, $meterAfter - $meterBefore), 2);
                                $periodeBulan = $data['periode_bulan'];

                                $waterObj = $record->waterObject;
                                $tarifTiers = null;
                                if ($waterObj && $waterObj->kelompok_pemakaian && $waterObj->kriteria_sda) {
                                    $tarifTiers = NpaAirTanah::lookupTiers(
                                        $waterObj->kelompok_pemakaian,
                                        $waterObj->kriteria_sda,
                                        $periodeBulan . '-01'
                                    );
                                }
                                if (!$tarifTiers) {
                                    $tarifTiers = json_decode($record->tarif_per_m3, true);
                                }

                                $dasar = 0;
                                if ($tarifTiers && is_array($tarifTiers)) {
                                    $remainingUsage = $usage;
                                    foreach ($tarifTiers as $tier) {
                                        if ($remainingUsage <= 0) break;
                                        $maxVolInTier = floatval($tier['max_vol'] - $tier['min_vol'] + 1);
                                        if ($tier['min_vol'] == 0) {
                                            $maxVolInTier = floatval($tier['max_vol']);
                                        }
                                        if ($tier['max_vol'] == null || $tier['max_vol'] >= 99999999) {
                                            $maxVolInTier = $remainingUsage;
                                        }
                                        $usedInTier = min($remainingUsage, $maxVolInTier);
                                        $dasar += $usedInTier * $tier['npa'];
                                        $remainingUsage = round($remainingUsage - $usedInTier, 2);
                                    }
                                }

                                $tarifPersen = (float) ($record->tarif_persen ?: 20);
                                $pajak = $dasar * ($tarifPersen / 100);

                                // Simpan original untuk perbandingan
                                $originalMeterAfter = $record->meter_reading_after;

                                $record->update([
                                    'nama_objek' => $data['nama_objek'],
                                    'alamat_objek' => $data['alamat_objek'],
                                    'kecamatan' => $data['kecamatan'],
                                    'kelurahan' => $data['kelurahan'],
                                    'meter_reading_before' => $meterBefore,
                                    'meter_reading_after' => $meterAfter,
                                    'usage' => $usage,
                                    'periode_bulan' => $periodeBulan,
                                    'tarif_per_m3' => json_encode($tarifTiers),
                                    'dasar_pengenaan' => $dasar,
                                    'jumlah_pajak' => $pajak,
                                    'status' => 'draft',
                                    'catatan_verifikasi' => null,
                                    'tanggal_verifikasi' => null,
                                    'verifikator_id' => null,
                                    'verifikator_nama' => null,
                                    'kode_billing' => null,
                                    'jatuh_tempo' => null,
                                    'pimpinan_id' => null,
                                ]);

                                // Sync ke objek pajak dilakukan saat verifikasi (approve), bukan saat revisi
                            }
                        });

                        Notification::make()
                            ->title('SKPD berhasil direvisi')
                            ->body('SKPD telah diajukan ulang untuk verifikasi.')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Tidak ada SKPD ditemukan')
            ->emptyStateDescription('Tidak ada data pada filter yang dipilih.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
