#!/bin/bash

# Скрипт для обработки загруженного логотипа

echo "🔍 Поиск логотипа..."

# Проверяем разные возможные места
if [ -f "/var/www/ftp/logo.png" ]; then
    echo "✅ Найден логотип в корне проекта"
    cp /var/www/ftp/logo.png /var/www/ftp/frontend/public/logo.png
    echo "📋 Скопирован в frontend/public/logo.png"
elif [ -f "/var/www/ftp/uploads/logo.png" ]; then
    echo "✅ Найден логотип в папке uploads"
    cp /var/www/ftp/uploads/logo.png /var/www/ftp/frontend/public/logo.png
    echo "📋 Скопирован в frontend/public/logo.png"
elif [ -f "/var/www/ftp/frontend/public/logo.png" ]; then
    echo "✅ Логотип уже в правильном месте"
else
    echo "❌ Логотип не найден!"
    echo "📁 Загрузите файл logo.png в одну из папок:"
    echo "   - /var/www/ftp/logo.png"
    echo "   - /var/www/ftp/uploads/logo.png"
    echo "   - /var/www/ftp/frontend/public/logo.png"
    exit 1
fi

# Проверяем размер файла
FILE_SIZE=$(stat -c%s "/var/www/ftp/frontend/public/logo.png")
if [ "$FILE_SIZE" -eq 0 ]; then
    echo "❌ Файл логотипа пустой (0 байт)!"
    echo "📁 Загрузите правильный PNG файл"
    exit 1
fi

echo "📊 Размер файла: $FILE_SIZE байт"

# Собираем проект
echo "🏗️ Сборка проекта..."
cd /var/www/ftp/frontend
npm run build

if [ $? -eq 0 ]; then
    echo "✅ Проект успешно собран!"
    echo "🌐 Логотип доступен по адресу: https://fishtrackpro.ru/logo.png"
else
    echo "❌ Ошибка при сборке проекта"
    exit 1
fi


