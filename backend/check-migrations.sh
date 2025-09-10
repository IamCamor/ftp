#!/bin/bash

echo "🔍 Checking FishTrackPro migrations..."

# Переходим в директорию backend
cd "$(dirname "$0")"

# Проверяем, что мы в правильной директории
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Make sure you're in the backend directory."
    exit 1
fi

echo "📁 Current directory: $(pwd)"

# Проверяем статус миграций
echo "📊 Migration status:"
php artisan migrate:status

echo ""
echo "🔍 Checking for migration conflicts..."

# Проверяем, есть ли проблемы с внешними ключами
echo "🔗 Checking foreign key constraints..."

# Проверяем таблицы
echo "📋 Checking if tables exist:"
php artisan tinker --execute="
try {
    \$tables = ['users', 'events', 'chats', 'event_subscriptions', 'event_news'];
    foreach (\$tables as \$table) {
        if (Schema::hasTable(\$table)) {
            echo \"✅ Table '\$table' exists\n\";
        } else {
            echo \"❌ Table '\$table' missing\n\";
        }
    }
} catch (Exception \$e) {
    echo \"❌ Error checking tables: \" . \$e->getMessage() . \"\n\";
}
"

echo ""
echo "🎯 Migration check completed!"
