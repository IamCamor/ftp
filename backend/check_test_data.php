<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Настройка подключения к базе данных
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'fishtrackpro',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "🐟 Проверка тестовых данных FishTrackPro\n";
echo "=====================================\n\n";

// Проверяем уловы
$catches = Capsule::table('catch_records')
    ->select('id', 'species', 'weight', 'length', 'lat', 'lng', 'photo_url', 'notes', 'caught_at')
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get();

echo "📊 Статистика:\n";
echo "• Всего уловов: " . Capsule::table('catch_records')->count() . "\n";
echo "• Уловы с координатами: " . Capsule::table('catch_records')->whereNotNull('lat')->count() . "\n";
echo "• Уловы с фотографиями: " . Capsule::table('catch_records')->whereNotNull('photo_url')->count() . "\n";
echo "• Уловы с весом: " . Capsule::table('catch_records')->whereNotNull('weight')->count() . "\n";
echo "• Уловы с длиной: " . Capsule::table('catch_records')->whereNotNull('length')->count() . "\n\n";

echo "🎣 Последние 10 уловов:\n";
foreach ($catches as $catch) {
    echo "• ID {$catch->id}: {$catch->species} ({$catch->weight} кг, {$catch->length} см)\n";
    echo "  📍 Координаты: {$catch->lat}, {$catch->lng}\n";
    echo "  🖼️  Фото: " . ($catch->photo_url ?: 'Нет') . "\n";
    echo "  📝 Заметки: " . substr($catch->notes, 0, 50) . "...\n";
    echo "  🕐 Время: {$catch->caught_at}\n\n";
}

// Проверяем треки
$tracks = Capsule::table('tracks')
    ->select('id', 'title', 'total_distance', 'total_catches', 'started_at', 'ended_at')
    ->orderBy('id', 'desc')
    ->get();

echo "🗺️  Треки:\n";
foreach ($tracks as $track) {
    echo "• ID {$track->id}: {$track->title}\n";
    echo "  📏 Дистанция: {$track->total_distance} км\n";
    echo "  🐟 Уловов: {$track->total_catches}\n";
    echo "  ⏱️  Время: {$track->started_at} - {$track->ended_at}\n\n";
}

// Проверяем пользователей
$users = Capsule::table('users')
    ->select('id', 'name', 'email')
    ->limit(5)
    ->get();

echo "👥 Пользователи:\n";
foreach ($users as $user) {
    echo "• ID {$user->id}: {$user->name} ({$user->email})\n";
}

echo "\n✅ Проверка завершена!\n";

