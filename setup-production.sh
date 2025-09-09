#!/bin/bash

# FishTrackPro Production Setup Script
# This script sets up the entire FishTrackPro application on a production server

echo "🎣 Setting up FishTrackPro Production Environment..."
echo "=================================================="

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    echo "⚠️  Running as root. This is not recommended for production."
    echo "   Consider creating a non-root user for the application."
    echo ""
fi

# Check system requirements
echo "🔍 Checking system requirements..."

# Check PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1+ first."
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "✅ PHP Version: $PHP_VERSION"

# Check Composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    exit 1
fi
echo "✅ Composer is installed"

# Check Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js 18+ first."
    exit 1
fi

NODE_VERSION=$(node -v)
echo "✅ Node.js Version: $NODE_VERSION"

# Check npm
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed. Please install npm first."
    exit 1
fi
echo "✅ npm is installed"

echo ""
echo "🚀 Starting setup process..."
echo ""

# Setup Backend
echo "📦 Setting up Backend..."
cd backend
if [ -f setup-server.sh ]; then
    chmod +x setup-server.sh
    ./setup-server.sh
    if [ $? -ne 0 ]; then
        echo "❌ Backend setup failed"
        exit 1
    fi
else
    echo "❌ Backend setup script not found"
    exit 1
fi

echo ""
echo "✅ Backend setup completed!"
echo ""

# Setup Frontend
echo "📦 Setting up Frontend..."
cd ../frontend
if [ -f setup-server.sh ]; then
    chmod +x setup-server.sh
    ./setup-server.sh
    if [ $? -ne 0 ]; then
        echo "❌ Frontend setup failed"
        exit 1
    fi
else
    echo "❌ Frontend setup script not found"
    exit 1
fi

echo ""
echo "✅ Frontend setup completed!"
echo ""

# Return to project root
cd ..

# Create systemd service files
echo "🔧 Creating systemd service files..."

# Backend service
cat > /tmp/fishtrackpro-backend.service << EOF
[Unit]
Description=FishTrackPro Backend API
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=$(pwd)/backend
ExecStart=/usr/bin/php artisan serve --host=0.0.0.0 --port=8000
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

# Frontend service
cat > /tmp/fishtrackpro-frontend.service << EOF
[Unit]
Description=FishTrackPro Frontend
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=$(pwd)/frontend
ExecStart=/usr/bin/npm run preview
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

echo "📋 Systemd service files created in /tmp/"
echo "   - fishtrackpro-backend.service"
echo "   - fishtrackpro-frontend.service"
echo ""
echo "To install systemd services:"
echo "   sudo cp /tmp/fishtrackpro-backend.service /etc/systemd/system/"
echo "   sudo cp /tmp/fishtrackpro-frontend.service /etc/systemd/system/"
echo "   sudo systemctl daemon-reload"
echo "   sudo systemctl enable fishtrackpro-backend"
echo "   sudo systemctl enable fishtrackpro-frontend"
echo "   sudo systemctl start fishtrackpro-backend"
echo "   sudo systemctl start fishtrackpro-frontend"
echo ""

# Create Nginx configuration
echo "🌐 Creating Nginx configuration..."

cat > /tmp/fishtrackpro-nginx.conf << EOF
server {
    listen 80;
    server_name your-domain.com;
    
    # Frontend
    location / {
        proxy_pass http://localhost:5173;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
    
    # Backend API
    location /api/ {
        proxy_pass http://localhost:8000/api/;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
    
    # Backend Admin
    location /admin/ {
        proxy_pass http://localhost:8000/admin/;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF

echo "📋 Nginx configuration created in /tmp/fishtrackpro-nginx.conf"
echo ""

# Create cron job for Laravel scheduler
echo "⏰ Creating cron job for Laravel scheduler..."

cat > /tmp/fishtrackpro-cron << EOF
# FishTrackPro Laravel Scheduler
* * * * * cd $(pwd)/backend && php artisan schedule:run >> /dev/null 2>&1
EOF

echo "📋 Cron job created in /tmp/fishtrackpro-cron"
echo "To install cron job:"
echo "   sudo crontab -e"
echo "   Add the line from /tmp/fishtrackpro-cron"
echo ""

echo "🎉 FishTrackPro Production Setup Completed!"
echo "=========================================="
echo ""
echo "🌐 Application URLs:"
echo "   - Frontend: http://localhost:5173"
echo "   - Backend API: http://localhost:8000/api/v1"
echo "   - Backend Admin: http://localhost:8000/admin"
echo ""
echo "🚀 To start the application:"
echo "   cd backend && php artisan serve --host=0.0.0.0 --port=8000 &"
echo "   cd frontend && npm run preview &"
echo ""
echo "📋 Next steps:"
echo "   1. Configure your domain name in Nginx config"
echo "   2. Set up SSL certificates (Let's Encrypt)"
echo "   3. Configure firewall rules"
echo "   4. Set up monitoring and logging"
echo "   5. Configure backup strategy"
echo "   6. Set up database backups"
echo ""
echo "📚 Documentation:"
echo "   - DEPLOYMENT.md - Detailed deployment guide"
echo "   - README.md - Project overview"
echo "   - Backend: backend/README.md"
echo "   - Frontend: frontend/README.md"
echo ""
echo "✅ Setup completed successfully!"
