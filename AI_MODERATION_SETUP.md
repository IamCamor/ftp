# AI Модерация FishTrackPro

Система AI модерации для автоматической проверки фото и комментариев к местам и уловам через различные AI провайдеры.

## 🤖 Поддерживаемые AI провайдеры

| Провайдер | Статус | Модель | Особенности |
|-----------|--------|--------|-------------|
| **YandexGPT** | ✅ | yandexgpt | Российский провайдер, быстрый |
| **GigaChat** | ✅ | GigaChat | Сбербанк, поддержка русского |
| **ChatGPT** | ✅ | gpt-4 | OpenAI, высокая точность |
| **DeepSeek** | ✅ | deepseek-chat | Экономичный, хорошее качество |

## 🏗️ Архитектура

### Backend компоненты

1. **Конфигурация**: `config/ai_moderation.php`
2. **Сервис**: `AIModerationService` - основная логика модерации
3. **События**: `ContentModerationRequested`, `ContentModerationCompleted`
4. **Слушатели**: `ProcessContentModeration`, `HandleModerationResult`
5. **Middleware**: `ContentModeration` - автоматическая модерация
6. **Контроллер**: `ModerationController` - API endpoints
7. **Модели**: обновлены для хранения статуса модерации

### Типы контента для модерации

- **Фото уловов** (`catch_photos`)
- **Комментарии к уловам** (`catch_comments`)
- **Описания уловов** (`catch_descriptions`)
- **Описания точек** (`point_descriptions`)
- **Комментарии к точкам** (`point_comments`)
- **Фото точек** (`point_photos`)

## ⚙️ Конфигурация

### Основные настройки

```php
// config/ai_moderation.php
'enabled' => env('AI_MODERATION_ENABLED', true),

'features' => [
    'photo_moderation' => env('AI_PHOTO_MODERATION', true),
    'comment_moderation' => env('AI_COMMENT_MODERATION', true),
    'catch_moderation' => env('AI_CATCH_MODERATION', true),
    'point_moderation' => env('AI_POINT_MODERATION', true),
    'auto_approve' => env('AI_AUTO_APPROVE', false),
    'auto_reject' => env('AI_AUTO_REJECT', false),
    'manual_review' => env('AI_MANUAL_REVIEW', true),
],
```

### Настройка провайдеров

```php
'providers' => [
    'yandexgpt' => [
        'enabled' => env('YANDEX_GPT_ENABLED', false),
        'api_key' => env('YANDEX_GPT_API_KEY'),
        'folder_id' => env('YANDEX_GPT_FOLDER_ID'),
        'model' => env('YANDEX_GPT_MODEL', 'yandexgpt'),
        'temperature' => env('YANDEX_GPT_TEMPERATURE', 0.1),
        'max_tokens' => env('YANDEX_GPT_MAX_TOKENS', 1000),
        'timeout' => env('YANDEX_GPT_TIMEOUT', 30),
    ],
    // ... другие провайдеры
],
```

### Пороги принятия решений

```php
'thresholds' => [
    'auto_approve_confidence' => env('AI_AUTO_APPROVE_CONFIDENCE', 0.9),
    'auto_reject_confidence' => env('AI_AUTO_REJECT_CONFIDENCE', 0.8),
    'manual_review_confidence' => env('AI_MANUAL_REVIEW_CONFIDENCE', 0.7),
],
```

## 🔧 Настройка провайдеров

### YandexGPT

