# FishTrackPro Events System

Система мероприятий для FishTrackPro, включающая выставки, соревнования, мастер-классы и встречи с возможностью подписки, уведомлений и AI модерации.

## 🎯 Основные возможности

### Для пользователей:
- **Просмотр мероприятий** - каталог всех доступных мероприятий
- **Поиск и фильтрация** - по типу, городу, региону, радиусу
- **Подписка на мероприятия** - получение уведомлений и новостей
- **Скрытие мероприятий** - отключение уведомлений без отписки
- **Подтверждение участия** - отметка о планируемом участии
- **Настройка уведомлений** - push, email, SMS

### Для Premium пользователей:
- **Создание мероприятий** - только Premium могут создавать
- **Управление новостями** - публикация новостей о мероприятии
- **Закрепление постов** - приоритетные новости в ленте
- **Детальная аналитика** - статистика по мероприятию

### AI модерация:
- **Автоматическая модерация** - контента мероприятий и новостей
- **Множественные провайдеры** - YandexGPT, GigaChat, ChatGPT, DeepSeek
- **Настраиваемые правила** - гибкая конфигурация модерации

## 🏗️ Архитектура

### Модели данных:

#### Event (Мероприятия)
```php
- id, title, description
- type (exhibition, competition, workshop, meeting)
- organizer, contact_email, contact_phone, website
- address, city, region, country
- latitude, longitude, radius_km
- registration_start, registration_end
- event_start, event_end, is_all_day
- max_participants, current_participants
- entry_fee, currency, requires_registration
- is_public, status, moderation_status
- cover_image, gallery, documents
- rules, prizes, schedule
- views_count, subscribers_count, shares_count
- rating, reviews_count
- notifications_enabled, reminders_enabled
- allow_comments, allow_sharing
- tags, categories
```

#### EventSubscription (Подписки)
```php
- user_id, event_id
- status (subscribed, unsubscribed, hidden)
- notifications_enabled, reminders_enabled, news_enabled
- reminder_hours_before
- email_notifications, push_notifications, sms_notifications
- notes, is_attending, attending_confirmed_at
- subscribed_at, last_notification_at
```

#### EventNews (Новости мероприятий)
```php
- event_id, user_id
- title, content, excerpt
- cover_image, gallery, attachments
- type (announcement, update, reminder, result, photo_report, other)
- priority (low, normal, high, urgent)
- status, moderation_status
- published_at, scheduled_at, is_pinned
- views_count, likes_count, shares_count, comments_count
- allow_comments, allow_sharing, send_notifications
- tags
```

## 🔌 API Endpoints

### Мероприятия

#### Публичные маршруты:
```http
GET /api/v1/events - Список мероприятий с фильтрами
GET /api/v1/events/nearby - Мероприятия рядом с пользователем
GET /api/v1/events/{id} - Детали мероприятия
```

#### Аутентифицированные маршруты:
```http
POST /api/v1/events - Создать мероприятие (Premium)
PUT /api/v1/events/{id} - Обновить мероприятие (Premium)
DELETE /api/v1/events/{id} - Удалить мероприятие (Premium)
```

### Подписки на мероприятия

```http
GET /api/v1/events/subscriptions/my - Мои подписки
POST /api/v1/events/{eventId}/subscribe - Подписаться
POST /api/v1/events/{eventId}/unsubscribe - Отписаться
POST /api/v1/events/{eventId}/hide - Скрыть мероприятие
POST /api/v1/events/{eventId}/unhide - Показать мероприятие
PUT /api/v1/events/{eventId}/subscription/settings - Настройки подписки
POST /api/v1/events/{eventId}/attendance/confirm - Подтвердить участие
POST /api/v1/events/{eventId}/attendance/cancel - Отменить участие
```

### Новости мероприятий

