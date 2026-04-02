<?php

namespace App\Domain\Tax\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use App\Domain\Tax\Models\TaxAssessmentLetter;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxAssessmentCompensation extends Model
{
    use SoftDeletes, HasFactory, HasUuids, HasEncryptedAttributes;
    protected $table = 'tax_assessment_compensations';

    protected array $encryptedAttributes = [
        'allocation_amount',
        'principal_allocated',
        'penalty_allocated',
    ];

    protected $fillable = [
        'tax_assessment_letter_id',
        'target_tax_id',
        'tax_payment_id',
        'allocation_amount',
        'principal_allocated',
        'penalty_allocated',
        'allocated_at',
        'allocated_by',
        'allocated_by_name',
    ];

    protected $casts = [
        'allocated_at' => 'datetime',
    ];

    public function letter(): BelongsTo
    {
        return $this->belongsTo(TaxAssessmentLetter::class, 'tax_assessment_letter_id');
    }

    public function targetTax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'target_tax_id');
    }

    public function taxPayment(): BelongsTo
    {
        return $this->belongsTo(TaxPayment::class, 'tax_payment_id');
    }

    public function allocator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }
}