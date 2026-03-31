<?php

namespace App\Domain\CMS\Models;

use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory, HasUuids, HasEncryptedAttributes;

    protected $table = 'destinations';

    /**
     * Kolom yang dienkripsi
     */
    protected array $encryptedAttributes = [
        'phone',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'address',
        'category',
        'image_url',
        'rating',
        'review_count',
        'price_range',
        'facilities',
        'phone',
        'website',
        'latitude',
        'longitude',
        'is_featured',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'review_count' => 'integer',
        'facilities' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_featured' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (Destination $destination) {
            if (empty($destination->slug)) {
                $destination->slug = static::generateUniqueSlug($destination->name);
            }
        });

        static::updating(function (Destination $destination) {
            if ($destination->isDirty('name') && !$destination->isDirty('slug')) {
                $destination->slug = static::generateUniqueSlug($destination->name, $destination->id);
            }
        });
    }

    protected static function generateUniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $slug = str()->slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    /**
     * Scope untuk kategori tertentu
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope untuk featured
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'wisata' => 'Wisata',
            'kuliner' => 'Kuliner',
            'hotel' => 'Hotel',
            'oleh-oleh' => 'Oleh-Oleh',
            'hiburan' => 'Hiburan',
            default => $this->category,
        };
    }
}
