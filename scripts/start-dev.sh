#!/bin/bash

# FishTrackPro Development Environment Startup Script
# This script starts both backend and frontend servers for development

set -e

echo "🚀 Starting FishTrackPro Development Environment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(dirname "$0")"
BACKEND_SCRIPT="$SCRIPT_DIR/start-backend.sh"
FRONTEND_SCRIPT="$SCRIPT_DIR/start-frontend.sh"

# Function to cleanup background processes
cleanup() {
    echo -e "\n${YELLOW}🛑 Shutting down servers...${NC}"
    if [ ! -z "$BACKEND_PID" ]; then
        kill $BACKEND_PID 2>/dev/null || true
        echo -e "${GREEN}✅ Backend server stopped${NC}"
    fi
    if [ ! -z "$FRONTEND_PID" ]; then
        kill $FRONTEND_PID 2>/dev/null || true
        echo -e "${GREEN}✅ Frontend server stopped${NC}"
    fi
    exit 0
}

# Set up signal handlers
trap cleanup SIGINT SIGTERM

# Check if scripts exist
if [ ! -f "$BACKEND_SCRIPT" ]; then
    echo -e "${RED}Backend startup script not found: $BACKEND_SCRIPT${NC}"
    exit 1
fi

if [ ! -f "$FRONTEND_SCRIPT" ]; then
    echo -e "${RED}Frontend startup script not found: $FRONTEND_SCRIPT${NC}"
    exit 1
fi

echo -e "${BLUE}📋 Starting development servers...${NC}"
echo -e "${YELLOW}📋 Backend: http://localhost:8000${NC}"
echo -e "${YELLOW}📋 Frontend: http://localhost:5173${NC}"
echo -e "${YELLOW}📋 Press Ctrl+C to stop all servers${NC}"
echo ""

# Start backend server in background
echo -e "${GREEN}🚀 Starting backend server...${NC}"
bash "$BACKEND_SCRIPT" &
BACKEND_PID=$!

# Wait a bit for backend to start
sleep 3

# Start frontend server in background
echo -e "${GREEN}🚀 Starting frontend server...${NC}"
bash "$FRONTEND_SCRIPT" &
FRONTEND_PID=$!

# Wait for both processes
echo -e "${BLUE}📋 Both servers are starting up...${NC}"
echo -e "${BLUE}📋 Backend PID: $BACKEND_PID${NC}"
echo -e "${BLUE}📋 Frontend PID: $FRONTEND_PID${NC}"
echo ""

# Wait for processes to complete
wait $BACKEND_PID $FRONTEND_PID
