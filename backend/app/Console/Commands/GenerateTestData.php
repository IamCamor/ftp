<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\TestDataSeeder;

class GenerateTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:generate {--count=50 : Количество уловов для создания}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерирует тестовые данные с картинками и координатами';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->option('count');
        
        $this->info("Генерация {$count} тестовых уловов с картинками и координатами...");
        
        // Запускаем сидер
        $seeder = new TestDataSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('Тестовые данные успешно созданы!');
        
        // Показываем статистику
        $this->showStatistics();
    }
    
    private function showStatistics()
    {
        $this->info("\n📊 Статистика тестовых данных:");
        
        $catchesCount = \App\Models\CatchRecord::count();
        $tracksCount = \App\Models\Track::count();
        $usersCount = \App\Models\User::count();
        
        $this->table(
            ['Тип данных', 'Количество'],
            [
                ['Пользователи', $usersCount],
                ['Уловы', $catchesCount],
                ['Треки', $tracksCount],
            ]
        );
        
        // Показываем примеры уловов
        $this->info("\n🎣 Примеры созданных уловов:");
        $catches = \App\Models\CatchRecord::with('user')
            ->latest()
            ->take(5)
            ->get();
            
        foreach ($catches as $catch) {
            $this->line("• {$catch->user->name}: {$catch->fish_type} ({$catch->weight} кг, {$catch->length} см)");
            $this->line("  📍 {$catch->location_name} ({$catch->lat}, {$catch->lng})");
            $this->line("  🖼️  {$catch->photo_url}");
            $this->line("");
        }
        
        // Показываем примеры треков
        $this->info("🗺️  Примеры созданных треков:");
        $tracks = \App\Models\Track::with('user')
            ->latest()
            ->take(3)
            ->get();
            
        foreach ($tracks as $track) {
            $this->line("• {$track->user->name}: {$track->title}");
            $this->line("  📏 Дистанция: {$track->total_distance} км");
            $this->line("  🐟 Уловов: {$track->total_catches}");
            $this->line("  ⏱️  Продолжительность: " . $track->started_at->diffForHumans($track->ended_at));
            $this->line("");
        }
    }
}

