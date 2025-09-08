<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Point;
use App\Models\User;

class PointsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        
        $points = [
            [
                'user_id' => $users->random()->id,
                'name' => 'Озеро Светлое',
                'description' => 'Отличное место для ловли щуки и окуня. Глубина до 8 метров.',
                'latitude' => 55.7558,
                'longitude' => 37.6176,
                'type' => 'lake',
                'is_public' => true,
                'rating' => 4.5,
                'visits_count' => 25,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Река Москва - Строгино',
                'description' => 'Хорошее место для спиннинга. Много судака и берша.',
                'latitude' => 55.7908,
                'longitude' => 37.4028,
                'type' => 'river',
                'is_public' => true,
                'rating' => 4.2,
                'visits_count' => 18,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Пруд в Сокольниках',
                'description' => 'Семейная рыбалка. Карп, карась, плотва.',
                'latitude' => 55.7896,
                'longitude' => 37.6792,
                'type' => 'pond',
                'is_public' => true,
                'rating' => 3.8,
                'visits_count' => 32,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Волга - Дубна',
                'description' => 'Легендарное место для ловли сома. Ночная рыбалка.',
                'latitude' => 56.7333,
                'longitude' => 37.1667,
                'type' => 'river',
                'is_public' => true,
                'rating' => 4.8,
                'visits_count' => 12,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Озеро Бисерово',
                'description' => 'Платная рыбалка. Крупный карп и амур.',
                'latitude' => 55.7167,
                'longitude' => 38.1167,
                'type' => 'lake',
                'is_public' => true,
                'rating' => 4.3,
                'visits_count' => 28,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Канал им. Москвы',
                'description' => 'Судоходный канал. Осторожно с судами!',
                'latitude' => 55.8500,
                'longitude' => 37.4833,
                'type' => 'canal',
                'is_public' => true,
                'rating' => 3.5,
                'visits_count' => 15,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Рыбинское водохранилище',
                'description' => 'Большая вода. Судак, лещ, щука.',
                'latitude' => 58.0500,
                'longitude' => 38.8333,
                'type' => 'reservoir',
                'is_public' => true,
                'rating' => 4.6,
                'visits_count' => 8,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Озеро Плещеево',
                'description' => 'Историческое место. Ряпушка и окунь.',
                'latitude' => 56.7167,
                'longitude' => 38.8333,
                'type' => 'lake',
                'is_public' => true,
                'rating' => 4.4,
                'visits_count' => 20,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Река Ока - Серпухов',
                'description' => 'Красивые места. Жерех, голавль, язь.',
                'latitude' => 54.9167,
                'longitude' => 37.4167,
                'type' => 'river',
                'is_public' => true,
                'rating' => 4.1,
                'visits_count' => 22,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Секретное место',
                'description' => 'Только для друзей. Отличная щука!',
                'latitude' => 55.6500,
                'longitude' => 37.7500,
                'type' => 'lake',
                'is_public' => false,
                'rating' => 5.0,
                'visits_count' => 5,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Пруд в Битце',
                'description' => 'Городская рыбалка. Карась и ротан.',
                'latitude' => 55.6000,
                'longitude' => 37.5500,
                'type' => 'pond',
                'is_public' => true,
                'rating' => 3.2,
                'visits_count' => 35,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Истринское водохранилище',
                'description' => 'Большое водохранилище. Разнообразная рыба.',
                'latitude' => 55.9167,
                'longitude' => 36.8667,
                'type' => 'reservoir',
                'is_public' => true,
                'rating' => 4.0,
                'visits_count' => 16,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Река Клязьма',
                'description' => 'Малая река. Плотва, окунь, щука.',
                'latitude' => 55.7500,
                'longitude' => 37.6000,
                'type' => 'river',
                'is_public' => true,
                'rating' => 3.7,
                'visits_count' => 24,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Озеро Белое',
                'description' => 'Кристально чистая вода. Форель и хариус.',
                'latitude' => 55.8000,
                'longitude' => 37.9000,
                'type' => 'lake',
                'is_public' => true,
                'rating' => 4.7,
                'visits_count' => 14,
            ],
            [
                'user_id' => $users->random()->id,
                'name' => 'Пруд в Кузьминках',
                'description' => 'Парковая зона. Семейный отдых.',
                'latitude' => 55.6833,
                'longitude' => 37.7833,
                'type' => 'pond',
                'is_public' => true,
                'rating' => 3.4,
                'visits_count' => 40,
            ],
        ];

        foreach ($points as $pointData) {
            Point::create($pointData);
        }
    }
}

