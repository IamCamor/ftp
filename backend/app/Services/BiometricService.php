<?php

namespace App\Services;

use App\Models\BiometricData;
use App\Models\FishingSession;
use App\Models\CatchRecord;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BiometricService
{
    /**
     * Calculate mood index based on biometric data
     */
    public function calculateMoodIndex(array $data): float
    {
        $heartRate = $data['heart_rate'] ?? null;
        $hrv = $data['hrv'] ?? null;
        $stressLevel = $data['stress_level'] ?? null;
        $activityLevel = $data['activity_level'] ?? null;
        $recentCatches = $data['recent_catches'] ?? 0;
        $timeSinceLastCatch = $data['time_since_last_catch'] ?? null; // minutes
        
        $moodScore = 50; // Base mood score
        
        // Heart rate influence (optimal range: 60-100 bpm)
        if ($heartRate) {
            if ($heartRate >= 60 && $heartRate <= 100) {
                $moodScore += 15; // Optimal heart rate
            } elseif ($heartRate < 60) {
                $moodScore += 5; // Low but acceptable
            } elseif ($heartRate > 140) {
                $moodScore -= 20; // High stress/excitement
            } elseif ($heartRate > 100) {
                $moodScore -= 10; // Elevated
            }
        }
        
        // HRV influence (higher HRV = better mood)
        if ($hrv) {
            if ($hrv > 50) {
                $moodScore += 20; // High HRV = relaxed
            } elseif ($hrv > 30) {
                $moodScore += 10; // Good HRV
            } elseif ($hrv < 20) {
                $moodScore -= 15; // Low HRV = stress
            }
        }
        
        // Stress level influence
        if ($stressLevel !== null) {
            if ($stressLevel < 30) {
                $moodScore += 15; // Low stress
            } elseif ($stressLevel < 50) {
                $moodScore += 5; // Moderate stress
            } elseif ($stressLevel > 70) {
                $moodScore -= 20; // High stress
            } elseif ($stressLevel > 50) {
                $moodScore -= 10; // Elevated stress
            }
        }
        
        // Recent catches influence
        if ($recentCatches > 0) {
            $moodScore += min($recentCatches * 10, 30); // Up to +30 for recent catches
        }
        
        // Time since last catch influence
        if ($timeSinceLastCatch !== null) {
            if ($timeSinceLastCatch < 30) {
                $moodScore += 10; // Recent catch
            } elseif ($timeSinceLastCatch > 120) {
                $moodScore -= 15; // Long time without catch
            } elseif ($timeSinceLastCatch > 60) {
                $moodScore -= 5; // Some time without catch
            }
        }
        
        // Activity level influence
        if ($activityLevel !== null) {
            if ($activityLevel > 70) {
                $moodScore += 10; // High activity = engagement
            } elseif ($activityLevel < 20) {
                $moodScore -= 10; // Low activity = boredom
            }
        }
        
        // Ensure mood index is between 0 and 100
        return max(0, min(100, $moodScore));
    }
    
    /**
     * Calculate stress level based on heart rate and HRV
     */
    public function calculateStressLevel(int $heartRate, ?float $hrv = null): float
    {
        $stressScore = 50; // Base stress score
        
        // Heart rate influence
        if ($heartRate > 120) {
            $stressScore += 30; // High heart rate
        } elseif ($heartRate > 100) {
            $stressScore += 15; // Elevated heart rate
        } elseif ($heartRate < 60) {
            $stressScore -= 20; // Low heart rate = relaxed
        }
        
        // HRV influence
        if ($hrv !== null) {
            if ($hrv < 20) {
                $stressScore += 25; // Low HRV = high stress
            } elseif ($hrv < 30) {
                $stressScore += 15; // Reduced HRV
            } elseif ($hrv > 50) {
                $stressScore -= 20; // High HRV = low stress
            }
        }
        
        // Ensure stress level is between 0 and 100
        return max(0, min(100, $stressScore));
    }
    
    /**
     * Get mood emoji based on mood index
     */
    public function getMoodEmoji(float $moodIndex): string
    {
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
    public function getMoodDescription(float $moodIndex): string
    {
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
     * Process and store biometric data
     */
    public function processBiometricData(array $data, int $userId, ?int $sessionId = null, ?int $catchId = null): BiometricData
    {
        // Calculate mood index
        $moodIndex = $this->calculateMoodIndex($data);
        
        // Calculate stress level if not provided
        $stressLevel = $data['stress_level'] ?? $this->calculateStressLevel(
            $data['heart_rate'] ?? 70,
            $data['hrv'] ?? null
        );
        
        // Get mood emoji
        $moodEmoji = $this->getMoodEmoji($moodIndex);
        
        // Create biometric data record
        $biometricData = BiometricData::create([
            'user_id' => $userId,
            'fishing_session_id' => $sessionId,
            'catch_record_id' => $catchId,
            'heart_rate' => $data['heart_rate'] ?? null,
            'hrv' => $data['hrv'] ?? null,
            'stress_level' => $stressLevel,
            'mood_index' => $moodIndex,
            'mood_emoji' => $moodEmoji,
            'temperature' => $data['temperature'] ?? null,
            'steps' => $data['steps'] ?? null,
            'calories_burned' => $data['calories_burned'] ?? null,
            'activity_level' => $data['activity_level'] ?? null,
            'acceleration_x' => $data['acceleration_x'] ?? null,
            'acceleration_y' => $data['acceleration_y'] ?? null,
            'acceleration_z' => $data['acceleration_z'] ?? null,
            'gyroscope_x' => $data['gyroscope_x'] ?? null,
            'gyroscope_y' => $data['gyroscope_y'] ?? null,
            'gyroscope_z' => $data['gyroscope_z'] ?? null,
            'watch_hand' => $data['watch_hand'] ?? null,
            'casts_count' => $data['casts_count'] ?? null,
            'reels_count' => $data['reels_count'] ?? null,
            'reels_meters' => $data['reels_meters'] ?? null,
            'recorded_at' => $data['recorded_at'] ?? now(),
        ]);
        
        // Update session statistics if session exists
        if ($sessionId) {
            $this->updateSessionStatistics($sessionId);
        }
        
        return $biometricData;
    }
    
    /**
     * Update fishing session statistics
     */
    public function updateSessionStatistics(int $sessionId): void
    {
        $session = FishingSession::find($sessionId);
        if (!$session) {
            return;
        }
        
        $biometricData = $session->biometricData()->get();
        
        if ($biometricData->isEmpty()) {
            return;
        }
        
        // Calculate averages and extremes
        $heartRates = $biometricData->whereNotNull('heart_rate')->pluck('heart_rate');
        $hrvValues = $biometricData->whereNotNull('hrv')->pluck('hrv');
        $stressLevels = $biometricData->whereNotNull('stress_level')->pluck('stress_level');
        $moodIndexes = $biometricData->whereNotNull('mood_index')->pluck('mood_index');
        
        $session->update([
            'avg_heart_rate' => $heartRates->isNotEmpty() ? round($heartRates->avg()) : null,
            'max_heart_rate' => $heartRates->isNotEmpty() ? $heartRates->max() : null,
            'min_heart_rate' => $heartRates->isNotEmpty() ? $heartRates->min() : null,
            'avg_hrv' => $hrvValues->isNotEmpty() ? round($hrvValues->avg(), 2) : null,
            'avg_stress_level' => $stressLevels->isNotEmpty() ? round($stressLevels->avg(), 2) : null,
            'avg_mood_index' => $moodIndexes->isNotEmpty() ? round($moodIndexes->avg(), 2) : null,
            'max_mood_index' => $moodIndexes->isNotEmpty() ? $moodIndexes->max() : null,
            'min_mood_index' => $moodIndexes->isNotEmpty() ? $moodIndexes->min() : null,
        ]);
        
        // Calculate time in different mood states
        $this->calculateMoodTimeDistribution($session, $biometricData);
        
        // Generate mood timeline
        $this->generateMoodTimeline($session, $biometricData);
    }
    
    /**
     * Calculate time distribution in different mood states
     */
    private function calculateMoodTimeDistribution(FishingSession $session, $biometricData): void
    {
        $highMoodTime = 0;
        $mediumMoodTime = 0;
        $lowMoodTime = 0;
        $stressedTime = 0;
        $calmTime = 0;
        
        $sortedData = $biometricData->sortBy('recorded_at');
        $previousTime = $session->started_at;
        
        foreach ($sortedData as $data) {
            $timeDiff = $previousTime->diffInMinutes($data->recorded_at);
            
            if ($data->mood_index !== null) {
                if ($data->mood_index >= 70) {
                    $highMoodTime += $timeDiff;
                } elseif ($data->mood_index >= 40) {
                    $mediumMoodTime += $timeDiff;
                } else {
                    $lowMoodTime += $timeDiff;
                }
            }
            
            if ($data->stress_level !== null) {
                if ($data->stress_level >= 60) {
                    $stressedTime += $timeDiff;
                } else {
                    $calmTime += $timeDiff;
                }
            }
            
            $previousTime = $data->recorded_at;
        }
        
        $session->update([
            'time_high_mood' => $highMoodTime,
            'time_medium_mood' => $mediumMoodTime,
            'time_low_mood' => $lowMoodTime,
            'time_stressed' => $stressedTime,
            'time_calm' => $calmTime,
        ]);
    }
    
    /**
     * Generate mood timeline for session
     */
    private function generateMoodTimeline(FishingSession $session, $biometricData): void
    {
        $timeline = $biometricData->map(function ($data) {
            return [
                'timestamp' => $data->recorded_at->toISOString(),
                'mood_index' => $data->mood_index,
                'mood_emoji' => $data->mood_emoji,
                'heart_rate' => $data->heart_rate,
                'stress_level' => $data->stress_level,
                'activity_level' => $data->activity_level,
            ];
        })->sortBy('timestamp')->values()->toArray();
        
        $session->update(['mood_timeline' => $timeline]);
    }
    
    /**
     * Get coach insights based on biometric data
     */
    public function getCoachInsights(FishingSession $session): array
    {
        $insights = [];
        
        // Heart rate insights
        if ($session->avg_heart_rate > 120) {
            $insights[] = "Ваш пульс повышен ({$session->avg_heart_rate} уд/мин). Сделайте паузу, глубоко вдохните. Рыба чувствует ваше волнение.";
        } elseif ($session->avg_heart_rate < 60) {
            $insights[] = "Отличные показатели пульса ({$session->avg_heart_rate} уд/мин). Вы находитесь в спокойном состоянии.";
        }
        
        // Mood insights
        if ($session->avg_mood_index >= 70) {
            $insights[] = "Отличные показатели настроения! Сейчас самое время попробовать более активную проводку.";
        } elseif ($session->avg_mood_index < 40) {
            $insights[] = "За последние 20 минут не было поклевок, а уровень стресса растет. Советую сменить точку ловли или приманку.";
        }
        
        // Stress insights
        if ($session->avg_stress_level > 70) {
            $insights[] = "Высокий уровень стресса ({$session->avg_stress_level}%). Попробуйте расслабиться и насладиться процессом.";
        }
        
        // Activity insights
        if ($session->casts_per_hour > 60) {
            $insights[] = "Вы очень активны ({$session->casts_per_hour} забросов/час). Попробуйте замедлиться и быть более терпеливым.";
        } elseif ($session->casts_per_hour < 10) {
            $insights[] = "Низкая активность ({$session->casts_per_hour} забросов/час). Попробуйте чаще менять приманки и точки ловли.";
        }
        
        // Catch correlation insights
        if ($session->catches_count > 0 && $session->avg_mood_index > 60) {
            $insights[] = "Отличная корреляция между уловами и настроением! Вы были счастливы {$session->mood_breakdown['high']}% времени.";
        }
        
        return $insights;
    }
    
    /**
     * Get biometric statistics for user
     */
    public function getUserBiometricStats(int $userId, int $days = 30): array
    {
        $sessions = FishingSession::where('user_id', $userId)
            ->where('started_at', '>=', now()->subDays($days))
            ->withBiometrics()
            ->get();
        
        if ($sessions->isEmpty()) {
            return [
                'total_sessions' => 0,
                'avg_mood_index' => 0,
                'avg_heart_rate' => 0,
                'avg_stress_level' => 0,
                'total_fishing_time' => 0,
                'mood_trend' => 'stable',
            ];
        }
        
        $totalSessions = $sessions->count();
        $avgMoodIndex = $sessions->whereNotNull('avg_mood_index')->avg('avg_mood_index') ?? 0;
        $avgHeartRate = $sessions->whereNotNull('avg_heart_rate')->avg('avg_heart_rate') ?? 0;
        $avgStressLevel = $sessions->whereNotNull('avg_stress_level')->avg('avg_stress_level') ?? 0;
        $totalFishingTime = $sessions->sum('duration');
        
        // Calculate mood trend
        $recentSessions = $sessions->take(5);
        $olderSessions = $sessions->skip(5)->take(5);
        
        $recentAvgMood = $recentSessions->whereNotNull('avg_mood_index')->avg('avg_mood_index') ?? 50;
        $olderAvgMood = $olderSessions->whereNotNull('avg_mood_index')->avg('avg_mood_index') ?? 50;
        
        $moodTrend = 'stable';
        if ($recentAvgMood > $olderAvgMood + 5) {
            $moodTrend = 'improving';
        } elseif ($recentAvgMood < $olderAvgMood - 5) {
            $moodTrend = 'declining';
        }
        
        return [
            'total_sessions' => $totalSessions,
            'avg_mood_index' => round($avgMoodIndex, 1),
            'avg_heart_rate' => round($avgHeartRate),
            'avg_stress_level' => round($avgStressLevel, 1),
            'total_fishing_time' => $totalFishingTime,
            'mood_trend' => $moodTrend,
            'best_session_mood' => $sessions->max('max_mood_index'),
            'worst_session_mood' => $sessions->min('min_mood_index'),
        ];
    }
}
