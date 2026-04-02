<?php

namespace App\Domain\Reklame\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Observers\SkpdReklameObserver;
use App\Domain\Shared\Traits\CalculatesJatuhTempo;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([SkpdReklameObserver::class])]
class SkpdReklame extends Model
{
    use SoftDeletes, HasFactory, HasUuids, HasEncryptedAttributes, CalculatesJatuhTempo;
    protected $table = 'skpd_reklame';

    /**
     * Kolom yang dienkripsi
     */
    protected array $encryptedAttributes = [
        'nik_wajib_pajak',
        'nama_wajib_pajak',
        'alamat_wajib_pajak',
        'nama_reklame',
        'alamat_reklame',
        'dasar_pengenaan',
        'jumlah_pajak',
        'tarif_pokok',
        'nilai_strategis',
        'pokok_pajak_dasar',
        'ttd_elektronik_url',
        'qr_code_url',
    ];

    protected $fillable = [
        'nomor_skpd',
        'tax_object_id',
        'request_id',
        'aset_reklame_pemkab_id',
        'permohonan_sewa_id',
        'jenis_pajak_id',
        'sub_jenis_pajak_id',
        'harga_patokan_reklame_id',
        'npwpd',
        'nik_wajib_pajak',
        'nama_wajib_pajak',
        'alamat_wajib_pajak',
        'nama_reklame',
        'jenis_reklame',
        'alamat_reklame',
        'kelompok_lokasi',
        'bentuk',
        'panjang',
        'lebar',
        'tinggi',
        'sisi_atas',
        'sisi_bawah',
        'diameter',
        'diameter2',
        'alas',
        'luas_m2',
        'jumlah_muka',
        'lokasi_penempatan',
        'jenis_produk',
        'jumlah_reklame',
        'satuan_waktu',
        'satuan_label',
        'durasi',
        'tarif_pokok',
        'nspr',
        'njopr',
        'penyesuaian_lokasi',
        'penyesuaian_produk',
        'nilai_strategis',
        'pokok_pajak_dasar',
        'masa_berlaku_mulai',
        'masa_berlaku_sampai',
        'jatuh_tempo',
        'dasar_pengenaan',
        'jumlah_pajak',
        'status',
        'tanggal_buat',
        'petugas_id',
        'petugas_nama',
        'tanggal_verifikasi',
        'verifikator_id',
        'verifikator_nama',
        'catatan_verifikasi',
        'pimpinan_id',
        'ttd_elektronik_url',
        'qr_code_url',
        'nomor_seri_dokumen',
        'kode_billing',
        'dasar_hukum',
        'is_legacy',
    ];

    protected $casts = [
        'luas_m2' => 'decimal:2',
        'panjang' => 'decimal:2',
        'lebar' => 'decimal:2',
        'tinggi' => 'decimal:2',
        'sisi_atas' => 'decimal:2',
        'sisi_bawah' => 'decimal:2',
        'diameter' => 'decimal:2',
        'diameter2' => 'decimal:2',
        'alas' => 'decimal:2',
        'jumlah_muka' => 'integer',
        'jumlah_reklame' => 'integer',
        'durasi' => 'integer',
        'penyesuaian_lokasi' => 'decimal:2',
        'penyesuaian_produk' => 'decimal:2',
        'masa_berlaku_mulai' => 'date',
        'masa_berlaku_sampai' => 'date',
        'jatuh_tempo' => 'date',
        'tanggal_buat' => 'datetime',
        'tanggal_verifikasi' => 'datetime',
        'is_legacy' => 'boolean',
    ];

    /**
     * Get reklame object (tax_objects via ReklameObject scope)
     */
    public function reklameObject(): BelongsTo
    {
        return $this->belongsTo(ReklameObject::class, 'tax_object_id');
    }

    /**
     * Get request
     */
    public function reklameRequest(): BelongsTo
    {
        return $this->belongsTo(ReklameRequest::class, 'request_id');
    }

    /**
     * Get jenis pajak
     */
    public function jenisPajak(): BelongsTo
    {
        return $this->belongsTo(JenisPajak::class, 'jenis_pajak_id');
    }

    /**
     * Get sub jenis pajak
     */
    public function subJenisPajak(): BelongsTo
    {
        return $this->belongsTo(SubJenisPajak::class, 'sub_jenis_pajak_id');
    }

    public function hargaPatokanReklame(): BelongsTo
    {
        return $this->belongsTo(HargaPatokanReklame::class, 'harga_patokan_reklame_id');
    }

