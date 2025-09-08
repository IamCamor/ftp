<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Александр Рыболов',
                'username' => 'alex_fisher',
                'email' => 'alex@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face',
                'bio' => 'Люблю рыбалку на спиннинг. Опыт 15 лет.',
                'location' => 'Москва, Россия',
                'role' => 'user',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'Мария Удачливая',
                'username' => 'maria_lucky',
                'email' => 'maria@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face',
                'bio' => 'Рыбалка - моя страсть! Особенно люблю зимнюю рыбалку.',
                'location' => 'Санкт-Петербург, Россия',
                'role' => 'user',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'Дмитрий Спиннингист',
                'username' => 'dmitry_spinner',
                'email' => 'dmitry@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face',
                'bio' => 'Профессиональный рыболов. Учу других ловить рыбу.',
                'location' => 'Краснодар, Россия',
                'role' => 'user',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'Анна Карпова',
                'username' => 'anna_carp',
                'email' => 'anna@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face',
                'bio' => 'Специализируюсь на карповой рыбалке. Люблю природу.',
                'location' => 'Волгоград, Россия',
                'role' => 'user',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'Сергей Щукарь',
                'username' => 'sergey_pike',
                'email' => 'sergey@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&h=150&fit=crop&crop=face',
                'bio' => 'Охотник за щукой. Рекорд: щука 12 кг!',
                'location' => 'Ростов-на-Дону, Россия',
                'role' => 'user',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'Елена Окунева',
                'username' => 'elena_perch',
                'email' => 'elena@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&h=150&fit=crop&crop=face',
                'bio' => 'Люблю окуневую рыбалку. Мастер по микроджигу.',
                'location' => 'Казань, Россия',
                'role' => 'user',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'Иван Сомов',
                'username' => 'ivan_catfish',
                'email' => 'ivan@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://images.unsplash.com/photo-1507591064344-4c6ce005b128?w=150&h=150&fit=crop&crop=face',
                'bio' => 'Ночная рыбалка на сома - моя стихия!',
                'location' => 'Астрахань, Россия',
                'role' => 'user',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'Ольга Форелева',
                'username' => 'olga_trout',
                'email' => 'olga@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=150&h=150&fit=crop&crop=face',
                'bio' => 'Нахлыст и форель - идеальное сочетание!',
                'location' => 'Сочи, Россия',
                'role' => 'user',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'Администратор',
                'username' => 'admin',
                'email' => 'admin@fishtrackpro.com',
                'password' => Hash::make('admin123'),
                'avatar_url' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=150&h=150&fit=crop&crop=face',
                'bio' => 'Администратор FishTrackPro',
                'location' => 'Москва, Россия',
                'role' => 'admin',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'Тестовый Пользователь',
                'username' => 'test_user',
                'email' => 'test@example.com',
                'password' => Hash::make('test123'),
                'avatar_url' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&h=150&fit=crop&crop=face',
                'bio' => 'Тестовый аккаунт для демонстрации',
                'location' => 'Тестовый город',
                'role' => 'user',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
