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
                'photo_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face',
                'role' => 'user',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'Мария Удачливая',
                'username' => 'maria_lucky',
                'email' => 'maria@example.com',
                'password' => Hash::make('password123'),
                'photo_url' => 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face',
                'role' => 'user',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'Дмитрий Спиннингист',
                'username' => 'dmitry_spinner',
                'email' => 'dmitry@example.com',
                'password' => Hash::make('password123'),
                'photo_url' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face',
                'role' => 'user',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'Анна Карпова',
                'username' => 'anna_carp',
                'email' => 'anna@example.com',
                'password' => Hash::make('password123'),
                'photo_url' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face',
                'role' => 'user',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'Администратор',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'photo_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&h=150&fit=crop&crop=face',
                'role' => 'admin',
                'email_verified_at' => \Carbon\Carbon::now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}