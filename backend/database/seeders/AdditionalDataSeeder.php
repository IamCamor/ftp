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
            $likesCount = rand(0, 15);
            $usersWhoLiked = $users->random($likesCount);
            
            foreach ($usersWhoLiked as $user) {
                CatchLike::create([
                    'user_id' => $user->id,
                    'catch_id' => $catch->id,
                    'created_at' => $catch->created_at->addMinutes(rand(1, 60)),
                ]);
            }
            
            // Обновляем счетчик лайков
            $catch->update(['likes_count' => $likesCount]);
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
            $commentsCount = rand(0, 8);
            $usersWhoCommented = $users->random($commentsCount);
            
            foreach ($usersWhoCommented as $user) {
                CatchComment::create([
                    'user_id' => $user->id,
                    'catch_id' => $catch->id,
                    'content' => $comments[array_rand($comments)],
                    'created_at' => $catch->created_at->addMinutes(rand(1, 120)),
                ]);
            }
            
            // Обновляем счетчик комментариев
            $catch->update(['comments_count' => $commentsCount]);
        }
        
        // Создаем уведомления
        $notifications = [
            [
                'user_id' => $users->where('username', 'alex_fisher')->first()->id,
                'type' => 'like',
                'title' => 'Новый лайк',
                'message' => 'Ваш улов понравился пользователю Мария Удачливая',
                'data' => json_encode(['catch_id' => $catches->random()->id]),
                'is_read' => false,
            ],
            [
                'user_id' => $users->where('username', 'maria_lucky')->first()->id,
                'type' => 'comment',
                'title' => 'Новый комментарий',
                'message' => 'Дмитрий Спиннингист прокомментировал ваш улов',
                'data' => json_encode(['catch_id' => $catches->random()->id]),
                'is_read' => false,
            ],
            [
                'user_id' => $users->where('username', 'dmitry_spinner')->first()->id,
                'type' => 'event',
                'title' => 'Новое событие',
                'message' => 'Создано новое событие "Выезд на Волгу за сомом"',
                'data' => json_encode(['event_id' => 1]),
                'is_read' => true,
                'read_at' => \Carbon\Carbon::now()->subHours(2),
            ],
            [
                'user_id' => $users->where('username', 'anna_carp')->first()->id,
                'type' => 'group',
                'title' => 'Приглашение в группу',
                'message' => 'Вас пригласили в группу "Карповая Рыбалка"',
                'data' => json_encode(['group_id' => 3]),
                'is_read' => false,
            ],
            [
                'user_id' => $users->where('username', 'sergey_pike')->first()->id,
                'type' => 'achievement',
                'title' => 'Новое достижение',
                'message' => 'Поздравляем! Вы получили достижение "Рекордсмен"',
                'data' => json_encode(['achievement' => 'record_breaker']),
                'is_read' => false,
            ],
            [
                'user_id' => $users->where('username', 'elena_perch')->first()->id,
                'type' => 'bonus',
                'title' => 'Бонус начислен',
                'message' => 'Вам начислено 100 бонусов за активность',
                'data' => json_encode(['bonus_amount' => 100]),
                'is_read' => true,
                'read_at' => \Carbon\Carbon::now()->subHours(5),
            ],
            [
                'user_id' => $users->where('username', 'ivan_catfish')->first()->id,
                'type' => 'weather',
                'title' => 'Прогноз погоды',
                'message' => 'На вашем избранном месте ожидается хорошая погода для рыбалки',
                'data' => json_encode(['point_id' => 4]),
                'is_read' => false,
            ],
            [
                'user_id' => $users->where('username', 'olga_trout')->first()->id,
                'type' => 'live',
                'title' => 'Началась трансляция',
                'message' => 'Александр Рыболов начал live-трансляцию рыбалки',
                'data' => json_encode(['live_session_id' => 1]),
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
                
                $messages = [
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
                    'message' => $messages[$type],
                    'data' => json_encode([]),
                    'is_read' => rand(0, 1),
                    'created_at' => \Carbon\Carbon::now()->subDays(rand(0, 7))->subHours(rand(0, 23)),
                    'read_at' => rand(0, 1) ? \Carbon\Carbon::now()->subDays(rand(0, 6))->subHours(rand(0, 22)) : null,
                ]);
            }
        }
    }
}
