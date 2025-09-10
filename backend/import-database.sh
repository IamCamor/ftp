#!/bin/bash

echo "🗄️ Importing FishTrackPro database from SQL dump..."

# Переходим в директорию backend
cd "$(dirname "$0")"

# Проверяем, что мы в правильной директории
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Make sure you're in the backend directory."
    exit 1
fi

echo "📁 Current directory: $(pwd)"

# Проверяем наличие дампа
if [ ! -f "database/fishtrackpro_dump.sql" ]; then
    echo "❌ Error: fishtrackpro_dump.sql not found in database/ directory."
    exit 1
fi

# Получаем параметры подключения к БД из .env
if [ ! -f ".env" ]; then
    echo "❌ Error: .env file not found. Please create it first."
    exit 1
fi

# Читаем параметры БД из .env
DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
DB_PORT=$(grep "^DB_PORT=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")

# Устанавливаем значения по умолчанию
DB_HOST=${DB_HOST:-localhost}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-fishtrackpro}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD:-}

echo "🔗 Database connection parameters:"
echo "   Host: $DB_HOST"
echo "   Port: $DB_PORT"
echo "   Database: $DB_DATABASE"
echo "   Username: $DB_USERNAME"

# Проверяем подключение к MySQL
echo "🔍 Testing MySQL connection..."
if [ -n "$DB_PASSWORD" ]; then
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1;" 2>/dev/null
else
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -e "SELECT 1;" 2>/dev/null
fi

if [ $? -ne 0 ]; then
    echo "❌ Error: Cannot connect to MySQL server."
    echo "💡 Please check your database credentials in .env file."
    exit 1
fi

echo "✅ MySQL connection successful!"

# Создаем базу данных если не существует
echo "📦 Creating database if not exists..."
if [ -n "$DB_PASSWORD" ]; then
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
else
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -e "CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
fi

if [ $? -ne 0 ]; then
    echo "❌ Error: Cannot create database."
    exit 1
fi

echo "✅ Database created/verified!"

# Импортируем дамп
echo "📥 Importing SQL dump..."
if [ -n "$DB_PASSWORD" ]; then
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < database/fishtrackpro_dump.sql
else
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" "$DB_DATABASE" < database/fishtrackpro_dump.sql
fi

if [ $? -ne 0 ]; then
    echo "❌ Error: Failed to import SQL dump."
    exit 1
fi

echo "✅ SQL dump imported successfully!"

# Очищаем кеш Laravel
echo "🧹 Clearing Laravel cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Генерируем ключ приложения если не существует
echo "🔑 Checking application key..."
if ! grep -q "APP_KEY=" .env || grep -q "APP_KEY=$" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Генерируем JWT секрет если не существует
echo "🔐 Checking JWT secret..."
if ! grep -q "JWT_SECRET=" .env || grep -q "JWT_SECRET=$" .env; then
    echo "🔐 Generating JWT secret..."
    php artisan jwt:secret
fi

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
echo "🎉 Database import completed successfully!"
echo "🎣 FishTrackPro database is ready!"
echo ""
echo "📋 Next steps:"
echo "   1. Run: php artisan serve"
echo "   2. Visit: http://localhost:8000"
echo "   3. Check API: http://localhost:8000/api/v1/health"
