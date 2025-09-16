<?php

/**
 * Простой тест API для проверки данных
 */

echo "🧪 Тестирование API FishTrackPro\n";
echo "================================\n\n";

// Тест 1: Feed API
echo "1️⃣ Тестирование Feed API...\n";
$feedResponse = file_get_contents('http://localhost:8000/api/v1/feed?type=all&page=1&limit=3');
$feedData = json_decode($feedResponse, true);

if ($feedData && $feedData['success']) {
    $tracks = $feedData['data']['data'];
    echo "✅ Feed API работает\n";
    echo "📊 Найдено треков: " . count($tracks) . "\n";
    
    if (count($tracks) > 0) {
        $track = $tracks[0];
        echo "🎣 Первый трек:\n";
        echo "   ID: " . $track['id'] . "\n";
        echo "   Название: " . $track['title'] . "\n";
        echo "   Уловов: " . count($track['catches']) . "\n";
        echo "   Пользователь: " . ($track['user']['name'] ?? 'Неизвестно') . "\n";
        
        if (count($track['catches']) > 0) {
            $catch = $track['catches'][0];
            echo "   Первый улов:\n";
            echo "     ID: " . $catch['id'] . "\n";
            echo "     Вид: " . $catch['species'] . "\n";
            echo "     Вес: " . $catch['weight'] . " кг\n";
            echo "     Длина: " . $catch['length'] . " см\n";
            echo "     Координаты: " . $catch['lat'] . ", " . $catch['lng'] . "\n";
            echo "     Фото: " . ($catch['photo_url'] ?: 'Нет') . "\n";
        }
    }
} else {
    echo "❌ Feed API не работает\n";
    echo "Ответ: " . $feedResponse . "\n";
}

echo "\n";

// Тест 2: Tracks API
echo "2️⃣ Тестирование Tracks API...\n";
$tracksResponse = file_get_contents('http://localhost:8000/api/v1/tracks', false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer simple_token_1_17578\r\n"
    ]
]));
$tracksData = json_decode($tracksResponse, true);

if ($tracksData && $tracksData['success']) {
    $tracks = $tracksData['data'];
    echo "✅ Tracks API работает\n";
    echo "📊 Найдено треков: " . count($tracks) . "\n";
    
    if (count($tracks) > 0) {
        $track = $tracks[0];
        echo "🗺️ Первый трек:\n";
        echo "   ID: " . $track['id'] . "\n";
        echo "   Название: " . $track['title'] . "\n";
        echo "   Дистанция: " . $track['total_distance'] . " км\n";
        echo "   Уловов: " . $track['total_catches'] . "\n";
        echo "   Статус: " . $track['status'] . "\n";
    }
} else {
    echo "❌ Tracks API не работает\n";
    echo "Ответ: " . $tracksResponse . "\n";
}

echo "\n";

// Тест 3: Проверка изображений
echo "3️⃣ Проверка изображений...\n";
$imageDir = __DIR__ . '/public/images/fish';
if (is_dir($imageDir)) {
    $images = glob($imageDir . '/*.jpg');
    echo "✅ Директория изображений существует\n";
    echo "📁 Найдено изображений: " . count($images) . "\n";
    
    if (count($images) > 0) {
        echo "🖼️ Примеры изображений:\n";
        foreach (array_slice($images, 0, 3) as $image) {
            $filename = basename($image);
            echo "   - " . $filename . "\n";
        }
    }
} else {
    echo "❌ Директория изображений не найдена\n";
}

echo "\n";

// Тест 4: Проверка базы данных
echo "4️⃣ Проверка базы данных...\n";
try {
    require_once 'vendor/autoload.php';
    
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection([
        'driver' => 'mysql',
        'host' => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', 'fishtrackpro'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ]);
    
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    
    $catchesCount = \App\Models\CatchRecord::count();
    $tracksCount = \App\Models\Track::count();
    $usersCount = \App\Models\User::count();
    
    echo "✅ Подключение к базе данных успешно\n";
    echo "📊 Статистика:\n";
    echo "   Уловы: " . $catchesCount . "\n";
    echo "   Треки: " . $tracksCount . "\n";
    echo "   Пользователи: " . $usersCount . "\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка подключения к базе данных: " . $e->getMessage() . "\n";
}

echo "\n🎉 Тестирование завершено!\n";

