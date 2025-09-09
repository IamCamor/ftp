#!/bin/bash

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    echo "⚠️  Warning: Running as root is not recommended for development."
    echo "   Consider creating a non-root user for development."
    echo "   Continuing anyway with --unsafe-perm flag..."
    echo ""
fi

# Set environment variables
export NODE_ENV=development
export VITE_API_BASE=http://localhost:8000/api/v1
export VITE_SITE_BASE=http://localhost:5173

# Start development server
echo "🚀 Starting FishTrackPro Frontend Development Server..."
echo "   API Base: $VITE_API_BASE"
echo "   Site Base: $VITE_SITE_BASE"
echo ""

npm run dev
