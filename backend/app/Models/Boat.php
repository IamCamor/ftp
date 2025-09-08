<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Boat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'model',
        'slug',
        'description',
        'type',
        'length',
        'width',
        'capacity',
        'max_weight',
        'material',
        'features',
        'price_min',
        'price_max',
        'photo_url',
        'additional_photos',
        'pros',
        'cons',
        'best_for',
        'view_count',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'additional_photos' => 'array',
        'pros' => 'array',
        'cons' => 'array',
        'best_for' => 'array',
        'is_active' => 'boolean',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
    ];

    /**
     * Get the catch records that used this boat.
     */
    public function catchRecords(): HasMany
    {
        return $this->hasMany(CatchRecord::class);
    }

    /**
     * Scope for active boats.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inflatable boats.
     */
    public function scopeInflatable($query)
    {
        return $query->where('type', 'inflatable');
    }

    /**
     * Scope for rigid boats.
     */
    public function scopeRigid($query)
    {
        return $query->where('type', 'rigid');
    }

    /**
     * Scope for kayaks.
     */
    public function scopeKayak($query)
    {
        return $query->where('type', 'kayak');
    }

    /**
     * Scope for canoes.
     */
    public function scopeCanoe($query)
    {
        return $query->where('type', 'canoe');
    }

    /**
     * Search boats by name, brand, or model.
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
            'inflatable' => 'Надувная лодка',
            'rigid' => 'Жесткая лодка',
            'kayak' => 'Каяк',
            'canoe' => 'Каноэ',
            default => 'Неизвестно'
        };
    }

    /**
     * Get the dimensions as a string.
     */
    public function getDimensionsAttribute(): string
    {
        if ($this->length && $this->width) {
            return "{$this->length}×{$this->width} см";
        } elseif ($this->length) {
            return "Длина: {$this->length} см";
        }
        return 'Не указано';
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
