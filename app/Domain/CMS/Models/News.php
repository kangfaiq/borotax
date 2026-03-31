<?php

namespace App\Domain\CMS\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'news';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'image_url',
        'published_at',
        'category',
        'author',
        'view_count',
        'is_featured',
        'source_url',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'is_featured' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (News $news) {
            if (empty($news->slug)) {
                $news->slug = static::generateUniqueSlug($news->title);
            }
        });

        static::updating(function (News $news) {
            if ($news->isDirty('title') && !$news->isDirty('slug')) {
                $news->slug = static::generateUniqueSlug($news->title, $news->id);
            }
        });
    }

    protected static function generateUniqueSlug(string $title, ?string $ignoreId = null): string
    {
        $slug = str()->slug($title);
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
     * Scope untuk published
     */
    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', now());
    }

    /**
     * Scope order by published
     */
    public function scopeLatestPublished($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}
