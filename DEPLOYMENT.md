# Инструкция по развертыванию FishTrackPro

## 🚀 Быстрый старт

### 1. Подготовка сервера

#### Требования к серверу:
- **ОС**: Ubuntu 20.04+ или CentOS 8+
- **RAM**: минимум 2GB, рекомендуется 4GB+
- **CPU**: 2+ ядра
- **Диск**: минимум 20GB свободного места
- **Сеть**: статический IP адрес

#### Установка необходимого ПО:

```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# Установка Nginx
sudo apt install nginx -y

# Установка PHP 8.2
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-redis -y

# Установка MySQL 8.0
sudo apt install mysql-server -y

# Установка Redis
sudo apt install redis-server -y

# Установка Node.js 18
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# Установка Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Настройка базы данных

```bash
# Вход в MySQL
sudo mysql

# Создание базы данных и пользователя
CREATE DATABASE fishtrackpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fishtrackpro'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON fishtrackpro.* TO 'fishtrackpro'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Развертывание Backend

```bash
# Клонирование проекта
cd /var/www
sudo git clone https://github.com/your-repo/fishtrackpro.git
sudo chown -R www-data:www-data fishtrackpro
cd fishtrackpro/backend

# Установка зависимостей
sudo -u www-data composer install --no-dev --optimize-autoloader

# Настройка окружения
sudo -u www-data cp env.example .env
sudo -u www-data nano .env
```

#### Настройка .env файла:

```env
APP_NAME=FishTrackPro
APP_ENV=production
APP_KEY=base64:your_generated_key_here
APP_DEBUG=false
APP_URL=https://api.fishtrackpro.ru
APP_TIMEZONE=UTC

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fishtrackpro
DB_USERNAME=fishtrackpro
DB_PASSWORD=secure_password_here

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

JWT_SECRET=your_jwt_secret_here
JWT_TTL=43200

CORS_ALLOWED_ORIGINS=https://www.fishtrackpro.ru

# OAuth настройки
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=https://api.fishtrackpro.ru/auth/google/callback

VKONTAKTE_CLIENT_ID=your_vk_client_id
VKONTAKTE_CLIENT_SECRET=your_vk_client_secret
VKONTAKTE_REDIRECT_URI=https://api.fishtrackpro.ru/auth/vk/callback

YANDEX_CLIENT_ID=your_yandex_client_id
YANDEX_CLIENT_SECRET=your_yandex_client_secret
YANDEX_REDIRECT_URI=https://api.fishtrackpro.ru/auth/yandex/callback

APPLE_CLIENT_ID=your_apple_client_id
APPLE_CLIENT_SECRET=your_apple_client_secret
APPLE_REDIRECT_URI=https://api.fishtrackpro.ru/auth/apple/callback

# Файловое хранилище
FILES_DISK=s3
AWS_ACCESS_KEY_ID=your_aws_access_key
AWS_SECRET_ACCESS_KEY=your_aws_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=fishtrackpro-storage
AWS_URL=https://s3.amazonaws.com/fishtrackpro-storage
```

```bash
# Генерация ключа приложения
sudo -u www-data php artisan key:generate

# Генерация JWT секрета
sudo -u www-data php artisan jwt:secret

# Запуск миграций
sudo -u www-data php artisan migrate --force

# Создание символической ссылки для storage
sudo -u www-data php artisan storage:link

# Очистка кеша
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### 4. Развертывание Frontend

```bash
cd /var/www/fishtrackpro/frontend

# Установка зависимостей
sudo -u www-data npm install

# Сборка для продакшена
sudo -u www-data npm run build

