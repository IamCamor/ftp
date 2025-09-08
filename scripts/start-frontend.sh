#!/bin/bash

# FishTrackPro Frontend Development Server Startup Script
# This script starts the Vite development server for the frontend

set -e

echo "🚀 Starting FishTrackPro Frontend Server..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
FRONTEND_DIR="$(dirname "$0")/../frontend"
PORT=5173

# Check if frontend directory exists
if [ ! -d "$FRONTEND_DIR" ]; then
    echo -e "${RED}Frontend directory not found: $FRONTEND_DIR${NC}"
    exit 1
fi

cd "$FRONTEND_DIR"

echo -e "${YELLOW}📁 Frontend directory: $(pwd)${NC}"

# Check if package.json exists
if [ ! -f "package.json" ]; then
    echo -e "${RED}package.json not found in frontend directory${NC}"
    exit 1
fi

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo -e "${YELLOW}📦 Installing Node.js dependencies...${NC}"
    if command -v npm &> /dev/null; then
        npm install
        echo -e "${GREEN}✅ Node.js dependencies installed${NC}"
    else
        echo -e "${RED}npm not found. Please install Node.js first.${NC}"
        exit 1
    fi
fi

# Create .env file if it doesn't exist
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚠️ .env file not found. Creating from env.example...${NC}"
    if [ -f "env.example" ]; then
        cp env.example .env
        echo -e "${GREEN}✅ Created .env file${NC}"
    else
        echo -e "${YELLOW}Creating default .env file...${NC}"
        cat > .env << EOF
# Development environment variables
VITE_API_BASE=http://localhost:8000/api/v1
VITE_SITE_BASE=http://localhost:5173
VITE_ASSETS_BASE=http://localhost:5173/assets
EOF
        echo -e "${GREEN}✅ Created default .env file${NC}"
    fi
fi

# Start the development server
echo -e "${GREEN}🚀 Starting Vite development server on port $PORT...${NC}"
echo -e "${YELLOW}📋 Frontend will be available at: http://localhost:$PORT${NC}"
echo -e "${YELLOW}📋 Make sure the backend is running on http://localhost:8000${NC}"
echo -e "${YELLOW}📋 Press Ctrl+C to stop the server${NC}"
echo ""

npm run dev -- --host 0.0.0.0 --port $PORT
