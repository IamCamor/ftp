#!/bin/bash

# FishTrackPro Backend Server Startup Script
# This script starts the Laravel backend server for development

set -e

echo "🚀 Starting FishTrackPro Backend Server..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
BACKEND_DIR="$(dirname "$0")/../backend"
PORT=8000

# Check if backend directory exists
if [ ! -d "$BACKEND_DIR" ]; then
    echo -e "${RED}Backend directory not found: $BACKEND_DIR${NC}"
    exit 1
fi

cd "$BACKEND_DIR"

echo -e "${YELLOW}📁 Backend directory: $(pwd)${NC}"

# Check if .env file exists
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚠️ .env file not found. Creating from env.example...${NC}"
    if [ -f "env.example" ]; then
        cp env.example .env
        echo -e "${GREEN}✅ Created .env file${NC}"
    else
        echo -e "${RED}env.example not found. Please create .env file manually.${NC}"
        exit 1
    fi
fi

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}📦 Installing Composer dependencies...${NC}"
    if command -v composer &> /dev/null; then
        composer install
        echo -e "${GREEN}✅ Composer dependencies installed${NC}"
    else
        echo -e "${RED}Composer not found. Please install Composer first.${NC}"
        exit 1
    fi
fi

# Generate application key if not set
if ! grep -q "APP_KEY=" .env || grep -q "APP_KEY=$" .env; then
    echo -e "${YELLOW}🔑 Generating application key...${NC}"
    php artisan key:generate
    echo -e "${GREEN}✅ Application key generated${NC}"
fi

# Check database connection
echo -e "${YELLOW}🗄️ Checking database connection...${NC}"
if php artisan migrate:status > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Database connection successful${NC}"
else
    echo -e "${YELLOW}⚠️ Database connection failed. Please check your database configuration in .env${NC}"
    echo -e "${YELLOW}Make sure your database is running and credentials are correct.${NC}"
fi

# Clear caches
echo -e "${YELLOW}🧹 Clearing caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✅ Caches cleared${NC}"

# Start the server
echo -e "${GREEN}🚀 Starting Laravel development server on port $PORT...${NC}"
echo -e "${YELLOW}📋 Server will be available at: http://localhost:$PORT${NC}"
echo -e "${YELLOW}📋 API endpoints will be available at: http://localhost:$PORT/api/v1${NC}"
echo -e "${YELLOW}📋 Press Ctrl+C to stop the server${NC}"
echo ""

php artisan serve --host=0.0.0.0 --port=$PORT
