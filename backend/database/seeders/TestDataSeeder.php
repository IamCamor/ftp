<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CatchRecord;
use App\Models\Track;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Создание тестовых данных...');

        // Получаем пользователей
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->error('Нет пользователей в базе данных!');
            return;
        }

        // Создаем тестовые уловы с картинками и координатами
        $this->createTestCatches($users);
        
        // Создаем тестовые треки
        $this->createTestTracks($users);

        $this->command->info('Тестовые данные созданы успешно!');
    }

    private function createTestCatches($users)
    {
        $this->command->info('Создание тестовых уловов...');

        // Координаты популярных рыбных мест в России
        $fishingSpots = [
            ['name' => 'Волга у Астрахани', 'lat' => 46.3497, 'lng' => 48.0408],
            ['name' => 'Ока у Рязани', 'lat' => 54.6253, 'lng' => 39.7400],
            ['name' => 'Дон у Ростова', 'lat' => 47.2225, 'lng' => 39.7186],
            ['name' => 'Нева у Санкт-Петербурга', 'lat' => 59.9311, 'lng' => 30.3609],
            ['name' => 'Москва-река у Москвы', 'lat' => 55.7558, 'lng' => 37.6176],
            ['name' => 'Ангара у Иркутска', 'lat' => 52.2978, 'lng' => 104.2964],
            ['name' => 'Енисей у Красноярска', 'lat' => 56.0184, 'lng' => 92.8672],
            ['name' => 'Обь у Новосибирска', 'lat' => 55.0084, 'lng' => 82.9357],
            ['name' => 'Лена у Якутска', 'lat' => 62.0339, 'lng' => 129.7331],
            ['name' => 'Амур у Хабаровска', 'lat' => 48.4827, 'lng' => 135.0840],
        ];

        // Виды рыб
        $fishTypes = [
            'Щука', 'Окунь', 'Лещ', 'Плотва', 'Карась', 'Карп', 'Сазан', 'Сом', 'Судак', 'Жерех',
            'Голавль', 'Язь', 'Линь', 'Красноперка', 'Уклейка', 'Ерш', 'Пескарь', 'Бычок', 'Ротан', 'Толстолобик'
        ];

        // Наживки и снасти
        $baits = [
            'Червь', 'Мотыль', 'Опарыш', 'Кукуруза', 'Хлеб', 'Тесто', 'Бойл', 'Воблер', 'Блесна', 'Джиг'
        ];

        $tackles = [
            'Поплавочная удочка', 'Спиннинг', 'Донка', 'Фидер', 'Нахлыст', 'Зимняя удочка', 'Кружки', 'Жерлицы'
        ];

        // Погодные условия
        $weatherConditions = [
            'Ясно', 'Облачно', 'Пасмурно', 'Дождь', 'Снег', 'Туман', 'Ветер', 'Штиль'
        ];

        // Создаем 50 тестовых уловов
        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $spot = $fishingSpots[array_rand($fishingSpots)];
            $fishType = $fishTypes[array_rand($fishTypes)];
            $bait = $baits[array_rand($baits)];
            $tackle = $tackles[array_rand($tackles)];
            $weather = $weatherConditions[array_rand($weatherConditions)];

            // Генерируем случайные координаты в радиусе 5 км от точки
            $lat = $spot['lat'] + (rand(-5000, 5000) / 100000);
            $lng = $spot['lng'] + (rand(-5000, 5000) / 100000);

            // Генерируем случайные параметры рыбы
            $weight = rand(50, 5000) / 100; // 0.5 - 50 кг
            $length = rand(15, 120); // 15 - 120 см

            // Генерируем случайную дату в последние 6 месяцев
            $caughtAt = Carbon::now()->subDays(rand(0, 180))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            // Создаем URL для тестового изображения
            $photoUrl = $this->generateTestImageUrl($fishType, $i);

            CatchRecord::create([
                'user_id' => $user->id,
                'species' => $fishType,
                'weight' => $weight,
                'length' => $length,
                'lat' => $lat,
                'lng' => $lng,
                'caught_at' => $caughtAt,
                'water_temp' => rand(5, 25) + (rand(0, 99) / 100),
                'temperature' => rand(-5, 35) + (rand(0, 99) / 100),
                'wind_speed' => rand(0, 15) + (rand(0, 99) / 100),
                'wind_direction' => ['С', 'Ю', 'В', 'З', 'СВ', 'СЗ', 'ЮВ', 'ЮЗ'][array_rand(['С', 'Ю', 'В', 'З', 'СВ', 'СЗ', 'ЮВ', 'ЮЗ'])],
                'pressure' => rand(750, 780) + (rand(0, 99) / 100),
                'cloudiness' => rand(0, 100),
                'precipitation' => rand(0, 50),
                'tackle' => $tackle,
                'lure' => $bait,
                'depth' => rand(50, 500) / 100, // 0.5 - 5 м
                'notes' => $this->generateCatchNotes($fishType, $weight, $length, $bait, $tackle) . " Место: " . $spot['name'],
                'photo_url' => $photoUrl,
                'image_url' => $photoUrl,
                'privacy' => 'all',
                'water_type' => ['река', 'озеро', 'пруд', 'море'][array_rand(['река', 'озеро', 'пруд', 'море'])],
                'companions' => rand(0, 3),
                'created_at' => $caughtAt,
                'updated_at' => $caughtAt,
            ]);
        }

        $this->command->info('Создано 50 тестовых уловов');
    }

    private function createTestTracks($users)
    {
        $this->command->info('Создание тестовых треков...');

        // Координаты для треков (маршруты по рекам)
        $trackRoutes = [
            [
                'title' => 'Рыбалка на Волге',
                'description' => 'Утренняя рыбалка на Волге у Астрахани',
                'points' => [
                    ['lat' => 46.3497, 'lng' => 48.0408],
                    ['lat' => 46.3500, 'lng' => 48.0410],
                    ['lat' => 46.3505, 'lng' => 48.0415],
                    ['lat' => 46.3510, 'lng' => 48.0420],
                    ['lat' => 46.3515, 'lng' => 48.0425],
                ]
            ],
            [
                'title' => 'Прогулка по Оке',
                'description' => 'Рыбалка с лодки на Оке',
                'points' => [
                    ['lat' => 54.6253, 'lng' => 39.7400],
                    ['lat' => 54.6260, 'lng' => 39.7410],
                    ['lat' => 54.6270, 'lng' => 39.7420],
                    ['lat' => 54.6280, 'lng' => 39.7430],
                ]
            ],
            [
                'title' => 'Зимняя рыбалка на Дону',
                'description' => 'Подледная рыбалка на Дону',
                'points' => [
                    ['lat' => 47.2225, 'lng' => 39.7186],
                    ['lat' => 47.2230, 'lng' => 39.7190],
                    ['lat' => 47.2235, 'lng' => 39.7195],
                ]
            ]
        ];

        foreach ($trackRoutes as $index => $route) {
            $user = $users->random();
            $startedAt = Carbon::now()->subDays(rand(1, 30))->subHours(rand(6, 18));
            $duration = rand(60, 480); // 1-8 часов
            $endedAt = $startedAt->copy()->addMinutes($duration);

            // Вычисляем общее расстояние
            $totalDistance = $this->calculateTrackDistance($route['points']);

            Track::create([
                'user_id' => $user->id,
                'title' => $route['title'],
                'description' => $route['description'],
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'status' => 'completed',
                'track_points' => $route['points'],
                'total_distance' => $totalDistance,
                'total_catches' => rand(0, 8),
                'total_weight' => rand(500, 5000) / 100, // 5-50 кг
                'created_at' => $startedAt,
                'updated_at' => $endedAt,
            ]);
        }

        $this->command->info('Создано ' . count($trackRoutes) . ' тестовых треков');
    }

    private function generateTestImageUrl($fishType, $index)
    {
        // Проверяем, есть ли локальные изображения
        $localImagePath = public_path("images/fish/{$fishType}_{$index}.jpg");
        if (file_exists($localImagePath)) {
            return "/images/fish/{$fishType}_{$index}.jpg";
        }
        
        // Если локального изображения нет, используем Lorem Picsum
        $fishNames = [
            'Щука' => 'pike',
            'Окунь' => 'perch',
            'Лещ' => 'bream',
            'Плотва' => 'roach',
            'Карась' => 'crucian',
            'Карп' => 'carp',
            'Сазан' => 'carp',
            'Сом' => 'catfish',
            'Судак' => 'pike-perch',
            'Жерех' => 'asp'
        ];

        $fishName = $fishNames[$fishType] ?? 'fish';
        
        // Используем Lorem Picsum для генерации случайных изображений рыб
        return "https://picsum.photos/800/600?random={$index}&text={$fishType}";
    }

    private function generateCatchNotes($fishType, $weight, $length, $bait, $tackle)
    {
        $notes = [
            "Отличная рыбалка! Поймал {$fishType} на {$bait}.",
            "Красивый экземпляр {$fishType} весом {$weight} кг.",
            "Рыба клевала активно на {$tackle}.",
            "Длина рыбы {$length} см, хороший размер!",
            "Поймал на {$bait}, рыба брала уверенно.",
            "Отличный улов! {$fishType} весом {$weight} кг.",
            "Рыба клевала с утра, поймал на {$tackle}.",
            "Красивый экземпляр, длина {$length} см.",
        ];

        return $notes[array_rand($notes)];
    }

    private function calculateTrackDistance($points)
    {
        $totalDistance = 0;
        for ($i = 0; $i < count($points) - 1; $i++) {
            $distance = $this->haversineDistance(
                $points[$i]['lat'], $points[$i]['lng'],
                $points[$i + 1]['lat'], $points[$i + 1]['lng']
            );
            $totalDistance += $distance;
        }
        return round($totalDistance, 2);
    }

    private function haversineDistance($lat1, $lng1, $lat2, $lng2)
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
}
