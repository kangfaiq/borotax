<?php

namespace App\Domain\Tax\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use App\Domain\Tax\Models\TaxAssessmentCompensation;
use App\Enums\TaxAssessmentLetterStatus;
use App\Enums\TaxAssessmentLetterType;
use App\Enums\TaxAssessmentReason;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxAssessmentLetter extends Model
{
    use HasFactory, HasUuids, HasEncryptedAttributes, SoftDeletes;

    protected $table = 'tax_assessment_letters';

    protected array $encryptedAttributes = [
        'base_amount',
        'interest_amount',
        'surcharge_amount',
        'total_assessment',
        'available_credit',
    ];

    protected $fillable = [
        'source_tax_id',
        'generated_tax_id',
        'parent_letter_id',
        'user_id',
        'tax_object_id',
        'letter_type',
        'issuance_reason',
        'status',
        'document_number',
        'issue_date',
        'due_date',
        'base_amount',
        'interest_rate',
        'interest_months',
        'interest_amount',
        'surcharge_rate',
        'surcharge_amount',
        'total_assessment',
        'available_credit',
        'notes',
        'verification_notes',
        'created_by',
        'created_by_name',
        'verified_by',
        'verified_by_name',
        'verified_at',
        'pimpinan_id',
    ];

    protected $casts = [
        'letter_type' => TaxAssessmentLetterType::class,
        'issuance_reason' => TaxAssessmentReason::class,
        'status' => TaxAssessmentLetterStatus::class,
        'issue_date' => 'date',
        'due_date' => 'date',
        'interest_rate' => 'decimal:2',
        'interest_months' => 'integer',
        'surcharge_rate' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function sourceTax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'source_tax_id');
    }

    public function generatedTax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'generated_tax_id');
    }

    public function parentLetter(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_letter_id');
    }

    public function childLetters(): HasMany
    {
        return $this->hasMany(self::class, 'parent_letter_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function taxObject(): BelongsTo
    {
        return $this->belongsTo(TaxObject::class, 'tax_object_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(Pimpinan::class, 'pimpinan_id');
    }

    public function compensations(): HasMany
    {
        return $this->hasMany(TaxAssessmentCompensation::class, 'tax_assessment_letter_id');
    }

    public function isDraft(): bool
    {
        return $this->status === TaxAssessmentLetterStatus::Draft;
    }

    public function isApproved(): bool
    {
        return $this->status === TaxAssessmentLetterStatus::Disetujui;
    }

    public function allowsCompensation(): bool
    {
        return $this->letter_type?->allowsCreditCompensation() ?? false;
    }

    public function getAllocatedAmount(): float
    {
        return (float) $this->compensations()->get()->sum(function (TaxAssessmentCompensation $compensation) {
            return (float) $compensation->allocation_amount;
        });
    }

    public function refreshAvailableCredit(): void
    {
        $creditBase = (float) ($this->total_assessment ?? 0);
        $allocated = $this->getAllocatedAmount();

        $this->available_credit = max(0, $creditBase - $allocated);
        $this->saveQuietly();
    }
}