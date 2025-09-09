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
        ];

        foreach ($groups as $groupData) {
            $group = Group::create($groupData);
            
            // Добавляем владельца в группу
            $group->members()->attach($groupData['owner_id'], [
                'role' => 'admin',
                'created_at' => \Carbon\Carbon::now()->subDays(rand(1, 30)),
            ]);
            
            // Добавляем случайных участников
            $availableUsers = $users->where('id', '!=', $groupData['owner_id']);
            if ($availableUsers->count() > 0) {
                $randomCount = min(rand(1, 3), $availableUsers->count());
                $randomMembers = $availableUsers->random($randomCount);
                foreach ($randomMembers as $member) {
                    $group->members()->attach($member->id, [
                        'role' => 'member',
                        'created_at' => \Carbon\Carbon::now()->subDays(rand(1, 25)),
                    ]);
                }
            }
            
            // Обновляем счетчик участников
            $group->update(['members_count' => $group->members()->count()]);
        }
    }
}
