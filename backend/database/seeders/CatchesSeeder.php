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
                'notes' => 'Отличная поклевка на рассвете! Трофейная щука на 2.5кг!',
                'photo_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop',
                'additional_photos' => json_encode([
                    'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop'
                ]),
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
                'notes' => 'Поймал на джиг-головку 12г. Красивый судак!',
                'photo_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop',
                'additional_photos' => json_encode([
                    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop'
                ]),
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
                'notes' => 'Хороший клев на червя. Первый карась сезона!',
                'photo_url' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop',
                'additional_photos' => json_encode([
                    'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop'
                ]),
                'privacy' => 'friends',
                'caught_at' => \Carbon\Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $users->random()->id,
                'lat' => 55.8000,
                'lng' => 37.5000,
                'species' => 'Окунь',
                'weight' => 0.6,
                'length' => 22,
                'style' => 'Спиннинг',
                'lure' => 'Вертушка',
                'tackle' => 'Плетенка 0.12',
                'notes' => 'Стайка окуней на вертушку! Поймал 5 штук за час.',
                'photo_url' => 'https://images.unsplash.com/photo-1564760055775-d63b17a55c44?w=800&h=600&fit=crop',
                'additional_photos' => json_encode([
                    'https://images.unsplash.com/photo-1564760055775-d63b17a55c44?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop'
                ]),
                'privacy' => 'all',
                'caught_at' => \Carbon\Carbon::now()->subDays(4),
            ],
            [
                'user_id' => $users->random()->id,
                'lat' => 55.7200,
                'lng' => 37.5500,
                'species' => 'Лещ',
                'weight' => 1.2,
                'length' => 32,
                'style' => 'Фидер',
                'lure' => 'Мотыль',
                'tackle' => 'Леска 0.25',
                'notes' => 'Крупный лещ на фидер! Отличная рыбалка.',
                'photo_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop',
                'additional_photos' => json_encode([
                    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop'
                ]),
                'privacy' => 'all',
                'caught_at' => \Carbon\Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $users->random()->id,
                'lat' => 55.7800,
                'lng' => 37.4500,
                'species' => 'Сом',
                'weight' => 8.5,
                'length' => 85,
                'style' => 'Донка',
                'lure' => 'Лягушка',
                'tackle' => 'Плетенка 0.4',
                'notes' => 'ТРОФЕЙНЫЙ СОМ! 8.5кг! Рыбалка мечты!',
                'photo_url' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop',
                'additional_photos' => json_encode([
                    'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1564760055775-d63b17a55c44?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop'
                ]),
                'privacy' => 'all',
                'caught_at' => \Carbon\Carbon::now()->subDays(6),
            ],
            [
                'user_id' => $users->random()->id,
                'lat' => 55.7600,
                'lng' => 37.5800,
                'species' => 'Плотва',
                'weight' => 0.3,
                'length' => 18,
                'style' => 'Поплавочная удочка',
                'lure' => 'Мотыль',
                'tackle' => 'Леска 0.15',
                'notes' => 'Много плотвы на мотыля. Хороший клев!',
                'photo_url' => 'https://images.unsplash.com/photo-1564760055775-d63b17a55c44?w=800&h=600&fit=crop',
                'additional_photos' => json_encode([
                    'https://images.unsplash.com/photo-1564760055775-d63b17a55c44?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop'
                ]),
                'privacy' => 'friends',
                'caught_at' => \Carbon\Carbon::now()->subDays(7),
            ],
            [
                'user_id' => $users->random()->id,
                'lat' => 55.7900,
                'lng' => 37.5200,
                'species' => 'Жерех',
                'weight' => 2.1,
                'length' => 48,
                'style' => 'Спиннинг',
                'lure' => 'Кастмастер',
                'tackle' => 'Плетенка 0.18',
                'notes' => 'Жерех на кастмастер! Отличная поклевка!',
                'photo_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop',
                'additional_photos' => json_encode([
                    'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop'
                ]),
                'privacy' => 'all',
                'caught_at' => \Carbon\Carbon::now()->subDays(8),
            ],
            [
                'user_id' => $users->random()->id,
                'lat' => 55.7400,
                'lng' => 37.4800,
                'species' => 'Голавль',
                'weight' => 1.5,
                'length' => 35,
                'style' => 'Спиннинг',
                'lure' => 'Воблер',
                'tackle' => 'Плетенка 0.16',
                'notes' => 'Красивый голавль на воблер!',
                'photo_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop',
                'additional_photos' => json_encode([
                    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop'
                ]),
                'privacy' => 'all',
                'caught_at' => \Carbon\Carbon::now()->subDays(9),
            ],
            [
                'user_id' => $users->random()->id,
                'lat' => 55.7700,
                'lng' => 37.5300,
                'species' => 'Язь',
                'weight' => 1.8,
                'length' => 40,
                'style' => 'Спиннинг',
                'lure' => 'Вертушка',
                'tackle' => 'Плетенка 0.14',
                'notes' => 'Язь на вертушку! Отличная рыбалка!',
                'photo_url' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop',
                'additional_photos' => json_encode([
                    'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop'
                ]),
                'privacy' => 'all',
                'caught_at' => \Carbon\Carbon::now()->subDays(10),
            ],
        ];

        foreach ($catches as $catchData) {
            CatchRecord::create($catchData);
        }
    }
}