<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetTelegramBotInfo extends Command
{
    protected $signature = 'telegram:info';
    protected $description = 'Get Telegram bot information';

    public function handle()
    {
        $botToken = config('telegram.bot_token');
        if (!$botToken) {
            $this->error('Bot token not configured');
            return 1;
        }
        
        $this->info("Bot Token: {$botToken}");
        
        try {
            // Get bot info
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$botToken}/getMe");
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['ok']) {
                    $bot = $data['result'];
                    $this->info('✅ Bot Info:');
                    $this->line("Name: {$bot['first_name']}");
                    $this->line("Username: @{$bot['username']}");
                    $this->line("ID: {$bot['id']}");
                    $this->line("Can join groups: " . ($bot['can_join_groups'] ? 'Yes' : 'No'));
                    $this->line("Can read all group messages: " . ($bot['can_read_all_group_messages'] ? 'Yes' : 'No'));
                    $this->line("Supports inline queries: " . ($bot['supports_inline_queries'] ? 'Yes' : 'No'));
                } else {
                    $this->error('❌ Bot API error: ' . $data['description']);
                    return 1;
                }
            } else {
                $this->error('❌ Failed to get bot info');
                $this->line('Response: ' . $response->body());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
        
        // Get webhook info
        $this->info("\n🔗 Webhook Info:");
        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$botToken}/getWebhookInfo");
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['ok']) {
                    $webhook = $data['result'];
                    $this->line("URL: " . ($webhook['url'] ?: 'Not set'));
                    $this->line("Pending updates: " . $webhook['pending_update_count']);
                    $this->line("Last error: " . ($webhook['last_error_message'] ?: 'None'));
                }
            }
        } catch (\Exception $e) {
            $this->error('Error getting webhook info: ' . $e->getMessage());
        }
        
        return 0;
    }
}