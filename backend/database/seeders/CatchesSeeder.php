<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatchRecord;
use App\Models\User;
use App\Models\Point;
use Carbon\Carbon;

class CatchesSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $points = Point::all();
        
        $fishTypes = [
            'Щука', 'Окунь', 'Судак', 'Лещ', 'Карп', 'Карась', 'Плотва', 
            'Голавль', 'Язь', 'Жерех', 'Сом', 'Налим', 'Форель', 'Хариус',
            'Берш', 'Ротан', 'Амур', 'Толстолобик', 'Линь', 'Красноперка'
        ];
        
        $baits = [
            'Воблер', 'Блесна', 'Джиг', 'Поппер', 'Вертушка', 'Червяк', 
            'Опарыш', 'Мотыль', 'Кукуруза', 'Тесто', 'Бойл', 'Мясо',
            'Малька', 'Лягушка', 'Мышь', 'Силикон', 'Поролон'
        ];
        
        $weather = ['sunny', 'cloudy', 'rainy', 'windy', 'foggy'];
        
        $catches = [];
        
        // Создаем 50 уловов
        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $point = $points->random();
            $fishType = $fishTypes[array_rand($fishTypes)];
            $bait = $baits[array_rand($baits)];
            $weight = rand(50, 5000) / 100; // от 0.5 до 50 кг
            $length = rand(15, 120); // от 15 до 120 см
            $caughtAt = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23));
            
            $catches[] = [
                'user_id' => $user->id,
                'point_id' => $point->id,
                'fish_type' => $fishType,
                'weight' => $weight,
                'length' => $length,
                'bait' => $bait,
                'weather' => $weather[array_rand($weather)],
                'temperature' => rand(-10, 35),
                'description' => $this->generateDescription($fishType, $weight, $bait),
                'caught_at' => $caughtAt,
                'is_public' => rand(0, 1),
                'likes_count' => rand(0, 25),
                'comments_count' => rand(0, 10),
                'created_at' => $caughtAt,
                'updated_at' => $caughtAt,
            ];
        }
        
        // Создаем несколько особо крупных уловов
        $bigCatches = [
            [
                'user_id' => $users->where('username', 'sergey_pike')->first()->id,
                'point_id' => $points->where('name', 'Волга - Дубна')->first()->id,
                'fish_type' => 'Щука',
                'weight' => 12.5,
                'length' => 95,
                'bait' => 'Воблер',
                'weather' => 'cloudy',
                'temperature' => 15,
                'description' => 'Рекордная щука! Поклевка была мощная, вываживание заняло 20 минут. Рыба оказалась самкой с икрой, отпустил обратно.',
                'caught_at' => Carbon::now()->subDays(5),
                'is_public' => true,
                'likes_count' => 45,
                'comments_count' => 12,
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $users->where('username', 'ivan_catfish')->first()->id,
                'point_id' => $points->where('name', 'Волга - Дубна')->first()->id,
                'fish_type' => 'Сом',
                'weight' => 28.3,
                'length' => 120,
                'bait' => 'Мясо',
                'weather' => 'rainy',
                'temperature' => 18,
                'description' => 'Ночная рыбалка на сома. Поклевка в 2:30 утра. Борьба длилась почти час! Сом оказался настоящим монстром.',
                'caught_at' => Carbon::now()->subDays(3),
                'is_public' => true,
                'likes_count' => 67,
                'comments_count' => 18,
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $users->where('username', 'anna_carp')->first()->id,
                'point_id' => $points->where('name', 'Озеро Бисерово')->first()->id,
                'fish_type' => 'Карп',
                'weight' => 15.7,
                'length' => 78,
                'bait' => 'Бойл',
                'weather' => 'sunny',
                'temperature' => 22,
                'description' => 'Красивый зеркальный карп! Поклевка была неожиданной, карп взял насадку очень аккуратно.',
                'caught_at' => Carbon::now()->subDays(1),
                'is_public' => true,
                'likes_count' => 32,
                'comments_count' => 8,
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
        ];
        
        $allCatches = array_merge($catches, $bigCatches);
        
        foreach ($allCatches as $catchData) {
            CatchRecord::create($catchData);
        }
    }
    
    private function generateDescription($fishType, $weight, $bait): string
    {
        $descriptions = [
            "Отличная поклевка на {$bait}! Рыба сопротивлялась активно.",
            "Красивая {$fishType} весом {$weight} кг. Поклевка была резкой.",
            "Долго ждал, но результат того стоил! {$fishType} на {$bait}.",
            "Утренняя рыбалка удалась. {$fishType} взяла на {$bait}.",
            "Вечерняя поклевка! {$fishType} весом {$weight} кг.",
            "Случайная поклевка, но какая рыба! {$fishType} на {$bait}.",
            "Первый заброс и сразу поклевка! {$fishType} не заставила ждать.",
            "Терпение и настойчивость дали результат. {$fishType} на {$bait}.",
        ];
        
        return $descriptions[array_rand($descriptions)];
    }
}