# Копирование файлов в веб-директорию
sudo cp -r dist/* /var/www/html/
sudo chown -R www-data:www-data /var/www/html
```

### 5. Настройка Nginx

#### Конфигурация для API (api.fishtrackpro.ru):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name api.fishtrackpro.ru;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.fishtrackpro.ru;

    root /var/www/fishtrackpro/backend/public;
    index index.php;

    # SSL сертификаты
    ssl_certificate /etc/letsencrypt/live/api.fishtrackpro.ru/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.fishtrackpro.ru/privkey.pem;

    # SSL настройки
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Безопасность
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # CORS заголовки
    add_header Access-Control-Allow-Origin "https://www.fishtrackpro.ru" always;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Authorization, Content-Type, X-Requested-With" always;

    # Обработка OPTIONS запросов
    if ($request_method = 'OPTIONS') {
        add_header Access-Control-Allow-Origin "https://www.fishtrackpro.ru";
        add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS";
        add_header Access-Control-Allow-Headers "Authorization, Content-Type, X-Requested-With";
        add_header Access-Control-Max-Age 1728000;
        add_header Content-Type 'text/plain charset=UTF-8';
        add_header Content-Length 0;
        return 204;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Логирование
    access_log /var/log/nginx/api.fishtrackpro.ru.access.log;
    error_log /var/log/nginx/api.fishtrackpro.ru.error.log;
}
```

#### Конфигурация для Frontend (www.fishtrackpro.ru):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name www.fishtrackpro.ru fishtrackpro.ru;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name www.fishtrackpro.ru fishtrackpro.ru;

    root /var/www/html;
    index index.html;

    # SSL сертификаты
    ssl_certificate /etc/letsencrypt/live/www.fishtrackpro.ru/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/www.fishtrackpro.ru/privkey.pem;

    # SSL настройки
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Безопасность
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Кеширование статических файлов
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # SPA маршрутизация
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Логирование
    access_log /var/log/nginx/www.fishtrackpro.ru.access.log;
    error_log /var/log/nginx/www.fishtrackpro.ru.error.log;
}
```

### 6. Настройка SSL сертификатов

```bash
# Установка Certbot
sudo apt install certbot python3-certbot-nginx -y

# Получение сертификатов
sudo certbot --nginx -d www.fishtrackpro.ru -d fishtrackpro.ru
sudo certbot --nginx -d api.fishtrackpro.ru

# Автоматическое обновление
sudo crontab -e
# Добавить строку:
# 0 12 * * * /usr/bin/certbot renew --quiet
```

### 7. Настройка Redis

```bash
# Редактирование конфигурации Redis
sudo nano /etc/redis/redis.conf

# Настройки для продакшена:
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000

# Перезапуск Redis
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

### 8. Настройка очередей Laravel

```bash
# Создание systemd сервиса для очередей
sudo nano /etc/systemd/system/fishtrackpro-worker.service
```

```ini
[Unit]
Description=FishTrackPro Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/fishtrackpro/backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
WorkingDirectory=/var/www/fishtrackpro/backend

[Install]
WantedBy=multi-user.target
```

```bash
# Запуск сервиса
sudo systemctl daemon-reload
sudo systemctl start fishtrackpro-worker
sudo systemctl enable fishtrackpro-worker
```

### 9. Настройка мониторинга

```bash
# Установка htop для мониторинга
sudo apt install htop -y

# Настройка логирования
sudo nano /etc/logrotate.d/fishtrackpro
```

```
/var/www/fishtrackpro/backend/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
}
```

### 10. Финальная проверка

```bash
# Перезапуск всех сервисов
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart mysql
sudo systemctl restart redis-server

# Проверка статуса
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo systemctl status redis-server
sudo systemctl status fishtrackpro-worker

# Проверка логов
sudo tail -f /var/log/nginx/api.fishtrackpro.ru.error.log
sudo tail -f /var/log/nginx/www.fishtrackpro.ru.error.log
```

## 🔧 Настройка OAuth провайдеров

### Google OAuth
1. Перейдите в [Google Cloud Console](https://console.cloud.google.com/)
2. Создайте новый проект или выберите существующий
3. Включите Google+ API
4. Создайте OAuth 2.0 credentials
5. Добавьте authorized redirect URIs:
   - `https://api.fishtrackpro.ru/auth/google/callback`

### VK OAuth
1. Перейдите в [VK Developers](https://vk.com/dev)
2. Создайте новое приложение
3. Настройте OAuth redirect URI:
   - `https://api.fishtrackpro.ru/auth/vk/callback`

### Яндекс OAuth
1. Перейдите в [Яндекс.Паспорт для разработчиков](https://oauth.yandex.ru/)
2. Создайте новое приложение
3. Настройте redirect URI:
   - `https://api.fishtrackpro.ru/auth/yandex/callback`

## 📊 Мониторинг и логирование

### Настройка логирования Laravel
```bash
# Создание директории для логов
sudo mkdir -p /var/log/fishtrackpro
sudo chown www-data:www-data /var/log/fishtrackpro

# Настройка в .env
LOG_CHANNEL=daily
LOG_LEVEL=info
LOG_DAILY_DAYS=14
```

### Мониторинг производительности
```bash
# Установка New Relic (опционально)
sudo apt install newrelic-php5 -y

# Настройка APM
sudo newrelic-install install
```

## 🚨 Безопасность

### Настройка файрвола
```bash
# Установка UFW
sudo apt install ufw -y

# Настройка правил
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### Настройка fail2ban
```bash
# Установка fail2ban
sudo apt install fail2ban -y

# Настройка для Nginx
sudo nano /etc/fail2ban/jail.local
```

```ini
[nginx-http-auth]
enabled = true

[nginx-limit-req]
enabled = true
```

## 🔄 Обновление приложения

```bash
# Создание скрипта обновления
sudo nano /usr/local/bin/update-fishtrackpro.sh
```

```bash
#!/bin/bash
cd /var/www/fishtrackpro

# Обновление кода
sudo -u www-data git pull origin main

# Backend обновления
cd backend
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Frontend обновления
cd ../frontend
sudo -u www-data npm install
sudo -u www-data npm run build
sudo cp -r dist/* /var/www/html/

# Перезапуск сервисов
sudo systemctl restart fishtrackpro-worker
sudo systemctl reload nginx

echo "FishTrackPro updated successfully!"
```

```bash
# Сделать скрипт исполняемым
sudo chmod +x /usr/local/bin/update-fishtrackpro.sh
```

## 📈 Масштабирование

### Горизонтальное масштабирование
- Использование Redis Cluster
- Настройка MySQL Master-Slave репликации
- CDN для статических файлов
- Load Balancer для распределения нагрузки

### Вертикальное масштабирование
- Увеличение RAM и CPU
- Оптимизация MySQL конфигурации
- Настройка PHP-FPM пулов
- Кеширование на уровне приложения

---

**Примечание**: Данная инструкция предназначена для продакшен развертывания. Для разработки используйте более простую настройку с `php artisan serve` и `npm run dev`.

