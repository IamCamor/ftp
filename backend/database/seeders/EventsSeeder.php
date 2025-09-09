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
                'title' => 'Спиннинговая рыбалка на Оке',
                'description' => 'Ловля жереха и голавля на спиннинг. Место проверенное, много поклевок. Берем воблеры и блесны.',
                'location_name' => 'Река Ока, Серпухов',
                'lat' => 54.9167,
                'lng' => 37.4167,
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
                'location_name' => 'Рыбинское водохранилище',
                'lat' => 58.0500,
                'lng' => 38.8333,
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
                'location_name' => 'Озеро Бисерово',
                'lat' => 55.7167,
                'lng' => 38.1167,
                'start_at' => \Carbon\Carbon::now()->addDays(10)->setTime(8, 0),
                'end_at' => \Carbon\Carbon::now()->addDays(12)->setTime(18, 0),
                'max_participants' => 6,
                'status' => 'published',
                'organizer_id' => $users->where('username', 'anna_carp')->first()->id,
                'group_id' => $groups->where('name', 'Карповая Рыбалка')->first()->id,
            ],
            [
                'title' => 'Семейная рыбалка в Сокольниках',
                'description' => 'Рыбалка для всей семьи. Ловим карася и плотву. Дети могут участвовать. Берем простые поплавочные удочки.',
                'location_name' => 'Пруд в Сокольниках',
                'lat' => 55.7896,
                'lng' => 37.6792,
                'start_at' => \Carbon\Carbon::now()->addDays(2)->setTime(10, 0),
                'end_at' => \Carbon\Carbon::now()->addDays(2)->setTime(16, 0),
                'max_participants' => 20,
                'status' => 'published',
                'organizer_id' => $users->where('username', 'dmitry_spinner')->first()->id,
                'group_id' => $groups->where('name', 'Семейная Рыбалка')->first()->id,
            ],
            [
                'title' => 'Турнир по спиннингу',
                'description' => 'Соревнование по ловле на спиннинг. Призы для победителей. Регистрация обязательна.',
                'location_name' => 'Истринское водохранилище',
                'lat' => 55.9167,
                'lng' => 36.8667,
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
                'created_at' => \Carbon\Carbon::now()->subDays(rand(1, 5)),
            ]);
            
            // Добавляем случайных участников
            $availableUsers = $users->where('id', '!=', $eventData['organizer_id']);
            if ($availableUsers->count() > 0) {
                $randomCount = min(rand(1, 3), $availableUsers->count());
                $randomParticipants = $availableUsers->random($randomCount);
                foreach ($randomParticipants as $participant) {
                    $statuses = ['confirmed', 'pending', 'declined'];
                    $event->participants()->attach($participant->id, [
                        'status' => $statuses[array_rand($statuses)],
                        'created_at' => \Carbon\Carbon::now()->subDays(rand(1, 3)),
                    ]);
                }
            }
        }
    }
}
