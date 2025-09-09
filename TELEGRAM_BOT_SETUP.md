# Telegram Bot Setup для FishTrackPro

Этот документ описывает настройку Telegram бота для получения уведомлений о событиях в приложении FishTrackPro.

## Возможности бота

🤖 **Автоматические уведомления:**
- 🚀 Уведомления о деплое
- 👤 Регистрация новых пользователей
- 🎣 Новые уловы
- 💳 Оплаты
- 📍 Новые рыболовные точки
- ❌ Ошибки приложения

📊 **Ежедневная статистика:**
- Количество новых пользователей
- Общее количество пользователей
- Активные пользователи
- Новые уловы
- Общий вес уловов
- Новые точки
- Оплаты и доходы
- Рост показателей

🎮 **Интерактивные команды:**
- `/start` - Начать работу с ботом
- `/help` - Показать доступные команды
- `/stats` - Получить текущую статистику
- `/users` - Статистика пользователей
- `/catches` - Статистика уловов
- `/payments` - Статистика оплат
- `/points` - Статистика точек
- `/status` - Статус приложения

## Быстрая настройка

### 1. Создание бота

1. Откройте Telegram и найдите [@BotFather](https://t.me/botfather)
2. Отправьте команду `/newbot`
3. Введите имя бота (например, "FishTrackPro Bot")
4. Введите username бота (например, "fishtrackpro_bot")
5. Сохраните полученный токен

### 2. Получение Chat ID

1. Найдите [@userinfobot](https://t.me/userinfobot)
2. Отправьте любое сообщение
3. Скопируйте ваш Chat ID

### 3. Автоматическая настройка

Запустите скрипт настройки:

```bash
./scripts/setup-telegram-bot.sh
```

Скрипт запросит:
- Токен бота
- Chat ID
- URL webhook (например, `https://fishtrackpro.ru/api/telegram/webhook`)

### 4. Ручная настройка

Если автоматическая настройка не подходит, выполните следующие шаги:

#### 4.1. Настройка переменных окружения

Добавьте в файл `backend/.env`:

```env
# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here
TELEGRAM_ADMIN_CHAT_ID=your_chat_id_here
TELEGRAM_WEBHOOK_SECRET=your_webhook_secret_here
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/api/telegram/webhook
TELEGRAM_WEBHOOK_ENABLED=true

# Telegram Feature Flags
TELEGRAM_DEPLOYMENT_NOTIFICATIONS=true
TELEGRAM_USER_REGISTRATION_NOTIFICATIONS=true
TELEGRAM_CATCH_NOTIFICATIONS=true
TELEGRAM_PAYMENT_NOTIFICATIONS=true
TELEGRAM_POINT_NOTIFICATIONS=true
TELEGRAM_DAILY_STATISTICS=true
TELEGRAM_ERROR_NOTIFICATIONS=true
```

#### 4.2. Настройка webhook

```bash
cd backend
php artisan telegram:setup --webhook-url=https://yourdomain.com/api/telegram/webhook
```

#### 4.3. Настройка GitHub Secrets

Для уведомлений о деплое добавьте в GitHub Secrets:

- `TELEGRAM_BOT_TOKEN` - токен вашего бота
- `TELEGRAM_CHAT_ID` - ваш Chat ID

## Feature Flags

Вы можете включать/выключать различные типы уведомлений через переменные окружения:

```env
# Уведомления о деплое
TELEGRAM_DEPLOYMENT_NOTIFICATIONS=true

# Уведомления о регистрации пользователей
TELEGRAM_USER_REGISTRATION_NOTIFICATIONS=true

# Уведомления о новых уловах
TELEGRAM_CATCH_NOTIFICATIONS=true

# Уведомления об оплатах
TELEGRAM_PAYMENT_NOTIFICATIONS=true

# Уведомления о новых точках
TELEGRAM_POINT_NOTIFICATIONS=true

# Ежедневная статистика
TELEGRAM_DAILY_STATISTICS=true

# Уведомления об ошибках
TELEGRAM_ERROR_NOTIFICATIONS=true
```

## Команды Artisan

### Настройка бота

```bash
# Настройка webhook
php artisan telegram:setup --webhook-url=https://yourdomain.com/api/telegram/webhook

# Удаление webhook
php artisan telegram:setup --delete-webhook

# Отправка ежедневной статистики
php artisan telegram:daily-stats
```

### Тестирование

```bash
# Тест уведомления
curl -X POST "https://yourdomain.com/api/telegram/test" \
  -H "Content-Type: application/json" \
  -d '{"type": "test", "chat_id": "your_chat_id"}'
```

## API Endpoints

### Webhook

- `POST /api/telegram/webhook` - Обработка сообщений от Telegram

### Управление

- `POST /api/telegram/set-webhook` - Установка webhook
- `GET /api/telegram/webhook-info` - Информация о webhook
- `DELETE /api/telegram/webhook` - Удаление webhook
- `POST /api/telegram/test` - Тест уведомления

## Мониторинг

### Логи

Проверьте логи для отладки:

```bash
# Laravel логи
tail -f backend/storage/logs/laravel.log

# Nginx логи
tail -f /var/log/nginx/access.log
```

### Статус webhook

```bash
curl "https://yourdomain.com/api/telegram/webhook-info"
```

## Безопасность

1. **Секретный токен**: Используйте `TELEGRAM_WEBHOOK_SECRET` для проверки webhook
2. **HTTPS**: Обязательно используйте HTTPS для webhook URL
3. **Ограничение доступа**: Настройте firewall для ограничения доступа к API

## Troubleshooting

### Бот не отвечает

1. Проверьте токен бота
2. Убедитесь, что webhook настроен правильно
3. Проверьте логи приложения

### Webhook не работает

1. Проверьте доступность URL
2. Убедитесь, что используется HTTPS
3. Проверьте секретный токен

### Уведомления не приходят

1. Проверьте feature flags в `.env`
2. Убедитесь, что события генерируются
3. Проверьте логи слушателей событий

## Примеры уведомлений

### Регистрация пользователя

```
👤 New User Registration

Name: Иван Петров
Username: ivan_fisher
Email: ivan@example.com
Role: user
Time: 2024-01-15 14:30:25
```

### Новый улов

```
🎣 New Catch Recorded

User: Мария Удачливая (@maria_lucky)
Fish: Щука
Weight: 2.5 kg
Length: 45 cm
Location: Озеро Светлое
Time: 2024-01-15 16:45:12
```

### Ежедневная статистика

```
📊 Daily Statistics - 2024-01-15

👥 Users:
• New registrations: 5
• Total users: 1,234
• Active users: 89

🎣 Catches:
• New catches: 23
• Total catches: 5,678
• Total weight: 45.2 kg

📍 Points:
• New points: 3
• Total points: 456

💳 Payments:
• New payments: 2
• Total revenue: 398.00 RUB

📈 Growth:
• Users growth: 12.5%
• Catches growth: 8.3%
• Revenue growth: 15.2%
```

## Поддержка

При возникновении проблем:

1. Проверьте логи приложения
2. Убедитесь в правильности конфигурации
3. Протестируйте webhook вручную
4. Обратитесь к документации Telegram Bot API