```http
GET /api/v1/events/{eventId}/news - Новости мероприятия
GET /api/v1/events/{eventId}/news/{newsId} - Детали новости
POST /api/v1/events/{eventId}/news - Создать новость (Premium)
PUT /api/v1/events/{eventId}/news/{newsId} - Обновить новость (Premium)
DELETE /api/v1/events/{eventId}/news/{newsId} - Удалить новость (Premium)
POST /api/v1/events/{eventId}/news/{newsId}/pin - Закрепить новость (Premium)
POST /api/v1/events/{eventId}/news/{newsId}/unpin - Открепить новость (Premium)
```

### Админские маршруты:

```http
GET /api/v1/admin/events/statistics - Статистика мероприятий
GET /api/v1/admin/events/pending - Ожидающие модерации
POST /api/v1/admin/events/{id}/approve - Одобрить мероприятие
POST /api/v1/admin/events/{id}/reject - Отклонить мероприятие
GET /api/v1/admin/events/{id}/subscriptions - Подписки на мероприятие
GET /api/v1/admin/events/{id}/news - Новости мероприятия
GET /api/v1/admin/events/{eventId}/news/pending - Ожидающие модерации новости
POST /api/v1/admin/events/{eventId}/news/{newsId}/approve - Одобрить новость
POST /api/v1/admin/events/{eventId}/news/{newsId}/reject - Отклонить новость
```

## 📊 Фильтры и поиск

### Параметры запроса для списка мероприятий:

```http
GET /api/v1/events?type=exhibition&city=Москва&radius_km=50&upcoming=true&search=рыбалка
```

**Доступные параметры:**
- `type` - тип мероприятия (exhibition, competition, workshop, meeting)
- `status` - статус (draft, published, cancelled, completed)
- `city` - город
- `region` - регион
- `latitude, longitude` - координаты для поиска по радиусу
- `radius_km` - радиус поиска в км (по умолчанию 50)
- `upcoming` - только предстоящие мероприятия
- `search` - поиск по названию, описанию, организатору
- `page` - номер страницы
- `per_page` - количество на странице (максимум 100)

### Поиск по радиусу:

```http
GET /api/v1/events/nearby?latitude=55.7558&longitude=37.6176&radius_km=25&limit=20
```

## 🔔 Система уведомлений

### Типы уведомлений:

1. **Уведомления о мероприятии:**
   - Новые мероприятия в радиусе
   - Изменения в мероприятии
   - Напоминания о начале

2. **Уведомления о новостях:**
   - Новые новости от подписанных мероприятий
   - Важные объявления (высокий приоритет)
   - Результаты соревнований

3. **Напоминания:**
   - За 24 часа до начала (настраивается)
   - За 1 час до начала
   - Начало регистрации

### Каналы уведомлений:

- **Push уведомления** - через мобильное приложение
- **Email уведомления** - на почту пользователя
- **SMS уведомления** - на телефон (платная функция)

### Настройки пользователя:

```json
{
  "notifications_enabled": true,
  "reminders_enabled": true,
  "news_enabled": true,
  "reminder_hours_before": 24,
  "email_notifications": false,
  "push_notifications": true,
  "sms_notifications": false
}
```

## 🤖 AI Модерация

### Контент для модерации:

1. **Мероприятия:**
   - Название и описание
   - Правила и призы
   - Изображения (обложка, галерея)

2. **Новости мероприятий:**
   - Заголовок и содержание
   - Изображения и вложения
   - Теги и категории

### Провайдеры AI:

- **YandexGPT** - основной провайдер для русскоязычного контента
- **GigaChat** - альтернативный провайдер
- **ChatGPT** - для международного контента
- **DeepSeek** - резервный провайдер

### Статусы модерации:

- `pending` - ожидает модерации
- `approved` - одобрено
- `rejected` - отклонено
- `pending_review` - требует проверки администратором

## 📱 Интеграция с мобильным приложением

### Основные экраны:

1. **Каталог мероприятий:**
   - Список с фильтрами
   - Карта с мероприятиями
   - Поиск по геолокации

2. **Детали мероприятия:**
   - Полная информация
   - Кнопка подписки
   - Новости мероприятия
   - Участники

