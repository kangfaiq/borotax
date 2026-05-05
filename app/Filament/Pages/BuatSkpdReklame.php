<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Exception;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Domain\Reklame\Models\KelompokLokasiJalan;
use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Domain\Reklame\Models\ReklameRequest;
use App\Domain\Reklame\Models\ReklameNilaiStrategis;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Reklame\Models\ReklameTariff;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Reklame\Services\ReklameService;
use App\Domain\Shared\Services\NotificationService;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Filament\Resources\SkpdReklameResource;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class BuatSkpdReklame extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon  = 'heroicon-o-document-check';
    protected static string | \UnitEnum | null $navigationGroup = 'Laporan Petugas';
    protected static ?string $navigationLabel = 'Buat SKPD Reklame';
    protected static ?string $title           = 'Buat SKPD Reklame';
    protected static ?int    $navigationSort  = 4;
    protected string  $view            = 'filament.pages.buat-skpd-reklame';

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

    // ── State: Mode ───────────────────────────────────────────────────────
    public string $mode = 'objek_wp'; // 'objek_wp' | 'aset_pemkab'

    // ── State: Search ───────────────────────────────────────────────────────
    public ?string $searchKeyword     = null;
    public array   $searchResults     = [];
    public ?string $expandedDetailId  = null;

    // ── State: Selection ────────────────────────────────────────────────────
    public ?string $selectedReklameObjectId   = null;
    public ?array  $selectedReklameObjectData = null;
    public ?array  $wajibPajakData            = null;
    public ?string $requestId                 = null;
    public ?array  $requestData               = null;

    // ── State: Aset Pemkab ──────────────────────────────────────────────────
    public ?string $searchAsetKeyword       = null;
    public array   $searchAsetResults       = [];
    public ?string $selectedAsetPemkabId    = null;
    public ?array  $selectedAsetPemkabData  = null;
    public ?string $permohonanId            = null;
    public ?array  $permohonanData          = null;

    // ── State: Penyewa (Aset Pemkab mode) ───────────────────────────────────
    public ?string $penyewaNik       = null;
    public ?string $penyewaNama      = null;
    public ?string $penyewaAlamat    = null;
    public ?string $penyewaNoTelepon = null;

    // ── State: NPWPD Search (Aset Pemkab mode) ─────────────────────────────────
    public ?string $searchNpwpdKeyword  = null;
    public array   $searchNpwpdResults  = [];
    public ?array  $selectedWpData      = null;

    // ── State: Form Perhitungan ─────────────────────────────────────────────
    public ?string $subJenisPajakId    = null;
    public ?string $hargaPatokanReklameId = null;
    public ?string $lokasiJalanId      = null;
    public ?string $kelompokLokasi     = null;
    public ?string $satuanWaktu        = null;
    public ?float  $luasM2             = null;
    public ?int    $jumlahMuka         = null;
    public ?int    $durasi             = 1;
    public ?int    $jumlahReklame      = 1;
    public string  $lokasiPenempatan   = 'luar_ruangan';
    public string  $jenisProduk        = 'non_rokok';
    public ?string $isiMateriReklame   = null;
    public ?string $masaBerlakuMulai   = null;
    public ?string $masaBerlakuSampai  = null;

    // ── State: Result ───────────────────────────────────────────────────────
    public ?array $skpdResult = null;

    // ────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->masaBerlakuMulai = now()->format('Y-m-d');

        // Pre-fill from permohonan_id query param (from PermohonanSewaReklameResource)
        $permohonanId = request()->query('permohonan_id');
        if ($permohonanId) {
            $permohonan = PermohonanSewaReklame::with('asetReklame')->find($permohonanId);
            if ($permohonan && $permohonan->asetReklame) {
                $this->mode = 'aset_pemkab';
                $this->permohonanId = $permohonanId;
                $this->permohonanData = [
                    'id'                      => $permohonan->id,
                    'nik'                     => $permohonan->nik,
                    'nama'                    => $permohonan->nama,
                    'alamat'                  => $permohonan->alamat,
                    'no_telepon'              => $permohonan->no_telepon,
                    'jenis_reklame_dipasang'  => $permohonan->jenis_reklame_dipasang,
                    'durasi_sewa_hari'        => $permohonan->durasi_sewa_hari,
                    'tanggal_mulai_diinginkan' => $permohonan->tanggal_mulai_diinginkan?->format('Y-m-d'),
                ];

                // Auto-select the aset
                $this->selectAsetPemkab($permohonan->aset_reklame_pemkab_id);

                // Pre-fill penyewa from permohonan
                $this->penyewaNik       = $permohonan->nik;
                $this->penyewaNama      = $permohonan->nama;
                $this->penyewaAlamat    = $permohonan->alamat;
                $this->penyewaNoTelepon = $permohonan->no_telepon;

                // Auto-lookup WP by NIK from permohonan
                if ($permohonan->nik) {
                    $nikHash = WajibPajak::generateHash($permohonan->nik);
                    $wp = WajibPajak::where('nik_hash', $nikHash)
                        ->where('status', 'disetujui')
                        ->first();
                    if ($wp) {
                        $this->selectedWpData = [
                            'id'           => $wp->id,
                            'npwpd'        => $wp->npwpd,
                            'nik'          => $wp->nik,
                            'nama_lengkap' => $wp->nama_lengkap,
                            'alamat'       => $wp->alamat,
                        ];
                        $this->penyewaNik    = $wp->nik;
                        $this->penyewaNama   = $wp->nama_lengkap;
                        $this->penyewaAlamat = $wp->alamat;
                    }
                }

                // Pre-fill masa berlaku
                if ($permohonan->tanggal_mulai_diinginkan) {
                    $this->masaBerlakuMulai = $permohonan->tanggal_mulai_diinginkan->format('Y-m-d');
                }
                if ($permohonan->durasi_sewa_hari) {
                    $this->durasi = $permohonan->durasi_sewa_hari;
                    $this->satuanWaktu = 'perHari';
                    $this->hitungMasaBerlakuSampai();
                }
            }
        }

        $requestId = request()->query('request_id');
        if ($requestId) {
            $reklameRequest = ReklameRequest::with(['reklameObject.subJenisPajak'])->find($requestId);

            if ($reklameRequest && $reklameRequest->reklameObject) {
                $this->mode = 'objek_wp';
                $this->requestId = $requestId;
                $this->requestData = [
                    'id' => $reklameRequest->id,
                    'status' => $reklameRequest->status,
                    'durasi_perpanjangan_hari' => $reklameRequest->durasi_perpanjangan_hari,
                    'catatan_pengajuan' => $reklameRequest->catatan_pengajuan,
                ];

                $selected = $this->mapReklameObject($reklameRequest->reklameObject);
                $this->selectedReklameObjectId = $reklameRequest->reklameObject->id;
                $this->selectedReklameObjectData = $selected;
                $this->skpdResult = null;
                $this->subJenisPajakId = $selected['sub_jenis_pajak_id'];
                $this->hargaPatokanReklameId = $selected['harga_patokan_reklame_id'];
                $this->lokasiJalanId = $selected['lokasi_jalan_id'];
                $this->kelompokLokasi = $selected['kelompok_lokasi'];
                $this->jumlahMuka = (int) $selected['jumlah_muka'];
                $this->luasM2 = (float) $selected['luas_m2'];
                $this->jumlahReklame = 1;
                $this->lokasiPenempatan = 'luar_ruangan';
                $this->jenisProduk = 'non_rokok';

                [$this->satuanWaktu, $this->durasi] = match ((int) ($reklameRequest->durasi_perpanjangan_hari ?? 0)) {
                    365 => ['perTahun', 1],
                    180 => ['perBulan', 6],
                    90 => ['perBulan', 3],
                    30 => ['perBulan', 1],
                    default => [null, 1],
                };

                $this->masaBerlakuMulai = $reklameRequest->reklameObject->masa_berlaku_sampai?->copy()->addDay()->format('Y-m-d')
                    ?? now()->format('Y-m-d');
                $this->hitungMasaBerlakuSampai();

                $this->wajibPajakData = null;
                if (!empty($selected['nik_hash'])) {
                    $wp = WajibPajak::where('nik_hash', $selected['nik_hash'])
                        ->where('status', 'disetujui')
                        ->first();

                    if ($wp) {
                        $this->wajibPajakData = [
                            'id' => $wp->id,
                            'user_id' => $wp->user_id,
                            'nik' => $wp->nik,
                            'nama_lengkap' => $wp->nama_lengkap,
                            'alamat' => $wp->alamat,
                            'npwpd' => $wp->npwpd,
                        ];
                    }
                }
            }
        }
    }

    // ── Live Search (pattern: BuatBillingSelfAssessment) ────────────────────

    public function updatedSearchKeyword(): void
    {
        $this->search();
    }

    public function updatedMasaBerlakuMulai(): void
    {
        $this->hitungMasaBerlakuSampai();

        if ($this->lokasiJalanId) {
            $this->syncKelompokLokasiFromLokasiJalan($this->lokasiJalanId);
        }
    }

    public function updatedLokasiJalanId(?string $state): void
    {
        $this->syncKelompokLokasiFromLokasiJalan($state);
    }

    public function updatedSatuanWaktu(): void
    {
        $this->hitungMasaBerlakuSampai();
    }

    public function updatedDurasi(): void
    {
        if ($this->mode !== 'aset_pemkab') {
            $this->durasi = 1;
        }
        $this->hitungMasaBerlakuSampai();
    }

    public function hitungMasaBerlakuSampai(): void
    {
        if (!$this->masaBerlakuMulai || !$this->satuanWaktu || !$this->durasi) {
            $this->masaBerlakuSampai = null;
            return;
        }

        try {
            $mulai = Carbon::parse($this->masaBerlakuMulai);
            $durasi = max(1, (int) $this->durasi);

            $sampai = match ($this->satuanWaktu) {
                'perTahun'                     => $mulai->copy()->addYears($durasi)->subDay(),
                'perBulan'                     => $mulai->copy()->addMonths($durasi)->subDay(),
                'perMinggu', 'perMingguPerBuah' => $mulai->copy()->addWeeks($durasi)->subDay(),
                'perHari', 'perHariPerBuah'     => $mulai->copy()->addDays($durasi)->subDay(),
                default                        => $mulai->copy()->addYears($durasi)->subDay(),
            };

            $this->masaBerlakuSampai = $sampai->format('Y-m-d');
        } catch (Exception $e) {
            $this->masaBerlakuSampai = null;
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
                $nikHash = ReklameObject::generateHash($keyword);
                $results = ReklameObject::where('nik_hash', $nikHash)
                    ->where('is_active', true)
                    ->with(['subJenisPajak'])
                    ->get()
                    ->map(fn ($obj) => $this->mapReklameObject($obj))
                    ->toArray();
            }

            // Fallback: NPWPD / nama objek / NIK (decrypt + filter)
            if (empty($results)) {
                $kw = strtolower($keyword);
                $results = ReklameObject::where('is_active', true)
                    ->with(['subJenisPajak'])
                    ->get()
                    ->filter(fn ($obj) =>
                        str_contains(strtolower($obj->npwpd ?? ''), $kw)
                        || str_contains(strtolower($obj->nama_objek_pajak ?? ''), $kw)
                        || str_contains(strtolower($obj->nik ?? ''), $kw)
                    )
                    ->take(20)
                    ->map(fn ($obj) => $this->mapReklameObject($obj))
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

    // ── Mode Switching ──────────────────────────────────────────────────────

    public function switchMode(string $mode): void
    {
        if ($this->mode === $mode) return;
        $this->mode = $mode;
        $this->buatBaru();
    }

    // ── Aset Pemkab Search ──────────────────────────────────────────────────

    public function updatedSearchAsetKeyword(): void
    {
        $this->searchAset();
    }

    // ── NPWPD Search (Aset Pemkab mode) ─────────────────────────────────────

    public function updatedSearchNpwpdKeyword(): void
    {
        $this->searchNpwpd();
    }

    public function searchNpwpd(): void
    {
        $keyword = trim($this->searchNpwpdKeyword ?? '');

        if (strlen($keyword) < 3) {
            $this->searchNpwpdResults = [];
            return;
        }

        try {
            $results = [];

            // NIK — all-digit ≥ 5 chars → search by hash
            if (ctype_digit($keyword) && strlen($keyword) >= 5) {
                $nikHash = WajibPajak::generateHash($keyword);
                $results = WajibPajak::where('nik_hash', $nikHash)
                    ->where('status', 'disetujui')
                    ->get()
                    ->map(fn ($wp) => [
                        'id'           => $wp->id,
                        'npwpd'        => $wp->npwpd,
                        'nik'          => $wp->nik,
                        'nama_lengkap' => $wp->nama_lengkap,
                        'alamat'       => $wp->alamat,
                    ])
                    ->toArray();
            }

            // Fallback: NPWPD / nama / NIK (decrypt + filter)
            if (empty($results)) {
                $kw = strtolower($keyword);
                $results = WajibPajak::where('status', 'disetujui')
                    ->get()
                    ->filter(fn ($wp) =>
                        str_contains(strtolower($wp->npwpd ?? ''), $kw)
                        || str_contains(strtolower($wp->nama_lengkap ?? ''), $kw)
                        || str_contains(strtolower($wp->nik ?? ''), $kw)
                    )
                    ->take(20)
                    ->map(fn ($wp) => [
                        'id'           => $wp->id,
                        'npwpd'        => $wp->npwpd,
                        'nik'          => $wp->nik,
                        'nama_lengkap' => $wp->nama_lengkap,
                        'alamat'       => $wp->alamat,
                    ])
                    ->values()
                    ->toArray();
            }

            $this->searchNpwpdResults = $results;
        } catch (Exception $e) {
            $this->searchNpwpdResults = [];
        }
    }

    public function selectWp(string $id): void
    {
        $wp = WajibPajak::where('id', $id)->where('status', 'disetujui')->first();
        if (!$wp) return;

        $this->selectedWpData = [
            'id'           => $wp->id,
            'npwpd'        => $wp->npwpd,
            'nik'          => $wp->nik,
            'nama_lengkap' => $wp->nama_lengkap,
            'alamat'       => $wp->alamat,
        ];

        $this->penyewaNik    = $wp->nik;
        $this->penyewaNama   = $wp->nama_lengkap;
        $this->penyewaAlamat = $wp->alamat;

        $this->searchNpwpdResults = [];
    }

    public function deselectWp(): void
    {
        $this->selectedWpData     = null;
        $this->searchNpwpdKeyword = null;
        $this->searchNpwpdResults = [];
        $this->penyewaNik         = null;
        $this->penyewaNama        = null;
        $this->penyewaAlamat      = null;
        $this->penyewaNoTelepon   = null;
    }

    public function searchAset(): void
    {
        AsetReklamePemkab::syncExpiredOpdBorrowings();

        $keyword = trim($this->searchAsetKeyword ?? '');

        if (strlen($keyword) < 2) {
            $this->searchAsetResults = [];
            return;
        }

        $kw = strtolower($keyword);
        $this->searchAsetResults = AsetReklamePemkab::where('is_active', true)
            ->where(function ($q) use ($kw) {
                $q->where('kode_aset', 'like', "%{$kw}%")
                  ->orWhere('nama', 'like', "%{$kw}%");
            })
            ->take(20)
            ->get()
            ->map(fn ($aset) => [
                'id'                    => $aset->id,
                'kode_aset'             => $aset->kode_aset,
                'nama'                  => $aset->nama,
                'jenis'                 => $aset->jenis,
                'lokasi'                => $aset->lokasi,
                'kawasan'               => $aset->kawasan,
                'panjang'               => $aset->panjang,
                'lebar'                 => $aset->lebar,
                'luas_m2'               => $aset->luas_m2,
                'jumlah_muka'           => $aset->jumlah_muka,
                'kelompok_lokasi'       => $aset->kelompok_lokasi,
                'status_ketersediaan'   => $aset->status_ketersediaan,
                'status_label'          => $aset->statusLabel,
                'status_color'          => $aset->statusColor,
                'ukuran_formatted'      => $aset->ukuranFormatted,
                'harga_sewa_per_tahun'  => $aset->harga_sewa_per_tahun,
                'harga_sewa_per_bulan'  => $aset->harga_sewa_per_bulan,
                'harga_sewa_per_minggu' => $aset->harga_sewa_per_minggu,
            ])
            ->toArray();
    }

    public function selectAsetPemkab(string $id): void
    {
        AsetReklamePemkab::syncExpiredOpdBorrowings();

        $aset = AsetReklamePemkab::find($id);
        if (! $aset) {
            return;
        }

        $this->selectedAsetPemkabId = $id;
        $this->selectedAsetPemkabData = [
            'id'                    => $aset->id,
            'kode_aset'             => $aset->kode_aset,
            'nama'                  => $aset->nama,
            'jenis'                 => $aset->jenis,
            'lokasi'                => $aset->lokasi,
            'kawasan'               => $aset->kawasan,
            'panjang'               => $aset->panjang,
            'lebar'                 => $aset->lebar,
            'luas_m2'               => $aset->luas_m2,
            'jumlah_muka'           => $aset->jumlah_muka,
            'kelompok_lokasi'       => $aset->kelompok_lokasi,
            'status_ketersediaan'   => $aset->status_ketersediaan,
            'status_label'          => $aset->statusLabel,
            'status_color'          => $aset->statusColor,
            'ukuran_formatted'      => $aset->ukuranFormatted,
            'harga_sewa_per_tahun'  => $aset->harga_sewa_per_tahun,
            'harga_sewa_per_bulan'  => $aset->harga_sewa_per_bulan,
            'harga_sewa_per_minggu' => $aset->harga_sewa_per_minggu,
        ];

        $this->skpdResult = null;

        // Auto-populate form for aset pemkab (simplified)
        $this->lokasiJalanId = null;
        $this->kelompokLokasi = $aset->kelompok_lokasi;
        $this->jumlahMuka     = (int) $aset->jumlah_muka;
        $this->luasM2         = (float) $aset->luas_m2;
        $this->durasi         = 1;
        $this->jumlahReklame  = 1;
        $this->lokasiPenempatan = 'luar_ruangan';
        $this->jenisProduk    = 'non_rokok';
        $this->satuanWaktu    = null;
        $this->isiMateriReklame = null;
        $this->masaBerlakuMulai = now()->format('Y-m-d');
        $this->masaBerlakuSampai = null;

        // Reset NPWPD search when switching aset
        $this->searchNpwpdKeyword = null;
        $this->searchNpwpdResults = [];
        // Keep selectedWpData if already selected (don't reset)
    }

    // ── Object Selection ────────────────────────────────────────────────────

    public function selectObject(string $id): void
    {
        $selected = collect($this->searchResults)->firstWhere('id', $id);
        if (!$selected) {
            return;
        }

        $this->selectedReklameObjectId   = $id;
        $this->selectedReklameObjectData = $selected;
        $this->skpdResult                = null;

        // Auto-populate form fields from selected object (readonly fields from object)
        $this->subJenisPajakId  = $selected['sub_jenis_pajak_id'];
        $this->hargaPatokanReklameId = $selected['harga_patokan_reklame_id'];
        $this->lokasiJalanId    = $selected['lokasi_jalan_id'];
        $this->kelompokLokasi   = $selected['kelompok_lokasi'];
        $this->jumlahMuka       = (int) $selected['jumlah_muka'];

        // Luas dihitung otomatis dari dimensi objek sesuai rumus bentuk reklame
        $reklameObj = ReklameObject::find($id);
        $this->luasM2 = $reklameObj ? round($reklameObj->hitungLuas(), 2) : (float) $selected['luas_m2'];
        $this->durasi           = 1;
        $this->jumlahReklame    = 1;
        $this->lokasiPenempatan = 'luar_ruangan';
        $this->jenisProduk      = 'non_rokok';
        $this->satuanWaktu      = null;
        $this->isiMateriReklame = null;
        $this->masaBerlakuMulai = now()->format('Y-m-d');
        $this->masaBerlakuSampai = null;

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

    protected function resolveIsiMateriReklameObjekWp(): ?string
    {
        $materi = trim((string) ($this->isiMateriReklame ?? ''));

        return $materi !== '' ? $materi : null;
    }

    protected function resolveIsiMateriReklameAsetPemkab(): ?string
    {
        $materiPermohonan = trim((string) data_get($this->permohonanData, 'jenis_reklame_dipasang', ''));

        return $materiPermohonan !== '' ? $materiPermohonan : null;
    }

    // ── Form Options ────────────────────────────────────────────────────────

    public function getHargaPatokanReklameOptions(): array
    {
        if (!$this->subJenisPajakId) {
            return [];
        }

        return HargaPatokanReklame::where('sub_jenis_pajak_id', $this->subJenisPajakId)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->pluck('nama', 'id')
            ->toArray();
    }

    public function getLokasiJalanOptions(): array
    {
        return KelompokLokasiJalan::getActiveOptions($this->getTanggalReferensi(), $this->lokasiJalanId);
    }

    /**
     * Available satuan waktu for selected sub jenis (Objek WP mode).
     */
    public function getAvailableSatuanWaktu(): array
    {
        if (!$this->hargaPatokanReklameId) {
            return [];
        }

        // Untuk reklame insidentil, paksa kelompok_lokasi = null
        $hargaPatokanReklame = HargaPatokanReklame::find($this->hargaPatokanReklameId);
        $kelompokLokasi = ($hargaPatokanReklame?->is_insidentil) ? null : $this->kelompokLokasi;

        return ReklameTariff::getAvailableSatuanWaktu($this->hargaPatokanReklameId, $kelompokLokasi, $this->getTanggalReferensi());
    }

    /**
     * Available satuan waktu for aset pemkab (based on harga_sewa fields).
     */
    public function getAvailableSatuanWaktuAset(): array
    {
        if (!$this->selectedAsetPemkabData) {
            return [];
        }

        $options = [];
        $data = $this->selectedAsetPemkabData;

        if (!empty($data['harga_sewa_per_minggu']) && (float) $data['harga_sewa_per_minggu'] > 0) {
            $options['perMinggu'] = 'Per Minggu — Rp ' . number_format((float) $data['harga_sewa_per_minggu'], 0, ',', '.');
        }
        if (!empty($data['harga_sewa_per_bulan']) && (float) $data['harga_sewa_per_bulan'] > 0) {
            $options['perBulan'] = 'Per Bulan — Rp ' . number_format((float) $data['harga_sewa_per_bulan'], 0, ',', '.');
        }
        if (!empty($data['harga_sewa_per_tahun']) && (float) $data['harga_sewa_per_tahun'] > 0) {
            $options['perTahun'] = 'Per Tahun — Rp ' . number_format((float) $data['harga_sewa_per_tahun'], 0, ',', '.');
        }

        return $options;
    }

    /**
     * Live preview for aset pemkab (fixed-price calculation).
     */
    public function getPreviewPajakAset(): ?array
    {
        if (!$this->selectedAsetPemkabData || !$this->satuanWaktu || !$this->durasi) {
            return null;
        }

        $data = $this->selectedAsetPemkabData;
        $hargaSewa = match ($this->satuanWaktu) {
            'perTahun'  => (float) ($data['harga_sewa_per_tahun'] ?? 0),
            'perBulan'  => (float) ($data['harga_sewa_per_bulan'] ?? 0),
            'perMinggu' => (float) ($data['harga_sewa_per_minggu'] ?? 0),
            default     => 0,
        };

        if ($hargaSewa <= 0) {
            return null;
        }

        $durasi = max(1, (int) $this->durasi);
        $totalPajak = $hargaSewa * $durasi;

        $satuanLabel = match ($this->satuanWaktu) {
            'perTahun'  => 'per Tahun',
            'perBulan'  => 'per Bulan',
            'perMinggu' => 'per Minggu',
            default     => $this->satuanWaktu,
        };

        $jatuhTempo = null;
        if ($this->masaBerlakuMulai) {
            try {
                $jatuhTempo = SkpdReklame::hitungJatuhTempoReklame($this->masaBerlakuMulai)->format('d/m/Y');
            } catch (Exception $e) {
                // ignore
            }
        }

        return [
            'harga_sewa'   => $hargaSewa,
            'durasi'       => $durasi,
            'satuan_label' => $satuanLabel,
            'jumlah_pajak' => $totalPajak,
            'jatuh_tempo'  => $jatuhTempo,
        ];
    }

    /**
     * Live preview of tax calculation (null-safe, no throw).
     */
    public function getPreviewPajak(): ?array
    {
        if (!$this->hargaPatokanReklameId || !$this->satuanWaktu || !$this->luasM2 || !$this->jumlahMuka) {
            return null;
        }

        $tarifPokok = ReklameTariff::lookupTarif(
            $this->hargaPatokanReklameId,
            $this->kelompokLokasi,
            $this->satuanWaktu,
            $this->getTanggalReferensi()
        );

        if ($tarifPokok === null) {
            return null;
        }

        $durasi        = max(1, (int) ($this->durasi ?: 1));
        $jumlahReklame = max(1, (int) ($this->jumlahReklame ?: 1));
        $luasM2        = (float) $this->luasM2;
        $jumlahMuka    = (int) $this->jumlahMuka;

        $penyesuaianLokasi = $this->lokasiPenempatan === 'dalam_ruangan' ? 0.25 : 1.00;
        $penyesuaianProduk = $this->jenisProduk === 'rokok' ? 1.10 : 1.00;

        $pokokDasar       = $tarifPokok * $luasM2 * $jumlahMuka * $durasi * $jumlahReklame;
        $pokokPenyesuaian = $pokokDasar * $penyesuaianLokasi * $penyesuaianProduk;

        // Nilai strategis — only for tetap reklame
        $nilaiStrategis = 0;
        $hargaPatokanReklame = HargaPatokanReklame::find($this->hargaPatokanReklameId);
        $isInsidentil = $hargaPatokanReklame?->is_insidentil ?? true;

        if (!$isInsidentil && $this->kelompokLokasi) {
            $nilaiStrategis = ReklameNilaiStrategis::hitungNilaiStrategis(
                $this->kelompokLokasi,
                $luasM2,
                $this->satuanWaktu,
                $durasi,
                $jumlahReklame,
                $this->getTanggalReferensi()
            );
        }

        $totalPajak = $pokokPenyesuaian + $nilaiStrategis;

        // Jatuh tempo
        $jatuhTempo = null;
        if ($this->masaBerlakuMulai) {
            try {
                $jatuhTempo = SkpdReklame::hitungJatuhTempoReklame($this->masaBerlakuMulai)->format('d/m/Y');
            } catch (Exception $e) {
                // ignore
            }
        }

        return [
            'tarif_pokok'        => $tarifPokok,
            'pokok_pajak_dasar'  => $pokokDasar,
            'penyesuaian_lokasi' => $penyesuaianLokasi,
            'penyesuaian_produk' => $penyesuaianProduk,
            'dasar_pengenaan'    => $pokokPenyesuaian,
            'nilai_strategis'    => $nilaiStrategis,
            'jumlah_pajak'       => $totalPajak,
            'is_insidentil'      => $isInsidentil,
            'jatuh_tempo'        => $jatuhTempo,
        ];
    }

    // ── Submit (Aset Pemkab — Fixed Price) ──────────────────────────────────

    public function buatSkpdAsetPemkab(): void
    {
        if (!$this->selectedAsetPemkabData) {
            Notification::make()->warning()->title('Pilih aset reklame pemkab terlebih dahulu')->send();
            return;
        }
        if (!$this->selectedWpData) {
            Notification::make()->warning()->title('Pilih wajib pajak (NPWPD) terlebih dahulu')->send();
            return;
        }
        if (!$this->satuanWaktu) {
            Notification::make()->warning()->title('Pilih satuan waktu')->send();
            return;
        }
        if (!$this->durasi || $this->durasi <= 0) {
            Notification::make()->warning()->title('Masukkan durasi yang valid')->send();
            return;
        }
        if (!$this->masaBerlakuMulai || !$this->masaBerlakuSampai) {
            Notification::make()->warning()->title('Isi masa berlaku mulai dan sampai')->send();
            return;
        }

        try {
            // Determine harga sewa from aset based on satuan waktu
            $asetData = $this->selectedAsetPemkabData;
            $hargaSewa = match ($this->satuanWaktu) {
                'perTahun'  => (float) ($asetData['harga_sewa_per_tahun'] ?? 0),
                'perBulan'  => (float) ($asetData['harga_sewa_per_bulan'] ?? 0),
                'perMinggu' => (float) ($asetData['harga_sewa_per_minggu'] ?? 0),
                default     => 0,
            };

            if ($hargaSewa <= 0) {
                Notification::make()->warning()->title('Harga sewa tidak tersedia untuk satuan waktu yang dipilih')->send();
                return;
            }

            $subJenis = SubJenisPajak::where('kode', 'REKLAME_TETAP')->first();
            $hargaPatokanReklame = match ($asetData['jenis'] ?? null) {
                'neon_box' => HargaPatokanReklame::where('kode', 'RKL_NEON_BOX')->first(),
                'billboard' => (float) ($asetData['luas_m2'] ?? 0) >= 10
                    ? HargaPatokanReklame::where('kode', 'RKL_BILLBOARD_GTE_10')->first()
                    : HargaPatokanReklame::where('kode', 'RKL_BILLBOARD_LT_10')->first(),
                default => null,
            };

            if (!$subJenis || !$hargaPatokanReklame) {
                Notification::make()->warning()->title('Master reklame aset pemkab belum lengkap')->send();
                return;
            }

            $skpdData = [
                'sub_jenis_pajak_id'      => $subJenis->id,
                'harga_patokan_reklame_id' => $hargaPatokanReklame->id,
                'satuan_waktu'            => $this->satuanWaktu,
                'harga_sewa'              => $hargaSewa,
                'durasi'                  => $this->durasi,
                'luas_m2'                 => $asetData['luas_m2'],
                'jumlah_muka'             => $asetData['jumlah_muka'],
                'aset_reklame_pemkab_id'  => $this->selectedAsetPemkabId,
                'npwpd'                   => $this->selectedWpData['npwpd'],
                'nik_wajib_pajak'         => $this->selectedWpData['nik'],
                'nama_wajib_pajak'        => $this->selectedWpData['nama_lengkap'],
                'alamat_wajib_pajak'      => $this->selectedWpData['alamat'] ?? '-',
                'nama_reklame'            => $asetData['nama'],
                'isi_materi_reklame'      => $this->resolveIsiMateriReklameAsetPemkab(),
                'alamat_reklame'          => $asetData['lokasi'] ?? '-',
                'bentuk'                  => 'persegi',
                'panjang'                 => $asetData['panjang'] ?? null,
                'lebar'                   => $asetData['lebar'] ?? null,
                'masa_berlaku_mulai'      => $this->masaBerlakuMulai,
                'masa_berlaku_sampai'     => $this->masaBerlakuSampai,
                'petugas_id'              => auth()->id(),
                'petugas_nama'            => auth()->user()->nama_lengkap ?? auth()->user()->name,
            ];

            // Use fixed-price service method
            $skpd = app(ReklameService::class)->createDraftSkpdSewa($skpdData);

            // If from permohonan, update permohonan status
            if ($this->permohonanId) {
                $permohonan = PermohonanSewaReklame::find($this->permohonanId);
                if ($permohonan) {
                    $permohonan->update([
                        'status'           => 'diproses',
                        'tanggal_diproses' => now(),
                        'skpd_id'          => $skpd->id,
                        'petugas_id'       => auth()->id(),
                        'petugas_nama'     => auth()->user()->nama_lengkap ?? auth()->user()->name,
                    ]);
                }
            }

            $preview = $this->getPreviewPajakAset();

            $this->skpdResult = [
                'nomor_skpd'      => $skpd->nomor_skpd,
                'nama_wp'         => $this->selectedWpData['nama_lengkap'],
                'nama_reklame'    => $skpd->nama_reklame,
                'isi_materi_reklame' => $skpd->isi_materi_reklame,
                'alamat_reklame'  => $asetData['lokasi'] ?? '-',
                'jumlah_pajak'    => $preview['jumlah_pajak'] ?? 0,
                'jatuh_tempo'     => $preview['jatuh_tempo'] ?? '-',
                'daftar_url'      => DaftarSkpdSaya::getUrl(),
                'verifikasi_url'  => SkpdReklameResource::getUrl('index'),
            ];

            Notification::make()
                ->success()
                ->title('Draft SKPD Reklame (Aset Pemkab) Berhasil Dibuat')
                ->body("Nomor: {$skpd->nomor_skpd}. Menunggu verifikasi.")
                ->send();

            NotificationService::notifyRole(
                'verifikator',
                'Draft SKPD Reklame Menunggu Verifikasi',
                "Draft SKPD Reklame {$skpd->nomor_skpd} telah dibuat dan menunggu verifikasi.",
                actionUrl: SkpdReklameResource::getUrl('index', ['tableSearch' => $skpd->nomor_skpd]),
            );
        } catch (Exception $e) {
            Notification::make()
                ->danger()
                ->title('Gagal membuat SKPD')
                ->body($e->getMessage())
                ->send();
        }
    }

    // ── Submit ──────────────────────────────────────────────────────────────

    public function buatSkpd(): void
    {
        if (!$this->selectedReklameObjectData) {
            Notification::make()->warning()->title('Pilih objek reklame terlebih dahulu')->send();
            return;
        }
        if (!$this->wajibPajakData) {
            Notification::make()->warning()->title('Data wajib pajak tidak ditemukan untuk objek ini')->send();
            return;
        }
        if (!$this->subJenisPajakId) {
            Notification::make()->warning()->title('Sub jenis pajak reklame objek belum tersedia')->send();
            return;
        }
        if (!$this->hargaPatokanReklameId) {
            Notification::make()->warning()->title('Pilih detail jenis reklame')->send();
            return;
        }

        $hargaPatokanReklame = HargaPatokanReklame::find($this->hargaPatokanReklameId);
        $isInsidentil = $hargaPatokanReklame?->is_insidentil ?? false;

        if (!$isInsidentil && !$this->lokasiJalanId) {
            Notification::make()->warning()->title('Pilih lokasi jalan yang berlaku untuk masa pajak ini')->send();
            return;
        }
        if (!$isInsidentil && !$this->kelompokLokasi) {
            Notification::make()->warning()->title('Kelompok lokasi belum dapat ditentukan dari master jalan yang dipilih')->send();
            return;
        }
        if (!$this->satuanWaktu) {
            Notification::make()->warning()->title('Pilih satuan waktu')->send();
            return;
        }
        if (!$this->luasM2 || $this->luasM2 <= 0) {
            Notification::make()->warning()->title('Masukkan luas yang valid')->send();
            return;
        }
        if (!$this->jumlahMuka || $this->jumlahMuka <= 0) {
            Notification::make()->warning()->title('Masukkan jumlah muka yang valid')->send();
            return;
        }
        if (!$this->durasi || $this->durasi <= 0) {
            Notification::make()->warning()->title('Masukkan durasi yang valid')->send();
            return;
        }
        if (!$this->masaBerlakuMulai || !$this->masaBerlakuSampai) {
            Notification::make()->warning()->title('Isi masa berlaku mulai dan sampai')->send();
            return;
        }

        // Cek SKPD aktif dengan masa berlaku overlap
        $existingSkpd = SkpdReklame::where('tax_object_id', $this->selectedReklameObjectId)
            ->whereIn('status', ['draft', 'disetujui'])
            ->where('masa_berlaku_sampai', '>=', $this->masaBerlakuMulai)
            ->where('masa_berlaku_mulai', '<=', $this->masaBerlakuSampai)
            ->first();

        if ($existingSkpd) {
            Notification::make()
                ->danger()
                ->title('Objek reklame sudah memiliki SKPD aktif')
                ->body('No: ' . $existingSkpd->nomor_skpd . '. Masa pajak masih dalam rentang berlaku.')
                ->send();
            return;
        }

        try {
            $skpd = app(ReklameService::class)->createDraftSkpd([
                'sub_jenis_pajak_id' => $this->subJenisPajakId,
                'harga_patokan_reklame_id' => $this->hargaPatokanReklameId,
                'kelompok_lokasi'    => $this->kelompokLokasi,
                'satuan_waktu'       => $this->satuanWaktu,
                'luas_m2'            => $this->luasM2,
                'jumlah_muka'        => $this->jumlahMuka,
                'durasi'             => $this->durasi,
                'jumlah_reklame'     => $this->jumlahReklame ?? 1,
                'lokasi_penempatan'  => $this->lokasiPenempatan,
                'jenis_produk'       => $this->jenisProduk,
                'tax_object_id'      => $this->selectedReklameObjectId,
                'nik_wajib_pajak'    => $this->wajibPajakData['nik'],
                'nama_wajib_pajak'   => $this->wajibPajakData['nama_lengkap'],
                'alamat_wajib_pajak' => $this->wajibPajakData['alamat'] ?? '-',
                'nama_reklame'       => $this->selectedReklameObjectData['nama'],
                'isi_materi_reklame' => $this->resolveIsiMateriReklameObjekWp(),
                'alamat_reklame'     => $this->selectedReklameObjectData['alamat'] ?? '-',
                'bentuk'             => $this->selectedReklameObjectData['bentuk'] ?? null,
                'panjang'            => $this->selectedReklameObjectData['panjang'] ?? null,
                'lebar'              => $this->selectedReklameObjectData['lebar'] ?? null,
                'tinggi'             => $this->selectedReklameObjectData['tinggi'] ?? null,
                'sisi_atas'          => $this->selectedReklameObjectData['sisi_atas'] ?? null,
                'sisi_bawah'         => $this->selectedReklameObjectData['sisi_bawah'] ?? null,
                'diameter'           => $this->selectedReklameObjectData['diameter'] ?? null,
                'diameter2'          => $this->selectedReklameObjectData['diameter2'] ?? null,
                'alas'               => $this->selectedReklameObjectData['alas'] ?? null,
                'masa_berlaku_mulai'  => $this->masaBerlakuMulai,
                'masa_berlaku_sampai' => $this->masaBerlakuSampai,
                'petugas_id'         => auth()->id(),
                'petugas_nama'       => auth()->user()->nama_lengkap ?? auth()->user()->name,
            ]);

            if ($this->requestId) {
                $reklameRequest = ReklameRequest::find($this->requestId);

                if ($reklameRequest) {
                    $reklameRequest->update([
                        'status' => 'diproses',
                        'tanggal_diproses' => now(),
                        'petugas_id' => auth()->id(),
                        'petugas_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                        'skpd_id' => $skpd->id,
                    ]);
                }
            }

            $preview = $this->getPreviewPajak();

            $this->skpdResult = [
                'nomor_skpd'      => $skpd->nomor_skpd,
                'nama_wp'         => $this->wajibPajakData['nama_lengkap'],
                'nama_reklame'    => $skpd->nama_reklame,
                'isi_materi_reklame' => $skpd->isi_materi_reklame,
                'alamat_reklame'  => $this->selectedReklameObjectData['alamat'] ?? '-',
                'jumlah_pajak'    => $preview['jumlah_pajak'] ?? 0,
                'jatuh_tempo'     => $preview['jatuh_tempo'] ?? '-',
                'daftar_url'      => DaftarSkpdSaya::getUrl(),
                'verifikasi_url'  => SkpdReklameResource::getUrl('index'),
            ];

            Notification::make()
                ->success()
                ->title('Draft SKPD Reklame Berhasil Dibuat')
                ->body("Nomor: {$skpd->nomor_skpd}. Menunggu verifikasi.")
                ->send();

            NotificationService::notifyRole(
                'verifikator',
                'Draft SKPD Reklame Menunggu Verifikasi',
                "Draft SKPD Reklame {$skpd->nomor_skpd} telah dibuat dan menunggu verifikasi.",
                actionUrl: SkpdReklameResource::getUrl('index', ['tableSearch' => $skpd->nomor_skpd]),
            );
        } catch (Exception $e) {
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
        // Reset objek WP state
        $this->selectedReklameObjectId   = null;
        $this->selectedReklameObjectData = null;
        $this->wajibPajakData            = null;

        // Reset aset pemkab state
        $this->selectedAsetPemkabId    = null;
        $this->selectedAsetPemkabData  = null;
        $this->searchAsetKeyword       = null;
        $this->searchAsetResults       = [];
        $this->permohonanId            = null;
        $this->permohonanData          = null;
        $this->penyewaNik              = null;
        $this->penyewaNama             = null;
        $this->penyewaAlamat           = null;
        $this->penyewaNoTelepon        = null;

        // Reset NPWPD search state
        $this->searchNpwpdKeyword      = null;
        $this->searchNpwpdResults      = [];
        $this->selectedWpData          = null;

        // Reset form
        $this->subJenisPajakId           = null;
        $this->hargaPatokanReklameId     = null;
        $this->lokasiJalanId             = null;
        $this->kelompokLokasi            = null;
        $this->satuanWaktu               = null;
        $this->luasM2                    = null;
        $this->jumlahMuka                = null;
        $this->durasi                    = 1;
        $this->jumlahReklame             = 1;
        $this->lokasiPenempatan          = 'luar_ruangan';
        $this->jenisProduk               = 'non_rokok';
        $this->masaBerlakuMulai          = now()->format('Y-m-d');
        $this->masaBerlakuSampai         = null;
        $this->skpdResult                = null;
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function mapReklameObject($obj): array
    {
        $subJenis = $obj->subJenisPajak;
        $lokasiJalan = $obj->relationLoaded('lokasiJalan') ? $obj->lokasiJalan : $obj->lokasiJalan()->first();

        return [
            'id'                 => $obj->id,
            'nama'               => $obj->nama_objek_pajak,
            'alamat'             => $obj->alamat_objek,
            'npwpd'              => $obj->npwpd,
            'nopd'               => $obj->nopd,
            'nik_hash'           => $obj->nik_hash,
            'sub_jenis'          => $subJenis?->nama ?? '-',
            'sub_jenis_pajak_id' => $obj->sub_jenis_pajak_id,
            'harga_patokan_reklame_id' => $obj->harga_patokan_reklame_id,
            'jenis_pajak_id'     => $obj->jenis_pajak_id,
            'lokasi_jalan_id'    => $obj->lokasi_jalan_id,
            'lokasi_jalan_label' => $lokasiJalan?->nama_jalan,
            'kelompok_lokasi'    => $obj->kelompok_lokasi,
            'bentuk'             => $obj->bentuk,
            'panjang'            => (float) $obj->panjang,
            'lebar'              => (float) $obj->lebar,
            'tinggi'             => $obj->tinggi ? (float) $obj->tinggi : null,
            'sisi_atas'          => $obj->sisi_atas ? (float) $obj->sisi_atas : null,
            'sisi_bawah'         => $obj->sisi_bawah ? (float) $obj->sisi_bawah : null,
            'diameter'           => $obj->diameter ? (float) $obj->diameter : null,
            'diameter2'          => $obj->diameter2 ? (float) $obj->diameter2 : null,
            'alas'               => $obj->alas ? (float) $obj->alas : null,
            'luas_m2'            => (float) $obj->luas_m2,
            'jumlah_muka'        => (int) $obj->jumlah_muka,
            'masa_berlaku_sampai' => $obj->masa_berlaku_sampai?->format('d/m/Y'),
            'status'             => $obj->status,
            'is_insidentil'      => (bool) ($subJenis?->is_insidentil ?? false),
            'ukuran_formatted'   => $obj->ukuran_formatted,
        ];
    }

    private function getTanggalReferensi(): string
    {
        return $this->masaBerlakuMulai ?: now()->toDateString();
    }

    private function syncKelompokLokasiFromLokasiJalan(?string $lokasiJalanId): void
    {
        if (!$lokasiJalanId) {
            $this->kelompokLokasi = null;
            return;
        }

        $selectedLokasiJalan = KelompokLokasiJalan::find($lokasiJalanId);
        if (!$selectedLokasiJalan) {
            $this->lokasiJalanId = null;
            $this->kelompokLokasi = null;
            return;
        }

        $lokasiJalan = KelompokLokasiJalan::query()
            ->where('id', $lokasiJalanId)
            ->berlakuPada($this->getTanggalReferensi())
            ->first();

        if (!$lokasiJalan) {
            $lokasiJalan = KelompokLokasiJalan::query()
                ->where('nama_jalan', $selectedLokasiJalan->nama_jalan)
                ->active()
                ->berlakuPada($this->getTanggalReferensi())
                ->orderByDesc('berlaku_mulai')
                ->first();
        }

        if (!$lokasiJalan) {
            $this->lokasiJalanId = null;
            $this->kelompokLokasi = null;
            return;
        }

        if ($this->lokasiJalanId !== $lokasiJalan->id) {
            $this->lokasiJalanId = $lokasiJalan->id;
        }

        $this->kelompokLokasi = $lokasiJalan->kelompok;
    }
}
