<?php

namespace App\Domain\Tax\Models;

use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxPayment extends Model
{
    use HasFactory, HasUuids, HasEncryptedAttributes, SoftDeletes;

    protected $table = 'tax_payments';

    protected $fillable = [
        'tax_id',
        'external_ref',
        'amount_paid',
        'principal_paid',
        'penalty_paid',
        'payment_channel',
        'paid_at',
        'raw_response',
        'description',
        'attachment_url',
        'cancelled_reason',
        'cancelled_by',
    ];

    protected $encryptedAttributes = [
        'amount_paid',
        'principal_paid',
        'penalty_paid',
        'attachment_url',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'raw_response' => 'array',
    ];

    /**
     * Get tax record
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }
}
