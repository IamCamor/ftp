#!/bin/bash

# Telegram Bot Setup Script for FishTrackPro
# This script helps configure Telegram bot for notifications

set -e

echo "🤖 Setting up Telegram bot for FishTrackPro..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Check if required tools are installed
if ! command -v curl &> /dev/null; then
    print_error "curl is not installed. Please install it first."
    exit 1
fi

# Get bot token
print_status "Enter your Telegram bot token (get it from @BotFather):"
read -r BOT_TOKEN

if [ -z "$BOT_TOKEN" ]; then
    print_error "Bot token is required"
    exit 1
fi

# Get chat ID
print_status "Enter your Telegram chat ID (you can get it by messaging @userinfobot):"
read -r CHAT_ID

if [ -z "$CHAT_ID" ]; then
    print_error "Chat ID is required"
    exit 1
fi

# Get webhook URL
print_status "Enter your webhook URL (e.g., https://fishtrackpro.ru/api/telegram/webhook):"
read -r WEBHOOK_URL

if [ -z "$WEBHOOK_URL" ]; then
    print_error "Webhook URL is required"
    exit 1
fi

# Generate webhook secret
WEBHOOK_SECRET=$(openssl rand -hex 32)

print_status "Setting up Telegram bot..."

# Test bot token
print_status "Testing bot token..."
BOT_INFO=$(curl -s "https://api.telegram.org/bot${BOT_TOKEN}/getMe")

if echo "$BOT_INFO" | grep -q '"ok":true'; then
    BOT_USERNAME=$(echo "$BOT_INFO" | grep -o '"username":"[^"]*"' | cut -d'"' -f4)
    print_success "Bot token is valid! Bot username: @${BOT_USERNAME}"
else
    print_error "Invalid bot token"
    exit 1
fi

# Set webhook
print_status "Setting webhook URL..."
WEBHOOK_RESPONSE=$(curl -s -X POST "https://api.telegram.org/bot${BOT_TOKEN}/setWebhook" \
    -H "Content-Type: application/json" \
    -d "{
        \"url\": \"${WEBHOOK_URL}\",
        \"secret_token\": \"${WEBHOOK_SECRET}\",
        \"allowed_updates\": [\"message\", \"callback_query\"]
    }")

if echo "$WEBHOOK_RESPONSE" | grep -q '"ok":true'; then
    print_success "Webhook set successfully!"
else
    print_error "Failed to set webhook: $WEBHOOK_RESPONSE"
    exit 1
fi

# Test webhook
print_status "Testing webhook..."
TEST_RESPONSE=$(curl -s -X POST "https://api.telegram.org/bot${BOT_TOKEN}/sendMessage" \
    -H "Content-Type: application/json" \
    -d "{
        \"chat_id\": \"${CHAT_ID}\",
        \"text\": \"🤖 *Telegram Bot Setup Complete*\\n\\nBot is now configured and ready to receive notifications!\\nTime: $(date '+%Y-%m-%d %H:%M:%S')\",
        \"parse_mode\": \"Markdown\"
    }")

if echo "$TEST_RESPONSE" | grep -q '"ok":true'; then
    print_success "Test message sent successfully!"
else
    print_warning "Test message failed, but webhook is set"
fi

# Update .env file
print_status "Updating .env file..."
cd backend

# Add Telegram configuration to .env
cat >> .env << EOF

# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=${BOT_TOKEN}
TELEGRAM_CHAT_ID=${CHAT_ID}
TELEGRAM_ADMIN_CHAT_ID=${CHAT_ID}
TELEGRAM_WEBHOOK_SECRET=${WEBHOOK_SECRET}
TELEGRAM_WEBHOOK_URL=${WEBHOOK_URL}
TELEGRAM_WEBHOOK_ENABLED=true

# Telegram Feature Flags
TELEGRAM_DEPLOYMENT_NOTIFICATIONS=true
TELEGRAM_USER_REGISTRATION_NOTIFICATIONS=true
TELEGRAM_CATCH_NOTIFICATIONS=true
TELEGRAM_PAYMENT_NOTIFICATIONS=true
TELEGRAM_POINT_NOTIFICATIONS=true
TELEGRAM_DAILY_STATISTICS=true
TELEGRAM_ERROR_NOTIFICATIONS=true
EOF

print_success "Configuration added to .env file!"

# Set up GitHub secrets
print_status "Setting up GitHub secrets..."

if command -v gh &> /dev/null; then
    if gh auth status &> /dev/null; then
        gh secret set TELEGRAM_BOT_TOKEN --body "$BOT_TOKEN"
        gh secret set TELEGRAM_CHAT_ID --body "$CHAT_ID"
        print_success "GitHub secrets configured!"
    else
        print_warning "GitHub CLI not authenticated. Please run 'gh auth login' and set secrets manually:"
        echo "gh secret set TELEGRAM_BOT_TOKEN --body \"$BOT_TOKEN\""
        echo "gh secret set TELEGRAM_CHAT_ID --body \"$CHAT_ID\""
    fi
else
    print_warning "GitHub CLI not installed. Please set secrets manually in GitHub repository settings:"
    echo "TELEGRAM_BOT_TOKEN = $BOT_TOKEN"
    echo "TELEGRAM_CHAT_ID = $CHAT_ID"
fi

print_success "Telegram bot setup completed!"
echo ""
print_status "Bot Information:"
echo "Username: @${BOT_USERNAME}"
echo "Chat ID: ${CHAT_ID}"
echo "Webhook URL: ${WEBHOOK_URL}"
echo ""
print_status "Available Commands:"
echo "/start - Start the bot"
echo "/help - Show available commands"
echo "/stats - Get current statistics"
echo "/users - Get user statistics"
echo "/catches - Get catch statistics"
echo "/payments - Get payment statistics"
echo "/points - Get fishing points statistics"
echo "/status - Get application status"
echo ""
print_status "Next Steps:"
echo "1. Test the bot by sending /start to @${BOT_USERNAME}"
echo "2. The bot will automatically send notifications for:"
echo "   - New user registrations"
echo "   - New catch records"
echo "   - New payments"
echo "   - New fishing points"
echo "   - Daily statistics (at 9:00 AM Moscow time)"
echo "   - Deployment updates"
echo ""
print_status "Feature Flags:"
echo "You can enable/disable specific notifications by setting environment variables:"
echo "TELEGRAM_DEPLOYMENT_NOTIFICATIONS=true/false"
echo "TELEGRAM_USER_REGISTRATION_NOTIFICATIONS=true/false"
echo "TELEGRAM_CATCH_NOTIFICATIONS=true/false"
echo "TELEGRAM_PAYMENT_NOTIFICATIONS=true/false"
echo "TELEGRAM_POINT_NOTIFICATIONS=true/false"
echo "TELEGRAM_DAILY_STATISTICS=true/false"
echo "TELEGRAM_ERROR_NOTIFICATIONS=true/false"
echo ""
print_warning "Important:"
echo "- Make sure your server is accessible from the internet"
echo "- Configure firewall to allow HTTP/HTTPS traffic"
echo "- Set up SSL certificate for secure webhook communication"
echo "- Monitor webhook delivery in Telegram Bot API"
