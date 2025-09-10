#!/bin/bash

echo "🔧 Fixing Laravel permissions..."

# Переходим в директорию backend
cd "$(dirname "$0")"

# Проверяем, что мы в правильной директории
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Make sure you're in the backend directory."
    exit 1
fi

echo "📁 Current directory: $(pwd)"

# Создаем необходимые директории если не существуют
echo "📁 Creating required directories..."
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# Устанавливаем правильные права доступа
echo "🔐 Setting permissions..."

# Устанавливаем владельца (замените www-data на нужного пользователя)
if [ -n "$1" ]; then
    OWNER="$1"
else
    # Определяем владельца автоматически
    if id "www-data" &>/dev/null; then
        OWNER="www-data"
    elif id "nginx" &>/dev/null; then
        OWNER="nginx"
    elif id "apache" &>/dev/null; then
        OWNER="apache"
    else
        OWNER="$(whoami)"
    fi
fi

echo "👤 Setting owner to: $OWNER"

# Устанавливаем владельца для всех файлов Laravel
sudo chown -R "$OWNER:$OWNER" .

# Устанавливаем права доступа
echo "📝 Setting file permissions..."

# Основные права для файлов
find . -type f -exec chmod 644 {} \;

# Права для исполняемых файлов
chmod +x artisan
chmod +x *.sh

# Права для директорий
find . -type d -exec chmod 755 {} \;

# Специальные права для storage и bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Устанавливаем права для логов
chmod 664 storage/logs/*.log 2>/dev/null || true

# Создаем файл лога если не существует
touch storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
chown "$OWNER:$OWNER" storage/logs/laravel.log

# Проверяем права доступа
echo "🔍 Checking permissions..."
echo "Storage directory:"
ls -la storage/
echo ""
echo "Logs directory:"
ls -la storage/logs/
echo ""
echo "Bootstrap cache:"
ls -la bootstrap/cache/

# Проверяем, может ли веб-сервер писать в storage
echo "🧪 Testing write permissions..."
if [ -w "storage/logs" ]; then
    echo "✅ Can write to storage/logs"
else
    echo "❌ Cannot write to storage/logs"
fi

if [ -w "storage/framework/cache" ]; then
    echo "✅ Can write to storage/framework/cache"
else
    echo "❌ Cannot write to storage/framework/cache"
fi

if [ -w "bootstrap/cache" ]; then
    echo "✅ Can write to bootstrap/cache"
else
    echo "❌ Cannot write to bootstrap/cache"
fi

# Очищаем кеш Laravel
echo "🧹 Clearing Laravel cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Проверяем конфигурацию
echo "⚙️ Checking Laravel configuration..."
php artisan config:cache

echo ""
echo "✅ Permissions fixed successfully!"
echo ""
echo "📋 Summary:"
echo "   Owner: $OWNER"
echo "   Storage: 775"
echo "   Bootstrap/cache: 775"
echo "   Files: 644"
echo "   Directories: 755"
echo ""
echo "💡 If you're still having issues, try:"
echo "   1. Check web server user: ps aux | grep nginx"
echo "   2. Run: ./fix-permissions.sh www-data"
echo "   3. Restart web server: sudo systemctl restart nginx"
