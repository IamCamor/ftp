<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetTelegramUpdates extends Command
{
    protected $signature = 'telegram:updates';
    protected $description = 'Get recent Telegram updates';

    public function handle()
    {
        $botToken = config('telegram.bot_token');
        if (!$botToken) {
            $this->error('Bot token not configured');
            return 1;
        }
        
        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$botToken}/getUpdates");
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['ok']) {
                    $updates = $data['result'];
                    $this->info("Found " . count($updates) . " updates");
                    
                    foreach ($updates as $update) {
                        if (isset($update['message'])) {
                            $message = $update['message'];
                            $this->line("Message from: " . $message['from']['first_name'] . " (ID: " . $message['from']['id'] . ")");
                            $this->line("Chat ID: " . $message['chat']['id']);
                            $this->line("Text: " . ($message['text'] ?? 'No text'));
                            $this->line("---");
                        }
                    }
                    
                    if (count($updates) === 0) {
                        $this->info("No updates found. Send a message to @fishtrackpro_bot to get your chat ID.");
                    }
                } else {
                    $this->error('❌ Bot API error: ' . $data['description']);
                    return 1;
                }
            } else {
                $this->error('❌ Failed to get updates');
                $this->line('Response: ' . $response->body());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}