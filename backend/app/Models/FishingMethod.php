<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'technique',
        'equipment_needed',
        'difficulty',
        'season',
        'best_conditions',
        'target_fish',
        'equipment_list',
        'step_by_step',
        'photo_url',
        'additional_photos',
        'video_urls',
        'tips',
        'common_mistakes',
        'view_count',
        'is_active',
    ];

    protected $casts = [
        'best_conditions' => 'array',
        'target_fish' => 'array',
        'equipment_list' => 'array',
        'additional_photos' => 'array',
        'video_urls' => 'array',
        'tips' => 'array',
        'common_mistakes' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the catch records that used this method.
     */
    public function catchRecords(): HasMany
    {
        return $this->hasMany(CatchRecord::class);
    }

    /**
     * Scope for active methods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for easy methods.
     */
    public function scopeEasy($query)
    {
        return $query->where('difficulty', 'easy');
    }

    /**
     * Scope for medium difficulty methods.
     */
    public function scopeMedium($query)
    {
        return $query->where('difficulty', 'medium');
    }

    /**
     * Scope for hard methods.
     */
    public function scopeHard($query)
    {
        return $query->where('difficulty', 'hard');
    }

    /**
     * Scope for specific season.
     */
    public function scopeForSeason($query, string $season)
    {
        return $query->where('season', $season);
    }

    /**
     * Search methods by name or description.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('technique', 'like', "%{$search}%");
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
     * Get the season display name.
     */
    public function getSeasonDisplayNameAttribute(): string
    {
        return match($this->season) {
            'spring' => 'Весна',
            'summer' => 'Лето',
            'autumn' => 'Осень',
            'winter' => 'Зима',
            'all' => 'Круглый год',
            default => 'Не указано'
        };
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