1. Получите API ключ в [Yandex Cloud](https://cloud.yandex.ru/)
2. Создайте папку для модели
3. Настройте переменные окружения:

```env
YANDEX_GPT_ENABLED=true
YANDEX_GPT_API_KEY=your_api_key
YANDEX_GPT_FOLDER_ID=your_folder_id
YANDEX_GPT_MODEL=yandexgpt
```

### GigaChat

1. Получите API ключ в [GigaChat](https://developers.sber.ru/portal/products/gigachat)
2. Настройте переменные окружения:

```env
GIGACHAT_ENABLED=true
GIGACHAT_API_KEY=your_api_key
GIGACHAT_MODEL=GigaChat
```

### ChatGPT

1. Получите API ключ в [OpenAI](https://platform.openai.com/)
2. Настройте переменные окружения:

```env
CHATGPT_ENABLED=true
CHATGPT_API_KEY=your_api_key
CHATGPT_MODEL=gpt-4
```

### DeepSeek

1. Получите API ключ в [DeepSeek](https://platform.deepseek.com/)
2. Настройте переменные окружения:

```env
DEEPSEEK_ENABLED=true
DEEPSEEK_API_KEY=your_api_key
DEEPSEEK_MODEL=deepseek-chat
```

## 🔌 API Endpoints

### Публичные маршруты

```http
POST /api/v1/moderation/moderate-text
```
Модерация текстового контента

**Тело запроса:**
```json
{
    "text": "Текст для модерации",
    "content_type": "catch_comments"
}
```

```http
POST /api/v1/moderation/moderate-image
```
Модерация изображений

**Тело запроса:**
```json
{
    "image_path": "path/to/image.jpg",
    "content_type": "catch_photos"
}
```

```http
POST /api/v1/moderation/request
```
Запрос модерации контента

**Тело запроса:**
```json
{
    "content_type": "catch_photos",
    "content_id": "123",
    "content": "path/to/image.jpg",
    "format": "image"
}
```

### Админские маршруты

```http
GET /api/admin/moderation/statistics
```
Получить статистику модерации

```http
GET /api/admin/moderation/pending
```
Получить контент, ожидающий модерации

```http
POST /api/admin/moderation/approve
```
Одобрить контент

**Тело запроса:**
```json
{
    "content_type": "catch",
    "content_id": 123
}
```

```http
POST /api/admin/moderation/reject
```
Отклонить контент

**Тело запроса:**
```json
{
    "content_type": "catch",
    "content_id": 123,
    "reason": "Причина отклонения"
}
```

```http
GET /api/admin/moderation/config
```
Получить конфигурацию модерации

```http
POST /api/admin/moderation/test-provider
```
Тестировать подключение к провайдеру

```http
POST /api/admin/moderation/clear-cache
```
Очистить кэш модерации

## 🎯 Автоматическая модерация

### Middleware

Middleware `ContentModeration` автоматически запускает модерацию при создании контента:

- **Уловы** - модерация описания и фото
- **Комментарии** - модерация текста
- **Точки** - модерация описания и фото

### События

1. **ContentModerationRequested** - запрос модерации
2. **ContentModerationCompleted** - завершение модерации

### Слушатели

1. **ProcessContentModeration** - обработка модерации
2. **HandleModerationResult** - применение результатов

## 📊 Статусы модерации

| Статус | Описание | Действие |
|--------|----------|----------|
| `pending` | Ожидает модерации | Контент скрыт |
| `approved` | Одобрено | Контент виден |
| `rejected` | Отклонено | Контент скрыт |
| `pending_review` | Требует ручной проверки | Контент виден, но помечен |

## 🔍 Категории контента

### Для изображений
- `explicit_content` - откровенный контент
- `violence` - насилие
- `hate_speech` - разжигание ненависти
- `spam` - спам
- `inappropriate` - неподходящий контент

### Для текста
- `hate_speech` - разжигание ненависти
- `harassment` - домогательства
- `spam` - спам
- `inappropriate` - неподходящий контент
- `offensive` - оскорбительный контент
- `adult_content` - контент для взрослых
- `violence` - насилие
- `illegal_activities` - незаконная деятельность

## 🚀 Использование

### Программная модерация

```php
use App\Services\AIModerationService;

$moderationService = app(AIModerationService::class);

// Модерация текста
$result = $moderationService->moderateText(
    'Текст для проверки',
    'catch_comments'
);

// Модерация изображения
$result = $moderationService->moderateImage(
    'path/to/image.jpg',
    'catch_photos'
);
```

### Результат модерации

```php
[
    'approved' => true,
    'confidence' => 0.95,
    'reason' => 'Контент соответствует правилам',
    'categories' => [],
    'raw_response' => '...'
]
```

### События

```php
use App\Events\ContentModerationRequested;

// Запрос модерации
event(new ContentModerationRequested(
    'catch_photos',
    '123',
    'path/to/image.jpg',
    'image',
    $userId
));
```

## 📈 Мониторинг и логирование

### Логи

Все операции модерации логируются:

```php
Log::info('AI moderation completed', [
    'content_type' => 'catch_photos',
    'approved' => true,
    'confidence' => 0.95,
    'provider' => 'yandexgpt'
]);
```

### Уведомления

- **Telegram** - уведомления о отклоненном контенте
- **Email** - уведомления админам о ручной проверке
- **Push** - уведомления пользователям о статусе

### Метрики

Отслеживайте:
- Количество модераций по провайдерам
- Время ответа AI
- Точность модерации
- Количество ложных срабатываний

## 🛠️ Troubleshooting

### Проблемы с API

1. **Ошибка аутентификации**
   - Проверьте API ключи
   - Убедитесь в правильности настроек

2. **Таймауты**
   - Увеличьте `timeout` в конфигурации
   - Проверьте стабильность сети

3. **Превышение лимитов**
   - Настройте rate limiting
   - Используйте кэширование

### Проблемы с модерацией

1. **Неточные результаты**
   - Настройте промпты
   - Измените пороги confidence
   - Переключитесь на другой провайдер

2. **Медленная модерация**
   - Включите кэширование
   - Используйте очереди
   - Оптимизируйте изображения

### Отладка

```php
// Включить детальное логирование
Log::debug('AI moderation request', [
    'provider' => $provider,
    'content_type' => $contentType,
    'request_data' => $requestData
]);
```

## 🔒 Безопасность

### Защита API ключей

- Храните ключи в `.env`
- Не коммитьте ключи в репозиторий
- Используйте разные ключи для разных сред

### Валидация контента

- Проверяйте размер файлов
- Валидируйте форматы
- Ограничивайте длину текста

### Rate Limiting

```php
'rate_limiting' => [
    'enabled' => true,
    'max_requests_per_minute' => 60,
    'max_requests_per_hour' => 1000,
    'max_requests_per_day' => 10000,
],
```

## 📚 Дополнительные ресурсы

- [YandexGPT API](https://yandex.cloud/ru/docs/foundation-models/)
- [GigaChat API](https://developers.sber.ru/portal/products/gigachat)
- [OpenAI API](https://platform.openai.com/docs)
- [DeepSeek API](https://platform.deepseek.com/api-docs/)

## 🎯 Лучшие практики

1. **Тестирование** - всегда тестируйте провайдеров перед продакшеном
2. **Fallback** - настройте резервные провайдеры
3. **Мониторинг** - отслеживайте качество модерации
4. **Обновления** - регулярно обновляйте промпты
5. **Аналитика** - анализируйте статистику для улучшения

## 🚀 Развертывание

### 1. Настройка переменных окружения

```env
AI_MODERATION_ENABLED=true
YANDEX_GPT_ENABLED=true
YANDEX_GPT_API_KEY=your_key
YANDEX_GPT_FOLDER_ID=your_folder
```

### 2. Запуск миграций

```bash
php artisan migrate
```

### 3. Очистка кэша

```bash
php artisan config:clear
php artisan cache:clear
```

### 4. Тестирование

```bash
php artisan tinker
>>> app(\App\Services\AIModerationService::class)->moderateText('test', 'catch_comments');
```

Система AI модерации готова к использованию! 🤖✨
