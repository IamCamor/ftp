<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FishingTackle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'model',
        'slug',
        'description',
        'type',
        'category',
        'specifications',
        'features',
        'price_min',
        'price_max',
        'photo_url',
        'additional_photos',
        'pros',
        'cons',
        'best_for',
        'compatible_with',
        'maintenance',
        'view_count',
        'is_active',
    ];

    protected $casts = [
        'specifications' => 'array',
        'features' => 'array',
        'additional_photos' => 'array',
        'pros' => 'array',
        'cons' => 'array',
        'best_for' => 'array',
        'compatible_with' => 'array',
        'maintenance' => 'array',
        'is_active' => 'boolean',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
    ];

    /**
     * Scope for active tackle.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for specific brand.
     */
    public function scopeOfBrand($query, string $brand)
    {
        return $query->where('brand', $brand);
    }

    /**
     * Search tackle by name, brand, or model.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('brand', 'like', "%{$search}%")
              ->orWhere('model', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Get the type display name.
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match($this->type) {
            'rod' => 'Удилище',
            'reel' => 'Катушка',
            'line' => 'Леска',
            'hook' => 'Крючок',
            'lure' => 'Приманка',
            'bait' => 'Наживка',
            'sinker' => 'Грузило',
            'float' => 'Поплавок',
            'leader' => 'Поводок',
            'swivel' => 'Вертлюжок',
            'split_ring' => 'Разрезное кольцо',
            default => 'Снасть'
        };
    }

    /**
     * Get the price range as a string.
     */
    public function getPriceRangeAttribute(): string
    {
        if ($this->price_min && $this->price_max) {
            return "{$this->price_min} - {$this->price_max} ₽";
        } elseif ($this->price_min) {
            return "от {$this->price_min} ₽";
        } elseif ($this->price_max) {
            return "до {$this->price_max} ₽";
        }
        return 'Цена не указана';
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
