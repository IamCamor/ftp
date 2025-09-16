# 🧪 Руководство по тестированию Google OAuth

## ✅ Текущий статус

**Google OAuth настроен и готов к тестированию!**

- ✅ CLIENT_ID: `[НАСТРОЕН]`
- ✅ CLIENT_SECRET: `[НАСТРОЕН]`
- ✅ REDIRECT_URI: `https://api.fishtrackpro.ru/auth/google/callback`
- ✅ Таблица `oauth_identities` создана
- ✅ OAuthController обновлен

## 🔗 Ссылки для тестирования

### 1. Тестовая страница OAuth
**URL:** `https://api.fishtrackpro.ru/test-oauth`

Содержит:
- Ссылки для тестирования Google и Yandex OAuth
- Прямые ссылки для ручного тестирования

### 2. Тест OAuth callback
**URL:** `https://api.fishtrackpro.ru/test-oauth-callback`

Содержит:
- Ссылки для тестирования callback с тестовыми данными
- Симуляция процесса OAuth

### 3. Прямые ссылки OAuth

**Google OAuth:**
```
https://api.fishtrackpro.ru/auth/google/redirect
```

**Yandex OAuth:**
```
https://api.fishtrackpro.ru/auth/yandex/redirect
```

## 🧪 Пошаговое тестирование

### Шаг 1: Проверка OAuth redirect

1. Откройте браузер
2. Перейдите на: `https://api.fishtrackpro.ru/test-oauth`
3. Нажмите на ссылку "Test Google OAuth"
4. **Ожидаемый результат:** Перенаправление на Google OAuth страницу

### Шаг 2: Тестирование с реальным Google аккаунтом

1. Перейдите на: `https://api.fishtrackpro.ru/auth/google/redirect`
2. Войдите в свой Google аккаунт
3. Разрешите доступ приложению
4. **Ожидаемый результат:** Перенаправление на `https://www.fishtrackpro.ru/feed?token=simple_token_X_timestamp`

### Шаг 3: Проверка создания пользователя

После успешного OAuth:
1. Проверьте, что пользователь создан в базе данных
2. Проверьте, что создана запись в `oauth_identities`
3. Проверьте, что токен сохранен в localStorage фронтенда

## 🔍 Отладка проблем

### Проблема: "oauth_failed" ошибка

**Причины:**
1. Неправильный CLIENT_ID или CLIENT_SECRET
2. Неправильный redirect_uri в Google Console
3. Домен не добавлен в авторизованные домены

**Решение:**
1. Проверьте настройки в Google Console
2. Убедитесь, что redirect_uri точно совпадает
3. Проверьте логи: `tail -f storage/logs/laravel.log`

### Проблема: "Invalid redirect_uri"

**Причины:**
1. redirect_uri в .env не совпадает с настройками в Google Console
2. Используется HTTP вместо HTTPS

**Решение:**
1. Обновите redirect_uri в Google Console
2. Убедитесь, что используется HTTPS

### Проблема: "Access denied"

**Причины:**
1. Пользователь отклонил разрешения
2. Неправильные настройки OAuth приложения

**Решение:**
1. Проверьте настройки в Google Console
2. Убедитесь, что все необходимые разрешения включены

## 📊 Проверка логов

### Просмотр OAuth логов

```bash
# Просмотр всех логов
tail -f storage/logs/laravel.log

# Поиск OAuth логов
tail -f storage/logs/laravel.log | grep -i oauth

# Поиск ошибок
tail -f storage/logs/laravel.log | grep -i error
```

### Ожидаемые логи при успешном OAuth

```
[timestamp] local.INFO: OAuth callback for provider: google {"code":"...","state":"..."}
[timestamp] local.INFO: Social user data received {"id":"...","name":"...","email":"..."}
```

## 🎯 Ожидаемый результат

После успешного тестирования:

1. **Пользователь создан** в таблице `users`
2. **OAuth identity создана** в таблице `oauth_identities`
3. **Токен сгенерирован** и передан на фронтенд
4. **Перенаправление** на `https://www.fishtrackpro.ru/feed?token=...`
5. **Пользователь авторизован** на фронтенде

## 🚀 Готово к продакшену

После успешного тестирования OAuth готов к использованию в продакшене!

**Следующие шаги:**
1. Протестировать с реальными пользователями
2. Настроить мониторинг OAuth ошибок
3. Добавить дополнительные OAuth провайдеры при необходимости

