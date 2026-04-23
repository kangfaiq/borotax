<?php

namespace App\Filament\Pages;

use Exception;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\AirTanah\Models\NpaAirTanah;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\Shared\Services\DecimalInputNormalizer;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\Tax\Models\TarifPajak;
use App\Domain\Shared\Services\NotificationService;
use App\Domain\Tax\Models\TaxObject;
use App\Filament\Resources\SkpdAirTanahResource;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class BuatSkpdAirTanah extends Page implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    protected static string | \BackedEnum | null $navigationIcon  = 'heroicon-o-document-check';
    protected static string | \UnitEnum | null $navigationGroup = 'Laporan Petugas';
    protected static ?string $navigationLabel = 'Buat SKPD Air Tanah';
    protected static ?string $title           = 'Buat SKPD Air Tanah';
    protected static ?int    $navigationSort  = 5;
    protected string  $view            = 'filament.pages.buat-skpd-air-tanah';

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'petugas', 'verifikator']);
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'petugas', 'verifikator']);
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    // ── State: Search ───────────────────────────────────────────────────────
    public ?string $searchKeyword    = null;
    public array   $searchResults    = [];
    public ?string $expandedDetailId = null;

    // ── State: Selection ────────────────────────────────────────────────────
    public ?string $selectedWaterObjectId   = null;
    public ?array  $selectedWaterObjectData = null;
    public ?array  $wajibPajakData          = null;

    // ── State: Skenario Detection ───────────────────────────────────────────
    public bool    $isNewObject     = false;   // Skenario 1: objek baru
    public bool    $usesMeter       = true;    // Skenario 2: false = tanpa meteran
    public bool    $hasHistory      = false;   // Skenario 4: ada histori meter
    public bool    $isMeterChange   = false;   // Skenario 3: pergantian meteran

    // ── State: Form Perhitungan ─────────────────────────────────────────────
    public string|float|null $meterReadingBefore = null;
    public string|float|null $meterReadingAfter  = null;
    public string|float|null $directUsage        = null; // Skenario 2: pemakaian langsung (tanpa meter)
    public string|float|null $meterOldEnd        = null; // Skenario 3: meter akhir meteran lama
    public string|float|null $meterNewStart      = null; // Skenario 3: meter awal meteran baru
    public string|float|null $meterNewEnd        = null; // Skenario 3: meter akhir meteran baru
    public ?string $catatanMeter       = null; // Skenario 3: catatan pergantian

    public ?string $periodeBulan       = null;
    public ?array  $tarifTiers         = null;
    public ?float  $tarifPersen        = null;
    public ?string $dasarHukumTarif    = null;
    public ?string $dasarHukumNpa      = null;
    public $lampiranUploadTemp         = null;

    // ── State: Result ───────────────────────────────────────────────────────
    public ?array $skpdResult = null;

    // ────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->periodeBulan = now()->format('Y-m');
    }

    public function updatingLampiranUploadTemp(): void
    {
        $this->deleteTemporaryLampiran();
    }

    // ── Live Search ─────────────────────────────────────────────────────────

    public function updatedSearchKeyword(): void
    {
        $this->search();
    }

    public function updatedPeriodeBulan(): void
    {
        if ($this->selectedWaterObjectId) {
            $this->selectObject($this->selectedWaterObjectId);
        }
    }

    public function search(): void
    {
        $keyword = trim($this->searchKeyword ?? '');

        if (strlen($keyword) < 3) {
            $this->searchResults = [];
            return;
        }

        try {
            $results = [];

            // NIK — all-digit ≥ 5 chars → search by hash
            if (ctype_digit($keyword) && strlen($keyword) >= 5) {
                $nikHash = WaterObject::generateHash($keyword);
                $results = WaterObject::where('nik_hash', $nikHash)
                    ->where('is_active', true)
                    ->get()
                    ->map(fn ($obj) => $this->mapWaterObject($obj))
                    ->toArray();
            }

            // Fallback: NPWPD / nama objek / NIK (decrypt + filter)
            if (empty($results)) {
                $kw = strtolower($keyword);
                $results = WaterObject::where('is_active', true)
                    ->get()
                    ->filter(fn ($obj) =>
                        str_contains(strtolower($obj->npwpd ?? ''), $kw)
                        || str_contains(strtolower($obj->nama_objek ?? ''), $kw)
                        || str_contains(strtolower($obj->alamat_objek ?? ''), $kw)
                        || str_contains(strtolower($obj->nik ?? ''), $kw)
                    )
                    ->take(20)
                    ->map(fn ($obj) => $this->mapWaterObject($obj))
                    ->values()
                    ->toArray();
            }

            $this->searchResults    = $results;
            $this->expandedDetailId = null;
        } catch (Exception $e) {
            $this->searchResults = [];
        }
    }

    public function toggleDetail(string $id): void
    {
        $this->expandedDetailId = $this->expandedDetailId === $id ? null : $id;
    }

    // ── Object Selection ────────────────────────────────────────────────────

    public function selectObject(string $id): void
    {
        $selected = collect($this->searchResults)->firstWhere('id', $id);
        if (!$selected) {
            return;
        }

        $this->selectedWaterObjectId   = $id;
        $this->selectedWaterObjectData = $selected;
        $this->skpdResult              = null;

        // Reset meter change state
        $this->isMeterChange = false;
        $this->meterOldEnd   = null;
        $this->meterNewStart = null;
        $this->meterNewEnd   = null;
        $this->catatanMeter  = null;
        $this->directUsage   = null;

        // ── Deteksi Skenario ─────────────────────────────────────────────
        $waterObj = WaterObject::find($id);
        $this->usesMeter = $waterObj->uses_meter ?? true;

        $hasExistingSkpd = SkpdAirTanah::where('tax_object_id', $id)
            ->whereIn('status', ['draft', 'disetujui'])
            ->exists();

        $lastMeter = $selected['last_meter_reading'];

        if (!$this->usesMeter) {
            // Skenario 2: Objek tidak pakai meteran
            $this->isNewObject  = false;
            $this->hasHistory   = false;
            $this->meterReadingBefore = null;
            $this->meterReadingAfter  = null;
        } elseif (is_null($lastMeter) && !$hasExistingSkpd) {
            // Skenario 1: Objek baru, belum ada histori meter
            $this->isNewObject  = true;
            $this->hasHistory   = false;
            $this->meterReadingBefore = null;
            $this->meterReadingAfter  = null;
        } else {
            // Skenario 4 (default): Objek lama dengan histori meter
            $this->isNewObject  = false;
            $this->hasHistory   = true;
            $this->meterReadingBefore = $lastMeter ?? 0;
            $this->meterReadingAfter  = null;
        }

        // Auto-lookup tarif persen dari tarif_pajak berdasarkan sub_jenis_pajak Air Tanah
        $airTanah = JenisPajak::where('kode', '41108')->first();
        if ($airTanah) {
            $subPat = SubJenisPajak::where('jenis_pajak_id', $airTanah->id)
                ->where('is_active', true)->first();
            if ($subPat) {
                $tarifInfo = TarifPajak::lookupWithDasarHukum($subPat->id);
                $this->tarifPersen = $tarifInfo['tarif_persen'] ?? $subPat->tarif_persen;
                $this->dasarHukumTarif = $tarifInfo['dasar_hukum'] ?? null;
            }
        }

        // Auto-lookup NPA dari npa_air_tanah berdasarkan kelompok_pemakaian + kriteria_sda objek
        if ($waterObj && $waterObj->kelompok_pemakaian && $waterObj->kriteria_sda) {
            $npa = NpaAirTanah::lookupTiers($waterObj->kelompok_pemakaian, $waterObj->kriteria_sda, $this->periodeBulan . '-01');
            if ($npa !== null) {
                $this->tarifTiers = $npa;
                $npaRecord = NpaAirTanah::active()->berlakuPada($this->periodeBulan . '-01')
                    ->where('kelompok_pemakaian', NpaAirTanah::resolveKelompok($waterObj->kelompok_pemakaian))
                    ->where('kriteria_sda', NpaAirTanah::resolveKriteria($waterObj->kriteria_sda))
                    ->first();
                $this->dasarHukumNpa = $npaRecord?->dasar_hukum;
            } else {
                $this->tarifTiers = null;
            }
        } else {
            $this->tarifTiers = null;
        }

        // Lookup WP data from nik_hash
        $this->wajibPajakData = null;
        if (!empty($selected['nik_hash'])) {
            $wp = WajibPajak::where('nik_hash', $selected['nik_hash'])
                ->where('status', 'disetujui')
                ->first();

            if ($wp) {
                $this->wajibPajakData = [
                    'id'           => $wp->id,
                    'user_id'      => $wp->user_id,
                    'nik'          => $wp->nik,
                    'nama_lengkap' => $wp->nama_lengkap,
                    'alamat'       => $wp->alamat,
                    'npwpd'        => $wp->npwpd,
                ];
            }
        }
    }

    // ── Toggle Pergantian Meteran ────────────────────────────────────────────

    public function toggleMeterChange(): void
    {
        $this->isMeterChange = !$this->isMeterChange;
        if (!$this->isMeterChange) {
            $this->meterOldEnd   = null;
            $this->meterNewStart = null;
            $this->meterNewEnd   = null;
            $this->catatanMeter  = null;
        }
    }

    // ── Preview Pajak ───────────────────────────────────────────────────────

    public function getPreviewPajak(): ?array
    {
        if (!$this->selectedWaterObjectData) {
            return null;
        }

        $this->normalizeDecimalInputs();

        // ── Hitung Usage berdasarkan skenario ────────────────────────────
        $usage = 0;

        if (!$this->usesMeter) {
            // Skenario 2: Input langsung
            $usage = round(max(0.00, (float) ($this->directUsage ?: 0)), 2);
        } elseif ($this->isMeterChange) {
            // Skenario 3: Pergantian meteran
            $oldUsage = round(max(0.00, (float) ($this->meterOldEnd ?: 0) - (float) ($this->meterReadingBefore ?: 0)), 2);
            $newUsage = round(max(0.00, (float) ($this->meterNewEnd ?: 0) - (float) ($this->meterNewStart ?: 0)), 2);
            $usage = round($oldUsage + $newUsage, 2);
        } else {
            // Skenario 1 & 4: Normal meter awal → akhir
            $before = (float) ($this->meterReadingBefore ?: 0);
            $after  = (float) ($this->meterReadingAfter ?: 0);
            $usage  = round(max(0.00, $after - $before), 2);
        }

        $tarif = (float) ($this->tarifPersen ?: 0);

        $dasar = 0;
        $tierBreakdown = [];

        if ($this->tarifTiers && is_array($this->tarifTiers)) {
            $remainingUsage = $usage;

            foreach ($this->tarifTiers as $i => $tier) {
                if ($remainingUsage <= 0) break;

                $maxVolInTier = floatval($tier['max_vol'] - $tier['min_vol'] + 1);
                if ($tier['min_vol'] == 0) {
                    $maxVolInTier = floatval($tier['max_vol']);
                }
                if ($tier['max_vol'] == null || $tier['max_vol'] >= 99999999) {
                    $maxVolInTier = $remainingUsage;
                }

                $usedInTier = min($remainingUsage, $maxVolInTier);
                $npaInTier = $usedInTier * $tier['npa'];
                $dasar += $npaInTier;
                $remainingUsage = round($remainingUsage - $usedInTier, 2);

                $tierBreakdown[] = [
                    'tier'     => $i + 1,
                    'min_vol'  => $tier['min_vol'],
                    'max_vol'  => $tier['max_vol'],
                    'volume'   => $usedInTier,
                    'npa_rate' => $tier['npa'],
                    'npa'      => $npaInTier,
                ];
            }
        }

        $pajak = $dasar * ($tarif / 100);

        return [
            'usage'     => $usage,
            'dasar'     => $dasar,
            'pajak'     => $pajak,
            'tiers'     => $tierBreakdown,
        ];
    }

    // ── Submit ──────────────────────────────────────────────────────────────

    public function buatSkpd(): void
    {
        $this->normalizeDecimalInputs();

        if (!$this->selectedWaterObjectData) {
            Notification::make()->warning()->title('Pilih objek air tanah terlebih dahulu')->send();
            return;
        }
        if (!$this->wajibPajakData) {
            Notification::make()->warning()->title('Data wajib pajak tidak ditemukan untuk objek ini')->send();
            return;
        }

        // ── Validasi per skenario ────────────────────────────────────────
        if (!$this->usesMeter) {
            // Skenario 2: validasi penggunaan langsung
            if (!$this->directUsage || $this->directUsage <= 0) {
                Notification::make()->warning()->title('Masukkan penggunaan air (m³)')->send();
                return;
            }
        } elseif ($this->isMeterChange) {
            // Skenario 3: validasi 4 field meter
            if (!$this->meterOldEnd || $this->meterOldEnd <= ($this->meterReadingBefore ?? 0)) {
                Notification::make()->warning()->title('Meter akhir (meteran lama) harus lebih besar dari meter awal')->send();
                return;
            }
            if (is_null($this->meterNewStart) || $this->meterNewStart < 0) {
                Notification::make()->warning()->title('Masukkan angka meter awal meteran baru')->send();
                return;
            }
            if (!$this->meterNewEnd || $this->meterNewEnd <= ($this->meterNewStart ?? 0)) {
                Notification::make()->warning()->title('Meter akhir (meteran baru) harus lebih besar dari meter awal (meteran baru)')->send();
                return;
            }
        } else {
            // Skenario 1 & 4: validasi normal
            if (!$this->meterReadingAfter || $this->meterReadingAfter <= 0) {
                Notification::make()->warning()->title('Masukkan angka meter akhir')->send();
                return;
            }
            if ($this->meterReadingAfter <= ($this->meterReadingBefore ?? 0)) {
                Notification::make()->warning()->title('Meter akhir harus lebih besar dari meter awal')->send();
                return;
            }
        }

        // Cek SKPD aktif untuk periode yang sama (kecuali mode pergantian meteran diizinkan)
        $existingSkpd = SkpdAirTanah::where('tax_object_id', $this->selectedWaterObjectId)
            ->whereIn('status', ['draft', 'disetujui'])
            ->where('periode_bulan', $this->periodeBulan)
            ->first();

        if ($existingSkpd) {
            Notification::make()
                ->danger()
                ->title('Objek air tanah sudah memiliki SKPD aktif')
                ->body('No: ' . $existingSkpd->nomor_skpd . ' untuk periode ' . $existingSkpd->periode_bulan . '.')
                ->send();
            return;
        }

        try {
            if ($this->lampiranUploadTemp) {
                validator(
                    ['lampiran' => $this->lampiranUploadTemp],
                    ['lampiran' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:1024']],
                    [
                        'lampiran.mimes' => 'Lampiran harus berupa JPG, JPEG, PNG, WEBP, atau PDF.',
                        'lampiran.max' => 'Ukuran lampiran maksimal 1 MB.',
                    ]
                )->validate();
            }

            $calc = $this->getPreviewPajak();
            if (!$calc) {
                 Notification::make()->warning()->title('Gagal melakukan perhitungan. Pastikan Tarif NPA sudah tersedia untuk Masa Pajak ini.')->send();
                 return;
            }
            
            $usage = $calc['usage'];
            $dasar = $calc['dasar'];
            $pajak = $calc['pajak'];

            $jenisPajak = JenisPajak::where('kode', '41108')->first();

            // Determine dasar hukum (gabungan NPA + tarif jika berbeda)
            $dasarHukum = collect([$this->dasarHukumNpa, $this->dasarHukumTarif])
                ->filter()->unique()->implode('; ');

            // ── Build meter reading values berdasarkan skenario ──────────
            $meterBefore = $this->meterReadingBefore;
            $meterAfter  = $this->meterReadingAfter;

            if (!$this->usesMeter) {
                // Skenario 2: schema live masih mewajibkan kolom meter, gunakan 0 sebagai sentinel non-meter.
                $meterBefore = 0;
                $meterAfter  = 0;
            } elseif ($this->isMeterChange) {
                // Skenario 3: simpan meter awal asli + meter akhir meteran baru sebagai meter_reading_after
                $meterAfter = $this->meterNewEnd;
            }

            $lampiranPath = null;

            if ($this->lampiranUploadTemp) {
                $lampiranPath = $this->lampiranUploadTemp->store('skpd-air-tanah/lampiran/' . now()->format('Y/m'), 'public');
            }

            $skpd = SkpdAirTanah::create([
                'nomor_skpd'         => SkpdAirTanah::generateNomorSkpd() . ' (DRAFT)',
                'tax_object_id'      => $this->selectedWaterObjectId,
                'jenis_pajak_id'     => $jenisPajak?->id ?? $this->selectedWaterObjectData['jenis_pajak_id'],
                'nik_wajib_pajak'    => $this->wajibPajakData['nik'],
                'nama_wajib_pajak'   => $this->wajibPajakData['nama_lengkap'],
                'alamat_wajib_pajak' => $this->wajibPajakData['alamat'] ?? '-',
                'nama_objek'         => $this->selectedWaterObjectData['nama'],
                'alamat_objek'       => $this->selectedWaterObjectData['alamat'],
                'nopd'               => $this->selectedWaterObjectData['nopd'],
                'kecamatan'          => $this->selectedWaterObjectData['kecamatan'],
                'kelurahan'          => $this->selectedWaterObjectData['kelurahan'],
                'meter_reading_before' => $meterBefore,
                'meter_reading_after'  => $meterAfter,
                'usage'              => $usage,
                'is_meter_change'    => $this->isMeterChange,
                'meter_old_end'      => $this->isMeterChange ? $this->meterOldEnd : null,
                'meter_new_start'    => $this->isMeterChange ? $this->meterNewStart : null,
                'meter_new_end'      => $this->isMeterChange ? $this->meterNewEnd : null,
                'catatan_meter'      => $this->isMeterChange ? $this->catatanMeter : null,
                'periode_bulan'      => $this->periodeBulan,
                'tarif_per_m3'       => json_encode($this->tarifTiers),
                'tarif_persen'       => $this->tarifPersen,
                'dasar_pengenaan'    => $dasar,
                'jumlah_pajak'       => $pajak,
                'status'             => 'draft',
                'tanggal_buat'       => now(),
                'petugas_id'         => auth()->id(),
                'petugas_nama'       => auth()->user()->nama_lengkap ?? auth()->user()->name,
                'dasar_hukum'        => $dasarHukum ?: null,
                'lampiran_path'      => $lampiranPath,
            ]);

            // Update last_meter_reading on tax object
            $updateData = ['last_report_date' => now()];
            if ($this->usesMeter) {
                if ($this->isMeterChange) {
                    // Skenario 3: set ke meter akhir meteran baru
                    $updateData['last_meter_reading'] = $this->meterNewEnd;
                } else {
                    $updateData['last_meter_reading'] = $this->meterReadingAfter;
                }
            }
            TaxObject::where('id', $this->selectedWaterObjectId)->update($updateData);

            $this->skpdResult = [
                'nomor_skpd'      => $skpd->nomor_skpd,
                'nama_wp'         => $this->wajibPajakData['nama_lengkap'],
                'nama_objek'      => $this->selectedWaterObjectData['nama'],
                'periode'         => $this->periodeBulan,
                'meter_before'    => $meterBefore,
                'meter_after'     => $meterAfter,
                'usage'           => $usage,
                'tarif_tiers'     => $this->tarifTiers,
                'dasar_pengenaan' => $dasar,
                'tarif_persen'    => $this->tarifPersen,
                'jumlah_pajak'    => $pajak,
                'is_meter_change' => $this->isMeterChange,
                'uses_meter'      => $this->usesMeter,
                'lampiran_path'   => $lampiranPath,
                'daftar_url'      => DaftarSkpdSaya::getUrl(),
                'verifikasi_url'  => SkpdAirTanahResource::getUrl('index'),
            ];

            $this->deleteTemporaryLampiran();
            $this->lampiranUploadTemp = null;

            Notification::make()
                ->success()
                ->title('Draft SKPD Air Tanah Berhasil Dibuat')
                ->body("Nomor: {$skpd->nomor_skpd}. Menunggu verifikasi.")
                ->send();

            NotificationService::notifyRole(
                'verifikator',
                'Draft SKPD Air Tanah Menunggu Verifikasi',
                "Draft SKPD Air Tanah {$skpd->nomor_skpd} telah dibuat dan menunggu verifikasi.",
                actionUrl: SkpdAirTanahResource::getUrl('view', ['record' => $skpd->id]),
            );
        } catch (Exception $e) {
            if (isset($lampiranPath) && $lampiranPath && Storage::disk('public')->exists($lampiranPath)) {
                Storage::disk('public')->delete($lampiranPath);
            }

            Notification::make()
                ->danger()
                ->title('Gagal membuat SKPD')
                ->body($e->getMessage())
                ->send();
        }
    }

    // ── Reset ───────────────────────────────────────────────────────────────

    public function buatBaru(): void
    {
        $this->selectedWaterObjectId   = null;
        $this->selectedWaterObjectData = null;
        $this->wajibPajakData          = null;
        $this->meterReadingBefore      = null;
        $this->meterReadingAfter       = null;
        $this->directUsage             = null;
        $this->isMeterChange           = false;
        $this->meterOldEnd             = null;
        $this->meterNewStart           = null;
        $this->meterNewEnd             = null;
        $this->catatanMeter            = null;
        $this->isNewObject             = false;
        $this->usesMeter               = true;
        $this->hasHistory              = false;
        $this->periodeBulan            = now()->format('Y-m');
        $this->tarifTiers              = null;
        $this->tarifPersen             = null;
        $this->dasarHukumTarif         = null;
        $this->dasarHukumNpa           = null;
        $this->deleteTemporaryLampiran();
        $this->lampiranUploadTemp      = null;
        $this->skpdResult              = null;
    }

    public function removeLampiranUpload(): void
    {
        $this->deleteTemporaryLampiran();
        $this->lampiranUploadTemp = null;
    }

    private function deleteTemporaryLampiran(): void
    {
        if (! $this->lampiranUploadTemp) {
            return;
        }

        if (method_exists($this->lampiranUploadTemp, 'delete')) {
            $this->lampiranUploadTemp->delete();
        }
    }

    private function normalizeDecimalInputs(): void
    {
        $this->meterReadingBefore = DecimalInputNormalizer::toFloat($this->meterReadingBefore);
        $this->meterReadingAfter = DecimalInputNormalizer::toFloat($this->meterReadingAfter);
        $this->directUsage = DecimalInputNormalizer::toFloat($this->directUsage);
        $this->meterOldEnd = DecimalInputNormalizer::toFloat($this->meterOldEnd);
        $this->meterNewStart = DecimalInputNormalizer::toFloat($this->meterNewStart);
        $this->meterNewEnd = DecimalInputNormalizer::toFloat($this->meterNewEnd);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function mapWaterObject($obj): array
    {
        return [
            'id'                 => $obj->id,
            'nama'               => $obj->nama_objek,
            'alamat'             => $obj->alamat_objek,
            'npwpd'              => $obj->npwpd,
            'nopd'               => $obj->nopd,
            'nik_hash'           => $obj->nik_hash,
            'jenis_pajak_id'     => $obj->jenis_pajak_id,
            'last_meter_reading' => $obj->last_meter_reading,
            'uses_meter'         => $obj->uses_meter,
            'kelurahan'          => $obj->kelurahan,
            'kecamatan'          => $obj->kecamatan,
        ];
    }
}