3. **Мои подписки:**
   - Список подписанных мероприятий
   - Настройки уведомлений
   - Календарь событий

4. **Создание мероприятия (Premium):**
   - Форма создания
   - Загрузка изображений
   - Настройка уведомлений

### Уведомления в приложении:

- **In-app уведомления** - в центре уведомлений
- **Push уведомления** - системные уведомления
- **Бейджи** - счетчики непрочитанных

## 🔧 Технические детали

### Индексы базы данных:

```sql
-- Основные индексы для производительности
INDEX idx_events_type_status (type, status)
INDEX idx_events_event_start_end (event_start, event_end)
INDEX idx_events_latitude_longitude (latitude, longitude)
INDEX idx_events_city_region (city, region)
INDEX idx_events_moderation_status (moderation_status)

INDEX idx_event_subscriptions_user_status (user_id, status)
INDEX idx_event_subscriptions_event_status (event_id, status)
INDEX idx_event_subscriptions_subscribed_at (subscribed_at)

INDEX idx_event_news_event_status (event_id, status)
INDEX idx_event_news_published_at (published_at)
INDEX idx_event_news_is_pinned (is_pinned)
INDEX idx_event_news_moderation_status (moderation_status)
```

### Кэширование:

- **Список мероприятий** - кэш на 5 минут
- **Популярные мероприятия** - кэш на 1 час
- **Статистика** - кэш на 30 минут
- **Настройки пользователя** - кэш на 15 минут

### Ограничения:

- **Создание мероприятий** - только Premium пользователи
- **Количество мероприятий** - максимум 10 активных на пользователя
- **Размер описания** - максимум 5000 символов
- **Количество изображений** - максимум 10 в галерее
- **Размер файлов** - максимум 5MB на изображение

## 🚀 Развертывание

### Миграции:

```bash
php artisan migrate
```

### Сидеры:

```bash
php artisan db:seed --class=EventTypesSeeder
php artisan db:seed --class=EventCategoriesSeeder
```

### Настройка AI модерации:

```bash
# Настройка провайдеров в .env
YANDEX_GPT_API_KEY=your_key
GIGACHAT_API_KEY=your_key
CHATGPT_API_KEY=your_key
DEEPSEEK_API_KEY=your_key
```

### Настройка уведомлений:

```bash
# Настройка Telegram бота для уведомлений
TELEGRAM_BOT_TOKEN=your_token
TELEGRAM_CHAT_ID=your_chat_id

# Настройка email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
```

## 📈 Мониторинг и аналитика

### Метрики для отслеживания:

- **Количество мероприятий** - созданных, опубликованных, завершенных
- **Подписки** - активные, скрытые, отписанные
- **Уведомления** - отправленные, доставленные, прочитанные
- **Модерация** - время обработки, процент одобрения
- **География** - популярные города и регионы

### Дашборд администратора:

- **Общая статистика** - мероприятия, подписки, новости
- **Модерация** - очередь, статистика по провайдерам
- **Популярные мероприятия** - по просмотрам и подпискам
- **География** - карта с мероприятиями
- **Пользователи** - активность, предпочтения

## 🔒 Безопасность

### Права доступа:

- **Обычные пользователи** - просмотр, подписка, настройки
- **Premium пользователи** - создание мероприятий, новостей
- **Администраторы** - модерация, управление, статистика

### Валидация данных:

- **Sanitization** - очистка HTML контента
- **Validation** - проверка типов и ограничений
- **Rate limiting** - ограничение частоты запросов
- **CSRF protection** - защита от CSRF атак

### Модерация контента:

- **Автоматическая** - AI провайдеры
- **Ручная** - администраторы
- **Жалобы пользователей** - система репортов
- **Апелляции** - возможность обжалования

---

**FishTrackPro Events System** - полнофункциональная система мероприятий с AI модерацией, геолокацией и гибкими настройками уведомлений! 🎣📅🤖✨
