#!/bin/bash

echo "🌊 Заполнение базы данных демо данными FishTrackPro..."
echo ""

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Проверка наличия .env файла
if [ ! -f "backend/.env" ]; then
    echo -e "${RED}❌ Файл backend/.env не найден${NC}"
    echo "Создайте .env файл: cp backend/env.example backend/.env"
    exit 1
fi

# Проверка подключения к базе данных
echo -e "${BLUE}🔍 Проверка подключения к базе данных...${NC}"
cd backend

# Проверка наличия artisan
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Файл artisan не найден${NC}"
    echo "Убедитесь, что вы находитесь в папке backend"
    exit 1
fi

# Генерация APP_KEY если не установлен
echo -e "${BLUE}🔑 Проверка APP_KEY...${NC}"
if ! grep -q "APP_KEY=base64:" .env; then
    echo -e "${YELLOW}⚠️  Генерируем APP_KEY...${NC}"
    php artisan key:generate
fi

# Запуск миграций
echo -e "${BLUE}🗄️  Запуск миграций...${NC}"
php artisan migrate --force

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Ошибка при выполнении миграций${NC}"
    echo "Проверьте настройки базы данных в .env файле"
    exit 1
fi

# Запуск сидеров
echo -e "${BLUE}🌱 Запуск сидеров...${NC}"
php artisan db:seed --force

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}🎉 Демо данные успешно добавлены в базу данных!${NC}"
    echo ""
    echo -e "${GREEN}📊 Созданные данные:${NC}"
    echo -e "${GREEN}  • 10 пользователей${NC}"
    echo -e "${GREEN}  • 15 точек на карте${NC}"
    echo -e "${GREEN}  • 53 улова${NC}"
    echo -e "${GREEN}  • 10 групп${NC}"
    echo -e "${GREEN}  • 10 событий${NC}"
    echo -e "${GREEN}  • Лайки и комментарии${NC}"
    echo -e "${GREEN}  • Уведомления${NC}"
    echo ""
    echo -e "${BLUE}👤 Тестовые аккаунты:${NC}"
    echo -e "${BLUE}  • admin@fishtrackpro.com / admin123 (администратор)${NC}"
    echo -e "${BLUE}  • test@example.com / test123 (тестовый пользователь)${NC}"
    echo -e "${BLUE}  • alex@example.com / password123 (Александр Рыболов)${NC}"
    echo -e "${BLUE}  • maria@example.com / password123 (Мария Удачливая)${NC}"
    echo ""
    echo -e "${YELLOW}🚀 Теперь можно запустить приложение:${NC}"
    echo -e "${YELLOW}  Backend: cd backend && php artisan serve${NC}"
    echo -e "${YELLOW}  Frontend: cd frontend && npm run dev${NC}"
else
    echo -e "${RED}❌ Ошибка при заполнении базы данных${NC}"
    exit 1
fi

cd ..