    /**
     * Get aset reklame pemkab (jika sewa aset pemkab)
     */
    public function asetReklamePemkab(): BelongsTo
    {
        return $this->belongsTo(AsetReklamePemkab::class, 'aset_reklame_pemkab_id');
    }

    /**
     * Get permohonan sewa (jika dari permohonan online)
     */
    public function permohonanSewa(): BelongsTo
    {
        return $this->belongsTo(PermohonanSewaReklame::class, 'permohonan_sewa_id');
    }

    /**
     * Get petugas
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Get verifikator
     */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    /**
     * Get pimpinan penandatangan
     */
    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(Pimpinan::class, 'pimpinan_id');
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk disetujui
     */
    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    /**
     * Generate nomor SKPD
     * Format: SKPD-RKL/[TAHUN]/[BULAN]/[6 DIGIT]
     */
    public static function generateNomorSkpd(): string
    {
        $tahun = date('Y');
        $bulan = date('m');
        $count = self::whereYear('tanggal_buat', $tahun)
            ->whereMonth('tanggal_buat', $bulan)
            ->count() + 1;
        $number = str_pad($count, 6, '0', STR_PAD_LEFT);

        return "SKPD-RKL/{$tahun}/{$bulan}/{$number}";
    }

    /**
     * Hitung pajak reklame (formula baru sesuai spesifikasi teknis).
     *
     * POKOK DASAR = tarif_pokok × luas × muka × durasi × jumlah_reklame
     * POKOK PENYESUAIAN = POKOK DASAR × penyesuaian_lokasi × penyesuaian_produk
     * TOTAL PAJAK = POKOK PENYESUAIAN + nilai_strategis
     *
     * Tarif pokok sudah termasuk pajak 25%, sehingga TIDAK dikalikan tarif_persen lagi.
     */
    public function hitungPajak(): float
    {
        // Jika menggunakan formula baru (ada tarif_pokok)
        if ($this->tarif_pokok) {
            $pokokDasar = (float) $this->tarif_pokok
                * (float) $this->luas_m2
                * (int) $this->jumlah_muka
                * (int) ($this->durasi ?: 1)
                * (int) ($this->jumlah_reklame ?: 1);

            $this->pokok_pajak_dasar = $pokokDasar;

            $pokokPenyesuaian = $pokokDasar
                * (float) ($this->penyesuaian_lokasi ?: 1.0)
                * (float) ($this->penyesuaian_produk ?: 1.0);

            $this->dasar_pengenaan = $pokokPenyesuaian;

            $totalPajak = $pokokPenyesuaian + (float) ($this->nilai_strategis ?: 0);
            $this->jumlah_pajak = $totalPajak;

            return $totalPajak;
        }

        return 0;
    }

    /**
     * Lookup tarif pokok dari tabel reklame_tariffs.
     */
    public function lookupTarifPokok(): ?float
    {
        if (!$this->harga_patokan_reklame_id || !$this->satuan_waktu) {
            return null;
        }

        return ReklameTariff::lookupTarif(
            $this->harga_patokan_reklame_id,
            $this->kelompok_lokasi,
            $this->satuan_waktu
        );
    }

    /**
     * Hitung nilai strategis.
     * Hanya berlaku untuk reklame TETAP dengan luas ≥ 10m².
     */
    public function hitungNilaiStrategis(): float
    {
        $hargaPatokanReklame = $this->hargaPatokanReklame;
        if (!$hargaPatokanReklame || $hargaPatokanReklame->is_insidentil) {
            return 0;
        }

        if (!$this->kelompok_lokasi || !$this->satuan_waktu) {
            return 0;
        }

        return ReklameNilaiStrategis::hitungNilaiStrategis(
            $this->kelompok_lokasi,
            (float) $this->luas_m2,
            $this->satuan_waktu,
            (int) ($this->durasi ?: 1),
            (int) ($this->jumlah_reklame ?: 1)
        );
    }

    /**
     * Hitung dan set tanggal jatuh tempo
     * Reklame: masa_berlaku_mulai + 1 bulan - 1 hari
     * Konsisten dengan kalkulator sanksi web & mobile
     */
    public function hitungDanSetJatuhTempo(): void
    {
        if ($this->masa_berlaku_mulai) {
            $this->setAttribute('jatuh_tempo', self::hitungJatuhTempoReklame($this->masa_berlaku_mulai));
        }
    }

    /**
     * Check apakah sudah melewati jatuh tempo
     */
    public function isOverdue(): bool
    {
        return $this->jatuh_tempo && now()->gt($this->jatuh_tempo);
    }
}
