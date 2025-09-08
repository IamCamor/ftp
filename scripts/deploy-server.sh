#!/bin/bash

# FishTrackPro Server Deployment Script
# This script sets up the Laravel backend on the server

set -e

echo "🚀 Starting FishTrackPro server deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/var/www/ftp"
BACKEND_DIR="$PROJECT_DIR/backend"
FRONTEND_DIR="$PROJECT_DIR/frontend"

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   echo -e "${RED}This script should not be run as root${NC}"
   exit 1
fi

# Check if project directory exists
if [ ! -d "$PROJECT_DIR" ]; then
    echo -e "${RED}Project directory $PROJECT_DIR does not exist${NC}"
    exit 1
fi

cd "$PROJECT_DIR"

echo -e "${YELLOW}📁 Current directory: $(pwd)${NC}"

# 1. Install Composer dependencies
echo -e "${YELLOW}📦 Installing Composer dependencies...${NC}"
cd "$BACKEND_DIR"

if [ ! -f "composer.json" ]; then
    echo -e "${RED}composer.json not found in $BACKEND_DIR${NC}"
    exit 1
fi

# Install Composer if not exists
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}Installing Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
fi

# Install dependencies
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${GREEN}✅ Composer dependencies installed${NC}"

# 2. Install Node.js dependencies
echo -e "${YELLOW}📦 Installing Node.js dependencies...${NC}"
cd "$FRONTEND_DIR"

if [ ! -f "package.json" ]; then
    echo -e "${RED}package.json not found in $FRONTEND_DIR${NC}"
    exit 1
fi

# Install Node.js if not exists
if ! command -v node &> /dev/null; then
    echo -e "${YELLOW}Installing Node.js...${NC}"
    curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
    sudo apt-get install -y nodejs
fi

# Install dependencies
npm install --production

echo -e "${GREEN}✅ Node.js dependencies installed${NC}"

# 3. Build frontend
echo -e "${YELLOW}🔨 Building frontend...${NC}"
npm run build

echo -e "${GREEN}✅ Frontend built${NC}"

# 4. Set up Laravel environment
echo -e "${YELLOW}⚙️ Setting up Laravel environment...${NC}"
cd "$BACKEND_DIR"

# Copy .env.example to .env if .env doesn't exist
if [ ! -f ".env" ]; then
    if [ -f "env.example" ]; then
        cp env.example .env
        echo -e "${GREEN}✅ Created .env file from env.example${NC}"
    else
        echo -e "${RED}env.example not found${NC}"
        exit 1
    fi
fi

# Generate application key if not set
if ! grep -q "APP_KEY=" .env || grep -q "APP_KEY=$" .env; then
    php artisan key:generate
    echo -e "${GREEN}✅ Generated application key${NC}"
fi

# 5. Set up database
echo -e "${YELLOW}🗄️ Setting up database...${NC}"

# Check if database configuration is set
if grep -q "DB_DATABASE=" .env; then
    DB_NAME=$(grep "DB_DATABASE=" .env | cut -d '=' -f2)
    if [ "$DB_NAME" != "" ]; then
        echo -e "${YELLOW}Database name: $DB_NAME${NC}"
        
        # Run migrations
        php artisan migrate --force
        
        # Seed database if needed
        read -p "Do you want to seed the database with demo data? (y/n): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            php artisan db:seed --force
            echo -e "${GREEN}✅ Database seeded with demo data${NC}"
        fi
    else
        echo -e "${YELLOW}⚠️ Database name not set in .env file${NC}"
    fi
else
    echo -e "${YELLOW}⚠️ Database configuration not found in .env file${NC}"
fi

# 6. Set up storage and cache
echo -e "${YELLOW}📁 Setting up storage and cache...${NC}"

# Create storage directories
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Create storage link
php artisan storage:link

echo -e "${GREEN}✅ Storage and cache directories created${NC}"

# 7. Optimize Laravel
echo -e "${YELLOW}⚡ Optimizing Laravel...${NC}"

# Clear and cache configuration
php artisan config:clear
php artisan config:cache

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache views
php artisan view:clear
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

echo -e "${GREEN}✅ Laravel optimized${NC}"

# 8. Set up web server permissions
echo -e "${YELLOW}🔐 Setting up web server permissions...${NC}"

# Get web server user (usually www-data)
WEB_USER=$(ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)

if [ -z "$WEB_USER" ]; then
    WEB_USER="www-data"
fi

echo -e "${YELLOW}Web server user: $WEB_USER${NC}"

# Set ownership
sudo chown -R $WEB_USER:$WEB_USER "$PROJECT_DIR"

# Set permissions
sudo chmod -R 755 "$PROJECT_DIR"
sudo chmod -R 775 "$BACKEND_DIR/storage"
sudo chmod -R 775 "$BACKEND_DIR/bootstrap/cache"

echo -e "${GREEN}✅ Web server permissions set${NC}"

# 9. Test the installation
echo -e "${YELLOW}🧪 Testing the installation...${NC}"

# Test artisan
if php artisan --version > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Artisan is working${NC}"
else
    echo -e "${RED}❌ Artisan is not working${NC}"
    exit 1
fi

# Test database connection
if php artisan migrate:status > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Database connection is working${NC}"
else
    echo -e "${YELLOW}⚠️ Database connection test failed${NC}"
fi

echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo -e "${YELLOW}📋 Next steps:${NC}"
echo -e "1. Configure your web server (Nginx/Apache) to point to $FRONTEND_DIR/dist"
echo -e "2. Set up SSL certificate if needed"
echo -e "3. Configure your database settings in $BACKEND_DIR/.env"
echo -e "4. Set up cron jobs for Laravel scheduler if needed"
echo -e "5. Configure Redis if you want to use it for caching/sessions"
