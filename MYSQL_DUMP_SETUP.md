# Настройка FishTrackPro с MySQL дампом

## Обзор

Вместо использования миграций Laravel, мы создали SQL дамп для быстрого развертывания FishTrackPro с MySQL. Это решает проблемы с порядком миграций и внешними ключами.

## Файлы

- `backend/database/fishtrackpro_dump.sql` - Полный SQL дамп базы данных
- `backend/setup-mysql.sh` - Скрипт автоматической настройки
- `backend/import-database.sh` - Скрипт импорта дампа

## Быстрая установка

### 1. Автоматическая настройка

```bash
cd backend
./setup-mysql.sh
```

Этот скрипт:
- ✅ Создает .env файл с настройками MySQL
- ✅ Проверяет подключение к MySQL
- ✅ Создает базу данных `fishtrackpro`
- ✅ Импортирует SQL дамп
- ✅ Генерирует ключи приложения и JWT
- ✅ Очищает кеш Laravel
- ✅ Проверяет структуру базы данных

### 2. Ручная настройка

#### Шаг 1: Настройка .env

```bash
cd backend
cp env.example .env
```

Отредактируйте `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fishtrackpro
DB_USERNAME=root
DB_PASSWORD=your_password
```

#### Шаг 2: Создание базы данных

```bash
mysql -u root -p
```

```sql
CREATE DATABASE fishtrackpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

#### Шаг 3: Импорт дампа

```bash
mysql -u root -p fishtrackpro < database/fishtrackpro_dump.sql
```

#### Шаг 4: Генерация ключей

```bash
php artisan key:generate
php artisan jwt:secret
```

#### Шаг 5: Очистка кеша

```bash
php artisan config:clear
php artisan cache:clear
```

## Структура базы данных

### Основные таблицы

1. **users** - Пользователи
2. **groups** - Группы
3. **group_members** - Участники групп
4. **events** - Мероприятия
5. **chats** - Чаты
6. **chat_messages** - Сообщения чатов
7. **event_participants** - Участники мероприятий
8. **event_subscriptions** - Подписки на мероприятия
9. **event_news** - Новости мероприятий

### Таблицы рыбалки

1. **catch_records** - Записи уловов
2. **points** - Места рыбалки
3. **catch_likes** - Лайки уловов
4. **catch_comments** - Комментарии к уловам

### Социальные функции

1. **follows** - Подписки на пользователей
2. **notifications** - Уведомления

### Подписки и платежи

1. **subscriptions** - Подписки пользователей
2. **payments** - Платежи
3. **bonus_transactions** - Бонусные транзакции

### Умные часы

1. **biometric_data** - Биометрические данные
2. **fishing_sessions** - Сессии рыбалки

### Справочные данные

1. **fish_species** - Виды рыб
2. **fishing_methods** - Способы ловли

### Системные таблицы

1. **personal_access_tokens** - Токены доступа (Laravel Sanctum)
2. **oauth_identities** - OAuth идентификации

## Внешние ключи

Все внешние ключи настроены правильно:

- `chats.event_id` → `events.id`
- `chats.group_id` → `groups.id`
- `event_subscriptions.event_id` → `events.id`
- `event_subscriptions.user_id` → `users.id`
- `event_news.event_id` → `events.id`
- `event_news.user_id` → `users.id`
- `catch_records.user_id` → `users.id`
- `points.user_id` → `users.id`
- `biometric_data.user_id` → `users.id`
- `biometric_data.session_id` → `fishing_sessions.id`
- `fishing_sessions.user_id` → `users.id`

## Проверка установки

### 1. Проверка таблиц

```bash
php artisan tinker
```

```php
// Проверяем основные таблицы
Schema::hasTable('users');        // true
Schema::hasTable('events');       // true
Schema::hasTable('chats');        // true
Schema::hasTable('catch_records'); // true
Schema::hasTable('points');       // true
```

### 2. Проверка внешних ключей

```sql
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE 
    REFERENCED_TABLE_SCHEMA = 'fishtrackpro'
    AND REFERENCED_TABLE_NAME IS NOT NULL;
```

### 3. Проверка API

```bash
# Запуск сервера
php artisan serve

# Проверка API
curl http://localhost:8000/api/v1/health
```

## Образцы данных

Дамп включает образцы данных:

- **3 вида рыб**: Щука, Окунь, Карп
- **3 способа ловли**: Спиннинг, Поплавочная удочка, Фидер

## Преимущества дампа

1. ✅ **Быстрое развертывание** - один SQL файл
2. ✅ **Правильный порядок** - все таблицы создаются в нужной последовательности
3. ✅ **Внешние ключи** - настроены корректно
4. ✅ **Образцы данных** - включены базовые справочники
5. ✅ **Совместимость** - работает с MySQL 5.7+
6. ✅ **Кодировка** - UTF8MB4 для поддержки эмодзи

## Устранение неполадок

### Ошибка подключения к MySQL

```bash
# Проверка статуса MySQL
sudo systemctl status mysql

# Запуск MySQL
sudo systemctl start mysql

# Проверка подключения
mysql -u root -p -e "SELECT 1;"
```

### Ошибка прав доступа

```sql
-- Создание пользователя для приложения
CREATE USER 'fishtrackpro'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON fishtrackpro.* TO 'fishtrackpro'@'localhost';
FLUSH PRIVILEGES;
```

### Ошибка кодировки

```sql
-- Проверка кодировки базы данных
SHOW VARIABLES LIKE 'character_set%';
SHOW VARIABLES LIKE 'collation%';

-- Установка правильной кодировки
ALTER DATABASE fishtrackpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Миграция с SQLite

Если у вас была база данных SQLite:

1. Экспортируйте данные из SQLite
2. Импортируйте в MySQL
3. Обновите .env файл
4. Очистите кеш Laravel

## Резервное копирование

```bash
# Создание резервной копии
mysqldump -u root -p fishtrackpro > fishtrackpro_backup_$(date +%Y%m%d_%H%M%S).sql

# Восстановление из резервной копии
mysql -u root -p fishtrackpro < fishtrackpro_backup_20240910_120000.sql
```

## Производительность

Для улучшения производительности:

1. **Индексы** - все необходимые индексы созданы
2. **Внешние ключи** - настроены для целостности данных
3. **Кодировка** - UTF8MB4 для полной поддержки Unicode
4. **Типы данных** - оптимизированы для MySQL

## Безопасность

1. **Пароли** - используйте надежные пароли для MySQL
2. **Пользователи** - создайте отдельного пользователя для приложения
3. **Права доступа** - ограничьте права доступа
4. **SSL** - используйте SSL соединения в продакшене

## Заключение

SQL дамп обеспечивает быстрое и надежное развертывание FishTrackPro с MySQL, решая все проблемы с миграциями и внешними ключами.
