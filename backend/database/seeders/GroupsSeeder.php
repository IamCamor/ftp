<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;

class GroupsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        
        $groups = [
            [
                'name' => 'Московские Спиннингисты',
                'description' => 'Сообщество любителей спиннинговой ловли в Москве и области. Обмен опытом, совместные выезды, обсуждение снастей.',
                'cover_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=400&fit=crop',
                'privacy' => 'public',
                'owner_id' => $users->where('username', 'alex_fisher')->first()->id,
                'members_count' => 0,
            ],
            [
                'name' => 'Зимняя Рыбалка',
                'description' => 'Группа для любителей зимней рыбалки. Советы по выбору снастей, техники ловли, безопасности на льду.',
                'cover_url' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=400&fit=crop',
                'privacy' => 'public',
                'owner_id' => $users->where('username', 'maria_lucky')->first()->id,
                'members_count' => 0,
            ],
            [
                'name' => 'Карповая Рыбалка',
                'description' => 'Профессиональная карповая рыбалка. Обсуждение тактик, прикормок, снастей для ловли карпа.',
                'cover_url' => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=400&fit=crop',
                'privacy' => 'public',
                'owner_id' => $users->where('username', 'anna_carp')->first()->id,
                'members_count' => 0,
            ],
            [
                'name' => 'Щукари Подмосковья',
                'description' => 'Секретные места ловли щуки в Подмосковье. Только для опытных рыболовов.',
                'cover_url' => 'https://images.unsplash.com/photo-1583212292454-1fe6229603b7?w=800&h=400&fit=crop',
                'privacy' => 'private',
                'owner_id' => $users->where('username', 'sergey_pike')->first()->id,
                'members_count' => 0,
            ],
            [
                'name' => 'Нахлыст и Форель',
                'description' => 'Элегантная ловля нахлыстом. Форель, хариус, голавль. Обсуждение мушек и техник.',
                'cover_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=400&fit=crop',
                'privacy' => 'public',
                'owner_id' => $users->where('username', 'olga_trout')->first()->id,
                'members_count' => 0,
            ],
            [
                'name' => 'Ночная Рыбалка',
                'description' => 'Ловля сома и налима в темное время суток. Советы по безопасности и снастям.',
                'cover_url' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=400&fit=crop',
                'privacy' => 'public',
                'owner_id' => $users->where('username', 'ivan_catfish')->first()->id,
                'members_count' => 0,
            ],
            [
                'name' => 'Микроджиг Мастера',
                'description' => 'Тонкая ловля на микроджиг. Окунь, судак, берш. Обсуждение приманок и проводок.',
                'cover_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=400&fit=crop',
                'privacy' => 'public',
                'owner_id' => $users->where('username', 'elena_perch')->first()->id,
                'members_count' => 0,
            ],
            [
                'name' => 'Семейная Рыбалка',
                'description' => 'Рыбалка с детьми. Безопасные места, простые снасти, семейный отдых на природе.',
                'cover_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=400&fit=crop',
                'privacy' => 'public',
                'owner_id' => $users->where('username', 'dmitry_spinner')->first()->id,
                'members_count' => 0,
            ],
            [
                'name' => 'VIP Рыболовы',
                'description' => 'Закрытое сообщество для профессиональных рыболовов. Только по приглашениям.',
                'cover_url' => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=400&fit=crop',
                'privacy' => 'closed',
                'owner_id' => $users->where('username', 'admin')->first()->id,
                'members_count' => 0,
            ],
            [
                'name' => 'Новички в Рыбалке',
                'description' => 'Группа для начинающих рыболовов. Основы, советы, ответы на вопросы.',
                'cover_url' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=400&fit=crop',
                'privacy' => 'public',
                'owner_id' => $users->where('username', 'test_user')->first()->id,
                'members_count' => 0,
            ],
        ];

        foreach ($groups as $groupData) {
            $group = Group::create($groupData);
            
            // Добавляем владельца в группу
            $group->members()->attach($groupData['owner_id'], [
                'role' => 'admin',
                'joined_at' => \Carbon\Carbon::now()->subDays(rand(1, 30)),
            ]);
            
            // Добавляем случайных участников
            $randomMembers = $users->where('id', '!=', $groupData['owner_id'])->random(rand(3, 8));
            foreach ($randomMembers as $member) {
                $group->members()->attach($member->id, [
                    'role' => 'member',
                    'joined_at' => \Carbon\Carbon::now()->subDays(rand(1, 25)),
                ]);
            }
            
            // Обновляем счетчик участников
            $group->update(['members_count' => $group->members()->count()]);
        }
    }
}
