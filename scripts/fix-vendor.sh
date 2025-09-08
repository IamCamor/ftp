#!/bin/bash

# Quick fix for missing vendor directory on server
# Run this script on your server to install Composer dependencies

set -e

echo "🔧 Fixing missing vendor directory..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/var/www/ftp"
BACKEND_DIR="$PROJECT_DIR/backend"

# Check if project directory exists
if [ ! -d "$PROJECT_DIR" ]; then
    echo -e "${RED}Project directory $PROJECT_DIR does not exist${NC}"
    exit 1
fi

cd "$BACKEND_DIR"

echo -e "${YELLOW}📁 Current directory: $(pwd)${NC}"

# Check if composer.json exists
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
    echo -e "${GREEN}✅ Composer installed${NC}"
fi

# Install dependencies
echo -e "${YELLOW}📦 Installing Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${GREEN}✅ Composer dependencies installed${NC}"

# Test artisan
echo -e "${YELLOW}🧪 Testing artisan...${NC}"
if php artisan --version > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Artisan is now working!${NC}"
    php artisan --version
else
    echo -e "${RED}❌ Artisan is still not working${NC}"
    exit 1
fi

echo -e "${GREEN}🎉 Fix completed successfully!${NC}"
