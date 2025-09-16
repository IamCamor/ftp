<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'started_at',
        'ended_at',
        'status',
        'smartwatch_data',
        'track_points',
        'total_distance',
        'total_catches',
        'total_weight'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'smartwatch_data' => 'array',
        'track_points' => 'array',
        'total_distance' => 'decimal:2',
        'total_weight' => 'decimal:2'
    ];

    /**
     * Пользователь, которому принадлежит трек
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Уловы в этом треке
     */
    public function catches(): HasMany
    {
        return $this->hasMany(CatchRecord::class);
    }

    /**
     * Получить последний улов в треке
     */
    public function lastCatch(): ?CatchRecord
    {
        return $this->catches()->latest()->first();
    }

    /**
     * Проверить, активен ли трек
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Проверить, завершен ли трек
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Получить продолжительность трека в минутах
     */
    public function getDurationInMinutes(): ?int
    {
        if (!$this->ended_at) {
            return null;
        }
        
        return $this->started_at->diffInMinutes($this->ended_at);
    }

    /**
     * Получить средний вес улова
     */
    public function getAverageWeight(): ?float
    {
        if ($this->total_catches === 0 || $this->total_catches === null) {
            return null;
        }
        
        return round($this->total_weight / $this->total_catches, 2);
    }

    /**
     * Обновить статистику трека
     */
    public function updateStats(): void
    {
        $catches = $this->catches;
        
        $this->update([
            'total_catches' => $catches->count(),
            'total_weight' => $catches->sum('weight'),
            'total_distance' => $this->calculateTotalDistance()
        ]);
    }

    /**
     * Вычислить общую дистанцию трека
     */
    private function calculateTotalDistance(): float
    {
        $points = $this->track_points ?? [];
        if (count($points) < 2) {
            return 0;
        }

        $totalDistance = 0;
        for ($i = 1; $i < count($points); $i++) {
            $prev = $points[$i - 1];
            $current = $points[$i];
            
            $distance = $this->calculateDistance(
                $prev['lat'], $prev['lng'],
                $current['lat'], $current['lng']
            );
            
            $totalDistance += $distance;
        }

        return round($totalDistance, 2);
    }

    /**
     * Вычислить расстояние между двумя точками (формула Haversine)
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // Радиус Земли в км

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Добавить точку трека
     */
    public function addTrackPoint(float $lat, float $lng, ?array $smartwatchData = null): void
    {
        $points = $this->track_points ?? [];
        
        $points[] = [
            'lat' => $lat,
            'lng' => $lng,
            'timestamp' => now()->toISOString(),
            'smartwatch_data' => $smartwatchData
        ];

        $this->update([
            'track_points' => $points,
            'total_distance' => $this->calculateTotalDistance()
        ]);
    }
}