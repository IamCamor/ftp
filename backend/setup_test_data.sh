#!/bin/bash

echo "🐟 Настройка тестовых данных для FishTrackPro"
echo "=============================================="

# Переходим в директорию backend
cd "$(dirname "$0")"

echo "📦 Установка зависимостей Python..."
# Устанавливаем Pillow для генерации изображений
pip3 install Pillow

echo "🎨 Генерация изображений рыб..."
python3 generate_fish_images.py

echo "📁 Создание директории для изображений..."
mkdir -p public/images/fish

echo "🗄️  Очистка существующих тестовых данных..."
php artisan tinker --execute="
\App\Models\CatchRecord::where('user_id', '>', 0)->delete();
\App\Models\Track::where('user_id', '>', 0)->delete();
echo 'Существующие тестовые данные удалены';
"

echo "🌱 Генерация новых тестовых данных..."
php artisan test:generate --count=50

echo "📊 Показ статистики..."
php artisan tinker --execute="
echo 'Пользователи: ' . \App\Models\User::count();
echo 'Уловы: ' . \App\Models\CatchRecord::count();
echo 'Треки: ' . \App\Models\Track::count();
echo 'Уловы с координатами: ' . \App\Models\CatchRecord::whereNotNull('lat')->count();
echo 'Уловы с фотографиями: ' . \App\Models\CatchRecord::whereNotNull('photo_url')->count();
"

echo "✅ Тестовые данные успешно созданы!"
echo ""
echo "📋 Что было создано:"
echo "• 50 уловов с реальными координатами рыбных мест России"
echo "• 3 трека с маршрутами по рекам"
echo "• Изображения рыб (если Python установлен)"
echo "• Данные о погоде, снастях, наживках"
echo ""
echo "🌍 Координаты покрывают:"
echo "• Волга у Астрахани"
echo "• Ока у Рязани" 
echo "• Дон у Ростова"
echo "• Нева у Санкт-Петербурга"
echo "• Москва-река у Москвы"
echo "• Ангара у Иркутска"
echo "• Енисей у Красноярска"
echo "• Обь у Новосибирска"
echo "• Лена у Якутска"
echo "• Амур у Хабаровска"
echo ""
echo "🎣 Виды рыб: Щука, Окунь, Лещ, Плотва, Карась, Карп, Сазан, Сом, Судак, Жерех и др."
echo ""
echo "🚀 Теперь можно тестировать приложение с реальными данными!"

