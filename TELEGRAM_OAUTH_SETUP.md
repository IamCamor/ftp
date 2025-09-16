# Настройка входа через Telegram для FishTrackPro

## Обзор

Данная инструкция описывает настройку входа через Telegram для приложения FishTrackPro.

## Возможности

- 🔐 **Авторизация через Telegram**: Пользователи могут входить в приложение через Telegram
- 👤 **Автоматическая регистрация**: Новые пользователи создаются автоматически
- 🔗 **Связывание аккаунтов**: Существующие пользователи могут связать Telegram аккаунт
- 📱 **Мобильная интеграция**: Удобный вход с мобильных устройств

## Настройка

### 1. Создание Telegram бота

1. Откройте Telegram и найдите [@BotFather](https://t.me/botfather)
2. Отправьте команду `/newbot`
3. Введите имя бота (например, "FishTrackPro Auth Bot")
4. Введите username бота (например, "fishtrackpro_auth_bot")
5. Сохраните полученный токен

### 2. Настройка переменных окружения

Добавьте в файл `backend/.env`:

```env
# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_BOT_USERNAME=fishtrackpro_auth_bot
TELEGRAM_CHAT_ID=your_chat_id_here
TELEGRAM_ADMIN_CHAT_ID=your_chat_id_here
TELEGRAM_WEBHOOK_SECRET=your_webhook_secret_here
TELEGRAM_WEBHOOK_URL=https://api.fishtrackpro.ru/api/telegram/webhook
TELEGRAM_WEBHOOK_ENABLED=true

# Telegram Feature Flags
TELEGRAM_DEPLOYMENT_NOTIFICATIONS=true
TELEGRAM_USER_REGISTRATION_NOTIFICATIONS=true
TELEGRAM_CATCH_NOTIFICATIONS=true
TELEGRAM_PAYMENT_NOTIFICATIONS=true
TELEGRAM_POINT_NOTIFICATIONS=true
TELEGRAM_DAILY_STATISTICS=true
TELEGRAM_ERROR_NOTIFICATIONS=true
TELEGRAM_BONUS_NOTIFICATIONS=true
```

### 3. Настройка бота

1. Установите команды бота:
   ```
   /setcommands
   start - Начать авторизацию
   help - Помощь
   ```

2. Настройте описание бота:
   ```
   /setdescription
   Авторизация в FishTrackPro - приложении для рыболовов
   ```

3. Настройте краткое описание:
   ```
   /setabouttext
   Авторизация в FishTrackPro
   ```

## API Endpoints

### OAuth маршруты

- `GET /auth/telegram/redirect` - Перенаправление на Telegram для авторизации
- `POST /auth/telegram/callback` - Обработка callback от Telegram

### Пример использования

#### 1. Инициация авторизации

```javascript
// Перенаправление пользователя на Telegram
window.location.href = 'https://api.fishtrackpro.ru/auth/telegram/redirect';
```

#### 2. Обработка callback

Telegram отправляет данные пользователя на callback URL:

```json
{
  "id": 123456789,
  "first_name": "Иван",
  "last_name": "Петров",
  "username": "ivan_petrov",
  "photo_url": "https://t.me/i/userpic/320/ivan_petrov.jpg"
}
```

#### 3. Получение JWT токена

После успешной авторизации пользователь перенаправляется на:
```
https://www.fishtrackpro.ru/feed?token=JWT_TOKEN_HERE
```

## Интеграция с фронтендом

### 1. Кнопка входа через Telegram

```html
<button onclick="loginWithTelegram()" class="telegram-login-btn">
  <img src="/icons/telegram.svg" alt="Telegram">
  Войти через Telegram
</button>
```

```javascript
function loginWithTelegram() {
  window.location.href = 'https://api.fishtrackpro.ru/auth/telegram/redirect';
}
```

### 2. Обработка токена

```javascript
// Получение токена из URL
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token');

if (token) {
  // Сохранение токена
  localStorage.setItem('auth_token', token);
  
  // Перенаправление на главную страницу
  window.location.href = '/feed';
}
```

## Безопасность

### 1. Валидация данных

- Проверка ID пользователя Telegram
- Валидация username (если предоставлен)
- Проверка формата данных

### 2. Ограничения

- Один Telegram аккаунт может быть связан только с одним аккаунтом FishTrackPro
- Email генерируется автоматически: `{telegram_id}@telegram.local`
- Username берется из Telegram (если доступен)

## Troubleshooting

### Проблемы с авторизацией

1. **Бот не отвечает**:
   - Проверьте токен бота
   - Убедитесь, что бот запущен
   - Проверьте логи приложения

2. **Ошибка "Invalid provider"**:
   - Убедитесь, что используете правильный URL
   - Проверьте регистр в URL

3. **Ошибка "telegram_auth_failed"**:
   - Проверьте данные, отправляемые Telegram
   - Убедитесь, что callback URL доступен

### Логи

Проверьте логи для отладки:

```bash
# Laravel логи
tail -f backend/storage/logs/laravel.log

# Фильтр по Telegram
grep -i telegram backend/storage/logs/laravel.log
```

## Тестирование

### 1. Тест авторизации

```bash
# Тест перенаправления
curl -I "https://api.fishtrackpro.ru/auth/telegram/redirect"

# Ожидаемый ответ: 302 Redirect на t.me
```

### 2. Тест callback

```bash
# Тест callback (замените данные на реальные)
curl -X POST "https://api.fishtrackpro.ru/auth/telegram/callback" \
  -H "Content-Type: application/json" \
  -d '{
    "id": 123456789,
    "first_name": "Test",
    "last_name": "User",
    "username": "testuser"
  }'
```

## Мониторинг

### 1. Статистика авторизаций

```sql
-- Количество авторизаций через Telegram
SELECT COUNT(*) FROM oauth_identities WHERE provider = 'telegram';

-- Новые пользователи через Telegram за последний день
SELECT COUNT(*) FROM oauth_identities 
WHERE provider = 'telegram' 
AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY);
```

### 2. Логи авторизаций

```bash
# Поиск успешных авторизаций
grep "Telegram OAuth success" backend/storage/logs/laravel.log

# Поиск ошибок авторизации
grep "Telegram OAuth error" backend/storage/logs/laravel.log
```

## Поддержка

При возникновении проблем:

1. Проверьте логи приложения
2. Убедитесь в правильности конфигурации
3. Протестируйте API endpoints
4. Обратитесь к документации Telegram Bot API

## Дополнительные возможности

### 1. Уведомления

После настройки Telegram бота можно использовать его для:
- Уведомлений о новых уловах
- Статистики приложения
- Административных уведомлений

### 2. Интеграция с существующими функциями

Telegram OAuth интегрируется с:
- Системой бонусов
- Уведомлениями
- Профилем пользователя
- Статистикой

