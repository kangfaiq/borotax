<?php

namespace App\Domain\Tax\Models;

use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxMblbDetail extends Model
{
    use SoftDeletes, HasFactory, HasUuids, HasEncryptedAttributes;
    protected $table = 'tax_mblb_details';

    protected array $encryptedAttributes = [
        'harga_patokan',
        'subtotal_dpp',
    ];

    protected $fillable = [
        'tax_id',
        'harga_patokan_mblb_id',
        'jenis_mblb',
        'volume',
        'harga_patokan',
        'subtotal_dpp',
    ];

    protected $casts = [
        'volume' => 'decimal:2',
    ];

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function hargaPatokanMblb(): BelongsTo
    {
        return $this->belongsTo(HargaPatokanMblb::class, 'harga_patokan_mblb_id');
    }
}
