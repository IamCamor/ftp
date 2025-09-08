<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoatEngine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'model',
        'slug',
        'description',
        'type',
        'fuel_type',
        'power_hp',
        'power_kw',
        'weight',
        'cylinders',
        'displacement',
        'specifications',
        'features',
        'price_min',
        'price_max',
        'photo_url',
        'additional_photos',
        'pros',
        'cons',
        'best_for',
        'compatible_boats',
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
        'compatible_boats' => 'array',
        'maintenance' => 'array',
        'is_active' => 'boolean',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
    ];

    /**
     * Get the catch records that used this engine.
     */
    public function catchRecords(): HasMany
    {
        return $this->hasMany(CatchRecord::class);
    }

    /**
     * Scope for active engines.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for outboard engines.
     */
    public function scopeOutboard($query)
    {
        return $query->where('type', 'outboard');
    }

    /**
     * Scope for inboard engines.
     */
    public function scopeInboard($query)
    {
        return $query->where('type', 'inboard');
    }

    /**
     * Scope for electric engines.
     */
    public function scopeElectric($query)
    {
        return $query->where('type', 'electric');
    }

    /**
     * Scope for specific fuel type.
     */
    public function scopeFuelType($query, string $fuelType)
    {
        return $query->where('fuel_type', $fuelType);
    }

    /**
     * Scope for power range.
     */
    public function scopePowerRange($query, int $minPower, int $maxPower)
    {
        return $query->whereBetween('power_hp', [$minPower, $maxPower]);
    }

    /**
     * Search engines by name, brand, or model.
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
            'outboard' => 'Подвесной мотор',
            'inboard' => 'Стационарный мотор',
            'electric' => 'Электромотор',
            'hybrid' => 'Гибридный мотор',
            default => 'Неизвестно'
        };
    }

    /**
     * Get the fuel type display name.
     */
    public function getFuelTypeDisplayNameAttribute(): string
    {
        return match($this->fuel_type) {
            'petrol' => 'Бензин',
            'diesel' => 'Дизель',
            'electric' => 'Электричество',
            'hybrid' => 'Гибрид',
            default => 'Не указано'
        };
    }

    /**
     * Get the power display string.
     */
    public function getPowerDisplayAttribute(): string
    {
        if ($this->power_hp) {
            return "{$this->power_hp} л.с.";
        } elseif ($this->power_kw) {
            return "{$this->power_kw} кВт";
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
