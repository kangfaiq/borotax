<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use App\Filament\Resources\MeterReportResource\Pages\ListMeterReports;
use App\Filament\Resources\MeterReportResource\Pages;
use App\Domain\AirTanah\Models\MeterReport;
use App\Domain\AirTanah\Models\NpaAirTanah;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\TarifPajak;
use App\Domain\Tax\Models\TaxObject;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Domain\Shared\Services\NotificationService;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

class MeterReportResource extends Resource
{
    protected static ?string $model = MeterReport::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-camera';

    protected static string | \UnitEnum | null $navigationGroup = 'Verifikasi';

    protected static ?string $navigationLabel = 'Laporan Air Tanah';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::submitted()->count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Laporan')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('user_name')
                            ->label('Pelapor')
                            ->disabled(),
                        TextInput::make('waterObject.name')
                            ->label('Objek Air Tanah')
                            ->disabled(),
                        DateTimePicker::make('reported_at')
                            ->label('Waktu Lapor')
                            ->disabled(),
                        TextInput::make('status')->disabled(),
                    ])->columns(2),

                Section::make('Data Meteran')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('meter_reading_before')
                            ->label('Meter Awal')
                            ->disabled(),
                        TextInput::make('meter_reading_after')
                            ->label('Meter Akhir')
                            ->disabled(),
                        TextInput::make('usage')
                            ->label('Pemakaian (m3)')
                            ->disabled(),
                        Placeholder::make('photo_preview')
                            ->label('Foto Meteran')
                            ->content(fn($record) => $record?->photo_url
                                ? view('filament.components.image-preview', ['url' => $record->photo_url])
                                : 'Tidak ada foto'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reported_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('user_name')
                    ->label('Pelapor')
                    ->searchable(),
                TextColumn::make('waterObject.name')
                    ->label('Lokasi'),
                TextColumn::make('usage')
                    ->label('Usage (m3)')
                    ->numeric(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'submitted' => 'warning',
                        'processing' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Menunggu',
                        'processing' => 'Diproses',
                        'approved' => 'Disetujui',
                    ]),
            
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('process')
                    ->label('Proses SKPD')
                    ->icon('heroicon-o-calculator')
                    ->color('info')
                    ->visible(fn(MeterReport $record) => $record->status === 'submitted' && auth()->user()->can('update', $record))
                    ->schema(function (MeterReport $record) {
                        // Auto-lookup NPA dan tarif
                        $defaultNpa = 1000;
                        $defaultTarif = 20;
                        $waterObj = $record->waterObject;
                        if ($waterObj && $waterObj->kelompok_pemakaian && $waterObj->kriteria_sda) {
                            $npa = NpaAirTanah::lookup($waterObj->kelompok_pemakaian, $waterObj->kriteria_sda);
                            if ($npa !== null) $defaultNpa = $npa;
                        }
                        $airTanah = JenisPajak::where('kode', '41108')->first();
                        if ($airTanah) {
                            $subPat = SubJenisPajak::where('jenis_pajak_id', $airTanah->id)->where('is_active', true)->first();
                            if ($subPat) {
                                $tarifLookup = TarifPajak::lookup($subPat->id);
                                if ($tarifLookup !== null) $defaultTarif = $tarifLookup;
                            }
                        }

                        return [
                            Select::make('sub_jenis_pajak_id')
                                ->label('Detail ABT')
                                ->options(SubJenisPajak::query()->pluck('nama', 'id')) // Harusnya filter kode 4.1.01.08
                                ->required(),
                            TextInput::make('meter_reading_before')
                                ->label('Meter Awal')
                                ->numeric()
                                ->default($record->meter_reading_before)
                                ->required(),
                            TextInput::make('meter_reading_after')
                                ->label('Meter Akhir')
                                ->numeric()
                                ->default($record->meter_reading_after)
                                ->required(),
                            TextInput::make('tarif_per_m3')
                                ->label('NPA (Rp/m3)')
                                ->numeric()
                                ->default($defaultNpa)
                                ->required(),
                            TextInput::make('tarif_persen')
                                ->label('Tarif Pajak (%)')
                                ->numeric()
                                ->default($defaultTarif)
                                ->required(),
                        ];
                    })
                    ->action(function (MeterReport $record, array $data): void {
                        // Recalculate usage
                        $usage = $data['meter_reading_after'] - $data['meter_reading_before'];
                        if ($usage < 0)
                            $usage = 0;

                        // Hitung Pajak
                        $dasar = $usage * $data['tarif_per_m3'];
                        $pajak = $dasar * ($data['tarif_persen'] / 100);

                        // Collect dasar hukum for SKPD
                        $dasarHukumParts = [];
                        $waterObj = $record->waterObject;
                        if ($waterObj && $waterObj->kelompok_pemakaian && $waterObj->kriteria_sda) {
                            $npaRec = NpaAirTanah::active()->berlakuPada()
                                ->where('kelompok_pemakaian', $waterObj->kelompok_pemakaian)
                                ->where('kriteria_sda', $waterObj->kriteria_sda)->first();
                            if ($npaRec?->dasar_hukum) $dasarHukumParts[] = $npaRec->dasar_hukum;
                        }
                        $airTanahJp = JenisPajak::where('kode', '41108')->first();
                        if ($airTanahJp) {
                            $subPat = SubJenisPajak::where('jenis_pajak_id', $airTanahJp->id)->where('is_active', true)->first();
                            if ($subPat) {
                                $tarifInfo = TarifPajak::lookupWithDasarHukum($subPat->id);
                                if (!empty($tarifInfo['dasar_hukum'])) $dasarHukumParts[] = $tarifInfo['dasar_hukum'];
                            }
                        }
                        $dasarHukum = collect($dasarHukumParts)->unique()->implode('; ') ?: null;

                        // Create SKPD Draft
                        $skpd = SkpdAirTanah::create([
                            'nomor_skpd' => SkpdAirTanah::generateNomorSkpd() . ' (DRAFT)',
                            'meter_report_id' => $record->id,
                            'tax_object_id' => $record->tax_object_id,
                            'jenis_pajak_id' => JenisPajak::where('kode', '41108')->first()?->id,
                            'sub_jenis_pajak_id' => $data['sub_jenis_pajak_id'],
                            'nik_wajib_pajak' => $record->user_nik,
                            'nama_wajib_pajak' => $record->user_name,
                            'alamat_wajib_pajak' => $record->user->alamat ?? '-',
                            'nama_objek' => $record->waterObject->name,
                            'alamat_objek' => $record->waterObject->address,
                            'meter_reading_before' => $data['meter_reading_before'],
                            'meter_reading_after' => $data['meter_reading_after'],
                            'usage' => $usage,
                            'periode_bulan' => now()->format('Y-m'),
                            'tarif_per_m3' => $data['tarif_per_m3'],
                            'tarif_persen' => $data['tarif_persen'],
                            'dasar_pengenaan' => $dasar,
                            'jumlah_pajak' => $pajak,
                            'status' => 'draft',
                            'tanggal_buat' => now(),
                            'petugas_id' => auth()->id(),
                            'petugas_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                            'dasar_hukum' => $dasarHukum,
                        ]);

                        // Update Request Status
                        $record->update([
                            'status' => 'processing',
                            'skpd_id' => $skpd->id,
                            // Update meter reading if changed
                            'meter_reading_before' => $data['meter_reading_before'],
                            'meter_reading_after' => $data['meter_reading_after'],
                            'usage' => $usage,
                        ]);

                        // Notify WP: laporan meter sedang diproses
                        if ($record->user) {
                            NotificationService::notifyUserBoth(
                                $record->user,
                                'Laporan Meter Sedang Diproses',
                                'Laporan meter air tanah Anda sedang diproses oleh petugas. Draft SKPD telah dibuat dan menunggu verifikasi.',
                                'info',
                                actionUrl: route('portal.air-tanah.skpd-list'),
                            );
                        }

                        // Notify verifikator: draft SKPD baru perlu diverifikasi
                        NotificationService::notifyRole(
                            'verifikator',
                            'Draft SKPD Air Tanah Menunggu Verifikasi',
                            'Draft SKPD ABT untuk ' . ($record->user_name ?? 'WP') . ' perlu diverifikasi.',
                            actionUrl: \App\Filament\Resources\SkpdAirTanahResource::getUrl('view', ['record' => $skpd->id]),
                        );

                        Notification::make()
                            ->title('Draft SKPD ABT Berhasil Dibuat')
                            ->body('Status laporan diproses.')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
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
            'index' => ListMeterReports::route('/'),
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
