# 🚀 FishTrackPro Deployment Guide

## 🔧 Исправление ошибки "This script should not be run as root"

### Вариант 1: Локальная разработка

```bash
# Перейти в папку frontend
cd frontend

# Запустить безопасным способом
npm run dev:safe

# Или обычным способом (если не root)
npm run dev
```

### Вариант 2: Docker (рекомендуется для продакшена)

```bash
# Запустить весь стек
docker-compose up -d

# Или только frontend
cd frontend
docker build -t fishtrackpro-frontend .
docker run -p 5173:5173 fishtrackpro-frontend
```

### Вариант 3: Создание пользователя (для сервера)

```bash
# Создать пользователя для приложения
sudo adduser fishtrackpro
sudo usermod -aG sudo fishtrackpro

# Переключиться на пользователя
su - fishtrackpro

# Клонировать и запустить
git clone <repository>
cd fishtrackpro/frontend
npm install
npm run dev
```

### Вариант 4: Использование nvm (Node Version Manager)

```bash
# Установить nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

# Перезагрузить терминал
source ~/.bashrc

# Установить Node.js
nvm install 18.17.0
nvm use 18.17.0

# Запустить приложение
npm run dev
```

## 🌐 Доступ к приложению

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000/api/v1
- **Backend Web**: http://localhost:8000

## 🔍 Проверка статуса

```bash
# Проверить процессы
ps aux | grep -E "(vite|php|artisan)"

# Проверить порты
netstat -tlnp | grep -E "(5173|8000)"

# Проверить логи
docker-compose logs -f
```

## 🛠️ Troubleshooting

### Ошибка "This script should not be run as root"

1. **Используйте безопасный скрипт**: `npm run dev:safe`
2. **Создайте пользователя**: `adduser fishtrackpro`
3. **Используйте Docker**: `docker-compose up`
4. **Установите .npmrc**: файл уже создан с `unsafe-perm=true`

### Ошибка подключения к API

1. **Проверьте backend**: `curl http://localhost:8000/api/v1/languages`
2. **Проверьте CORS**: настройки в `backend/config/cors.php`
3. **Проверьте переменные**: `VITE_API_BASE` в `.env`

### Ошибки TypeScript/ESLint

1. **Проверьте типы**: `npx tsc --noEmit`
2. **Проверьте линтер**: `npm run lint`
3. **Исправьте ошибки**: все критические ошибки уже исправлены

## 📝 Переменные окружения

### Frontend (.env)
```env
VITE_API_BASE=http://localhost:8000/api/v1
VITE_SITE_BASE=http://localhost:5173
VITE_ASSETS_BASE=http://localhost:5173/assets
```

### Backend (.env)
```env
APP_ENV=production
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
JWT_SECRET=your-jwt-secret-here
```

## 🎯 Готовые команды

```bash
# Полный запуск (Docker)
docker-compose up -d

# Локальная разработка
cd backend && php artisan serve &
cd frontend && npm run dev:safe

# Проверка статуса
curl -I http://localhost:8000/api/v1/languages
curl -I http://localhost:5173
```