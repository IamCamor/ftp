<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TelegramService
{
    private ?string $botToken;
    private ?string $chatId;
    private ?string $adminChatId;
    private array $features;

    public function __construct()
    {
        $this->botToken = config('telegram.bot_token');
        $this->chatId = config('telegram.chat_id');
        $this->adminChatId = config('telegram.admin_chat_id');
        $this->features = config('telegram.features', []);
    }

    /**
     * Send message to Telegram
     */
    public function sendMessage(string $message, ?string $chatId = null, array $options = []): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Telegram bot not configured');
            return false;
        }

        $chatId = $chatId ?? $this->chatId;
        
        // Check rate limiting
        if (!$this->checkRateLimit()) {
            Log::warning('Telegram rate limit exceeded');
            return false;
        }

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                ...$options
            ]);

            if ($response->successful()) {
                $this->incrementRateLimit();
                Log::info('Telegram message sent successfully', ['chat_id' => $chatId]);
                return true;
            } else {
                Log::error('Failed to send Telegram message', [
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Telegram API error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send deployment notification
     */
    public function sendDeploymentNotification(array $data): bool
    {
        if (!$this->isFeatureEnabled('deployment_notifications')) {
            return false;
        }

        $message = $this->formatMessage('deployment', $data);
        return $this->sendMessage($message, $this->adminChatId);
    }

    /**
     * Send user registration notification
     */
    public function sendUserRegistrationNotification(array $data): bool
    {
        if (!$this->isFeatureEnabled('user_registration_notifications')) {
            return false;
        }

        $message = $this->formatMessage('user_registration', $data);
        return $this->sendMessage($message, $this->adminChatId);
    }

    /**
     * Send catch notification
     */
    public function sendCatchNotification(array $data): bool
    {
        if (!$this->isFeatureEnabled('catch_notifications')) {
            return false;
        }

        $message = $this->formatMessage('catch', $data);
        return $this->sendMessage($message, $this->chatId);
    }

    /**
     * Send payment notification
     */
    public function sendPaymentNotification(array $data): bool
    {
        if (!$this->isFeatureEnabled('payment_notifications')) {
            return false;
        }

        $message = $this->formatMessage('payment', $data);
        return $this->sendMessage($message, $this->adminChatId);
    }

    /**
     * Send fishing point notification
     */
    public function sendPointNotification(array $data): bool
    {
        if (!$this->isFeatureEnabled('point_notifications')) {
            return false;
        }

        $message = $this->formatMessage('point', $data);
        return $this->sendMessage($message, $this->chatId);
    }

    /**
     * Send error notification
     */
    public function sendErrorNotification(array $data): bool
    {
        if (!$this->isFeatureEnabled('error_notifications')) {
            return false;
        }

        $message = $this->formatMessage('error', $data);
        return $this->sendMessage($message, $this->adminChatId);
    }

    /**
     * Send daily statistics
     */
    public function sendDailyStatistics(): bool
    {
        if (!$this->isFeatureEnabled('daily_statistics')) {
            return false;
        }

        $stats = $this->getDailyStatistics();
        $message = $this->formatDailyStatistics($stats);
        
        return $this->sendMessage($message, $this->adminChatId);
    }

    /**
     * Send bonus notification
     */
    public function sendBonusNotification(array $data): bool
    {
        if (!$this->isFeatureEnabled('bonus_notifications')) {
            return false;
        }

        $message = $this->formatMessage('bonus', $data);
        return $this->sendMessage($message, $this->chatId);
    }

    /**
     * Handle bot commands
     */
    public function handleCommand(string $command, array $data = []): string
    {
        switch ($command) {
            case 'start':
                return $this->getWelcomeMessage();
            case 'help':
                return $this->getHelpMessage();
            case 'stats':
                return $this->getCurrentStatistics();
            case 'users':
                return $this->getUserStatistics();
            case 'catches':
                return $this->getCatchStatistics();
            case 'payments':
                return $this->getPaymentStatistics();
            case 'points':
                return $this->getPointStatistics();
            case 'status':
                return $this->getApplicationStatus();
            default:
                return "Unknown command. Use /help to see available commands.";
        }
    }

    /**
     * Format message using template
     */
    private function formatMessage(string $type, array $data): string
    {
        $template = config("telegram.notifications.{$type}.template", '');
        
        $replacements = [
            '{time}' => now()->format('Y-m-d H:i:s'),
            '{date}' => now()->format('Y-m-d'),
            ...$data
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Format daily statistics
     */
    private function formatDailyStatistics(array $stats): string
    {
        $template = config('telegram.daily_stats_template', '');
        
        $replacements = [
            '{date}' => now()->format('Y-m-d'),
            '{new_users}' => $stats['new_users'],
            '{total_users}' => $stats['total_users'],
            '{active_users}' => $stats['active_users'],
            '{new_catches}' => $stats['new_catches'],
            '{total_catches}' => $stats['total_catches'],
            '{total_weight}' => $stats['total_weight'],
            '{new_points}' => $stats['new_points'],
            '{total_points}' => $stats['total_points'],
            '{new_payments}' => $stats['new_payments'],
            '{total_revenue}' => $stats['total_revenue'],
            '{currency}' => 'RUB',
            '{users_growth}' => $stats['users_growth'],
            '{catches_growth}' => $stats['catches_growth'],
            '{revenue_growth}' => $stats['revenue_growth'],
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Get daily statistics
     */
    private function getDailyStatistics(): array
    {
        $today = now()->startOfDay();
        $yesterday = $today->copy()->subDay();
        $weekAgo = $today->copy()->subWeek();

        // Users
        $newUsers = \App\Models\User::whereDate('created_at', $today)->count();
        $totalUsers = \App\Models\User::count();
        $activeUsers = \App\Models\User::where('last_seen_at', '>=', $today->subDay())->count();
        $usersGrowth = $this->calculateGrowth(
            \App\Models\User::whereDate('created_at', $yesterday)->count(),
            $newUsers
        );

        // Catches
        $newCatches = \App\Models\CatchRecord::whereDate('created_at', $today)->count();
        $totalCatches = \App\Models\CatchRecord::count();
        $totalWeight = \App\Models\CatchRecord::whereDate('created_at', $today)->sum('weight');
        $catchesGrowth = $this->calculateGrowth(
            \App\Models\CatchRecord::whereDate('created_at', $yesterday)->count(),
            $newCatches
        );

        // Points
        $newPoints = \App\Models\Point::whereDate('created_at', $today)->count();
        $totalPoints = \App\Models\Point::count();

        // Payments
        $newPayments = \App\Models\Payment::whereDate('created_at', $today)->count();
        $totalRevenue = \App\Models\Payment::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('amount');
        $revenueGrowth = $this->calculateGrowth(
            \App\Models\Payment::whereDate('created_at', $yesterday)
                ->where('status', 'completed')
                ->sum('amount'),
            $totalRevenue
        );

        return [
            'new_users' => $newUsers,
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'new_catches' => $newCatches,
            'total_catches' => $totalCatches,
            'total_weight' => round($totalWeight, 2),
            'new_points' => $newPoints,
            'total_points' => $totalPoints,
            'new_payments' => $newPayments,
            'total_revenue' => round($totalRevenue, 2),
            'users_growth' => $usersGrowth,
            'catches_growth' => $catchesGrowth,
            'revenue_growth' => $revenueGrowth,
        ];
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowth(float $previous, float $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Get welcome message
     */
    private function getWelcomeMessage(): string
    {
        return "🤖 *FishTrackPro Bot*\n\n" .
               "Welcome! I'm here to keep you updated about FishTrackPro.\n\n" .
               "Use /help to see available commands.";
    }

    /**
     * Get help message
     */
    private function getHelpMessage(): string
    {
        $commands = config('telegram.commands', []);
        $message = "📋 *Available Commands:*\n\n";
        
        foreach ($commands as $command => $description) {
            $message .= "/{$command} - {$description}\n";
        }
        
        return $message;
    }

    /**
     * Get current statistics
     */
    private function getCurrentStatistics(): string
    {
        $stats = $this->getDailyStatistics();
        return $this->formatDailyStatistics($stats);
    }

    /**
     * Get user statistics
     */
    private function getUserStatistics(): string
    {
        $total = \App\Models\User::count();
        $today = \App\Models\User::whereDate('created_at', today())->count();
        $active = \App\Models\User::where('last_seen_at', '>=', now()->subDay())->count();
        
        return "👥 *User Statistics*\n\n" .
               "Total users: {$total}\n" .
               "New today: {$today}\n" .
               "Active (24h): {$active}";
    }

    /**
     * Get catch statistics
     */
    private function getCatchStatistics(): string
    {
        $total = \App\Models\CatchRecord::count();
        $today = \App\Models\CatchRecord::whereDate('created_at', today())->count();
        $totalWeight = \App\Models\CatchRecord::sum('weight');
        
        return "🎣 *Catch Statistics*\n\n" .
               "Total catches: {$total}\n" .
               "New today: {$today}\n" .
               "Total weight: " . round($totalWeight, 2) . " kg";
    }

    /**
     * Get payment statistics
     */
    private function getPaymentStatistics(): string
    {
        $total = \App\Models\Payment::where('status', 'completed')->count();
        $today = \App\Models\Payment::whereDate('created_at', today())
            ->where('status', 'completed')->count();
        $revenue = \App\Models\Payment::where('status', 'completed')->sum('amount');
        
        return "💳 *Payment Statistics*\n\n" .
               "Total payments: {$total}\n" .
               "New today: {$today}\n" .
               "Total revenue: " . round($revenue, 2) . " RUB";
    }

    /**
     * Get point statistics
     */
    private function getPointStatistics(): string
    {
        $total = \App\Models\Point::count();
        $today = \App\Models\Point::whereDate('created_at', today())->count();
        
        return "📍 *Fishing Points Statistics*\n\n" .
               "Total points: {$total}\n" .
               "New today: {$today}";
    }

    /**
     * Get application status
     */
    private function getApplicationStatus(): string
    {
        $status = "🟢 Online";
        $uptime = "Unknown";
        
        try {
            $response = Http::timeout(5)->get(config('app.url') . '/api/health');
            if (!$response->successful()) {
                $status = "🔴 Offline";
            }
        } catch (\Exception $e) {
            $status = "🔴 Offline";
        }
        
        return "📊 *Application Status*\n\n" .
               "Status: {$status}\n" .
               "Environment: " . config('app.env') . "\n" .
               "Version: " . config('app.version', '1.0.0');
    }

    /**
     * Check if feature is enabled
     */
    private function isFeatureEnabled(string $feature): bool
    {
        return $this->features[$feature] ?? false;
    }

    /**
     * Check if bot is configured
     */
    private function isConfigured(): bool
    {
        return !empty($this->botToken) && !empty($this->chatId);
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit(): bool
    {
        if (!config('telegram.rate_limiting.enabled', true)) {
            return true;
        }

        $key = 'telegram_rate_limit_' . now()->format('Y-m-d-H-i');
        $count = Cache::get($key, 0);
        
        return $count < config('telegram.rate_limiting.max_notifications_per_minute', 10);
    }

    /**
     * Increment rate limit counter
     */
    private function incrementRateLimit(): void
    {
        if (!config('telegram.rate_limiting.enabled', true)) {
            return;
        }

        $key = 'telegram_rate_limit_' . now()->format('Y-m-d-H-i');
        Cache::increment($key);
        Cache::expire($key, 60); // Expire after 1 minute
    }

    /**
     * Set webhook URL
     */
    public function setWebhook(string $url): array
    {
        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/setWebhook", [
                'url' => $url,
                'secret_token' => config('telegram.webhook.secret_token'),
                'allowed_updates' => ['message', 'callback_query']
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to set Telegram webhook', ['error' => $e->getMessage()]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): array
    {
        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$this->botToken}/getWebhookInfo");
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to get Telegram webhook info', ['error' => $e->getMessage()]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(): array
    {
        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/deleteWebhook");
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to delete Telegram webhook', ['error' => $e->getMessage()]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
