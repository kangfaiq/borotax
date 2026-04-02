<?php

namespace App\Domain\Tax\Models;

use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxSarangWaletDetail extends Model
{
    use SoftDeletes, HasFactory, HasUuids, HasEncryptedAttributes;
    protected $table = 'tax_sarang_walet_details';

    protected array $encryptedAttributes = [
        'harga_patokan',
        'subtotal_dpp',
    ];

    protected $fillable = [
        'tax_id',
        'harga_patokan_sarang_walet_id',
        'jenis_sarang',
        'volume_kg',
        'harga_patokan',
        'subtotal_dpp',
    ];

    protected $casts = [
        'volume_kg' => 'decimal:2',
    ];

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function hargaPatokanSarangWalet(): BelongsTo
    {
        return $this->belongsTo(HargaPatokanSarangWalet::class, 'harga_patokan_sarang_walet_id');
    }
}
