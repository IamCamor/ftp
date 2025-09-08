<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishingKnot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'purpose',
        'instructions',
        'difficulty',
        'strength_percentage',
        'photo_url',
        'step_photos',
        'video_urls',
        'use_cases',
        'line_types',
        'view_count',
        'is_active',
    ];

    protected $casts = [
        'step_photos' => 'array',
        'video_urls' => 'array',
        'use_cases' => 'array',
        'line_types' => 'array',
        'is_active' => 'boolean',
        'strength_percentage' => 'decimal:2',
    ];

    /**
     * Get the catch records that use this knot.
     */
    public function catchRecords(): HasMany
    {
        return $this->hasMany(CatchRecord::class);
    }

    /**
     * Scope for active knots.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for easy knots.
     */
    public function scopeEasy($query)
    {
        return $query->where('difficulty', 'easy');
    }

    /**
     * Scope for medium difficulty knots.
     */
    public function scopeMedium($query)
    {
        return $query->where('difficulty', 'medium');
    }

    /**
     * Scope for hard knots.
     */
    public function scopeHard($query)
    {
        return $query->where('difficulty', 'hard');
    }

    /**
     * Search knots by name or description.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('purpose', 'like', "%{$search}%");
        });
    }

    /**
     * Get the difficulty display name.
     */
    public function getDifficultyDisplayNameAttribute(): string
    {
        return match($this->difficulty) {
            'easy' => 'Легкий',
            'medium' => 'Средний',
            'hard' => 'Сложный',
            default => 'Неизвестно'
        };
    }

    /**
     * Get the strength percentage as a string.
     */
    public function getStrengthDisplayAttribute(): string
    {
        if ($this->strength_percentage) {
            return "{$this->strength_percentage}%";
        }
        return 'Не указано';
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
