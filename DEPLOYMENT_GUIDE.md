# Руководство по развертыванию FishTrackPro

## Обзор

Это руководство описывает процесс настройки автоматического развертывания FishTrackPro с использованием GitHub Actions и webhook.

## Архитектура развертывания

```
GitHub Repository → GitHub Actions → Webhook → Production Server
     ↓                    ↓              ↓           ↓
   Push Event → Run Tests → Trigger → Deploy App
```

## Варианты развертывания

### 1. Прямое развертывание через SSH (рекомендуется для простых случаев)

**Файл**: `.github/workflows/deploy.yml`

**Преимущества**:
- Простая настройка
- Прямое подключение к серверу
- Полный контроль над процессом

**Недостатки**:
- Требует SSH ключи на сервере
- Менее безопасно для публичных репозиториев

### 2. Развертывание через Webhook (рекомендуется для продакшена)

**Файлы**: 
- `.github/workflows/webhook-deploy.yml`
- `backend/app/Http/Controllers/WebhookController.php`

**Преимущества**:
- Более безопасно
- Сервер контролирует процесс развертывания
- Можно добавить дополнительные проверки
- Лучше для масштабирования

**Недостатки**:
- Более сложная настройка
- Требует настройки webhook endpoint

## Пошаговая настройка

### Шаг 1: Подготовка сервера

1. **Создайте VPS сервер** (Ubuntu 20.04+ рекомендуется)
2. **Запустите скрипт настройки**:
   ```bash
   chmod +x scripts/setup-server.sh
   ./scripts/setup-server.sh
   ```

3. **Скрипт автоматически**:
   - Установит все необходимые пакеты
   - Настроит Nginx, PHP, MySQL, Redis
   - Создаст базу данных
   - Настроит SSL сертификат
   - Создаст скрипт развертывания

### Шаг 2: Настройка GitHub

#### Для прямого развертывания:

1. **Добавьте секреты в GitHub**:
   - `HOST` - IP адрес сервера
   - `USERNAME` - имя пользователя на сервере
   - `SSH_KEY` - приватный SSH ключ
   - `PORT` - порт SSH (обычно 22)
   - `SLACK_WEBHOOK` - URL для уведомлений (опционально)

2. **Настройте SSH доступ**:
   ```bash
   ssh-copy-id username@server_ip
   ```

#### Для webhook развертывания:

1. **Запустите скрипт настройки webhook**:
   ```bash
   chmod +x scripts/setup-github-webhook.sh
   ./scripts/setup-github-webhook.sh
   ```

2. **Скрипт автоматически**:
   - Создаст webhook в GitHub
   - Настроит секреты
   - Сгенерирует webhook secret
   - Протестирует подключение

### Шаг 3: Настройка переменных окружения

1. **Обновите `.env` файл на сервере**:
   ```bash
   cd /var/www/fishtrackpro/backend
   nano .env
   ```

2. **Установите важные параметры**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://fishtrackpro.ru
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fishtrackpro
   DB_USERNAME=fishtrackpro
   DB_PASSWORD=secure_password_here
   
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   
   GITHUB_WEBHOOK_SECRET=your_webhook_secret_here
   ```

### Шаг 4: Тестирование развертывания

1. **Сделайте тестовый коммит**:
   ```bash
   git add .
   git commit -m "Test deployment"
   git push origin main
   ```

2. **Проверьте логи**:
   ```bash
   # GitHub Actions
   # Перейдите в раздел Actions в GitHub репозитории
   
   # Серверные логи
   tail -f /var/log/nginx/access.log
   journalctl -u php8.2-fpm -f
   ```

## Мониторинг и обслуживание

### Проверка состояния приложения

```bash
# Health check
curl https://fishtrackpro.ru/health

# Проверка статуса сервисов
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo systemctl status redis-server
```

### Ручное развертывание

```bash
# Запуск скрипта развертывания
/usr/local/bin/deploy-fishtrackpro

# Или через systemd
sudo systemctl start fishtrackpro-deploy
```

### Резервное копирование

```bash
# Создание резервной копии базы данных
mysqldump -u fishtrackpro -p fishtrackpro > backup_$(date +%Y%m%d_%H%M%S).sql

# Создание резервной копии файлов
tar -czf fishtrackpro_backup_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/fishtrackpro
```

## Безопасность

### Рекомендации по безопасности

1. **Используйте сильные пароли** для базы данных
2. **Настройте firewall** для ограничения доступа
3. **Регулярно обновляйте** систему и пакеты
4. **Настройте мониторинг** для отслеживания атак
5. **Используйте HTTPS** для всех соединений
6. **Ограничьте SSH доступ** по IP адресам

### Настройка firewall

```bash
# Разрешить только необходимые порты
sudo ufw allow 22    # SSH
sudo ufw allow 80    # HTTP
sudo ufw allow 443   # HTTPS
sudo ufw enable
```

## Устранение неполадок

### Частые проблемы

1. **Ошибка "Permission denied"**:
   ```bash
   sudo chown -R www-data:www-data /var/www/fishtrackpro
   sudo chmod -R 755 /var/www/fishtrackpro
   ```

2. **Ошибка "Database connection failed"**:
   - Проверьте настройки в `.env`
   - Убедитесь, что MySQL запущен
   - Проверьте права пользователя базы данных

3. **Ошибка "Webhook signature verification failed"**:
   - Проверьте `GITHUB_WEBHOOK_SECRET` в `.env`
   - Убедитесь, что секрет совпадает в GitHub

4. **Ошибка "Nginx configuration test failed"**:
   ```bash
   sudo nginx -t
   sudo systemctl reload nginx
   ```

### Логи для диагностики

```bash
# Nginx логи
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# PHP-FPM логи
sudo tail -f /var/log/php8.2-fpm.log

# Laravel логи
tail -f /var/www/fishtrackpro/backend/storage/logs/laravel.log

# Systemd логи
journalctl -u nginx -f
journalctl -u php8.2-fpm -f
```

## Масштабирование

### Горизонтальное масштабирование

1. **Настройте load balancer** (Nginx, HAProxy)
2. **Используйте Redis** для сессий
3. **Настройте CDN** для статических файлов
4. **Используйте отдельный сервер** для базы данных

### Вертикальное масштабирование

1. **Увеличьте ресурсы сервера** (CPU, RAM)
2. **Оптимизируйте PHP-FPM** настройки
3. **Настройте кэширование** (Redis, Memcached)
4. **Оптимизируйте базу данных** (индексы, запросы)

## Заключение

Система автоматического развертывания FishTrackPro готова к использованию. Следуйте инструкциям выше для настройки и мониторинга развертывания.

Для получения помощи обращайтесь к документации или создавайте issues в репозитории.
