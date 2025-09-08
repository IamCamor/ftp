#!/bin/bash

echo "🔍 Проверка зависимостей FishTrackPro..."
echo ""

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Функция проверки команды
check_command() {
    if command -v $1 &> /dev/null; then
        echo -e "${GREEN}✅ $1${NC} - $(command -v $1)"
        return 0
    else
        echo -e "${RED}❌ $1${NC} - не найден"
        return 1
    fi
}

# Функция проверки версии
check_version() {
    if command -v $1 &> /dev/null; then
        version=$($1 --version 2>/dev/null | head -n1)
        echo -e "${GREEN}✅ $1${NC} - $version"
        return 0
    else
        echo -e "${RED}❌ $1${NC} - не найден"
        return 1
    fi
}

echo "📋 Системные требования:"
echo "========================"

# Проверка PHP
echo ""
echo "🐘 PHP:"
if check_version "php"; then
    php_version=$(php -r "echo PHP_VERSION;")
    if [[ $(echo "$php_version 8.2" | awk '{print ($1 >= $2)}') == 1 ]]; then
        echo -e "   ${GREEN}✅ Версия PHP $php_version подходит${NC}"
    else
        echo -e "   ${YELLOW}⚠️  Требуется PHP 8.2+, текущая версия: $php_version${NC}"
    fi
fi

# Проверка Composer
echo ""
echo "📦 Composer:"
check_version "composer"

# Проверка Node.js
echo ""
echo "🟢 Node.js:"
if check_version "node"; then
    node_version=$(node --version | sed 's/v//')
    if [[ $(echo "$node_version 18.0" | awk '{print ($1 >= $2)}') == 1 ]]; then
        echo -e "   ${GREEN}✅ Версия Node.js $node_version подходит${NC}"
    else
        echo -e "   ${YELLOW}⚠️  Рекомендуется Node.js 18+, текущая версия: $node_version${NC}"
    fi
fi

# Проверка npm
echo ""
echo "📦 npm:"
check_version "npm"

# Проверка MySQL
echo ""
echo "🗄️  MySQL:"
check_command "mysql"

# Проверка Redis
echo ""
echo "🔴 Redis:"
check_command "redis-server"

echo ""
echo "📁 Проверка структуры проекта:"
echo "=============================="

# Проверка папок
if [ -d "backend" ]; then
    echo -e "${GREEN}✅ Папка backend${NC}"
else
    echo -e "${RED}❌ Папка backend не найдена${NC}"
fi

if [ -d "frontend" ]; then
    echo -e "${GREEN}✅ Папка frontend${NC}"
else
    echo -e "${RED}❌ Папка frontend не найдена${NC}"
fi

# Проверка файлов
if [ -f "backend/composer.json" ]; then
    echo -e "${GREEN}✅ backend/composer.json${NC}"
else
    echo -e "${RED}❌ backend/composer.json не найден${NC}"
fi

if [ -f "frontend/package.json" ]; then
    echo -e "${GREEN}✅ frontend/package.json${NC}"
else
    echo -e "${RED}❌ frontend/package.json не найден${NC}"
fi

echo ""
echo "🔧 Проверка установленных зависимостей:"
echo "======================================"

# Проверка backend зависимостей
if [ -d "backend/vendor" ]; then
    echo -e "${GREEN}✅ Backend зависимости установлены${NC}"
else
    echo -e "${YELLOW}⚠️  Backend зависимости не установлены${NC}"
    echo "   Запустите: cd backend && composer install"
fi

# Проверка frontend зависимостей
if [ -d "frontend/node_modules" ]; then
    echo -e "${GREEN}✅ Frontend зависимости установлены${NC}"
else
    echo -e "${YELLOW}⚠️  Frontend зависимости не установлены${NC}"
    echo "   Запустите: cd frontend && npm install"
fi

echo ""
echo "⚙️  Проверка конфигурации:"
echo "========================="

# Проверка .env файла
if [ -f "backend/.env" ]; then
    echo -e "${GREEN}✅ backend/.env файл существует${NC}"
    
    # Проверка ключевых настроек
    if grep -q "APP_KEY=" backend/.env && ! grep -q "APP_KEY=$" backend/.env; then
        echo -e "${GREEN}✅ APP_KEY настроен${NC}"
    else
        echo -e "${YELLOW}⚠️  APP_KEY не настроен${NC}"
        echo "   Запустите: cd backend && php artisan key:generate"
    fi
    
    if grep -q "DB_DATABASE=" backend/.env; then
        echo -e "${GREEN}✅ База данных настроена${NC}"
    else
        echo -e "${YELLOW}⚠️  База данных не настроена${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  backend/.env файл не найден${NC}"
    echo "   Скопируйте: cp backend/env.example backend/.env"
fi

echo ""
echo "🌐 Проверка портов:"
echo "=================="

# Проверка портов
check_port() {
    if lsof -i :$1 &> /dev/null; then
        echo -e "${YELLOW}⚠️  Порт $1 занят${NC}"
        return 1
    else
        echo -e "${GREEN}✅ Порт $1 свободен${NC}"
        return 0
    fi
}

check_port 8000  # Laravel
check_port 3000  # React dev server
check_port 3306  # MySQL
check_port 6379  # Redis

echo ""
echo "📊 Итоговая оценка:"
echo "=================="

# Подсчет ошибок
errors=0
warnings=0

# Проверка критических зависимостей
if ! command -v php &> /dev/null; then ((errors++)); fi
if ! command -v composer &> /dev/null; then ((errors++)); fi
if ! command -v node &> /dev/null; then ((errors++)); fi
if ! command -v npm &> /dev/null; then ((errors++)); fi

# Проверка структуры
if [ ! -d "backend" ]; then ((errors++)); fi
if [ ! -d "frontend" ]; then ((errors++)); fi

# Проверка зависимостей
if [ ! -d "backend/vendor" ]; then ((warnings++)); fi
if [ ! -d "frontend/node_modules" ]; then ((warnings++)); fi

# Вывод результата
if [ $errors -eq 0 ]; then
    if [ $warnings -eq 0 ]; then
        echo -e "${GREEN}🎉 Все проверки пройдены успешно!${NC}"
        echo -e "${GREEN}✅ Проект готов к запуску${NC}"
    else
        echo -e "${YELLOW}⚠️  Есть предупреждения ($warnings)${NC}"
        echo -e "${YELLOW}💡 Установите зависимости для полной готовности${NC}"
    fi
else
    echo -e "${RED}❌ Найдены критические ошибки ($errors)${NC}"
    echo -e "${RED}🚫 Проект не готов к запуску${NC}"
fi

echo ""
echo "🚀 Следующие шаги:"
echo "=================="

if [ $errors -gt 0 ]; then
    echo "1. Установите недостающие системные зависимости"
    echo "2. Запустите проверку снова"
elif [ $warnings -gt 0 ]; then
    echo "1. Запустите: ./install.sh"
    echo "2. Настройте .env файл"
    echo "3. Создайте базу данных"
    echo "4. Запустите миграции"
else
    echo "1. Настройте .env файл (если не настроен)"
    echo "2. Создайте базу данных"
    echo "3. Запустите миграции: cd backend && php artisan migrate"
    echo "4. Запустите backend: cd backend && php artisan serve"
    echo "5. Запустите frontend: cd frontend && npm run dev"
fi

echo ""
echo "📚 Дополнительная информация:"
echo "============================"
echo "• README.md - общая документация"
echo "• DEPLOYMENT.md - инструкции по развертыванию"
echo "• FIXES.md - исправления ошибок"

