#!/bin/bash

echo "🔄 Resetting FishTrackPro database..."

# Переходим в директорию backend
cd "$(dirname "$0")"

# Проверяем, что мы в правильной директории
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Make sure you're in the backend directory."
    exit 1
fi

echo "📁 Current directory: $(pwd)"

# Удаляем существующую базу данных SQLite
if [ -f "database/database.sqlite" ]; then
    echo "🗑️ Removing existing SQLite database..."
    rm database/database.sqlite
fi

# Создаем новую базу данных
echo "📦 Creating new SQLite database..."
touch database/database.sqlite

# Сбрасываем все миграции
echo "🔄 Resetting migrations..."
php artisan migrate:reset --force

# Запускаем миграции заново
echo "🚀 Running migrations..."
php artisan migrate --force

# Запускаем сидеры
echo "🌱 Running seeders..."
php artisan db:seed --force

echo "✅ Database reset completed successfully!"
echo "🎣 FishTrackPro database is ready!"
