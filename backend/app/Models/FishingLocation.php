<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishingLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'water_type',
        'latitude',
        'longitude',
        'region',
        'country',
        'fish_species',
        'fishing_methods',
        'best_seasons',
        'access_info',
        'facilities',
        'regulations',
        'photo_url',
        'additional_photos',
        'tips',
        'warnings',
        'view_count',
        'is_active',
    ];

    protected $casts = [
        'fish_species' => 'array',
        'fishing_methods' => 'array',
        'best_seasons' => 'array',
        'access_info' => 'array',
        'facilities' => 'array',
        'regulations' => 'array',
        'additional_photos' => 'array',
        'tips' => 'array',
        'warnings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the catch records from this location.
     */
    public function catchRecords(): HasMany
    {
        return $this->hasMany(CatchRecord::class);
    }

    /**
     * Scope for active locations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for waterbodies.
     */
    public function scopeWaterbody($query)
    {
        return $query->where('type', 'waterbody');
    }

    /**
     * Scope for fishing spots.
     */
    public function scopeSpot($query)
    {
        return $query->where('type', 'spot');
    }

    /**
     * Scope for regions.
     */
    public function scopeRegion($query)
    {
        return $query->where('type', 'region');
    }

    /**
     * Scope for structures.
     */
    public function scopeStructure($query)
    {
        return $query->where('type', 'structure');
    }

    /**
     * Scope for specific water type.
     */
    public function scopeWaterType($query, string $waterType)
    {
        return $query->where('water_type', $waterType);
    }

    /**
     * Scope for specific region.
     */
    public function scopeInRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope for nearby locations.
     */
    public function scopeNearby($query, float $latitude, float $longitude, int $radiusKm = 50)
    {
        return $query->whereRaw(
            "ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?)) <= ?",
            [$longitude, $latitude, $radiusKm * 1000]
        );
    }

    /**
     * Search locations by name or description.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('region', 'like', "%{$search}%")
              ->orWhere('country', 'like', "%{$search}%");
        });
    }

    /**
     * Get the type display name.
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match($this->type) {
            'waterbody' => 'Водоем',
            'spot' => 'Место ловли',
            'region' => 'Регион',
            'structure' => 'Структура',
            default => 'Неизвестно'
        };
    }

    /**
     * Get the water type display name.
     */
    public function getWaterTypeDisplayNameAttribute(): string
    {
        return match($this->water_type) {
            'river' => 'Река',
            'lake' => 'Озеро',
            'sea' => 'Море',
            'pond' => 'Пруд',
            'reservoir' => 'Водохранилище',
            'canal' => 'Канал',
            'stream' => 'Ручей',
            default => 'Не указано'
        };
    }

    /**
     * Get the coordinates as a string.
     */
    public function getCoordinatesAttribute(): string
    {
        if ($this->latitude && $this->longitude) {
            return "{$this->latitude}, {$this->longitude}";
        }
        return 'Не указано';
    }

    /**
     * Get the full location name.
     */
    public function getFullNameAttribute(): string
    {
        $parts = [$this->name];
        
        if ($this->region) {
            $parts[] = $this->region;
        }
        
        if ($this->country) {
            $parts[] = $this->country;
        }
        
        return implode(', ', $parts);
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
