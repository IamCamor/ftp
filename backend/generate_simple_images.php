<?php

/**
 * Простой генератор изображений рыб для тестовых данных
 */

echo "🎨 Генерация простых изображений рыб...\n";

// Создаем директорию для изображений
$imageDir = __DIR__ . '/public/images/fish';
if (!is_dir($imageDir)) {
    mkdir($imageDir, 0755, true);
}

// Цвета для разных видов рыб
$fishColors = [
    'Щука' => [34, 139, 34],      // Зеленый
    'Окунь' => [255, 165, 0],     // Оранжевый
    'Лещ' => [192, 192, 192],     // Серебряный
    'Плотва' => [255, 192, 203],  // Розовый
    'Карась' => [255, 215, 0],    // Золотой
    'Карп' => [210, 180, 140],    // Бронзовый
    'Сазан' => [160, 82, 45],     // Коричневый
    'Сом' => [105, 105, 105],     // Серый
    'Судак' => [70, 130, 180],    // Стальной
    'Жерех' => [255, 255, 255],   // Белый
    'Голавль' => [255, 140, 0],   // Темно-оранжевый
    'Язь' => [255, 69, 0],        // Красно-оранжевый
    'Линь' => [0, 100, 0],        // Темно-зеленый
    'Красноперка' => [255, 0, 0], // Красный
    'Уклейка' => [173, 216, 230], // Светло-голубой
    'Ерш' => [128, 128, 128],     // Серый
    'Пескарь' => [139, 69, 19],   // Коричневый
    'Бычок' => [72, 61, 139],     // Темно-синий
    'Ротан' => [75, 0, 130],      // Индиго
    'Толстолобик' => [255, 255, 224] // Светло-желтый
];

// Получаем уловы из базы данных
require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
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

// Загружаем переменные окружения
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv("$key=$value");
        }
    }
}

try {
    $catches = Capsule::table('catch_records')
        ->select('id', 'species', 'weight', 'length')
        ->whereNotNull('species')
        ->orderBy('id', 'desc')
        ->limit(20)
        ->get();

    echo "Найдено " . count($catches) . " уловов для генерации изображений\n\n";

    foreach ($catches as $catch) {
        $fishType = $catch->species;
        $fishId = $catch->id;
        $weight = $catch->weight;
        $length = $catch->length;

        // Создаем изображение
        $image = createFishImage($fishType, $fishId, $weight, $length, $fishColors);
        
        if ($image) {
            $filename = "fish_{$fishId}.jpg";
            $filepath = $imageDir . '/' . $filename;
            
            if (imagejpeg($image, $filepath, 85)) {
                echo "✅ Создано изображение для {$fishType} (ID: {$fishId})\n";
                
                // Обновляем URL в базе данных
                Capsule::table('catch_records')
                    ->where('id', $fishId)
                    ->update([
                        'photo_url' => "/images/fish/{$filename}",
                        'image_url' => "/images/fish/{$filename}"
                    ]);
            } else {
                echo "❌ Ошибка сохранения изображения для {$fishType}\n";
            }
            
            imagedestroy($image);
        }
    }

    echo "\n🎉 Генерация изображений завершена!\n";
    echo "📁 Изображения сохранены в: {$imageDir}\n";

} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}

function createFishImage($fishType, $fishId, $weight, $length, $fishColors) {
    // Размеры изображения
    $width = 800;
    $height = 600;
    
    // Создаем изображение
    $image = imagecreatetruecolor($width, $height);
    
    // Цвета
    $waterColor = imagecolorallocate($image, 135, 206, 235); // Небесно-голубой
    $fishColor = isset($fishColors[$fishType]) ? $fishColors[$fishType] : [100, 149, 237];
    $fishColorRgb = imagecolorallocate($image, $fishColor[0], $fishColor[1], $fishColor[2]);
    $black = imagecolorallocate($image, 0, 0, 0);
    $white = imagecolorallocate($image, 255, 255, 255);
    
    // Заливаем фон (вода)
    imagefill($image, 0, 0, $waterColor);
    
    // Добавляем градиент воды
    for ($y = 0; $y < $height; $y++) {
        $r = 135 + intval(($y / $height) * 50);
        $g = 206 + intval(($y / $height) * 30);
        $b = 235 + intval(($y / $height) * 20);
        $color = imagecolorallocate($image, $r, $g, $b);
        imageline($image, 0, $y, $width, $y, $color);
    }
    
    // Рисуем волны
    for ($i = 0; $i < 5; $i++) {
        $waveY = $height - 100 + $i * 20;
        for ($x = 0; $x < $width; $x += 10) {
            $waveOffset = ($i % 2) * 10;
            imageellipse($image, $x + $waveOffset, $waveY, 20, 10, $black);
        }
    }
    
    // Рисуем рыбу
    $fishX = $width / 2;
    $fishY = $height / 2;
    $fishWidth = 200;
    $fishHeight = 80;
    
    // Тело рыбы
    imagefilledellipse($image, $fishX, $fishY, $fishWidth, $fishHeight, $fishColorRgb);
    imageellipse($image, $fishX, $fishY, $fishWidth, $fishHeight, $black);
    
    // Хвост
    $tailPoints = [
        $fishX + $fishWidth/2, $fishY,
        $fishX + $fishWidth/2 + 60, $fishY - 30,
        $fishX + $fishWidth/2 + 60, $fishY + 30
    ];
    imagefilledpolygon($image, $tailPoints, 3, $fishColorRgb);
    imagepolygon($image, $tailPoints, 3, $black);
    
    // Глаз
    $eyeX = $fishX - 40;
    $eyeY = $fishY - 10;
    imagefilledellipse($image, $eyeX, $eyeY, 16, 16, $white);
    imagefilledellipse($image, $eyeX, $eyeY, 8, 8, $black);
    
    // Чешуя
    for ($i = 0; $i < 5; $i++) {
        $scaleY = $fishY - 20 + $i * 10;
        imagearc($image, $fishX, $scaleY, $fishWidth - 40, 10, 0, 180, $black);
    }
    
    // Добавляем текст
    $text = $fishType;
    $fontSize = 5;
    $textX = ($width - strlen($text) * imagefontwidth($fontSize)) / 2;
    $textY = $height - 50;
    
    // Тень для текста
    imagestring($image, $fontSize, $textX + 2, $textY + 2, $text, $black);
    // Основной текст
    imagestring($image, $fontSize, $textX, $textY, $text, $white);
    
    // Информация об улове
    $infoText = "ID: {$fishId} | {$weight}кг | {$length}см";
    $infoX = ($width - strlen($infoText) * imagefontwidth(3)) / 2;
    $infoY = $textY + 25;
    
    imagestring($image, 3, $infoX + 1, $infoY + 1, $infoText, $black);
    imagestring($image, 3, $infoX, $infoY, $infoText, $white);
    
    return $image;
}

