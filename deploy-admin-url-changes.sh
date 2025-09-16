#!/bin/bash

# FishTrackPro Admin URL Changes Deployment Script
# Changes admin URL from /admin to /q/admin

set -e

echo "🚀 Deploying Admin URL Changes..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_ROOT="/var/www/ftp"
FRONTEND_DIR="$PROJECT_ROOT/frontend"
BACKEND_DIR="$PROJECT_ROOT/backend"

echo -e "${BLUE}📋 Deployment Configuration:${NC}"
echo "Project Root: $PROJECT_ROOT"
echo "Frontend Directory: $FRONTEND_DIR"
echo "Backend Directory: $BACKEND_DIR"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}❌ Please run as root${NC}"
    exit 1
fi

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check required tools
echo -e "${BLUE}🔍 Checking required tools...${NC}"
for tool in php composer node npm; do
    if command_exists "$tool"; then
        echo -e "${GREEN}✅ $tool is installed${NC}"
    else
        echo -e "${RED}❌ $tool is not installed${NC}"
        exit 1
    fi
done

# Pull latest changes from git
echo -e "${BLUE}📥 Pulling latest changes from git...${NC}"
cd "$PROJECT_ROOT"
git fetch origin
git checkout deploy-admin-url-changes
git pull origin deploy-admin-url-changes

# Backend deployment (minimal changes)
echo -e "${BLUE}🔧 Updating Backend...${NC}"
cd "$BACKEND_DIR"

# Install/update dependencies (if composer.json changed)
echo -e "${YELLOW}📦 Checking Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# Clear and cache configurations
echo -e "${YELLOW}🧹 Clearing and caching configurations...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate Swagger documentation
echo -e "${YELLOW}📚 Generating Swagger documentation...${NC}"
php artisan l5-swagger:generate

# Set proper permissions
echo -e "${YELLOW}🔐 Setting proper permissions...${NC}"
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Frontend deployment
echo -e "${BLUE}🎨 Deploying Frontend...${NC}"
cd "$FRONTEND_DIR"

# Install dependencies
echo -e "${YELLOW}📦 Installing NPM dependencies...${NC}"
npm ci --production

# Build for production
echo -e "${YELLOW}🏗️ Building for production...${NC}"
npm run build

# Set proper permissions
echo -e "${YELLOW}🔐 Setting proper permissions...${NC}"
chown -R www-data:www-data dist
chmod -R 755 dist

# Test Nginx configuration
echo -e "${YELLOW}🧪 Testing Nginx configuration...${NC}"
nginx -t

# Reload Nginx
echo -e "${YELLOW}🔄 Reloading Nginx...${NC}"
systemctl reload nginx

# Restart PHP-FPM
echo -e "${YELLOW}🔄 Restarting PHP-FPM...${NC}"
systemctl restart php8.2-fpm

# Final status check
echo -e "${BLUE}🔍 Final status check...${NC}"
echo -e "${GREEN}✅ Backend status:${NC}"
systemctl is-active --quiet php8.2-fpm && echo "  PHP-FPM: Running" || echo "  PHP-FPM: Not running"

echo -e "${GREEN}✅ Nginx status:${NC}"
systemctl is-active --quiet nginx && echo "  Nginx: Running" || echo "  Nginx: Not running"

echo ""
echo -e "${GREEN}🎉 Admin URL Changes Deployment Completed!${NC}"
echo ""
echo -e "${BLUE}📋 Changes Applied:${NC}"
echo "✅ Admin URL changed from /admin to /q/admin"
echo "✅ Frontend routes updated"
echo "✅ Navigation updated"
echo "✅ Documentation updated"
echo ""
echo -e "${BLUE}🔗 New Admin URLs:${NC}"
echo "• Main Admin Panel: https://fishtrackpro.ru/q/admin"
echo "• User Management: https://fishtrackpro.ru/q/admin/users"
echo "• Catch Management: https://fishtrackpro.ru/q/admin/catches"
echo "• Points Management: https://fishtrackpro.ru/q/admin/points"
echo "• Reports Management: https://fishtrackpro.ru/q/admin/reports"
echo ""
echo -e "${BLUE}🧪 Test the changes:${NC}"
echo "1. Visit: https://fishtrackpro.ru/q/admin"
echo "2. Login with admin credentials"
echo "3. Verify all navigation works correctly"
echo ""
echo -e "${GREEN}🚀 Admin URL changes are live!${NC}"
