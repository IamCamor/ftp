#!/bin/bash

echo "🚀 Deploying FishTrackPro with proper permissions..."

# Переходим в директорию backend
cd "$(dirname "$0")"

echo "📁 Current directory: $(pwd)"

# Проверяем, что мы в правильной директории
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Make sure you're in the backend directory."
    exit 1
fi

# Определяем веб-сервер и пользователя
WEB_SERVER=""
WEB_USER=""

if command -v nginx &> /dev/null; then
    WEB_SERVER="nginx"
    WEB_USER="nginx"
elif command -v apache2 &> /dev/null; then
    WEB_SERVER="apache2"
    WEB_USER="apache"
elif command -v httpd &> /dev/null; then
    WEB_SERVER="httpd"
    WEB_USER="apache"
else
    WEB_USER="www-data"
fi

echo "🌐 Web server: $WEB_SERVER"
echo "👤 Web user: $WEB_USER"

# 1. Создаем необходимые директории
echo "📁 Creating required directories..."
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# 2. Устанавливаем права доступа
echo "🔐 Setting permissions..."

# Основные права для файлов и директорий
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Специальные права для исполняемых файлов
chmod +x artisan
chmod +x *.sh

# Права для storage и bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# 3. Создаем файл лога
echo "📝 Creating log file..."
touch storage/logs/laravel.log
chmod 664 storage/logs/laravel.log

# 4. Устанавливаем владельца
echo "👤 Setting ownership..."
if command -v sudo &> /dev/null; then
    sudo chown -R "$WEB_USER:$WEB_USER" .
else
    chown -R "$WEB_USER:$WEB_USER" . 2>/dev/null || echo "⚠️ Cannot change ownership without sudo"
fi

# 5. Проверяем .env файл
echo "⚙️ Checking .env file..."
if [ ! -f ".env" ]; then
    echo "📝 Creating .env file..."
    if [ -f "env.example" ]; then
        cp env.example .env
    else
        echo "❌ env.example not found!"
        exit 1
    fi
fi

# 6. Генерируем ключи
echo "🔑 Generating keys..."
php artisan key:generate --force
php artisan jwt:secret --force

# 7. Очищаем кеш
echo "🧹 Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 8. Кешируем конфигурацию
echo "⚡ Caching configuration..."
php artisan config:cache

# 9. Проверяем права доступа
echo "🔍 Verifying permissions..."
echo "Storage directory:"
ls -la storage/
echo ""
echo "Logs directory:"
ls -la storage/logs/
echo ""

# 10. Тестируем запись в лог
echo "🧪 Testing log write..."
if [ -w "storage/logs/laravel.log" ]; then
    echo "✅ Can write to laravel.log"
else
    echo "❌ Cannot write to laravel.log"
fi

# 11. Проверяем конфигурацию Laravel
echo "🔧 Testing Laravel configuration..."
php artisan config:show app.name 2>/dev/null && echo "✅ Laravel configuration OK" || echo "❌ Laravel configuration error"

# 12. Проверяем подключение к БД
echo "🗄️ Testing database connection..."
php artisan tinker --execute="
try {
    DB::connection()->getPdo();
    echo '✅ Database connection OK';
} catch (Exception \$e) {
    echo '❌ Database connection error: ' . \$e->getMessage();
}
" 2>/dev/null || echo "⚠️ Cannot test database connection"

echo ""
echo "🎉 Deployment completed!"
echo ""
echo "📋 Summary:"
echo "   Web server: $WEB_SERVER"
echo "   Web user: $WEB_USER"
echo "   Storage permissions: 775"
echo "   Bootstrap cache: 775"
echo "   Log file: Created and writable"
echo ""
echo "💡 Next steps:"
echo "   1. Restart web server: systemctl restart $WEB_SERVER"
echo "   2. Check logs: tail -f storage/logs/laravel.log"
echo "   3. Test API: curl https://api.fishtrackpro.ru/api/v1/health"
echo ""
echo "🔧 If you have issues:"
echo "   1. Run: ./fix-permissions.sh"
echo "   2. Check web server logs"
echo "   3. Verify .env configuration"
