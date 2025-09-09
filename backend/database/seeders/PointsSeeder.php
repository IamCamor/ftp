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
                'title' => 'Озеро Светлое',
                'description' => 'Отличное место для ловли щуки и окуня. Глубина до 8 метров.',
                'lat' => 55.7558,
                'lng' => 37.6176,
                'privacy' => 'all',
            ],
            [
                'user_id' => $users->random()->id,
                'title' => 'Река Москва - Строгино',
                'description' => 'Хорошее место для спиннинга. Много судака и берша.',
                'lat' => 55.7944,
                'lng' => 37.4000,
                'privacy' => 'all',
            ],
            [
                'user_id' => $users->random()->id,
                'title' => 'Пруд в парке',
                'description' => 'Тихий пруд для семейной рыбалки. Есть карась и карп.',
                'lat' => 55.7500,
                'lng' => 37.6000,
                'privacy' => 'friends',
            ],
        ];

        foreach ($points as $pointData) {
            Point::create($pointData);
        }
    }
}