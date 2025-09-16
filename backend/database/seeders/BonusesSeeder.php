<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bonus;
use App\Models\User;
use App\Models\CatchRecord;

class BonusesSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $catches = CatchRecord::all();
        
        $bonuses = [];
        
        // Бонусы за добавление уловов
        foreach ($catches as $catch) {
            $bonuses[] = [
                'user_id' => $catch->user_id,
                'action' => 'add_catch',
                'amount' => 10,
                'meta' => json_encode([
                    'catch_id' => $catch->id,
                    'species' => $catch->species,
                    'weight' => $catch->weight,
                    'length' => $catch->length,
                    'description' => 'Добавление улова: ' . $catch->species . ' (' . $catch->weight . 'кг)'
                ]),
                'created_at' => $catch->created_at,
                'updated_at' => $catch->updated_at,
            ];
        }
        
        // Бонусы за лайки (случайные)
        foreach ($users as $user) {
            $likesCount = rand(5, 25);
            for ($i = 0; $i < $likesCount; $i++) {
                $bonuses[] = [
                    'user_id' => $user->id,
                    'action' => 'like_received',
                    'amount' => 2,
                    'meta' => json_encode([
                        'from_user_id' => $users->random()->id,
                        'catch_id' => $catches->random()->id ?? null,
                        'description' => 'Получен лайк за улов'
                    ]),
                    'created_at' => \Carbon\Carbon::now()->subDays(rand(1, 30)),
                    'updated_at' => \Carbon\Carbon::now()->subDays(rand(1, 30)),
                ];
            }
        }
        
        // Бонусы за комментарии (случайные)
        foreach ($users as $user) {
            $commentsCount = rand(3, 15);
            for ($i = 0; $i < $commentsCount; $i++) {
                $bonuses[] = [
                    'user_id' => $user->id,
                    'action' => 'comment_received',
                    'amount' => 5,
                    'meta' => json_encode([
                        'from_user_id' => $users->random()->id,
                        'catch_id' => $catches->random()->id ?? null,
                        'comment' => 'Отличный улов!',
                        'description' => 'Получен комментарий к улову'
                    ]),
                    'created_at' => \Carbon\Carbon::now()->subDays(rand(1, 30)),
                    'updated_at' => \Carbon\Carbon::now()->subDays(rand(1, 30)),
                ];
            }
        }
        
        // Бонусы за ежедневный вход
        foreach ($users as $user) {
            $loginDays = rand(10, 30);
            for ($i = 0; $i < $loginDays; $i++) {
                $bonuses[] = [
                    'user_id' => $user->id,
                    'action' => 'daily_login',
                    'amount' => 1,
                    'meta' => json_encode([
                        'streak' => $i + 1,
                        'description' => 'Ежедневный вход в приложение'
                    ]),
                    'created_at' => \Carbon\Carbon::now()->subDays($i),
                    'updated_at' => \Carbon\Carbon::now()->subDays($i),
                ];
            }
        }
        
        // Бонусы за добавление точек
        foreach ($users as $user) {
            $pointsCount = rand(2, 8);
            for ($i = 0; $i < $pointsCount; $i++) {
                $bonuses[] = [
                    'user_id' => $user->id,
                    'action' => 'add_point',
                    'amount' => 5,
                    'meta' => json_encode([
                        'lat' => 55.7 + (rand(0, 100) / 1000),
                        'lng' => 37.4 + (rand(0, 200) / 1000),
                        'name' => 'Точка ' . ($i + 1),
                        'description' => 'Добавление новой точки на карте'
                    ]),
                    'created_at' => \Carbon\Carbon::now()->subDays(rand(1, 20)),
                    'updated_at' => \Carbon\Carbon::now()->subDays(rand(1, 20)),
                ];
            }
        }
        
        // Специальные бонусы
        foreach ($users as $user) {
            // Бонус за первый улов
            $bonuses[] = [
                'user_id' => $user->id,
                'action' => 'add_catch',
                'amount' => 50,
                'meta' => json_encode([
                    'milestone' => 'first_catch',
                    'description' => 'Первый улов в приложении!'
                ]),
                'created_at' => \Carbon\Carbon::now()->subDays(rand(5, 30)),
                'updated_at' => \Carbon\Carbon::now()->subDays(rand(5, 30)),
            ];
            
            // Бонус за трофейный улов
            if (rand(0, 1)) {
                $bonuses[] = [
                    'user_id' => $user->id,
                    'action' => 'add_catch',
                    'amount' => 100,
                    'meta' => json_encode([
                        'milestone' => 'trophy_catch',
                        'weight' => rand(3, 10) + (rand(0, 99) / 100),
                        'description' => 'Трофейный улов!'
                    ]),
                    'created_at' => \Carbon\Carbon::now()->subDays(rand(1, 15)),
                    'updated_at' => \Carbon\Carbon::now()->subDays(rand(1, 15)),
                ];
            }
            
            // Бонус за активность
            if (rand(0, 1)) {
                $bonuses[] = [
                    'user_id' => $user->id,
                    'action' => 'daily_login',
                    'amount' => 25,
                    'meta' => json_encode([
                        'milestone' => 'activity_bonus',
                        'days_active' => rand(7, 30),
                        'description' => 'Бонус за активность в приложении'
                    ]),
                    'created_at' => \Carbon\Carbon::now()->subDays(rand(1, 10)),
                    'updated_at' => \Carbon\Carbon::now()->subDays(rand(1, 10)),
                ];
            }
        }
        
        // Создаем бонусы батчами для производительности
        $chunks = array_chunk($bonuses, 100);
        foreach ($chunks as $chunk) {
            Bonus::insert($chunk);
        }
    }
}
