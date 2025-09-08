#!/bin/bash

# FishTrackPro Server Setup Script
# This script sets up a production server for FishTrackPro

set -e

echo "🚀 Setting up FishTrackPro production server..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DOMAIN="fishtrackpro.ru"
APP_DIR="/var/www/fishtrackpro"
NGINX_CONFIG="/etc/nginx/sites-available/fishtrackpro"
PHP_VERSION="8.2"

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root"
   exit 1
fi

# Update system packages
print_status "Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install required packages
print_status "Installing required packages..."
sudo apt install -y nginx mysql-server redis-server php${PHP_VERSION}-fpm php${PHP_VERSION}-mysql php${PHP_VERSION}-redis php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml php${PHP_VERSION}-curl php${PHP_VERSION}-zip php${PHP_VERSION}-gd php${PHP_VERSION}-intl composer nodejs npm git unzip

# Install Node.js 18 LTS
print_status "Installing Node.js 18 LTS..."
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Configure MySQL
print_status "Configuring MySQL..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS fishtrackpro;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'fishtrackpro'@'localhost' IDENTIFIED BY 'secure_password_here';"
sudo mysql -e "GRANT ALL PRIVILEGES ON fishtrackpro.* TO 'fishtrackpro'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Create application directory
print_status "Creating application directory..."
sudo mkdir -p $APP_DIR
sudo chown -R $USER:$USER $APP_DIR

# Clone repository
print_status "Cloning repository..."
cd $APP_DIR
git clone https://github.com/IamCamor/ftp.git .

# Install backend dependencies
print_status "Installing backend dependencies..."
cd backend
composer install --no-dev --optimize-autoloader

# Install frontend dependencies
print_status "Installing frontend dependencies..."
cd ../frontend
npm ci

# Build frontend
print_status "Building frontend..."
npm run build

# Configure Laravel
print_status "Configuring Laravel..."
cd ../backend
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
print_status "Running database migrations..."
php artisan migrate --force

# Seed demo data
print_status "Seeding demo data..."
php artisan db:seed --force

# Set permissions
print_status "Setting permissions..."
sudo chown -R www-data:www-data $APP_DIR
sudo chmod -R 755 $APP_DIR
sudo chmod -R 775 $APP_DIR/storage
sudo chmod -R 775 $APP_DIR/bootstrap/cache

# Configure Nginx
print_status "Configuring Nginx..."
sudo tee $NGINX_CONFIG > /dev/null <<EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    root $APP_DIR/frontend/dist;
    index index.html;

    # Frontend routes
    location / {
        try_files \$uri \$uri/ /index.html;
    }

    # API routes
    location /api/ {
        try_files \$uri @backend;
    }

    # Admin routes
    location /admin {
        try_files \$uri @backend;
    }

    # Webhook routes
    location /webhook/ {
        try_files \$uri @backend;
    }

    # Health check
    location /health {
        try_files \$uri @backend;
    }

    # Backend handler
    location @backend {
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $APP_DIR/backend/public/index.php;
        include fastcgi_params;
    }

    # Static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
}
EOF

# Enable site
sudo ln -sf $NGINX_CONFIG /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Restart services
print_status "Restarting services..."
sudo systemctl restart nginx
sudo systemctl restart php${PHP_VERSION}-fpm
sudo systemctl restart mysql
sudo systemctl restart redis-server

# Enable services to start on boot
sudo systemctl enable nginx
sudo systemctl enable php${PHP_VERSION}-fpm
sudo systemctl enable mysql
sudo systemctl enable redis-server

# Configure firewall
print_status "Configuring firewall..."
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
sudo ufw --force enable

# Create deployment script
print_status "Creating deployment script..."
sudo tee /usr/local/bin/deploy-fishtrackpro > /dev/null <<'EOF'
#!/bin/bash
cd /var/www/fishtrackpro
git pull origin main
cd backend
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
cd ../frontend
npm ci
npm run build
sudo systemctl reload nginx
sudo systemctl restart php8.2-fpm
echo "Deployment completed successfully!"
EOF

sudo chmod +x /usr/local/bin/deploy-fishtrackpro

# Setup SSL with Let's Encrypt (optional)
print_status "Setting up SSL with Let's Encrypt..."
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN

# Create systemd service for auto-deployment
print_status "Creating systemd service..."
sudo tee /etc/systemd/system/fishtrackpro-deploy.service > /dev/null <<EOF
[Unit]
Description=FishTrackPro Auto Deploy
After=network.target

[Service]
Type=oneshot
User=www-data
WorkingDirectory=$APP_DIR
ExecStart=/usr/local/bin/deploy-fishtrackpro
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload

print_success "Server setup completed successfully!"
print_status "Next steps:"
echo "1. Update DNS records to point to this server"
echo "2. Configure GitHub webhook: https://$DOMAIN/api/webhook/github"
echo "3. Set up GitHub secrets:"
echo "   - HOST: $(curl -s ifconfig.me)"
echo "   - USERNAME: $USER"
echo "   - SSH_KEY: (your private SSH key)"
echo "   - PORT: 22"
echo "   - GITHUB_WEBHOOK_SECRET: (generate a random string)"
echo "4. Test deployment: /usr/local/bin/deploy-fishtrackpro"
echo "5. Access your application: https://$DOMAIN"

print_warning "Don't forget to:"
echo "- Update .env file with production settings"
echo "- Set secure database passwords"
echo "- Configure GitHub webhook secret"
echo "- Set up monitoring and backups"
