<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatchRecord;
use App\Models\User;
use App\Models\Point;

class CatchesSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $points = Point::all();
        
        $catches = [
            [
                'user_id' => $users->random()->id,
                'lat' => 55.7558,
                'lng' => 37.6176,
                'species' => 'Щука',
                'weight' => 2.5,
                'length' => 45,
                'style' => 'Спиннинг',
                'lure' => 'Воблер',
                'tackle' => 'Плетенка 0.2',
                'notes' => 'Отличная поклевка на рассвете!',
                'photo_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=400&h=300&fit=crop',
                'privacy' => 'all',
                'caught_at' => \Carbon\Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $users->random()->id,
                'lat' => 55.7944,
                'lng' => 37.4000,
                'species' => 'Судак',
                'weight' => 1.8,
                'length' => 38,
                'style' => 'Спиннинг',
                'lure' => 'Джиг',
                'tackle' => 'Плетенка 0.15',
                'notes' => 'Поймал на джиг-головку 12г',
                'photo_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=400&h=300&fit=crop',
                'privacy' => 'all',
                'caught_at' => \Carbon\Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $users->random()->id,
                'lat' => 55.7500,
                'lng' => 37.6000,
                'species' => 'Карась',
                'weight' => 0.8,
                'length' => 25,
                'style' => 'Поплавочная удочка',
                'lure' => 'Червь',
                'tackle' => 'Леска 0.2',
                'notes' => 'Хороший клев на червя',
                'photo_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=400&h=300&fit=crop',
                'privacy' => 'friends',
                'caught_at' => \Carbon\Carbon::now()->subDays(3),
            ],
        ];

        foreach ($catches as $catchData) {
            CatchRecord::create($catchData);
        }
    }
}