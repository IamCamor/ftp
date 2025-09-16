<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Track;
use App\Models\CatchRecord;
use App\Models\User;
use Carbon\Carbon;

class TrackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->info('No users found. Please run UserSeeder first.');
            return;
        }

        // Создаем треки для каждого пользователя
        foreach ($users as $user) {
            $this->createTracksForUser($user);
        }
    }

    private function createTracksForUser(User $user): void
    {
        // Трек 1: Ранняя утренняя рыбалка на Волге
        $track1 = Track::create([
            'user_id' => $user->id,
            'title' => 'Рассвет на Волге - Спиннинговая охота',
            'description' => 'Раннее утро, туман над водой, активная щука. Идеальные условия для спиннинга.',
            'started_at' => Carbon::now()->subDays(2)->setTime(5, 30),
            'ended_at' => Carbon::now()->subDays(2)->setTime(9, 15),
            'status' => 'completed',
            'smartwatch_data' => [
                'heart_rate' => [72, 75, 78, 82, 85, 88, 85, 82, 78, 75],
                'steps' => 3200,
                'calories' => 520,
                'sleep_quality' => 'excellent',
                'weather_conditions' => 'foggy_morning'
            ],
            'track_points' => $this->generateTrackPoints(55.7558, 37.6176, 4, 'river'),
            'total_distance' => 3.2,
            'total_catches' => 0,
            'total_weight' => 0
        ]);

        // Создаем уловы для первого трека
        $this->createCatchesForTrack($track1, [
            [
                'species' => 'Щука', 'weight' => 3.2, 'length' => 52, 'style' => 'спиннинг', 
                'lure' => 'воблер Rapala', 'tackle' => 'Shimano Stradic',
                'image_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=400&h=300&fit=crop',
                'weather' => ['temperature' => 8.5, 'pressure' => 765.2, 'wind_speed' => 3.1, 'cloudiness' => 'туман', 'precipitation' => 'без осадков', 'wind_direction' => 'СВ']
            ],
            [
                'species' => 'Окунь', 'weight' => 1.1, 'length' => 32, 'style' => 'спиннинг', 
                'lure' => 'джиг-головка', 'tackle' => 'Shimano Stradic',
                'image_url' => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=300&fit=crop',
                'weather' => ['temperature' => 9.2, 'pressure' => 765.8, 'wind_speed' => 2.8, 'cloudiness' => 'туман', 'precipitation' => 'без осадков', 'wind_direction' => 'СВ']
            ],
            [
                'species' => 'Судак', 'weight' => 2.8, 'length' => 48, 'style' => 'спиннинг', 
                'lure' => 'воблер Salmo', 'tackle' => 'Shimano Stradic',
                'image_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=300&fit=crop',
                'weather' => ['temperature' => 10.1, 'pressure' => 766.5, 'wind_speed' => 4.2, 'cloudiness' => 'малооблачно', 'precipitation' => 'без осадков', 'wind_direction' => 'В']
            ]
        ]);

        // Трек 2: Вечерняя рыбалка на озере
        $track2 = Track::create([
            'user_id' => $user->id,
            'title' => 'Тихий вечер на озере Селигер',
            'description' => 'Спокойная вода, поплавочная удочка, мирная рыба. Идеальный вечер для медитации.',
            'started_at' => Carbon::now()->subDays(3)->setTime(18, 0),
            'ended_at' => Carbon::now()->subDays(3)->setTime(22, 30),
            'status' => 'completed',
            'smartwatch_data' => [
                'heart_rate' => [68, 70, 72, 75, 73, 71, 69, 67],
                'steps' => 2100,
                'calories' => 380,
                'sleep_quality' => 'excellent',
                'weather_conditions' => 'calm_evening'
            ],
            'track_points' => $this->generateTrackPoints(57.2167, 33.0667, 4.5, 'lake'),
            'total_distance' => 2.1,
            'total_catches' => 0,
            'total_weight' => 0
        ]);

        // Создаем уловы для второго трека
        $this->createCatchesForTrack($track2, [
            [
                'species' => 'Карп', 'weight' => 4.8, 'length' => 62, 'style' => 'поплавок', 
                'lure' => 'кукуруза + опарыш', 'tackle' => 'Daiwa Crossfire',
                'image_url' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=400&h=300&fit=crop',
                'weather' => ['temperature' => 22.5, 'pressure' => 758.3, 'wind_speed' => 1.5, 'cloudiness' => 'ясно', 'precipitation' => 'без осадков', 'wind_direction' => 'ЮЗ']
            ],
            [
                'species' => 'Лещ', 'weight' => 2.1, 'length' => 45, 'style' => 'поплавок', 
                'lure' => 'кукуруза + опарыш', 'tackle' => 'Daiwa Crossfire',
                'image_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=300&fit=crop',
                'weather' => ['temperature' => 21.8, 'pressure' => 758.1, 'wind_speed' => 2.1, 'cloudiness' => 'ясно', 'precipitation' => 'без осадков', 'wind_direction' => 'ЮЗ']
            ],
            [
                'species' => 'Плотва', 'weight' => 0.4, 'length' => 22, 'style' => 'поплавок', 
                'lure' => 'опарыш', 'tackle' => 'Daiwa Crossfire',
                'image_url' => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=300&fit=crop',
                'weather' => ['temperature' => 20.9, 'pressure' => 757.8, 'wind_speed' => 1.8, 'cloudiness' => 'ясно', 'precipitation' => 'без осадков', 'wind_direction' => 'ЮЗ']
            ],
            [
                'species' => 'Карась', 'weight' => 0.7, 'length' => 28, 'style' => 'поплавок', 
                'lure' => 'червь', 'tackle' => 'Daiwa Crossfire',
                'image_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=400&h=300&fit=crop',
                'weather' => ['temperature' => 19.5, 'pressure' => 757.5, 'wind_speed' => 2.3, 'cloudiness' => 'малооблачно', 'precipitation' => 'без осадков', 'wind_direction' => 'З']
            ]
        ]);

        // Трек 3: Дождливая рыбалка на реке
        $track3 = Track::create([
            'user_id' => $user->id,
            'title' => 'Дождливая рыбалка на Оке',
            'description' => 'Несмотря на дождь, рыба активна. Джиг-спиннинг показывает отличные результаты.',
            'started_at' => Carbon::now()->subDays(1)->setTime(14, 0),
            'ended_at' => Carbon::now()->subDays(1)->setTime(18, 45),
            'status' => 'completed',
            'smartwatch_data' => [
                'heart_rate' => [75, 78, 82, 85, 88, 85, 82, 79, 76],
                'steps' => 2800,
                'calories' => 480,
                'sleep_quality' => 'good',
                'weather_conditions' => 'rainy'
            ],
            'track_points' => $this->generateTrackPoints(54.8000, 37.6000, 5, 'river'),
            'total_distance' => 4.1,
            'total_catches' => 0,
            'total_weight' => 0
        ]);

        // Создаем уловы для третьего трека
        $this->createCatchesForTrack($track3, [
            [
                'species' => 'Щука', 'weight' => 5.2, 'length' => 68, 'style' => 'джиг-спиннинг', 
                'lure' => 'силиконовая приманка', 'tackle' => 'Mikado Ultralight',
                'image_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=400&h=300&fit=crop',
                'weather' => ['temperature' => 16.5, 'pressure' => 752.8, 'wind_speed' => 6.2, 'cloudiness' => 'облачно', 'precipitation' => 'дождь', 'wind_direction' => 'ЮВ']
            ],
            [
                'species' => 'Окунь', 'weight' => 1.5, 'length' => 35, 'style' => 'джиг-спиннинг', 
                'lure' => 'силиконовая приманка', 'tackle' => 'Mikado Ultralight',
                'image_url' => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=300&fit=crop',
                'weather' => ['temperature' => 15.8, 'pressure' => 752.5, 'wind_speed' => 7.1, 'cloudiness' => 'облачно', 'precipitation' => 'дождь', 'wind_direction' => 'ЮВ']
            ],
            [
                'species' => 'Судак', 'weight' => 3.1, 'length' => 52, 'style' => 'джиг-спиннинг', 
                'lure' => 'силиконовая приманка', 'tackle' => 'Mikado Ultralight',
                'image_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=300&fit=crop',
                'weather' => ['temperature' => 15.2, 'pressure' => 752.1, 'wind_speed' => 8.5, 'cloudiness' => 'облачно', 'precipitation' => 'сильный дождь', 'wind_direction' => 'ЮВ']
            ]
        ]);

        // Трек 4: Активная рыбалка (текущая)
        $track4 = Track::create([
            'user_id' => $user->id,
            'title' => 'Текущая сессия - Смешанная ловля',
            'description' => 'Долгая сессия, разные методы ловли. Пробуем новые приманки.',
            'started_at' => Carbon::now()->subHours(3),
            'ended_at' => null,
            'status' => 'active',
            'smartwatch_data' => [
                'heart_rate' => [78, 80, 85, 88, 90, 87, 84, 81, 79],
                'steps' => 4200,
                'calories' => 680,
                'sleep_quality' => 'good',
                'weather_conditions' => 'mixed'
            ],
            'track_points' => $this->generateTrackPoints(55.7500, 37.6200, 3, 'mixed'),
            'total_distance' => 2.8,
            'total_catches' => 0,
            'total_weight' => 0
        ]);

        // Создаем уловы для четвертого трека
        $this->createCatchesForTrack($track4, [
            [
                'species' => 'Щука', 'weight' => 4.5, 'length' => 58, 'style' => 'спиннинг', 
                'lure' => 'воблер Lucky Craft', 'tackle' => 'Shimano Exsence',
                'image_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=400&h=300&fit=crop',
                'weather' => ['temperature' => 18.5, 'pressure' => 760.2, 'wind_speed' => 4.8, 'cloudiness' => 'малооблачно', 'precipitation' => 'без осадков', 'wind_direction' => 'СЗ']
            ],
            [
                'species' => 'Окунь', 'weight' => 0.9, 'length' => 30, 'style' => 'спиннинг', 
                'lure' => 'воблер Lucky Craft', 'tackle' => 'Shimano Exsence',
                'image_url' => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=300&fit=crop',
                'weather' => ['temperature' => 19.1, 'pressure' => 760.5, 'wind_speed' => 5.2, 'cloudiness' => 'малооблачно', 'precipitation' => 'без осадков', 'wind_direction' => 'СЗ']
            ]
        ]);

        // Трек 5: Зимняя рыбалка (завершенная)
        $track5 = Track::create([
            'user_id' => $user->id,
            'title' => 'Зимняя рыбалка на льду',
            'description' => 'Морозное утро, лунки во льду, мормышка. Классическая зимняя рыбалка.',
            'started_at' => Carbon::now()->subDays(7)->setTime(7, 0),
            'ended_at' => Carbon::now()->subDays(7)->setTime(12, 30),
            'status' => 'completed',
            'smartwatch_data' => [
                'heart_rate' => [65, 68, 72, 75, 78, 76, 73, 70, 67],
                'steps' => 1500,
                'calories' => 320,
                'sleep_quality' => 'excellent',
                'weather_conditions' => 'winter_ice'
            ],
            'track_points' => $this->generateTrackPoints(55.8000, 37.7000, 5.5, 'ice'),
            'total_distance' => 1.5,
            'total_catches' => 0,
            'total_weight' => 0
        ]);

        // Создаем уловы для пятого трека
        $this->createCatchesForTrack($track5, [
            [
                'species' => 'Окунь', 'weight' => 0.6, 'length' => 25, 'style' => 'мормышка', 
                'lure' => 'чертик', 'tackle' => 'Зимняя удочка',
                'image_url' => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=300&fit=crop',
                'weather' => ['temperature' => -8.5, 'pressure' => 770.2, 'wind_speed' => 2.1, 'cloudiness' => 'ясно', 'precipitation' => 'без осадков', 'wind_direction' => 'С']
            ],
            [
                'species' => 'Плотва', 'weight' => 0.3, 'length' => 20, 'style' => 'мормышка', 
                'lure' => 'чертик', 'tackle' => 'Зимняя удочка',
                'image_url' => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=300&fit=crop',
                'weather' => ['temperature' => -7.8, 'pressure' => 770.5, 'wind_speed' => 1.8, 'cloudiness' => 'ясно', 'precipitation' => 'без осадков', 'wind_direction' => 'С']
            ],
            [
                'species' => 'Карась', 'weight' => 0.8, 'length' => 26, 'style' => 'мормышка', 
                'lure' => 'чертик', 'tackle' => 'Зимняя удочка',
                'image_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=400&h=300&fit=crop',
                'weather' => ['temperature' => -6.2, 'pressure' => 770.8, 'wind_speed' => 2.5, 'cloudiness' => 'ясно', 'precipitation' => 'без осадков', 'wind_direction' => 'С']
            ]
        ]);

        // Обновляем статистику треков
        $track1->updateStats();
        $track2->updateStats();
        $track3->updateStats();
        $track4->updateStats();
        $track5->updateStats();
    }

    private function createCatchesForTrack(Track $track, array $catchesData): void
    {
        $trackPoints = $track->track_points ?? [];
        $lastCatchTime = $track->started_at;

        foreach ($catchesData as $index => $catchData) {
            // Выбираем случайную точку из трека
            $point = $trackPoints[array_rand($trackPoints)];
            
            // Добавляем время к последнему улову
            $lastCatchTime = $lastCatchTime->addMinutes(rand(15, 45));

            // Получаем погодные данные из массива или генерируем случайные
            $weather = $catchData['weather'] ?? [
                'temperature' => rand(15, 25) + (rand(0, 99) / 100),
                'pressure' => rand(750, 770) + (rand(0, 99) / 100),
                'wind_speed' => rand(2, 8) + (rand(0, 99) / 100),
                'cloudiness' => ['ясно', 'малооблачно', 'облачно'][rand(0, 2)],
                'precipitation' => ['без осадков', 'дождь'][rand(0, 1)],
                'wind_direction' => ['С', 'СВ', 'В', 'ЮВ', 'Ю', 'ЮЗ', 'З', 'СЗ'][rand(0, 7)]
            ];

            CatchRecord::create([
                'user_id' => $track->user_id,
                'track_id' => $track->id,
                'lat' => $point['lat'] + (rand(-100, 100) / 10000), // Небольшое отклонение
                'lng' => $point['lng'] + (rand(-100, 100) / 10000),
                'species' => $catchData['species'],
                'weight' => $catchData['weight'],
                'length' => $catchData['length'],
                'style' => $catchData['style'],
                'lure' => $catchData['lure'],
                'tackle' => $catchData['tackle'],
                'notes' => "Улов #" . ($index + 1) . " в треке",
                'caught_at' => $lastCatchTime,
                'privacy' => 'all',
                // Погодные данные
                'temperature' => $weather['temperature'],
                'pressure' => $weather['pressure'],
                'wind_speed' => $weather['wind_speed'],
                'cloudiness' => $weather['cloudiness'],
                'precipitation' => $weather['precipitation'],
                'wind_direction' => $weather['wind_direction'],
                // Добавляем URL картинки если есть
                'image_url' => $catchData['image_url'] ?? null
            ]);
        }
    }

    private function generateTrackPoints(float $startLat, float $startLng, int $hours, string $type = 'river'): array
    {
        $points = [];
        $currentTime = Carbon::now()->subHours($hours);
        
        // Настройки для разных типов водоемов
        $movementPatterns = [
            'river' => ['max_deviation' => 800, 'movement_frequency' => 0.7],
            'lake' => ['max_deviation' => 300, 'movement_frequency' => 0.3],
            'ice' => ['max_deviation' => 100, 'movement_frequency' => 0.1],
            'mixed' => ['max_deviation' => 600, 'movement_frequency' => 0.5]
        ];
        
        $pattern = $movementPatterns[$type] ?? $movementPatterns['river'];
        $currentLat = $startLat;
        $currentLng = $startLng;
        
        // Генерируем точки каждые 5 минут
        for ($i = 0; $i < $hours * 12; $i++) {
            // Движение зависит от типа водоема
            if (rand(1, 100) <= ($pattern['movement_frequency'] * 100)) {
                $currentLat += (rand(-$pattern['max_deviation'], $pattern['max_deviation']) / 100000);
                $currentLng += (rand(-$pattern['max_deviation'], $pattern['max_deviation']) / 100000);
            }
            
            $points[] = [
                'lat' => $currentLat,
                'lng' => $currentLng,
                'timestamp' => $currentTime->toISOString(),
                'smartwatch_data' => [
                    'heart_rate' => rand(65, 95),
                    'steps' => rand(30, 250),
                    'calories' => rand(3, 30),
                    'water_temperature' => $this->getWaterTemperature($type, $currentTime),
                    'air_humidity' => rand(40, 90)
                ]
            ];
            $currentTime->addMinutes(5);
        }
        
        return $points;
    }

    private function getWaterTemperature(string $type, Carbon $time): float
    {
        $baseTemps = [
            'river' => 12.0,
            'lake' => 15.0,
            'ice' => 0.5,
            'mixed' => 13.5
        ];
        
        $baseTemp = $baseTemps[$type] ?? 12.0;
        $hour = $time->hour;
        
        // Температура воды меняется в зависимости от времени суток
        $timeVariation = sin(($hour - 6) * M_PI / 12) * 2; // ±2°C в течение дня
        
        return $baseTemp + $timeVariation + (rand(-50, 50) / 100); // ±0.5°C случайное отклонение
    }
}