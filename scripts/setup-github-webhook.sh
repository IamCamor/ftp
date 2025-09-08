#!/bin/bash

# GitHub Webhook Setup Script for FishTrackPro
# This script helps configure GitHub webhook for automatic deployment

set -e

echo "🔧 Setting up GitHub webhook for FishTrackPro..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
REPO_OWNER="IamCamor"
REPO_NAME="ftp"
WEBHOOK_URL="https://fishtrackpro.ru"  # Change this to your domain
WEBHOOK_SECRET=$(openssl rand -hex 32)

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

# Check if GitHub CLI is installed
if ! command -v gh &> /dev/null; then
    print_error "GitHub CLI (gh) is not installed. Please install it first:"
    echo "curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | sudo dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg"
    echo "echo \"deb [arch=\$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main\" | sudo tee /etc/apt/sources.list.d/github-cli.list > /dev/null"
    echo "sudo apt update"
    echo "sudo apt install gh"
    exit 1
fi

# Check if user is authenticated with GitHub
if ! gh auth status &> /dev/null; then
    print_error "You are not authenticated with GitHub. Please run:"
    echo "gh auth login"
    exit 1
fi

print_status "Setting up webhook for repository: $REPO_OWNER/$REPO_NAME"

# Create webhook
print_status "Creating GitHub webhook..."
gh api repos/$REPO_OWNER/$REPO_NAME/hooks \
  --method POST \
  --field name=web \
  --field config[url]=$WEBHOOK_URL/api/webhook/github \
  --field config[content_type]=json \
  --field config[secret]=$WEBHOOK_SECRET \
  --field events[]=push \
  --field active=true

print_success "GitHub webhook created successfully!"

# Create GitHub secrets
print_status "Setting up GitHub secrets..."

# Get server IP
SERVER_IP=$(curl -s ifconfig.me)
print_status "Detected server IP: $SERVER_IP"

# Set secrets
gh secret set HOST --body "$SERVER_IP"
gh secret set USERNAME --body "$USER"
gh secret set PORT --body "22"
gh secret set WEBHOOK_URL --body "$WEBHOOK_URL"
gh secret set GITHUB_WEBHOOK_SECRET --body "$WEBHOOK_SECRET"

print_success "GitHub secrets configured!"

# Create .env entry for webhook secret
print_status "Creating .env entry for webhook secret..."
echo "GITHUB_WEBHOOK_SECRET=$WEBHOOK_SECRET" >> backend/.env

print_success "Setup completed successfully!"
echo ""
print_status "Next steps:"
echo "1. Add your SSH public key to the server:"
echo "   ssh-copy-id $USER@$SERVER_IP"
echo ""
echo "2. Set the SSH_KEY secret in GitHub:"
echo "   gh secret set SSH_KEY --body \"\$(cat ~/.ssh/id_rsa)\""
echo ""
echo "3. Test the webhook by pushing to main branch"
echo ""
echo "4. Monitor deployment logs:"
echo "   tail -f /var/log/nginx/access.log"
echo "   journalctl -u php8.2-fpm -f"
echo ""
print_warning "Important:"
echo "- Make sure your server is accessible from the internet"
echo "- Configure firewall to allow HTTP/HTTPS traffic"
echo "- Set up SSL certificate for secure webhook communication"
echo "- Monitor webhook delivery in GitHub repository settings"

# Test webhook endpoint
print_status "Testing webhook endpoint..."
if curl -f -s "$WEBHOOK_URL/health" > /dev/null; then
    print_success "Webhook endpoint is accessible!"
else
    print_warning "Webhook endpoint is not accessible. Please check:"
    echo "- Server is running and accessible"
    echo "- Nginx is configured correctly"
    echo "- SSL certificate is valid (if using HTTPS)"
fi
