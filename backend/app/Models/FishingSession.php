<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class FishingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'point_id',
        'name',
        'description',
        'started_at',
        'ended_at',
        'status',
        'watch_hand',
        'biometric_tracking',
        'gps_tracking',
        'mood_tracking',
        'total_casts',
        'total_reels',
        'total_reels_meters',
        'catches_count',
        'total_weight',
        'avg_heart_rate',
        'max_heart_rate',
        'min_heart_rate',
        'avg_hrv',
        'avg_stress_level',
        'avg_mood_index',
        'max_mood_index',
        'min_mood_index',
        'time_high_mood',
        'time_medium_mood',
        'time_low_mood',
        'time_stressed',
        'time_calm',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'total_distance',
        'temperature',
        'humidity',
        'wind_speed',
        'weather_condition',
        'gps_track',
        'mood_timeline',
        'heart_rate_timeline',
        'activity_timeline',
        'session_summary',
        'coach_insights',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'biometric_tracking' => 'boolean',
        'gps_tracking' => 'boolean',
        'mood_tracking' => 'boolean',
        'total_reels_meters' => 'decimal:2',
        'total_weight' => 'decimal:2',
        'avg_hrv' => 'decimal:2',
        'avg_stress_level' => 'decimal:2',
        'avg_mood_index' => 'decimal:2',
        'max_mood_index' => 'decimal:2',
        'min_mood_index' => 'decimal:2',
        'start_latitude' => 'decimal:7',
        'start_longitude' => 'decimal:7',
        'end_latitude' => 'decimal:7',
        'end_longitude' => 'decimal:7',
        'total_distance' => 'decimal:2',
        'temperature' => 'decimal:2',
        'wind_speed' => 'decimal:2',
        'gps_track' => 'array',
        'mood_timeline' => 'array',
        'heart_rate_timeline' => 'array',
        'activity_timeline' => 'array',
    ];

    /**
     * Get the user that owns the fishing session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the point that owns the fishing session
     */
    public function point(): BelongsTo
    {
        return $this->belongsTo(Point::class);
    }

    /**
     * Get the biometric data for the fishing session
     */
    public function biometricData(): HasMany
    {
        return $this->hasMany(BiometricData::class);
    }

    /**
     * Get the catch records for the fishing session
     */
    public function catchRecords(): HasMany
    {
        return $this->hasMany(CatchRecord::class);
    }

    /**
     * Get the latest biometric data
     */
    public function latestBiometricData(): HasOne
    {
        return $this->hasOne(BiometricData::class)->latest('recorded_at');
    }

    /**
     * Get session duration in minutes
     */
    public function getDurationAttribute(): int
    {
        if (!$this->started_at) {
            return 0;
        }

        $endTime = $this->ended_at ?? now();
        return $this->started_at->diffInMinutes($endTime);
    }

    /**
     * Get session duration in human readable format
     */
    public function getDurationHumanAttribute(): string
    {
        $duration = $this->duration;
        
        if ($duration < 60) {
            return "{$duration} мин";
        }
        
        $hours = intval($duration / 60);
        $minutes = $duration % 60;
        
        if ($minutes > 0) {
            return "{$hours}ч {$minutes}м";
        }
        
        return "{$hours}ч";
    }

    /**
     * Get average casts per hour
     */
    public function getCastsPerHourAttribute(): float
    {
        if ($this->duration <= 0) {
            return 0;
        }
        
        return round(($this->total_casts / $this->duration) * 60, 1);
    }

    /**
     * Get average reels per hour
     */
    public function getReelsPerHourAttribute(): float
    {
        if ($this->duration <= 0) {
            return 0;
        }
        
        return round(($this->total_reels / $this->duration) * 60, 1);
    }

    /**
     * Get average meters per hour
     */
    public function getMetersPerHourAttribute(): float
    {
        if ($this->duration <= 0) {
            return 0;
        }
        
        return round(($this->total_reels_meters / $this->duration) * 60, 1);
    }

    /**
     * Get mood percentage breakdown
     */
    public function getMoodBreakdownAttribute(): array
    {
        $total = $this->time_high_mood + $this->time_medium_mood + $this->time_low_mood;
        
        if ($total <= 0) {
            return [
                'high' => 0,
                'medium' => 0,
                'low' => 0,
            ];
        }
        
        return [
            'high' => round(($this->time_high_mood / $total) * 100, 1),
            'medium' => round(($this->time_medium_mood / $total) * 100, 1),
            'low' => round(($this->time_low_mood / $total) * 100, 1),
        ];
    }

    /**
     * Get stress percentage breakdown
     */
    public function getStressBreakdownAttribute(): array
    {
        $total = $this->time_stressed + $this->time_calm;
        
        if ($total <= 0) {
            return [
                'stressed' => 0,
                'calm' => 0,
            ];
        }
        
        return [
            'stressed' => round(($this->time_stressed / $total) * 100, 1),
            'calm' => round(($this->time_calm / $total) * 100, 1),
        ];
    }

    /**
     * Get session status description
     */
    public function getStatusDescriptionAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Активная',
            'paused' => 'Приостановлена',
            'completed' => 'Завершена',
            'cancelled' => 'Отменена',
            default => 'Неизвестно'
        };
    }

    /**
     * Get watch hand description
     */
    public function getWatchHandDescriptionAttribute(): string
    {
        return match ($this->watch_hand) {
            'casting' => 'На руке заброса',
            'reeling' => 'На руке сматывания',
            default => 'Не указано'
        };
    }

    /**
     * Get session summary with mood insights
     */
    public function getMoodSummaryAttribute(): string
    {
        $moodBreakdown = $this->mood_breakdown;
        $duration = $this->duration_human;
        
        if ($moodBreakdown['high'] >= 50) {
            return "Отличная сессия! Вы были счастливы {$moodBreakdown['high']}% времени ({$duration}).";
        } elseif ($moodBreakdown['high'] >= 30) {
            return "Хорошая сессия! Вы были счастливы {$moodBreakdown['high']}% времени ({$duration}).";
        } elseif ($moodBreakdown['low'] >= 50) {
            return "Сложная сессия. Вы были расстроены {$moodBreakdown['low']}% времени ({$duration}).";
        } else {
            return "Спокойная сессия. Время: {$duration}.";
        }
    }

    /**
     * Get peak mood moment
     */
    public function getPeakMoodMomentAttribute(): ?array
    {
        if (!$this->mood_timeline || !is_array($this->mood_timeline)) {
            return null;
        }
        
        $maxMood = 0;
        $peakTime = null;
        
        foreach ($this->mood_timeline as $entry) {
            if (isset($entry['mood_index']) && $entry['mood_index'] > $maxMood) {
                $maxMood = $entry['mood_index'];
                $peakTime = $entry['timestamp'] ?? null;
            }
        }
        
        if ($peakTime) {
            return [
                'mood_index' => $maxMood,
                'timestamp' => $peakTime,
                'time_formatted' => Carbon::parse($peakTime)->format('H:i'),
            ];
        }
        
        return null;
    }

    /**
     * Scope for active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed sessions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for recent sessions
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for sessions with biometric data
     */
    public function scopeWithBiometrics($query)
    {
        return $query->where('biometric_tracking', true);
    }

    /**
     * Scope for sessions with mood tracking
     */
    public function scopeWithMoodTracking($query)
    {
        return $query->where('mood_tracking', true);
    }

    /**
     * Scope for sessions by mood range
     */
    public function scopeByMoodRange($query, $min, $max)
    {
        return $query->whereBetween('avg_mood_index', [$min, $max]);
    }

    /**
     * Scope for sessions by duration range
     */
    public function scopeByDurationRange($query, $minMinutes, $maxMinutes)
    {
        return $query->whereRaw('TIMESTAMPDIFF(MINUTE, started_at, COALESCE(ended_at, NOW())) BETWEEN ? AND ?', [$minMinutes, $maxMinutes]);
    }
}