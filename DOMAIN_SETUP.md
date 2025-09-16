# Настройка доменов FishTrackPro

## 🌐 Домены проекта

- **Frontend:** `fishtrackpro.ru` (основной сайт)
- **API Backend:** `api.fishtrackpro.ru` (API сервер)

## 📋 Настройка DNS

### 1. A-записи
Создайте следующие A-записи в вашем DNS провайдере:

```
fishtrackpro.ru.        A    YOUR_SERVER_IP
www.fishtrackpro.ru.    A    YOUR_SERVER_IP
api.fishtrackpro.ru.    A    YOUR_SERVER_IP
```

### 2. CNAME-записи (альтернативно)
```
www.fishtrackpro.ru.    CNAME    fishtrackpro.ru.
```

## 🔒 SSL сертификаты

### Автоматическая настройка с Let's Encrypt

```bash
# Установка Certbot
sudo apt update
sudo apt install certbot python3-certbot-nginx

# Получение сертификатов
sudo certbot --nginx -d fishtrackpro.ru -d www.fishtrackpro.ru
sudo certbot --nginx -d api.fishtrackpro.ru

# Автоматическое обновление
sudo crontab -e
# Добавьте строку:
# 0 12 * * * /usr/bin/certbot renew --quiet
```

### Ручная настройка SSL

1. Получите SSL сертификаты от вашего провайдера
2. Разместите файлы в `/etc/ssl/certs/` и `/etc/ssl/private/`
3. Обновите конфигурацию Nginx:

```nginx
ssl_certificate /etc/ssl/certs/fishtrackpro.ru.crt;
ssl_certificate_key /etc/ssl/private/fishtrackpro.ru.key;
```

## 🚀 Развертывание

### Автоматическое развертывание
```bash
sudo ./deploy-production.sh
```

### Ручное развертывание

#### Backend (API)
```bash
cd /var/www/ftp/backend

# Настройка окружения
cp .env.example .env
# Отредактируйте .env файл

# Установка зависимостей
composer install --no-dev --optimize-autoloader

# Миграции
php artisan migrate --force

# Кэширование
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Права доступа
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

#### Frontend
```bash
cd /var/www/ftp/frontend

# Настройка окружения
cp .env.production .env

# Установка зависимостей
npm ci --production

# Сборка
npm run build

# Права доступа
chown -R www-data:www-data dist
chmod -R 755 dist
```

## 🔧 Конфигурация Nginx

### Frontend (fishtrackpro.ru)
```nginx
server {
    listen 443 ssl http2;
    server_name fishtrackpro.ru www.fishtrackpro.ru;
    
    root /var/www/ftp/frontend/dist;
    index index.html;
    
    # SSL настройки
    ssl_certificate /etc/ssl/certs/fishtrackpro.ru.crt;
    ssl_certificate_key /etc/ssl/private/fishtrackpro.ru.key;
    
    # SPA роутинг
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # Кэширование статики
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### API (api.fishtrackpro.ru)
```nginx
server {
    listen 443 ssl http2;
    server_name api.fishtrackpro.ru;
    
    root /var/www/ftp/backend/public;
    index index.php;
    
    # SSL настройки
    ssl_certificate /etc/ssl/certs/api.fishtrackpro.ru.crt;
    ssl_certificate_key /etc/ssl/private/api.fishtrackpro.ru.key;
    
    # CORS
    add_header Access-Control-Allow-Origin "https://fishtrackpro.ru" always;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept, Authorization" always;
    add_header Access-Control-Allow-Credentials "true" always;
    
    # PHP обработка
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Laravel роутинг
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

## 🧪 Тестирование

### Проверка доменов
```bash
# Проверка DNS
nslookup fishtrackpro.ru
nslookup api.fishtrackpro.ru

# Проверка SSL
curl -I https://fishtrackpro.ru
curl -I https://api.fishtrackpro.ru/api/health

# Проверка CORS
curl -H "Origin: https://fishtrackpro.ru" \
     -H "Access-Control-Request-Method: GET" \
     -H "Access-Control-Request-Headers: X-Requested-With" \
     -X OPTIONS \
     https://api.fishtrackpro.ru/api/v1/users
```

### Проверка приложения
1. Откройте https://fishtrackpro.ru
2. Проверьте авторизацию
3. Протестируйте основные функции
4. Проверьте API: https://api.fishtrackpro.ru/api/health

## 📊 Мониторинг

### Логи
```bash
# Backend логи
tail -f /var/www/ftp/backend/storage/logs/laravel.log

# Nginx логи
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# Системные логи
journalctl -u nginx -f
journalctl -u php8.2-fpm -f
```

### Статус сервисов
```bash
# Проверка статуса
systemctl status nginx
systemctl status php8.2-fpm
systemctl status fishtrackpro-worker

# Перезапуск сервисов
systemctl restart nginx
systemctl restart php8.2-fpm
systemctl restart fishtrackpro-worker
```

## 🔄 Обновление

### Обновление кода
```bash
cd /var/www/ftp
git pull origin main
./deploy-production.sh
```

### Обновление зависимостей
```bash
# Backend
cd /var/www/ftp/backend
composer update --no-dev --optimize-autoloader

# Frontend
cd /var/www/ftp/frontend
npm update
npm run build
```

## 🆘 Устранение неполадок

### Проблемы с CORS
- Проверьте настройки CORS в `/var/www/ftp/backend/config/cors.php`
- Убедитесь, что домены правильно указаны в `.env`

### Проблемы с SSL
- Проверьте срок действия сертификатов: `openssl x509 -in /path/to/cert.crt -text -noout`
- Обновите сертификаты: `sudo certbot renew`

### Проблемы с производительностью
- Включите кэширование: `php artisan config:cache`
- Оптимизируйте автозагрузку: `composer dump-autoload --optimize`
- Настройте Redis для кэширования

## 📞 Поддержка

При возникновении проблем:
1. Проверьте логи
2. Убедитесь в правильности DNS настроек
3. Проверьте SSL сертификаты
4. Обратитесь к документации Laravel и Nginx
