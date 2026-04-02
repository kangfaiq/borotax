<?php

namespace App\Domain\Region\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    use SoftDeletes, HasFactory;
    protected $fillable = ['regency_code', 'code', 'name'];

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'regency_code', 'code');
    }

    public function villages(): HasMany
    {
        return $this->hasMany(Village::class, 'district_code', 'code');
    }
}
