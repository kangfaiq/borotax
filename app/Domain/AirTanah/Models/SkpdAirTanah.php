<?php

namespace App\Domain\AirTanah\Models;

use Illuminate\Support\Carbon;
use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Shared\Traits\CalculatesJatuhTempo;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $id
 * @property string|null $nomor_skpd
 * @property string|null $meter_report_id
 * @property string|null $tax_object_id
 * @property string|null $jenis_pajak_id
 * @property string|null $sub_jenis_pajak_id
 * @property string|null $nik_wajib_pajak
 * @property string|null $nama_wajib_pajak
 * @property string|null $alamat_wajib_pajak
 * @property string|null $nama_objek
 * @property string|null $alamat_objek
 * @property string|null $nopd
 * @property string|null $kecamatan
 * @property string|null $kelurahan
 * @property int|null $meter_reading_before
 * @property int|null $meter_reading_after
 * @property int|null $usage
 * @property string|null $periode_bulan
 * @property Carbon|null $jatuh_tempo
 * @property string|null $tarif_per_m3
 * @property string|null $dasar_pengenaan
 * @property string|null $tarif_persen
 * @property string|null $jumlah_pajak
 * @property string $status
 * @property Carbon|null $tanggal_buat
 * @property string|null $petugas_id
 * @property string|null $petugas_nama
 * @property Carbon|null $tanggal_verifikasi
 * @property string|null $verifikator_id
 * @property string|null $verifikator_nama
 * @property string|null $catatan_verifikasi
 * @property string|null $ttd_elektronik_url
 * @property string|null $qr_code_url
 * @property string|null $nomor_seri_dokumen
 * @property string|null $kode_billing
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read MeterReport|null $meterReport
 * @property-read WaterObject|null $waterObject Tax object via WaterObject scope
 * @property-read JenisPajak|null $jenisPajak
 * @property-read SubJenisPajak|null $subJenisPajak
 */
class SkpdAirTanah extends Model
{
    use HasFactory, HasUuids, HasEncryptedAttributes, CalculatesJatuhTempo;

    protected $table = 'skpd_air_tanah';

    /**
     * Kolom yang dienkripsi
     */
    protected array $encryptedAttributes = [
        'nik_wajib_pajak',
        'nama_wajib_pajak',
        'alamat_wajib_pajak',
        'nama_objek',
        'alamat_objek',
        'tarif_per_m3',
        'dasar_pengenaan',
        'jumlah_pajak',
        'ttd_elektronik_url',
        'qr_code_url',
    ];

    protected $fillable = [
        'nomor_skpd',
        'meter_report_id',
        'tax_object_id',
        'jenis_pajak_id',
        'sub_jenis_pajak_id',
        'nik_wajib_pajak',
        'nama_wajib_pajak',
        'alamat_wajib_pajak',
        'nama_objek',
        'alamat_objek',
        'nopd',
        'kecamatan',
        'kelurahan',
        'meter_reading_before',
        'meter_reading_after',
        'usage',
        'is_meter_change',
        'meter_old_end',
        'meter_new_start',
        'meter_new_end',
        'catatan_meter',
        'periode_bulan',
        'jatuh_tempo',
        'tarif_per_m3',
        'dasar_pengenaan',
        'tarif_persen',
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
        'lampiran_path',
    ];

    protected $casts = [
        'meter_reading_before' => 'decimal:2',
        'meter_reading_after' => 'decimal:2',
        'usage' => 'decimal:2',
        'is_meter_change' => 'boolean',
        'meter_old_end' => 'decimal:2',
        'meter_new_start' => 'decimal:2',
        'meter_new_end' => 'decimal:2',
        'jatuh_tempo' => 'date',
        'tarif_persen' => 'decimal:2',
        'tanggal_buat' => 'datetime',
        'tanggal_verifikasi' => 'datetime',
        'is_legacy' => 'boolean',
    ];

    public function getLampiranUrlAttribute(): ?string
    {
        if (! $this->lampiran_path) {
            return null;
        }

        return Storage::disk('public')->url($this->lampiran_path);
    }

    /**
     * Get meter report
     */
    public function meterReport(): BelongsTo
    {
        return $this->belongsTo(MeterReport::class, 'meter_report_id');
    }

    /**
     * Get water object (tax_objects via WaterObject scope)
     */
    public function waterObject(): BelongsTo
    {
        return $this->belongsTo(WaterObject::class, 'tax_object_id');
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
     * Format: SKPD-ABT/[TAHUN]/[BULAN]/[6 DIGIT]
     */
    public static function generateNomorSkpd(): string
    {
        $tahun = date('Y');
        $bulan = date('m');
        $count = self::whereYear('tanggal_buat', $tahun)
            ->whereMonth('tanggal_buat', $bulan)
            ->count() + 1;
        $number = str_pad($count, 6, '0', STR_PAD_LEFT);

        return "SKPD-ABT/{$tahun}/{$bulan}/{$number}";
    }

    /**
     * Hitung pajak air tanah
     * usage × tarif_per_m3 × tarif_persen
     */
    public function hitungPajak(): float
    {
        $dasarPengenaan = $this->usage * (float) $this->tarif_per_m3;
        $pajak = $dasarPengenaan * ($this->tarif_persen / 100);

        return $pajak;
    }

    /**
     * Hitung dan set tanggal jatuh tempo
     * Air Tanah: Akhir bulan berikutnya dari periode
     * Konsisten dengan kalkulator sanksi web & mobile
     */
    public function hitungDanSetJatuhTempo(): void
    {
        if ($this->periode_bulan) {
            $this->setAttribute('jatuh_tempo', self::hitungJatuhTempoAirTanah($this->periode_bulan));
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
