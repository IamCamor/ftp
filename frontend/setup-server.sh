#!/bin/bash

# FishTrackPro Frontend Server Setup Script
# This script sets up the frontend on a production server

echo "🚀 Setting up FishTrackPro Frontend Server..."

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    echo "⚠️  Running as root. This is not recommended for production."
    echo "   Consider creating a non-root user for the application."
fi

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js first."
    echo "   Visit: https://nodejs.org/"
    exit 1
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed. Please install npm first."
    exit 1
fi

# Check Node.js version
NODE_VERSION=$(node -v | cut -d'v' -f2)
echo "📋 Node.js Version: $NODE_VERSION"

# Check if .env file exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cat > .env << EOF
VITE_API_BASE=http://localhost:8000/api/v1
VITE_SITE_BASE=http://localhost:5173
VITE_ASSETS_BASE=http://localhost:5173/assets
EOF
    echo "✅ Created .env file with default values"
fi

# Install npm dependencies
echo "📦 Installing npm dependencies..."
npm install

if [ $? -ne 0 ]; then
    echo "❌ Failed to install npm dependencies"
    exit 1
fi

# Build the application
echo "🔨 Building the application..."
npm run build

if [ $? -ne 0 ]; then
    echo "❌ Failed to build the application"
    exit 1
fi

# Set proper permissions
echo "🔒 Setting proper permissions..."
chmod -R 755 dist
chmod +x start-dev.sh
chmod +x docker-start.sh

echo "✅ Frontend server setup completed successfully!"
echo ""
echo "🌐 Server Information:"
echo "   - Frontend URL: http://localhost:5173"
echo "   - API Base: http://localhost:8000/api/v1"
echo ""
echo "🚀 To start the development server:"
echo "   npm run dev:safe"
echo ""
echo "🚀 To start the production server:"
echo "   npm run preview"
echo ""
echo "🐳 To start with Docker:"
echo "   docker build -t fishtrackpro-frontend ."
echo "   docker run -p 5173:5173 fishtrackpro-frontend"
echo ""
echo "📋 Next steps:"
echo "   1. Configure your web server (Nginx/Apache)"
echo "   2. Set up SSL certificates"
echo "   3. Configure environment variables in .env"
echo "   4. Set up CDN for static assets"
echo "   5. Configure caching headers"
