#!/bin/bash

# FishTrackPro Production Deployment Script
# Domains: fishtrackpro.ru (frontend), api.fishtrackpro.ru (backend)

set -e

echo "🚀 Starting FishTrackPro Production Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
FRONTEND_DOMAIN="fishtrackpro.ru"
API_DOMAIN="api.fishtrackpro.ru"
PROJECT_ROOT="/var/www/ftp"
BACKEND_DIR="$PROJECT_ROOT/backend"
FRONTEND_DIR="$PROJECT_ROOT/frontend"

echo -e "${BLUE}📋 Deployment Configuration:${NC}"
echo "Frontend Domain: https://$FRONTEND_DOMAIN"
echo "API Domain: https://$API_DOMAIN"
echo "Project Root: $PROJECT_ROOT"
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
for tool in php composer node npm nginx; do
    if command_exists "$tool"; then
        echo -e "${GREEN}✅ $tool is installed${NC}"
    else
        echo -e "${RED}❌ $tool is not installed${NC}"
        exit 1
    fi
done

# Backend deployment
echo -e "${BLUE}🔧 Deploying Backend (API)...${NC}"
cd "$BACKEND_DIR"

# Set production environment
echo -e "${YELLOW}📝 Setting production environment...${NC}"
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i "s|APP_URL=.*|APP_URL=https://$API_DOMAIN|" .env

# Install/update dependencies
echo -e "${YELLOW}📦 Installing Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# Run database migrations
echo -e "${YELLOW}🗄️ Running database migrations...${NC}"
php artisan migrate --force

# Clear and cache configurations
echo -e "${YELLOW}🧹 Clearing and caching configurations...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo -e "${YELLOW}🔐 Setting proper permissions...${NC}"
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Frontend deployment
echo -e "${BLUE}🎨 Deploying Frontend...${NC}"
cd "$FRONTEND_DIR"

# Set production environment
echo -e "${YELLOW}📝 Setting production environment...${NC}"
cp .env.development .env.development.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true
cp .env.production .env

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

# Nginx configuration
echo -e "${BLUE}🌐 Configuring Nginx...${NC}"

# Create Nginx configuration for frontend
cat > "/etc/nginx/sites-available/$FRONTEND_DOMAIN" << EOF
server {
    listen 80;
    listen [::]:80;
    server_name $FRONTEND_DOMAIN www.$FRONTEND_DOMAIN;
    
    # Redirect HTTP to HTTPS
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name $FRONTEND_DOMAIN www.$FRONTEND_DOMAIN;
    
    root $FRONTEND_DIR/dist;
    index index.html;
    
    # SSL configuration (you need to configure SSL certificates)
    # ssl_certificate /path/to/your/certificate.crt;
    # ssl_certificate_key /path/to/your/private.key;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript;
    
    # Handle client-side routing
    location / {
        try_files \$uri \$uri/ /index.html;
    }
    
    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF

# Create Nginx configuration for API
cat > "/etc/nginx/sites-available/$API_DOMAIN" << EOF
server {
    listen 80;
    listen [::]:80;
    server_name $API_DOMAIN;
    
    # Redirect HTTP to HTTPS
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name $API_DOMAIN;
    
    root $BACKEND_DIR/public;
    index index.php;
    
    # SSL configuration (you need to configure SSL certificates)
    # ssl_certificate /path/to/your/certificate.crt;
    # ssl_certificate_key /path/to/your/private.key;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    
    # CORS headers
    add_header Access-Control-Allow-Origin "https://$FRONTEND_DOMAIN" always;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept, Authorization" always;
    add_header Access-Control-Allow-Credentials "true" always;
    
    # Handle preflight requests
    if (\$request_method = 'OPTIONS') {
        add_header Access-Control-Allow-Origin "https://$FRONTEND_DOMAIN";
        add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS";
        add_header Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept, Authorization";
        add_header Access-Control-Allow-Credentials "true";
        add_header Content-Length 0;
        add_header Content-Type text/plain;
        return 204;
    }
    
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(storage|bootstrap/cache) {
        deny all;
    }
    
    # Handle Laravel routes
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
}
EOF

# Enable sites
echo -e "${YELLOW}🔗 Enabling Nginx sites...${NC}"
ln -sf "/etc/nginx/sites-available/$FRONTEND_DOMAIN" "/etc/nginx/sites-enabled/"
ln -sf "/etc/nginx/sites-available/$API_DOMAIN" "/etc/nginx/sites-enabled/"

# Test Nginx configuration
echo -e "${YELLOW}🧪 Testing Nginx configuration...${NC}"
nginx -t

# Reload Nginx
echo -e "${YELLOW}🔄 Reloading Nginx...${NC}"
systemctl reload nginx

# Restart PHP-FPM
echo -e "${YELLOW}🔄 Restarting PHP-FPM...${NC}"
systemctl restart php8.2-fpm

# Create systemd service for Laravel queue worker (if needed)
echo -e "${YELLOW}⚙️ Setting up Laravel queue worker...${NC}"
cat > "/etc/systemd/system/fishtrackpro-worker.service" << EOF
[Unit]
Description=FishTrackPro Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php $BACKEND_DIR/artisan queue:work --sleep=3 --tries=3 --max-time=3600
WorkingDirectory=$BACKEND_DIR

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable fishtrackpro-worker
systemctl start fishtrackpro-worker

# Setup log rotation
echo -e "${YELLOW}📝 Setting up log rotation...${NC}"
cat > "/etc/logrotate.d/fishtrackpro" << EOF
$BACKEND_DIR/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload php8.2-fpm
    endscript
}
EOF

# Final status check
echo -e "${BLUE}🔍 Final status check...${NC}"
echo -e "${GREEN}✅ Backend status:${NC}"
systemctl is-active --quiet php8.2-fpm && echo "  PHP-FPM: Running" || echo "  PHP-FPM: Not running"
systemctl is-active --quiet fishtrackpro-worker && echo "  Queue Worker: Running" || echo "  Queue Worker: Not running"

echo -e "${GREEN}✅ Nginx status:${NC}"
systemctl is-active --quiet nginx && echo "  Nginx: Running" || echo "  Nginx: Not running"

echo -e "${GREEN}✅ Services enabled:${NC}"
systemctl is-enabled --quiet php8.2-fpm && echo "  PHP-FPM: Enabled" || echo "  PHP-FPM: Not enabled"
systemctl is-enabled --quiet fishtrackpro-worker && echo "  Queue Worker: Enabled" || echo "  Queue Worker: Not enabled"
systemctl is-enabled --quiet nginx && echo "  Nginx: Enabled" || echo "  Nginx: Not enabled"

echo ""
echo -e "${GREEN}🎉 FishTrackPro Production Deployment Completed!${NC}"
echo ""
echo -e "${BLUE}📋 Next Steps:${NC}"
echo "1. Configure SSL certificates for both domains"
echo "2. Update DNS records to point to this server"
echo "3. Test the application:"
echo "   - Frontend: https://$FRONTEND_DOMAIN"
echo "   - API: https://$API_DOMAIN/api/health"
echo "4. Monitor logs:"
echo "   - Backend: tail -f $BACKEND_DIR/storage/logs/laravel.log"
echo "   - Nginx: tail -f /var/log/nginx/error.log"
echo ""
echo -e "${YELLOW}⚠️ Important:${NC}"
echo "- Make sure to configure SSL certificates before going live"
echo "- Update your DNS records to point to this server"
echo "- Test all functionality before announcing the launch"
echo ""
echo -e "${GREEN}🚀 FishTrackPro is ready for production!${NC}"
