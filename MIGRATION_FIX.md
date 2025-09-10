# Исправление миграций FishTrackPro

## Проблема

При выполнении миграций возникала ошибка:
```
SQLSTATE[HY000]: General error: 1824 Failed to open the referenced table 'events' 
(Connection: mysql, SQL: alter table `chats` add constraint `chats_event_id_foreign` 
foreign key (`event_id`) references `events` (`id`) on delete cascade)
```

## Причина

Миграция `chats` (000015) выполнялась раньше миграции `events` (000017), но пыталась создать внешний ключ на несуществующую таблицу `events`.

## Решение

### 1. Исправлена миграция chats

**Файл:** `backend/database/migrations/2024_01_01_000015_create_chats_table.php`

**Было:**
```php
$table->foreignId('event_id')->nullable()->constrained('events')->onDelete('cascade');
```

**Стало:**
```php
$table->unsignedBigInteger('event_id')->nullable();
```

### 2. Создана новая миграция для внешнего ключа

**Файл:** `backend/database/migrations/2024_01_01_000019_add_event_foreign_key_to_chats_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
        });
    }
};
```

### 3. Переименованы миграции

Все миграции после `000019` были переименованы, чтобы освободить место для новой миграции внешнего ключа:

- `000019` → `000020` (live_sessions)
- `000020` → `000021` (live_viewers)
- `000021` → `000022` (moderation_fields_users)
- `000022` → `000023` (moderation_fields_catch_records)
- `000023` → `000024` (moderation_fields_points)
- `000024` → `000025` (reports)
- `000025` → `000026` (subscriptions)
- `000026` → `000027` (payments)
- `000027` → `000028` (subscription_fields_users)
- `000028` → `000029` (fish_species)
- `000029` → `000030` (fishing_knots)
- `000030` → `000031` (boats)
- `000031` → `000032` (fishing_methods)
- `000032` → `000033` (fishing_tackle)
- `000033` → `000034` (boat_engines)
- `000034` → `000035` (fishing_locations)
- `000035` → `000036` (landing_pages)
- `000036` → `000037` (push_notifications)
- `000037` → `000038` (device_tokens)
- `000038` → `000039` (reference_fields_catch_records)
- `000039` → `000040` (follows)
- `000040` → `000041` (statistics_users)
- `000041` → `000042` (media_support_catch_records)

## Новый порядок миграций

```
000015 - create_chats_table (без внешнего ключа)
000016 - create_chat_messages_table
000017 - create_events_table
000018 - create_event_participants_table
000019 - add_event_foreign_key_to_chats_table (новый внешний ключ)
000020 - create_live_sessions_table
000021 - create_live_viewers_table
... (остальные миграции)
```

## Скрипты для работы с миграциями

### Сброс базы данных
```bash
cd backend
./reset-database.sh
```

### Проверка миграций
```bash
cd backend
./check-migrations.sh
```

### Ручной сброс
```bash
cd backend
php artisan migrate:reset --force
php artisan migrate --force
php artisan db:seed --force
```

## Проверка исправления

После исправления миграции должны выполняться без ошибок:

1. ✅ Таблица `chats` создается без внешнего ключа
2. ✅ Таблица `events` создается
3. ✅ Внешний ключ `chats.event_id` → `events.id` добавляется
4. ✅ Все остальные миграции выполняются корректно

## Совместимость

- ✅ **MySQL** - полная поддержка
- ✅ **SQLite** - полная поддержка  
- ✅ **PostgreSQL** - полная поддержка

## Тестирование

```bash
# Проверка статуса миграций
php artisan migrate:status

# Проверка структуры таблиц
php artisan tinker
>>> Schema::hasTable('chats')
>>> Schema::hasTable('events')
>>> DB::select('SHOW CREATE TABLE chats')
```

## Результат

После исправления:
- ✅ Миграции выполняются без ошибок
- ✅ Внешние ключи создаются корректно
- ✅ База данных имеет правильную структуру
- ✅ Все связи между таблицами работают
