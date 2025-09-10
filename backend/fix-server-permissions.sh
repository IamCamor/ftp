#!/bin/bash

echo "🚀 Quick server permissions fix for FishTrackPro..."

# Переходим в директорию backend
cd "$(dirname "$0")"

echo "📁 Current directory: $(pwd)"

# Определяем веб-сервер
WEB_SERVER=""
if command -v nginx &> /dev/null; then
    WEB_SERVER="nginx"
elif command -v apache2 &> /dev/null; then
    WEB_SERVER="apache2"
elif command -v httpd &> /dev/null; then
    WEB_SERVER="httpd"
fi

echo "🌐 Detected web server: $WEB_SERVER"

# Определяем пользователя веб-сервера
if [ "$WEB_SERVER" = "nginx" ]; then
    WEB_USER="nginx"
elif [ "$WEB_SERVER" = "apache2" ] || [ "$WEB_SERVER" = "httpd" ]; then
    WEB_USER="apache"
else
    WEB_USER="www-data"
fi

echo "👤 Web server user: $WEB_USER"

# Создаем необходимые директории
echo "📁 Creating directories..."
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# Устанавливаем права доступа
echo "🔐 Setting permissions..."

# Основные права
chmod -R 755 .
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Создаем файл лога
touch storage/logs/laravel.log
chmod 664 storage/logs/laravel.log

# Устанавливаем владельца (если есть sudo)
if command -v sudo &> /dev/null; then
    echo "🔑 Setting ownership with sudo..."
    sudo chown -R "$WEB_USER:$WEB_USER" .
else
    echo "⚠️ Sudo not available, trying without..."
    chown -R "$WEB_USER:$WEB_USER" . 2>/dev/null || echo "❌ Cannot change ownership without sudo"
fi

# Проверяем права
echo "🔍 Checking permissions..."
ls -la storage/logs/

# Очищаем кеш
echo "🧹 Clearing cache..."
php artisan config:clear 2>/dev/null || echo "⚠️ Cannot clear config cache"
php artisan cache:clear 2>/dev/null || echo "⚠️ Cannot clear application cache"

echo ""
echo "✅ Server permissions fix completed!"
echo ""
echo "💡 If issues persist:"
echo "   1. Check web server status: systemctl status $WEB_SERVER"
echo "   2. Restart web server: systemctl restart $WEB_SERVER"
echo "   3. Check logs: tail -f storage/logs/laravel.log"
