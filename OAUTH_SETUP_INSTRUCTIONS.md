# Инструкции по настройке OAuth

## ✅ Что уже настроено

1. **Таблица oauth_identities** - создана и готова к использованию
2. **OAuthController** - обновлен для работы с упрощенной аутентификацией
3. **Маршруты OAuth** - настроены в `routes/web.php`
4. **Переменные окружения** - добавлены в `.env`

## 🔧 Настройка Google OAuth

### 1. Google Cloud Console

1. Перейдите в [Google Cloud Console](https://console.cloud.google.com/)
2. Создайте новый проект или выберите существующий
3. Включите Google+ API
4. Перейдите в "Credentials" → "Create Credentials" → "OAuth 2.0 Client IDs"
5. Выберите "Web application"
6. Добавьте авторизованные URI перенаправления:
   - `https://api.fishtrackpro.ru/auth/google/callback`

### 2. Обновите переменные в .env

```env
GOOGLE_CLIENT_ID=ваш_client_id_из_google_console
GOOGLE_CLIENT_SECRET=ваш_client_secret_из_google_console
GOOGLE_REDIRECT_URI=https://api.fishtrackpro.ru/auth/google/callback
```

### 3. Очистите кэш

```bash
php artisan config:clear
php artisan route:clear
```

## 🔧 Настройка Yandex OAuth

### 1. Yandex Developer Console

1. Перейдите в [Yandex Developer Console](https://oauth.yandex.ru/)
2. Создайте новое приложение
3. Добавьте callback URL: `https://api.fishtrackpro.ru/auth/yandex/callback`

### 2. Обновите переменные в .env

```env
YANDEX_CLIENT_ID=ваш_client_id_из_yandex
YANDEX_CLIENT_SECRET=ваш_client_secret_из_yandex
YANDEX_REDIRECT_URI=https://api.fishtrackpro.ru/auth/yandex/callback
```

## 🔧 Настройка Telegram OAuth

### 1. Создайте Telegram Bot

1. Напишите [@BotFather](https://t.me/botfather) в Telegram
2. Создайте нового бота командой `/newbot`
3. Получите токен бота

### 2. Обновите переменные в .env

```env
TELEGRAM_BOT_TOKEN=ваш_токен_бота
TELEGRAM_BOT_USERNAME=имя_вашего_бота
TELEGRAM_REDIRECT_URI=https://api.fishtrackpro.ru/auth/telegram/callback
```

## 🧪 Тестирование OAuth

### 1. Тестовая страница

Перейдите на: `https://api.fishtrackpro.ru/test-oauth`

### 2. Прямое тестирование

- Google: `https://api.fishtrackpro.ru/auth/google/redirect`
- Yandex: `https://api.fishtrackpro.ru/auth/yandex/redirect`

### 3. Проверка логов

```bash
tail -f storage/logs/laravel.log
```

## 🚨 Возможные проблемы

### 1. Ошибка "oauth_failed"

- Проверьте правильность CLIENT_ID и CLIENT_SECRET
- Убедитесь, что callback URL точно совпадает с настройками в консоли провайдера
- Проверьте, что домен добавлен в авторизованные домены

### 2. Ошибка "Invalid redirect_uri"

- Убедитесь, что redirect_uri в .env точно совпадает с настройками в консоли
- Проверьте, что используется HTTPS

### 3. Ошибка "Access denied"

- Проверьте настройки OAuth приложения
- Убедитесь, что все необходимые разрешения включены

## 📝 Структура OAuth

### Таблица oauth_identities

```sql
- id (primary key)
- user_id (foreign key to users)
- provider (google, yandex, telegram)
- provider_user_id (ID от провайдера)
- access_token (токен доступа)
- refresh_token (токен обновления)
- expires_at (время истечения)
```

### Поток OAuth

1. Пользователь нажимает "Войти через Google"
2. Перенаправление на `https://api.fishtrackpro.ru/auth/google/redirect`
3. Перенаправление на Google OAuth
4. Пользователь авторизуется в Google
5. Google перенаправляет на `https://api.fishtrackpro.ru/auth/google/callback`
6. Создается или находится пользователь в базе
7. Создается запись в oauth_identities
8. Перенаправление на фронтенд с токеном

## 🔄 Обновление фронтенда

После настройки OAuth на бэкенде, фронтенд автоматически будет работать с OAuth кнопками на страницах входа и регистрации.

