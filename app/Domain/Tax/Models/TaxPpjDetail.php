<?php

namespace App\Domain\Tax\Models;

use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxPpjDetail extends Model
{
    use SoftDeletes, HasFactory, HasUuids, HasEncryptedAttributes;
    protected $table = 'tax_ppj_details';

    protected array $encryptedAttributes = [
        'harga_satuan',
        'njtl',
        'subtotal_dpp',
    ];

    protected $fillable = [
        'tax_id',
        'harga_satuan_listrik_id',
        'kapasitas_kva',
        'tingkat_penggunaan_persen',
        'jangka_waktu_jam',
        'harga_satuan',
        'njtl',
        'subtotal_dpp',
    ];

    protected $casts = [
        'kapasitas_kva' => 'decimal:2',
        'tingkat_penggunaan_persen' => 'decimal:2',
        'jangka_waktu_jam' => 'decimal:2',
    ];

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function hargaSatuanListrik(): BelongsTo
    {
        return $this->belongsTo(HargaSatuanListrik::class, 'harga_satuan_listrik_id');
    }
}
