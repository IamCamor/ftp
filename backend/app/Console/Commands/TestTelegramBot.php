<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestTelegramBot extends Command
{
    protected $signature = 'telegram:test {chat_id}';
    protected $description = 'Test Telegram bot by sending a message to specified chat ID';

    public function handle()
    {
        $chatId = $this->argument('chat_id');
        
        $this->info("Testing Telegram bot with chat ID: {$chatId}");
        
        // Test direct API call
        $botToken = config('telegram.bot_token');
        if (!$botToken) {
            $this->error('Bot token not configured');
            return 1;
        }
        
        $message = "🎣 *FishTrack Pro Bot Test*\n\n" .
                  "✅ Bot is working!\n" .
                  "🕐 Time: " . now()->format('Y-m-d H:i:s') . "\n" .
                  "🌐 Server: " . config('app.url');
        
        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);
            
            if ($response->successful()) {
                $this->info('✅ Message sent successfully!');
                $this->line('Response: ' . $response->body());
                return 0;
            } else {
                $this->error('❌ Failed to send message');
                $this->line('Response: ' . $response->body());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}