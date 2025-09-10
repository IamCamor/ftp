# Исправление ошибок прав доступа Laravel

## Проблема

Ошибка: `PHP Fatal error: Uncaught UnexpectedValueException: The stream or file "/var/www/ftp/backend/storage/logs/laravel.log" could not be opened`

Это происходит из-за неправильных прав доступа к директории `storage` и файлам логов.

## Быстрое решение

### 1. Автоматическое исправление

```bash
cd backend
./fix-permissions.sh
```

### 2. Быстрое исправление на сервере

```bash
cd backend
./fix-server-permissions.sh
```

### 3. Полное развертывание с исправлением прав

```bash
cd backend
./deploy-with-permissions.sh
```

## Ручное исправление

### 1. Создание необходимых директорий

```bash
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/public
mkdir -p bootstrap/cache
```

### 2. Установка прав доступа

```bash
# Основные права
chmod -R 755 .
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Создание файла лога
touch storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
```

### 3. Установка владельца

```bash
# Для nginx
sudo chown -R nginx:nginx .

# Для apache
sudo chown -R apache:apache .

# Для www-data
sudo chown -R www-data:www-data .
```

### 4. Очистка кеша

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
```

## Определение пользователя веб-сервера

### Nginx
```bash
ps aux | grep nginx
# Обычно: nginx
```

### Apache
```bash
ps aux | grep apache
# Обычно: apache или www-data
```

### Проверка конфигурации
```bash
# Nginx
cat /etc/nginx/nginx.conf | grep user

# Apache
cat /etc/apache2/envvars | grep APACHE_RUN_USER
```

## Проверка прав доступа

### 1. Проверка директорий
```bash
ls -la storage/
ls -la bootstrap/cache/
```

### 2. Проверка записи в лог
```bash
echo "test" >> storage/logs/laravel.log
```

### 3. Проверка через Laravel
```bash
php artisan tinker
```
```php
// Проверка записи в лог
Log::info('Test log entry');
```

## Типичные ошибки и решения

### Ошибка 1: Permission denied
```bash
# Решение
sudo chown -R www-data:www-data .
chmod -R 775 storage
```

### Ошибка 2: Directory not found
```bash
# Решение
mkdir -p storage/logs
mkdir -p storage/framework/cache
```

### Ошибка 3: Cannot write to log file
```bash
# Решение
touch storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
chown www-data:www-data storage/logs/laravel.log
```

## Конфигурация веб-сервера

### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/ftp/backend/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Apache
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/ftp/backend/public
    
    <Directory /var/www/ftp/backend/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## SELinux (если используется)

### Проверка статуса SELinux
```bash
sestatus
```

### Установка контекста
```bash
# Для Apache
sudo setsebool -P httpd_can_network_connect 1
sudo setsebool -P httpd_execmem 1

# Для Nginx
sudo setsebool -P httpd_can_network_connect 1
```

## Мониторинг логов

### Просмотр логов Laravel
```bash
tail -f storage/logs/laravel.log
```

### Просмотр логов веб-сервера
```bash
# Nginx
tail -f /var/log/nginx/error.log

# Apache
tail -f /var/log/apache2/error.log
```

## Автоматизация

### Создание systemd сервиса
```bash
sudo nano /etc/systemd/system/fishtrackpro.service
```

```ini
[Unit]
Description=FishTrackPro Laravel Application
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/ftp/backend
ExecStart=/usr/bin/php artisan serve --host=0.0.0.0 --port=8000
Restart=always

[Install]
WantedBy=multi-user.target
```

### Запуск сервиса
```bash
sudo systemctl enable fishtrackpro
sudo systemctl start fishtrackpro
```

## Проверка работоспособности

### 1. Проверка API
```bash
curl http://localhost/api/v1/health
```

### 2. Проверка логов
```bash
tail -f storage/logs/laravel.log
```

### 3. Проверка прав доступа
```bash
ls -la storage/logs/
```

## Резервное копирование

### Создание бэкапа
```bash
tar -czf fishtrackpro-backup-$(date +%Y%m%d).tar.gz \
    --exclude=storage/logs \
    --exclude=storage/framework/cache \
    --exclude=storage/framework/sessions \
    --exclude=storage/framework/views \
    --exclude=vendor \
    --exclude=node_modules \
    .
```

## Заключение

Правильные права доступа критически важны для работы Laravel приложения. Используйте предоставленные скрипты для автоматического исправления или следуйте ручным инструкциям для точной настройки.
