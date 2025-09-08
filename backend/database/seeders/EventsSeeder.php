<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;

class EventsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $groups = Group::all();
        
        $events = [
            [
                'title' => 'Выезд на Волгу за сомом',
                'description' => 'Ночная рыбалка на сома в районе Дубны. Сбор в 18:00, выезд в 19:00. Берем палатки, спальники, фонари.',
                'location' => 'Волга, Дубна',
                'latitude' => 56.7333,
                'longitude' => 37.1667,
                'start_at' => \Carbon\\Carbon\Carbon::now()->addDays(3)->setTime(19, 0),
                'end_at' => \Carbon\\Carbon\Carbon::now()->addDays(4)->setTime(8, 0),
                'max_participants' => 8,
                'status' => 'published',
                'organizer_id' => $users->where('username', 'ivan_catfish')->first()->id,
                'group_id' => $groups->where('name', 'Ночная Рыбалка')->first()->id,
            ],
            [
                'title' => 'Спиннинговая рыбалка на Оке',
                'description' => 'Ловля жереха и голавля на спиннинг. Место проверенное, много поклевок. Берем воблеры и блесны.',
                'location' => 'Река Ока, Серпухов',
                'latitude' => 54.9167,
                'longitude' => 37.4167,
                'start_at' => \Carbon\Carbon::now()->addDays(5)->setTime(6, 0),
                'end_at' => \Carbon\Carbon::now()->addDays(5)->setTime(14, 0),
                'max_participants' => 12,
                'status' => 'published',
                'organizer_id' => $users->where('username', 'alex_fisher')->first()->id,
                'group_id' => $groups->where('name', 'Московские Спиннингисты')->first()->id,
            ],
            [
                'title' => 'Зимняя рыбалка на Рыбинке',
                'description' => 'Ловля судака и леща со льда. Проверенные места, хороший клев. Берем зимние удочки и мормышки.',
                'location' => 'Рыбинское водохранилище',
                'latitude' => 58.0500,
                'longitude' => 38.8333,
                'start_at' => \Carbon\Carbon::now()->addDays(7)->setTime(7, 0),
                'end_at' => \Carbon\Carbon::now()->addDays(7)->setTime(16, 0),
                'max_participants' => 15,
                'status' => 'published',
                'organizer_id' => $users->where('username', 'maria_lucky')->first()->id,
                'group_id' => $groups->where('name', 'Зимняя Рыбалка')->first()->id,
            ],
            [
                'title' => 'Карповая сессия на Бисерово',
                'description' => 'Многодневная карповая рыбалка. Останавливаемся на 2 дня. Берем карповые удочки, бойлы, прикормку.',
                'location' => 'Озеро Бисерово',
                'latitude' => 55.7167,
                'longitude' => 38.1167,
                'start_at' => \Carbon\Carbon::now()->addDays(10)->setTime(8, 0),
                'end_at' => \Carbon\Carbon::now()->addDays(12)->setTime(18, 0),
                'max_participants' => 6,
                'status' => 'published',
                'organizer_id' => $users->where('username', 'anna_carp')->first()->id,
                'group_id' => $groups->where('name', 'Карповая Рыбалка')->first()->id,
            ],
            [
                'title' => 'Нахлыст на форель',
                'description' => 'Ловля форели нахлыстом на платнике. Обучение технике заброса для новичков. Снасти можно взять напрокат.',
                'location' => 'Форелевое хозяйство "Рыбное"',
                'latitude' => 55.8000,
                'longitude' => 37.9000,
                'start_at' => \Carbon\Carbon::now()->addDays(14)->setTime(9, 0),
                'end_at' => \Carbon\Carbon::now()->addDays(14)->setTime(15, 0),
                'max_participants' => 10,
                'status' => 'published',
                'organizer_id' => $users->where('username', 'olga_trout')->first()->id,
                'group_id' => $groups->where('name', 'Нахлыст и Форель')->first()->id,
            ],
            [
                'title' => 'Семейная рыбалка в Сокольниках',
                'description' => 'Рыбалка для всей семьи. Ловим карася и плотву. Дети могут участвовать. Берем простые поплавочные удочки.',
                'location' => 'Пруд в Сокольниках',
                'latitude' => 55.7896,
                'longitude' => 37.6792,
                'start_at' => \Carbon\Carbon::now()->addDays(2)->setTime(10, 0),
                'end_at' => \Carbon\Carbon::now()->addDays(2)->setTime(16, 0),
                'max_participants' => 20,
                'status' => 'published',
                'organizer_id' => $users->where('username', 'dmitry_spinner')->first()->id,
                'group_id' => $groups->where('name', 'Семейная Рыбалка')->first()->id,
            ],
            [
                'title' => 'Микроджиг на окуня',
                'description' => 'Тонкая ловля окуня на микроджиг. Обсуждение приманок и проводок. Подходит для новичков.',
                'location' => 'Озеро Светлое',
                'latitude' => 55.7558,
                'longitude' => 37.6176,
                'start_at' => \Carbon\Carbon::now()->addDays(6)->setTime(8, 0),
                'end_at' => \Carbon\Carbon::now()->addDays(6)->setTime(13, 0),
                'max_participants' => 8,
                'status' => 'published',
                'organizer_id' => $users->where('username', 'elena_perch')->first()->id,
                'group_id' => $groups->where('name', 'Микроджиг Мастера')->first()->id,
            ],
            [
                'title' => 'Щучья охота в Подмосковье',
                'description' => 'Секретное место для ловли крупной щуки. Только для участников группы. Берем спиннинги и воблеры.',
                'location' => 'Секретное озеро',
                'latitude' => 55.6500,
                'longitude' => 37.7500,
                'start_at' => \Carbon\Carbon::now()->addDays(9)->setTime(6, 0),
                'end_at' => \Carbon\Carbon::now()->addDays(9)->setTime(14, 0),
                'max_participants' => 5,
                'status' => 'published',
                'organizer_id' => $users->where('username', 'sergey_pike')->first()->id,
                'group_id' => $groups->where('name', 'Щукари Подмосковья')->first()->id,
            ],
            [
                'title' => 'Мастер-класс по рыбалке',
                'description' => 'Обучение основам рыбалки для новичков. Теория и практика. Снасти предоставляются.',
                'location' => 'Пруд в Кузьминках',
                'latitude' => 55.6833,
                'longitude' => 37.7833,
                'start_at' => \Carbon\Carbon::now()->addDays(4)->setTime(10, 0),
                'end_at' => \Carbon\Carbon::now()->addDays(4)->setTime(16, 0),
                'max_participants' => 15,
                'status' => 'published',
                'organizer_id' => $users->where('username', 'test_user')->first()->id,
                'group_id' => $groups->where('name', 'Новички в Рыбалке')->first()->id,
            ],
            [
                'title' => 'Турнир по спиннингу',
                'description' => 'Соревнование по ловле на спиннинг. Призы для победителей. Регистрация обязательна.',
                'location' => 'Истринское водохранилище',
                'latitude' => 55.9167,
                'longitude' => 36.8667,
                'start_at' => \Carbon\Carbon::now()->addDays(12)->setTime(7, 0),
                'end_at' => \Carbon\Carbon::now()->addDays(12)->setTime(17, 0),
                'max_participants' => 25,
                'status' => 'published',
                'organizer_id' => $users->where('username', 'admin')->first()->id,
                'group_id' => $groups->where('name', 'VIP Рыболовы')->first()->id,
            ],
        ];

        foreach ($events as $eventData) {
            $event = Event::create($eventData);
            
            // Добавляем организатора в участники
            $event->participants()->attach($eventData['organizer_id'], [
                'status' => 'confirmed',
                'joined_at' => \Carbon\Carbon::now()->subDays(rand(1, 5)),
            ]);
            
            // Добавляем случайных участников
            $randomParticipants = $users->where('id', '!=', $eventData['organizer_id'])->random(rand(2, 8));
            foreach ($randomParticipants as $participant) {
                $statuses = ['confirmed', 'pending', 'declined'];
                $event->participants()->attach($participant->id, [
                    'status' => $statuses[array_rand($statuses)],
                    'joined_at' => \Carbon\Carbon::now()->subDays(rand(1, 3)),
                ]);
            }
        }
    }
}
