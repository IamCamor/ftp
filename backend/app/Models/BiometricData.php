<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricData extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fishing_session_id',
        'catch_record_id',
        'heart_rate',
        'hrv',
        'stress_level',
        'mood_index',
        'mood_emoji',
        'temperature',
        'steps',
        'calories_burned',
        'activity_level',
        'acceleration_x',
        'acceleration_y',
        'acceleration_z',
        'gyroscope_x',
        'gyroscope_y',
        'gyroscope_z',
        'watch_hand',
        'casts_count',
        'reels_count',
        'reels_meters',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'hrv' => 'decimal:2',
        'stress_level' => 'decimal:2',
        'mood_index' => 'decimal:2',
        'temperature' => 'decimal:2',
        'calories_burned' => 'decimal:2',
        'activity_level' => 'decimal:2',
        'acceleration_x' => 'decimal:6',
        'acceleration_y' => 'decimal:6',
        'acceleration_z' => 'decimal:6',
        'gyroscope_x' => 'decimal:6',
        'gyroscope_y' => 'decimal:6',
        'gyroscope_z' => 'decimal:6',
        'reels_meters' => 'decimal:2',
    ];

    /**
     * Get the user that owns the biometric data
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fishing session that owns the biometric data
     */
    public function fishingSession(): BelongsTo
    {
        return $this->belongsTo(FishingSession::class);
    }

    /**
     * Get the catch record that owns the biometric data
     */
    public function catchRecord(): BelongsTo
    {
        return $this->belongsTo(CatchRecord::class);
    }

    /**
     * Get mood emoji based on mood index
     */
    public function getMoodEmojiAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        $moodIndex = $this->mood_index ?? 50;
        
        if ($moodIndex >= 80) {
            return '😊';
        } elseif ($moodIndex >= 60) {
            return '🙂';
        } elseif ($moodIndex >= 40) {
            return '😐';
        } elseif ($moodIndex >= 20) {
            return '😕';
        } else {
            return '😩';
        }
    }

    /**
     * Get mood description based on mood index
     */
    public function getMoodDescriptionAttribute(): string
    {
        $moodIndex = $this->mood_index ?? 50;
        
        if ($moodIndex >= 80) {
            return 'Отличное настроение';
        } elseif ($moodIndex >= 60) {
            return 'Хорошее настроение';
        } elseif ($moodIndex >= 40) {
            return 'Нейтральное настроение';
        } elseif ($moodIndex >= 20) {
            return 'Плохое настроение';
        } else {
            return 'Очень плохое настроение';
        }
    }

    /**
     * Get stress level description
     */
    public function getStressDescriptionAttribute(): string
    {
        $stressLevel = $this->stress_level ?? 50;
        
        if ($stressLevel >= 80) {
            return 'Высокий стресс';
        } elseif ($stressLevel >= 60) {
            return 'Повышенный стресс';
        } elseif ($stressLevel >= 40) {
            return 'Умеренный стресс';
        } elseif ($stressLevel >= 20) {
            return 'Низкий стресс';
        } else {
            return 'Очень низкий стресс';
        }
    }

    /**
     * Get heart rate zone
     */
    public function getHeartRateZoneAttribute(): string
    {
        $heartRate = $this->heart_rate;
        if (!$heartRate) {
            return 'unknown';
        }

        // Примерные зоны пульса (можно настроить под пользователя)
        if ($heartRate < 60) {
            return 'resting';
        } elseif ($heartRate < 100) {
            return 'light';
        } elseif ($heartRate < 140) {
            return 'moderate';
        } elseif ($heartRate < 180) {
            return 'vigorous';
        } else {
            return 'maximum';
        }
    }

    /**
     * Get heart rate zone description
     */
    public function getHeartRateZoneDescriptionAttribute(): string
    {
        return match ($this->heart_rate_zone) {
            'resting' => 'Покой',
            'light' => 'Легкая активность',
            'moderate' => 'Умеренная активность',
            'vigorous' => 'Интенсивная активность',
            'maximum' => 'Максимальная активность',
            default => 'Неизвестно'
        };
    }

    /**
     * Scope for filtering by mood range
     */
    public function scopeByMoodRange($query, $min, $max)
    {
        return $query->whereBetween('mood_index', [$min, $max]);
    }

    /**
     * Scope for filtering by heart rate range
     */
    public function scopeByHeartRateRange($query, $min, $max)
    {
        return $query->whereBetween('heart_rate', [$min, $max]);
    }

    /**
     * Scope for filtering by stress level range
     */
    public function scopeByStressRange($query, $min, $max)
    {
        return $query->whereBetween('stress_level', [$min, $max]);
    }

    /**
     * Scope for recent data
     */
    public function scopeRecent($query, $minutes = 60)
    {
        return $query->where('recorded_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope for fishing session data
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('fishing_session_id', $sessionId);
    }

    /**
     * Scope for catch data
     */
    public function scopeForCatch($query, $catchId)
    {
        return $query->where('catch_record_id', $catchId);
    }
}