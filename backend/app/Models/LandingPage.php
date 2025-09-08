<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'featured_image',
        'gallery',
        'template',
        'custom_fields',
        'status',
        'is_featured',
        'view_count',
        'published_at',
        'author_id',
    ];

    protected $casts = [
        'meta_keywords' => 'array',
        'gallery' => 'array',
        'custom_fields' => 'array',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Get the author of the landing page.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope for published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope for draft pages.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for archived pages.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope for featured pages.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Search pages by title or content.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }

    /**
     * Get the status display name.
     */
    public function getStatusDisplayNameAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Черновик',
            'published' => 'Опубликовано',
            'archived' => 'Архив',
            default => 'Неизвестно'
        };
    }

    /**
     * Check if page is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && 
               $this->published_at && 
               $this->published_at->isPast();
    }

    /**
     * Get the excerpt of the content.
     */
    public function getExcerptAttribute(int $length = 200): string
    {
        $content = strip_tags($this->content);
        return strlen($content) > $length 
            ? substr($content, 0, $length) . '...' 
            : $content;
    }

    /**
     * Increment view count.
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
