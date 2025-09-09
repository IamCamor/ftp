<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SetupTelegramBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:setup {--webhook-url=} {--delete-webhook}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Telegram bot webhook and commands';

    private TelegramService $telegramService;

    /**
     * Create a new command instance.
     */
    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            if ($this->option('delete-webhook')) {
                return $this->deleteWebhook();
            }

            $webhookUrl = $this->option('webhook-url');
            if (!$webhookUrl) {
                $webhookUrl = $this->ask('Enter webhook URL (e.g., https://yourdomain.com/api/telegram/webhook)');
            }

            if (!$webhookUrl) {
                $this->error('Webhook URL is required');
                return 1;
            }

            $this->info('Setting up Telegram bot...');
            
            // Set webhook
            $this->info('Setting webhook URL: ' . $webhookUrl);
            $result = $this->telegramService->setWebhook($webhookUrl);
            
            if ($result['ok']) {
                $this->info('✅ Webhook set successfully!');
            } else {
                $this->error('❌ Failed to set webhook: ' . ($result['description'] ?? 'Unknown error'));
                return 1;
            }

            // Get webhook info
            $this->info('Getting webhook info...');
            $info = $this->telegramService->getWebhookInfo();
            
            if ($info['ok']) {
                $webhookInfo = $info['result'];
                $this->info('Webhook URL: ' . ($webhookInfo['url'] ?? 'Not set'));
                $this->info('Pending updates: ' . ($webhookInfo['pending_update_count'] ?? 0));
                $this->info('Last error: ' . ($webhookInfo['last_error_message'] ?? 'None'));
            }

            // Test notification
            $this->info('Sending test notification...');
            $testMessage = "🤖 *Telegram Bot Setup Complete*\n\n" .
                          "Bot is now configured and ready to receive notifications!\n" .
                          "Time: " . now()->format('Y-m-d H:i:s');
            
            $success = $this->telegramService->sendMessage($testMessage);
            
            if ($success) {
                $this->info('✅ Test notification sent successfully!');
            } else {
                $this->warn('⚠️  Test notification failed, but webhook is set');
            }

            $this->info('');
            $this->info('🎉 Telegram bot setup completed!');
            $this->info('');
            $this->info('Available commands:');
            $this->info('/start - Start the bot');
            $this->info('/help - Show available commands');
            $this->info('/stats - Get current statistics');
            $this->info('/users - Get user statistics');
            $this->info('/catches - Get catch statistics');
            $this->info('/payments - Get payment statistics');
            $this->info('/points - Get fishing points statistics');
            $this->info('/status - Get application status');

            return 0;
        } catch (\Exception $e) {
            $this->error('Error setting up Telegram bot: ' . $e->getMessage());
            Log::error('Error setting up Telegram bot', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Delete webhook
     */
    private function deleteWebhook(): int
    {
        try {
            $this->info('Deleting webhook...');
            
            $result = $this->telegramService->deleteWebhook();
            
            if ($result['ok']) {
                $this->info('✅ Webhook deleted successfully!');
                return 0;
            } else {
                $this->error('❌ Failed to delete webhook: ' . ($result['description'] ?? 'Unknown error'));
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Error deleting webhook: ' . $e->getMessage());
            return 1;
        }
    }
}
