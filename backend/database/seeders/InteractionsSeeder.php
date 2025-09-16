<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatchLike;
use App\Models\CatchComment;
use App\Models\User;
use App\Models\CatchRecord;

class InteractionsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $catches = CatchRecord::all();
        
        // Создаем лайки
        $likes = [];
        $usedLikes = [];
        
        foreach ($catches as $catch) {
            $availableUsers = $users->where('id', '!=', $catch->user_id);
            if ($availableUsers->count() > 0) {
                $likesCount = rand(3, min(15, $availableUsers->count()));
                $likedUsers = $availableUsers->random($likesCount);
                
                foreach ($likedUsers as $user) {
                    // Дополнительная проверка, что пользователь не лайкает свой улов
                    if ($user->id !== $catch->user_id) {
                        $likeKey = $catch->id . '-' . $user->id;
                        if (!in_array($likeKey, $usedLikes)) {
                            $likes[] = [
                                'user_id' => $user->id,
                                'catch_id' => $catch->id,
                                'created_at' => $catch->created_at->addMinutes(rand(1, 1440)),
                                'updated_at' => $catch->created_at->addMinutes(rand(1, 1440)),
                            ];
                            $usedLikes[] = $likeKey;
                        }
                    }
                }
            }
        }
        
        // Создаем комментарии
        $comments = [];
        $commentTexts = [
            'Отличный улов! 🎣',
            'Красивая рыба!',
            'Поздравляю с трофеем!',
            'Какой воблер использовал?',
            'На какой глубине ловил?',
            'Супер! Где ловил?',
            'Классный улов!',
            'Поздравляю! 🎉',
            'Отличная рыбалка!',
            'Красивый экземпляр!',
            'Какой вес?',
            'На что ловил?',
            'Отличная поклевка!',
            'Поздравляю с уловом!',
            'Классная рыбалка!',
            'Красивая рыба! 🐟',
            'Отличный трофей!',
            'Поздравляю!',
            'Супер улов!',
            'Класс!'
        ];
        
        foreach ($catches as $catch) {
            $commentsCount = rand(2, min(8, $users->count()));
            $commentingUsers = $users->random($commentsCount);
            
            foreach ($commentingUsers as $user) {
                // Проверяем, что пользователь не комментирует свой собственный улов
                if ($user->id !== $catch->user_id) {
                    $comments[] = [
                        'user_id' => $user->id,
                        'catch_id' => $catch->id,
                        'body' => $commentTexts[array_rand($commentTexts)],
                        'moderation_status' => 'approved',
                        'is_approved' => true,
                        'created_at' => $catch->created_at->addMinutes(rand(5, 2880)),
                        'updated_at' => $catch->created_at->addMinutes(rand(5, 2880)),
                    ];
                }
            }
        }
        
        // Создаем лайки и комментарии батчами для производительности
        if (!empty($likes)) {
            $likeChunks = array_chunk($likes, 100);
            foreach ($likeChunks as $chunk) {
                CatchLike::insert($chunk);
            }
        }
        
        if (!empty($comments)) {
            $commentChunks = array_chunk($comments, 100);
            foreach ($commentChunks as $chunk) {
                CatchComment::insert($chunk);
            }
        }
    }
}
