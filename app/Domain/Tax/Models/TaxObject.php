<?php

namespace App\Domain\Tax\Models;

use App\Domain\Shared\Traits\HasEncryptedAttributes;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Domain\Reklame\Models\KelompokLokasiJalan;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxObject extends Model
{
    use SoftDeletes, HasFactory, HasUuids, HasEncryptedAttributes;
    protected $table = 'tax_objects';

    /**
     * Kolom yang dienkripsi
     */
    protected array $encryptedAttributes = [
        'nik',
        'nama_objek_pajak',
        'alamat_objek',
        'foto_objek_path',
    ];

    protected $fillable = [
        'nik',
        'nik_hash',
        'nama_objek_pajak',
        'jenis_pajak_id',
        'sub_jenis_pajak_id',
        'harga_patokan_reklame_id',
        'npwpd',
        'nopd',
        'alamat_objek',
        'kelurahan',
        'kecamatan',
        'latitude',
        'longitude',
        'tarif_persen',
        'tanggal_daftar',
        'is_active',
        'is_opd',
        'is_insidentil',
        'foto_objek_path',
        // Kolom konsolidasi
        'kelompok_lokasi',
        'lokasi_jalan_id',
        'kelompok_pemakaian',
        'kriteria_sda',
        // Reklame-specific
        'panjang',
        'lebar',
        'tinggi',
        'sisi_atas',
        'sisi_bawah',
        'diameter',
        'diameter2',
        'alas',
        'bentuk',
        'luas_m2',
        'jumlah_muka',
        'tanggal_pasang',
        'masa_berlaku_sampai',
        'status',
        // Air Tanah-specific
        'jenis_sumber',
        'last_meter_reading',
        'last_report_date',
    ];

    protected $casts = [
        'tarif_persen' => 'decimal:2',
        'tanggal_daftar' => 'date',
        'is_active' => 'boolean',
        'is_opd' => 'boolean',
        'is_insidentil' => 'boolean',
        'nopd' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        // Reklame
        'panjang' => 'decimal:2',
        'lebar' => 'decimal:2',
        'tinggi' => 'decimal:2',
        'sisi_atas' => 'decimal:2',
        'sisi_bawah' => 'decimal:2',
        'diameter' => 'decimal:2',
        'diameter2' => 'decimal:2',
        'alas' => 'decimal:2',
        'luas_m2' => 'decimal:2',
        'jumlah_muka' => 'integer',
        'tanggal_pasang' => 'date',
        'masa_berlaku_sampai' => 'date',
        // Air Tanah
        'last_meter_reading' => 'integer',
        'last_report_date' => 'date',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();
        // nik_hash generation is handled by HasEncryptedAttributes trait
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

    public function lokasiJalan(): BelongsTo
    {
        return $this->belongsTo(KelompokLokasiJalan::class, 'lokasi_jalan_id');
    }

    /**
     * Scope untuk objek aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope berdasarkan NIK
     */
    public function scopeByNik($query, string $nik)
    {
        return $query->where('nik_hash', self::generateHash($nik));
    }

    /**
     * Apakah objek pajak ini mendukung multi-billing per masa pajak.
     * True untuk OPD (Katering), Insidentil (Hiburan), dan MBLB WAPU.
     */
    public function isMultiBilling(): bool
    {
        if ((bool) $this->is_opd || (bool) $this->is_insidentil) {
            return true;
        }

        // MBLB WAPU: satu masa pajak bisa beberapa billing
        if ($this->sub_jenis_pajak_id) {
            $sub = $this->relationLoaded('subJenisPajak')
                ? $this->subJenisPajak
                : $this->subJenisPajak()->first();
            return $sub && $sub->kode === 'MBLB_WAPU';
        }

        return false;
    }

    /**
     * Get alamat lengkap
     */
    public function getAlamatLengkapAttribute(): string
    {
        return "{$this->alamat_objek}, Kel. {$this->kelurahan}, Kec. {$this->kecamatan}";
    }
}
