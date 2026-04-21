<?php

namespace App\Domain\Tax\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Instansi;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Enums\InstansiKategori;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PortalMblbSubmission extends Model
{
    use SoftDeletes, HasFactory, HasUuids;
    protected $table = 'portal_mblb_submissions';

    protected $fillable = [
        'jenis_pajak_id',
        'sub_jenis_pajak_id',
        'tax_object_id',
        'user_id',
        'instansi_id',
        'instansi_nama',
        'instansi_kategori',
        'masa_pajak_bulan',
        'masa_pajak_tahun',
        'tarif_persen',
        'opsen_persen',
        'total_dpp',
        'pokok_pajak',
        'opsen',
        'detail_items',
        'attachment_path',
        'notes',
        'status',
        'processed_by',
        'processed_at',
        'review_notes',
        'rejection_reason',
        'approved_tax_id',
    ];

    protected $casts = [
        'masa_pajak_bulan' => 'integer',
        'masa_pajak_tahun' => 'integer',
        'tarif_persen' => 'decimal:2',
        'opsen_persen' => 'decimal:2',
        'total_dpp' => 'decimal:2',
        'pokok_pajak' => 'decimal:2',
        'opsen' => 'decimal:2',
        'detail_items' => 'array',
        'processed_at' => 'datetime',
        'instansi_kategori' => InstansiKategori::class,
    ];

    public function jenisPajak(): BelongsTo
    {
        return $this->belongsTo(JenisPajak::class, 'jenis_pajak_id');
    }

    public function subJenisPajak(): BelongsTo
    {
        return $this->belongsTo(SubJenisPajak::class, 'sub_jenis_pajak_id');
    }

    public function taxObject(): BelongsTo
    {
        return $this->belongsTo(TaxObject::class, 'tax_object_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class, 'instansi_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approvedTax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'approved_tax_id');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment_path);
    }

    public function getTotalTagihanAttribute(): float
    {
        return (float) $this->pokok_pajak + (float) $this->opsen;
    }

    public function getMasaPajakLabelAttribute(): string
    {
        return Carbon::create($this->masa_pajak_tahun, $this->masa_pajak_bulan, 1)->translatedFormat('F Y');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}