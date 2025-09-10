#!/bin/bash

echo "🐬 Setting up FishTrackPro with MySQL..."

# Переходим в директорию backend
cd "$(dirname "$0")"

# Проверяем, что мы в правильной директории
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Make sure you're in the backend directory."
    exit 1
fi

echo "📁 Current directory: $(pwd)"

# Создаем .env файл для MySQL если не существует
if [ ! -f ".env" ]; then
    echo "📝 Creating .env file for MySQL..."
    cat > .env << 'EOF'
APP_NAME=FishTrackPro
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://api.fishtrackpro.ru
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# MySQL Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fishtrackpro
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# JWT Configuration
JWT_SECRET=
JWT_TTL=43200

# CORS Configuration
CORS_ALLOWED_ORIGINS=https://www.fishtrackpro.ru

# OAuth Providers
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://api.fishtrackpro.ru/auth/google/callback

VKONTAKTE_CLIENT_ID=
VKONTAKTE_CLIENT_SECRET=
VKONTAKTE_REDIRECT_URI=https://api.fishtrackpro.ru/auth/vk/callback

YANDEX_CLIENT_ID=
YANDEX_CLIENT_SECRET=
YANDEX_REDIRECT_URI=https://api.fishtrackpro.ru/auth/yandex/callback

APPLE_CLIENT_ID=
APPLE_CLIENT_SECRET=
APPLE_REDIRECT_URI=https://api.fishtrackpro.ru/auth/apple/callback

# File Storage
FILES_DISK=s3

# External Integrations
ADS_SOURCE_URL=
EOF
    echo "✅ .env file created!"
else
    echo "ℹ️ .env file already exists, updating database configuration..."
    
    # Обновляем конфигурацию БД в существующем .env
    sed -i.bak 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
    sed -i.bak 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env
    sed -i.bak 's/^DB_PORT=.*/DB_PORT=3306/' .env
    sed -i.bak 's/^DB_DATABASE=.*/DB_DATABASE=fishtrackpro/' .env
    sed -i.bak 's/^DB_USERNAME=.*/DB_USERNAME=root/' .env
    sed -i.bak 's/^DB_PASSWORD=.*/DB_PASSWORD=/' .env
    
    echo "✅ Database configuration updated!"
fi

# Проверяем подключение к MySQL
echo "🔍 Testing MySQL connection..."
mysql -h127.0.0.1 -P3306 -uroot -e "SELECT 1;" 2>/dev/null

if [ $? -ne 0 ]; then
    echo "❌ Error: Cannot connect to MySQL server."
    echo "💡 Please make sure MySQL is running and accessible."
    echo "   You may need to:"
    echo "   1. Start MySQL service: sudo systemctl start mysql"
    echo "   2. Set root password: sudo mysql_secure_installation"
    echo "   3. Update DB_PASSWORD in .env file"
    exit 1
fi

echo "✅ MySQL connection successful!"

# Создаем базу данных
echo "📦 Creating database..."
mysql -h127.0.0.1 -P3306 -uroot -e "CREATE DATABASE IF NOT EXISTS fishtrackpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

if [ $? -ne 0 ]; then
    echo "❌ Error: Cannot create database."
    exit 1
fi

echo "✅ Database created/verified!"

# Импортируем дамп
echo "📥 Importing database structure..."
if [ -f "database/fishtrackpro_dump.sql" ]; then
    mysql -h127.0.0.1 -P3306 -uroot fishtrackpro < database/fishtrackpro_dump.sql
    
    if [ $? -ne 0 ]; then
        echo "❌ Error: Failed to import SQL dump."
        exit 1
    fi
    
    echo "✅ Database structure imported successfully!"
else
    echo "❌ Error: fishtrackpro_dump.sql not found."
    echo "💡 Please make sure the SQL dump file exists in database/ directory."
    exit 1
fi

# Генерируем ключи
echo "🔑 Generating application key..."
php artisan key:generate

echo "🔐 Generating JWT secret..."
php artisan jwt:secret

# Очищаем кеш
echo "🧹 Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Проверяем структуру базы данных
echo "🔍 Verifying database structure..."
php artisan tinker --execute="
try {
    \$tables = ['users', 'events', 'chats', 'catch_records', 'points', 'event_subscriptions', 'event_news'];
    \$existing = 0;
    foreach (\$tables as \$table) {
        if (Schema::hasTable(\$table)) {
            echo \"✅ Table '\$table' exists\n\";
            \$existing++;
        } else {
            echo \"❌ Table '\$table' missing\n\";
        }
    }
    echo \"\n📊 Summary: \$existing/\" . count(\$tables) . \" tables exist\n\";
} catch (Exception \$e) {
    echo \"❌ Error checking tables: \" . \$e->getMessage() . \"\n\";
}
"

echo ""
echo "🎉 MySQL setup completed successfully!"
echo "🎣 FishTrackPro is ready with MySQL database!"
echo ""
echo "📋 Next steps:"
echo "   1. Run: php artisan serve"
echo "   2. Visit: http://localhost:8000"
echo "   3. Check API: http://localhost:8000/api/v1/health"
