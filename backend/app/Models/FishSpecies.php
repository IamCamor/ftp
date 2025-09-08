<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishSpecies extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'scientific_name',
        'slug',
        'description',
        'habitat',
        'feeding_habits',
        'spawning_info',
        'min_size',
        'max_size',
        'min_weight',
        'max_weight',
        'photo_url',
        'additional_photos',
        'category',
        'is_protected',
        'seasons',
        'best_times',
        'view_count',
        'is_active',
    ];

    protected $casts = [
        'additional_photos' => 'array',
        'seasons' => 'array',
        'best_times' => 'array',
        'is_protected' => 'boolean',
        'is_active' => 'boolean',
        'min_weight' => 'decimal:2',
        'max_weight' => 'decimal:2',
    ];

    /**
     * Get the catch records for this fish species.
     */
    public function catchRecords(): HasMany
    {
        return $this->hasMany(CatchRecord::class);
    }

    /**
     * Scope for active fish species.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for freshwater fish.
     */
    public function scopeFreshwater($query)
    {
        return $query->where('category', 'freshwater');
    }

    /**
     * Scope for saltwater fish.
     */
    public function scopeSaltwater($query)
    {
        return $query->where('category', 'saltwater');
    }

    /**
     * Scope for both freshwater and saltwater fish.
     */
    public function scopeBoth($query)
    {
        return $query->where('category', 'both');
    }

    /**
     * Scope for protected fish species.
     */
    public function scopeProtected($query)
    {
        return $query->where('is_protected', true);
    }

    /**
     * Search fish species by name or scientific name.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('scientific_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Get the average size of this fish species.
     */
    public function getAverageSizeAttribute(): ?int
    {
        if ($this->min_size && $this->max_size) {
            return ($this->min_size + $this->max_size) / 2;
        }
        return null;
    }

    /**
     * Get the average weight of this fish species.
     */
    public function getAverageWeightAttribute(): ?float
    {
        if ($this->min_weight && $this->max_weight) {
            return ($this->min_weight + $this->max_weight) / 2;
        }
        return null;
    }

    /**
     * Get the size range as a string.
     */
    public function getSizeRangeAttribute(): string
    {
        if ($this->min_size && $this->max_size) {
            return "{$this->min_size}-{$this->max_size} см";
        } elseif ($this->min_size) {
            return "от {$this->min_size} см";
        } elseif ($this->max_size) {
            return "до {$this->max_size} см";
        }
        return 'Не указано';
    }

    /**
     * Get the weight range as a string.
     */
    public function getWeightRangeAttribute(): string
    {
        if ($this->min_weight && $this->max_weight) {
            return "{$this->min_weight}-{$this->max_weight} кг";
        } elseif ($this->min_weight) {
            return "от {$this->min_weight} кг";
        } elseif ($this->max_weight) {
            return "до {$this->max_weight} кг";
        }
        return 'Не указано';
    }

    /**
     * Get the category display name.
     */
    public function getCategoryDisplayNameAttribute(): string
    {
        return match($this->category) {
            'freshwater' => 'Пресноводная',
            'saltwater' => 'Морская',
            'both' => 'Пресноводная и морская',
            default => 'Неизвестно'
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
