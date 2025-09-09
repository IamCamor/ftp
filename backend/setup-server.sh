#!/bin/bash

# FishTrackPro Backend Server Setup Script
# This script sets up the backend on a production server

echo "🚀 Setting up FishTrackPro Backend Server..."

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    echo "⚠️  Running as root. This is not recommended for production."
    echo "   Consider creating a non-root user for the application."
fi

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    echo "   Visit: https://getcomposer.org/download/"
    exit 1
fi

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP first."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "📋 PHP Version: $PHP_VERSION"

if [ "$(echo "$PHP_VERSION < 8.1" | bc -l)" -eq 1 ]; then
    echo "❌ PHP 8.1 or higher is required. Current version: $PHP_VERSION"
    exit 1
fi

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

if [ $? -ne 0 ]; then
    echo "❌ Failed to install Composer dependencies"
    exit 1
fi

# Check if .env file exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "❌ .env.example file not found"
        exit 1
    fi
fi

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Generate JWT secret
echo "🔐 Generating JWT secret..."
php artisan jwt:secret

# Create database if it doesn't exist
if [ ! -f database/database.sqlite ]; then
    echo "🗄️  Creating SQLite database..."
    touch database/database.sqlite
fi

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

if [ $? -ne 0 ]; then
    echo "❌ Failed to run migrations"
    exit 1
fi

# Clear and cache configuration
echo "🧹 Clearing and caching configuration..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "🔒 Setting proper permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Create storage directories if they don't exist
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Create symbolic link for storage
echo "🔗 Creating storage symbolic link..."
php artisan storage:link

echo "✅ Backend server setup completed successfully!"
echo ""
echo "🌐 Server Information:"
echo "   - Backend URL: http://localhost:8000"
echo "   - API URL: http://localhost:8000/api/v1"
echo "   - Admin Panel: http://localhost:8000/admin"
echo ""
echo "🚀 To start the server:"
echo "   php artisan serve --host=0.0.0.0 --port=8000"
echo ""
echo "📋 Next steps:"
echo "   1. Configure your web server (Nginx/Apache)"
echo "   2. Set up SSL certificates"
echo "   3. Configure environment variables in .env"
echo "   4. Set up cron jobs for scheduled tasks"
echo "   5. Configure log rotation"
