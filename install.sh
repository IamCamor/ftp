#!/bin/bash

echo "🚀 Установка FishTrackPro..."

# Проверка наличия необходимых инструментов
check_command() {
    if ! command -v $1 &> /dev/null; then
        echo "❌ $1 не найден. Пожалуйста, установите $1"
        exit 1
    fi
}

echo "📋 Проверка зависимостей..."
check_command "php"
check_command "composer"
check_command "node"
check_command "npm"

# Установка backend зависимостей
echo "🔧 Установка backend зависимостей..."
cd backend
composer install
echo "✅ Backend зависимости установлены"

# Установка frontend зависимостей
echo "🔧 Установка frontend зависимостей..."
cd ../frontend
npm install
echo "✅ Frontend зависимости установлены"

# Создание .env файла для backend
echo "⚙️ Настройка окружения..."
cd ../backend
if [ ! -f .env ]; then
    cp env.example .env
    echo "📝 Создан .env файл. Пожалуйста, настройте его вручную."
fi

echo "🎉 Установка завершена!"
echo ""
echo "📋 Следующие шаги:"
echo "1. Настройте .env файл в папке backend"
echo "2. Запустите миграции: cd backend && php artisan migrate"
echo "3. Запустите backend: cd backend && php artisan serve"
echo "4. Запустите frontend: cd frontend && npm run dev"

