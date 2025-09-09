#!/bin/bash

# Docker-compatible startup script
# This script handles root user execution safely

echo "🐳 Starting FishTrackPro Frontend in Docker mode..."

# Set environment variables
export NODE_ENV=production
export VITE_API_BASE=${VITE_API_BASE:-http://localhost:8000/api/v1}
export VITE_SITE_BASE=${VITE_SITE_BASE:-http://localhost:5173}

# Create non-root user if running as root
if [ "$EUID" -eq 0 ]; then
    echo "🔧 Creating non-root user for security..."
    
    # Create user if it doesn't exist
    if ! id "appuser" &>/dev/null; then
        adduser -D -s /bin/sh appuser
    fi
    
    # Change ownership of app directory
    chown -R appuser:appuser /app
    
    # Switch to non-root user
    exec su-exec appuser "$@"
else
    # Already running as non-root user
    exec "$@"
fi
