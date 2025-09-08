# Настройка среды разработки FishTrackPro

## Проблема: "Failed to fetch" ошибка

Если вы получаете ошибку:
```
Request failed: TypeError: Failed to fetch
```

Это означает, что фронтенд не может подключиться к backend API.

## Решение

### 1. Быстрый запуск для разработки

Используйте созданные скрипты для запуска:

```bash
# Запустить только backend
./scripts/start-backend.sh

# Запустить только frontend
./scripts/start-frontend.sh

# Запустить оба сервера одновременно
./scripts/start-dev.sh
```

### 2. Ручной запуск

#### Backend (Laravel)
```bash
cd backend

# Установить зависимости (если не установлены)
composer install

# Создать .env файл
cp env.example .env

# Сгенерировать ключ приложения
php artisan key:generate

# Запустить сервер
php artisan serve --host=0.0.0.0 --port=8000
```

#### Frontend (React + Vite)
```bash
cd frontend

# Установить зависимости (если не установлены)
npm install

# Создать .env файл
cp env.example .env

# Запустить сервер разработки
npm run dev
```

### 3. Проверка конфигурации

#### Backend (.env)
```env
APP_NAME=FishTrackPro
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fishtrackpro
DB_USERNAME=root
DB_PASSWORD=your_password
```

#### Frontend (.env)
```env
# Development environment variables
VITE_API_BASE=http://localhost:8000/api/v1
VITE_SITE_BASE=http://localhost:5173
VITE_ASSETS_BASE=http://localhost:5173/assets
```

### 4. Проверка доступности API

После запуска backend, проверьте доступность API:

```bash
# Проверить базовый endpoint
curl http://localhost:8000/api/v1

# Проверить health check
curl http://localhost:8000/api/v1/health
```

### 5. Отладка проблем

#### Проверка логов
```bash
# Backend логи
tail -f backend/storage/logs/laravel.log

# Frontend логи (в браузере)
# Откройте Developer Tools (F12) -> Console
```

#### Проверка CORS
Если возникают проблемы с CORS, убедитесь что в backend настроен CORS:

```php
// config/cors.php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:5173'],
'allowed_headers' => ['*'],
```

#### Проверка портов
```bash
# Проверить, что порты свободны
lsof -i :8000  # Backend
lsof -i :5173  # Frontend

# Если порты заняты, убейте процессы
kill -9 <PID>
```

### 6. Структура проекта

```
FishTrackPro/
├── backend/                 # Laravel API
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   └── artisan
├── frontend/               # React SPA
│   ├── src/
│   ├── public/
│   └── package.json
└── scripts/               # Утилиты
    ├── start-backend.sh
    ├── start-frontend.sh
    └── start-dev.sh
```

### 7. Полезные команды

#### Backend
```bash
# Очистить кэш
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Запустить миграции
php artisan migrate

# Заполнить базу демо-данными
php artisan db:seed

# Проверить маршруты
php artisan route:list
```

#### Frontend
```bash
# Собрать для продакшена
npm run build

# Проверить типы
npm run type-check

# Линтинг
npm run lint
```

### 8. Troubleshooting

#### Ошибка: "Could not open input file: artisan"
```bash
cd backend
php artisan --version
```

#### Ошибка: "Module not found"
```bash
# Backend
composer install

# Frontend
npm install
```

#### Ошибка: "Database connection failed"
```bash
# Проверить MySQL
mysql -u root -p

# Создать базу данных
CREATE DATABASE fishtrackpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Ошибка: "Port already in use"
```bash
# Найти процесс
lsof -i :8000
lsof -i :5173

# Убить процесс
kill -9 <PID>
```

### 9. Production настройка

Для продакшена измените переменные окружения:

#### Frontend (.env)
```env
VITE_API_BASE=https://api.fishtrackpro.ru/api/v1
VITE_SITE_BASE=https://www.fishtrackpro.ru
VITE_ASSETS_BASE=https://www.fishtrackpro.ru/assets
```

#### Backend (.env)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.fishtrackpro.ru
```

### 10. Мониторинг

#### Проверка статуса серверов
```bash
# Backend health check
curl http://localhost:8000/api/v1/health

# Frontend доступность
curl http://localhost:5173
```

#### Логи в реальном времени
```bash
# Backend
tail -f backend/storage/logs/laravel.log

# Frontend (в браузере)
# F12 -> Console -> Network
```

## Быстрый старт

1. **Клонируйте репозиторий**
2. **Запустите backend**: `./scripts/start-backend.sh`
3. **Запустите frontend**: `./scripts/start-frontend.sh`
4. **Откройте**: http://localhost:5173

Или используйте: `./scripts/start-dev.sh` для запуска обоих серверов одновременно.

## Поддержка

Если проблемы продолжаются:
1. Проверьте логи в консоли браузера
2. Проверьте логи Laravel в `backend/storage/logs/laravel.log`
3. Убедитесь, что все зависимости установлены
4. Проверьте, что порты 8000 и 5173 свободны
