# Настройка сервера FishTrackPro

## Проблема: Отсутствует vendor директория

Если вы получаете ошибку:
```
PHP Warning: require(/var/www/ftp/backend/vendor/autoload.php): Failed to open stream: No such file or directory
```

Это означает, что на сервере не установлены зависимости Composer.

## Быстрое исправление

### Вариант 1: Автоматический скрипт (рекомендуется)

1. Загрузите скрипт на сервер:
```bash
# На вашем локальном компьютере
scp scripts/fix-vendor.sh user@your-server:/tmp/
```

2. Запустите скрипт на сервере:
```bash
# На сервере
sudo chmod +x /tmp/fix-vendor.sh
sudo /tmp/fix-vendor.sh
```

### Вариант 2: Ручная установка

1. Подключитесь к серверу:
```bash
ssh user@your-server
```

2. Перейдите в директорию проекта:
```bash
cd /var/www/ftp/backend
```

3. Установите Composer (если не установлен):
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

4. Установите зависимости:
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

5. Проверьте, что artisan работает:
```bash
php artisan --version
```

## Полная настройка сервера

Для полной настройки сервера используйте скрипт `deploy-server.sh`:

1. Загрузите скрипт на сервер:
```bash
scp scripts/deploy-server.sh user@your-server:/tmp/
```

2. Запустите полную настройку:
```bash
sudo chmod +x /tmp/deploy-server.sh
sudo /tmp/deploy-server.sh
```

Этот скрипт выполнит:
- ✅ Установку Composer зависимостей
- ✅ Установку Node.js зависимостей
- ✅ Сборку фронтенда
- ✅ Настройку Laravel окружения
- ✅ Настройку базы данных
- ✅ Оптимизацию Laravel
- ✅ Настройку прав доступа

## Настройка веб-сервера

### Nginx конфигурация

Создайте файл `/etc/nginx/sites-available/fishtrackpro`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/ftp/frontend/dist;
    index index.html;

    # Frontend routes
    location / {
        try_files $uri $uri/ /index.html;
    }

    # API routes
    location /api {
        try_files $uri $uri/ /index.php?$query_string;
        root /var/www/ftp/backend/public;
    }

    # Laravel public files
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        root /var/www/ftp/backend/public;
    }

    # Static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Активируйте сайт:
```bash
sudo ln -s /etc/nginx/sites-available/fishtrackpro /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Apache конфигурация

Создайте файл `/etc/apache2/sites-available/fishtrackpro.conf`:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/ftp/frontend/dist

    # Frontend routes
    <Directory /var/www/ftp/frontend/dist>
        AllowOverride All
        Require all granted
    </Directory>

    # API routes
    Alias /api /var/www/ftp/backend/public
    <Directory /var/www/ftp/backend/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Laravel .htaccess
    <Directory /var/www/ftp/backend/public>
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
</VirtualHost>
```

Активируйте сайт:
```bash
sudo a2ensite fishtrackpro
sudo a2enmod rewrite
sudo systemctl reload apache2
```

## Настройка базы данных

1. Создайте базу данных:
```sql
CREATE DATABASE fishtrackpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fishtrackpro'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON fishtrackpro.* TO 'fishtrackpro'@'localhost';
FLUSH PRIVILEGES;
```

2. Настройте `.env` файл:
```bash
cd /var/www/ftp/backend
cp env.example .env
nano .env
```

Обновите настройки базы данных:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fishtrackpro
DB_USERNAME=fishtrackpro
DB_PASSWORD=your_password
```

3. Запустите миграции:
```bash
php artisan migrate
php artisan db:seed
```

## Настройка SSL (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

## Настройка cron для Laravel

Добавьте в crontab:
```bash
sudo crontab -e
```

Добавьте строку:
```
* * * * * cd /var/www/ftp/backend && php artisan schedule:run >> /dev/null 2>&1
```

## Мониторинг и логи

### Просмотр логов Laravel:
```bash
tail -f /var/www/ftp/backend/storage/logs/laravel.log
```

### Просмотр логов Nginx:
```bash
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

### Просмотр логов Apache:
```bash
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/apache2/access.log
```

## Полезные команды

```bash
# Очистка кэша Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Оптимизация Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Проверка статуса миграций
php artisan migrate:status

# Запуск тестов
php artisan test

# Генерация ключа приложения
php artisan key:generate
```

## Troubleshooting

### Проблема: Permission denied
```bash
sudo chown -R www-data:www-data /var/www/ftp
sudo chmod -R 755 /var/www/ftp
sudo chmod -R 775 /var/www/ftp/backend/storage
sudo chmod -R 775 /var/www/ftp/backend/bootstrap/cache
```

### Проблема: Composer memory limit
```bash
php -d memory_limit=-1 /usr/local/bin/composer install
```

### Проблема: Node.js не найден
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### Проблема: PHP расширения
```bash
sudo apt install php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd
sudo systemctl restart php8.2-fpm
```
