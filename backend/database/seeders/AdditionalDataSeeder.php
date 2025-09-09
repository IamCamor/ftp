<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatchRecord;
use App\Models\CatchLike;
use App\Models\CatchComment;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class AdditionalDataSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $catches = CatchRecord::all();
        
        // Создаем лайки для уловов
        foreach ($catches as $catch) {
            $likesCount = min(rand(0, 5), $users->count());
            if ($likesCount > 0) {
                $usersWhoLiked = $users->random($likesCount);
                
                foreach ($usersWhoLiked as $user) {
                    CatchLike::create([
                        'user_id' => $user->id,
                        'catch_id' => $catch->id,
                        'created_at' => $catch->created_at->addMinutes(rand(1, 60)),
                    ]);
                }
            }
            
            // Счетчик лайков будет вычисляться динамически
        }
        
        // Создаем комментарии к уловам
        $comments = [
            'Отличный улов! Поздравляю!',
            'Красивая рыба! На что ловил?',
            'Молодец! Такая рыба редко попадается.',
            'Завидую! Хочу тоже туда съездить.',
            'Отличное место! Спасибо за информацию.',
            'Какой вес у рыбы?',
            'На какую приманку ловил?',
            'Сколько времени вываживал?',
            'Классный улов! Поделись секретом.',
            'Поздравляю с трофеем!',
            'Отличная рыбалка!',
            'Хорошо провел время!',
            'Красивое фото!',
            'Интересное место для рыбалки.',
            'Спасибо за пост!',
        ];
        
        foreach ($catches as $catch) {
            $commentsCount = min(rand(0, 3), $users->count());
            if ($commentsCount > 0) {
                $usersWhoCommented = $users->random($commentsCount);
                
                foreach ($usersWhoCommented as $user) {
                CatchComment::create([
                    'user_id' => $user->id,
                    'catch_id' => $catch->id,
                    'body' => $comments[array_rand($comments)],
                    'created_at' => $catch->created_at->addMinutes(rand(1, 120)),
                ]);
                }
            }
            
            // Счетчик комментариев будет вычисляться динамически
        }
        
        // Создаем уведомления
        $notifications = [
            [
                'user_id' => $users->where('username', 'alex_fisher')->first()->id,
                'type' => 'like',
                'title' => 'Новый лайк',
                'body' => 'Ваш улов понравился пользователю Мария Удачливая',
                'is_read' => false,
            ],
            [
                'user_id' => $users->where('username', 'maria_lucky')->first()->id,
                'type' => 'comment',
                'title' => 'Новый комментарий',
                'body' => 'Дмитрий Спиннингист прокомментировал ваш улов',
                'is_read' => false,
            ],
            [
                'user_id' => $users->where('username', 'dmitry_spinner')->first()->id,
                'type' => 'event',
                'title' => 'Новое событие',
                'body' => 'Создано новое событие "Спиннинговая рыбалка на Оке"',
                'is_read' => true,
                'read_at' => \Carbon\Carbon::now()->subHours(2),
            ],
            [
                'user_id' => $users->where('username', 'anna_carp')->first()->id,
                'type' => 'group',
                'title' => 'Приглашение в группу',
                'body' => 'Вас пригласили в группу "Карповая Рыбалка"',
                'is_read' => false,
            ],
            [
                'user_id' => $users->where('username', 'admin')->first()->id,
                'type' => 'achievement',
                'title' => 'Новое достижение',
                'body' => 'Поздравляем! Вы получили достижение "Рекордсмен"',
                'is_read' => false,
            ],
        ];
        
        foreach ($notifications as $notificationData) {
            Notification::create($notificationData);
        }
        
        // Создаем дополнительные уведомления для каждого пользователя
        foreach ($users as $user) {
            $userNotifications = rand(3, 8);
            for ($i = 0; $i < $userNotifications; $i++) {
                $types = ['like', 'comment', 'event', 'group', 'achievement', 'bonus', 'weather', 'live'];
                $type = $types[array_rand($types)];
                
                $titles = [
                    'like' => 'Новый лайк',
                    'comment' => 'Новый комментарий',
                    'event' => 'Новое событие',
                    'group' => 'Обновление в группе',
                    'achievement' => 'Новое достижение',
                    'bonus' => 'Бонус начислен',
                    'weather' => 'Прогноз погоды',
                    'live' => 'Live-трансляция',
                ];
                
                $bodies = [
                    'like' => 'Ваш улов понравился пользователю',
                    'comment' => 'Новый комментарий к вашему улову',
                    'event' => 'Создано новое событие в вашей группе',
                    'group' => 'Новое сообщение в группе',
                    'achievement' => 'Поздравляем с новым достижением!',
                    'bonus' => 'Вам начислены бонусы за активность',
                    'weather' => 'Обновлен прогноз погоды для вашего места',
                    'live' => 'Началась live-трансляция рыбалки',
                ];
                
                Notification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $titles[$type],
                    'body' => $bodies[$type],
                    'is_read' => rand(0, 1),
                    'created_at' => \Carbon\Carbon::now()->subDays(rand(0, 7))->subHours(rand(0, 23)),
                    'read_at' => rand(0, 1) ? \Carbon\Carbon::now()->subDays(rand(0, 6))->subHours(rand(0, 22)) : null,
                ]);
            }
        }
    }
}
