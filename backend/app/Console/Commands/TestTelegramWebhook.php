<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestTelegramWebhook extends Command
{
    protected $signature = 'telegram:test-webhook';
    protected $description = 'Test Telegram webhook by sending a test message';

    public function handle()
    {
        $this->info('Testing Telegram webhook...');
        
        // Test webhook endpoint
        $webhookUrl = config('telegram.webhook.url');
        if (!$webhookUrl) {
            $this->error('Webhook URL not configured');
            return 1;
        }
        
        $this->info("Webhook URL: {$webhookUrl}");
        
        // Create test message
        $testMessage = [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 1,
                'from' => [
                    'id' => 123456789,
                    'is_bot' => false,
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'username' => 'testuser',
                    'language_code' => 'en'
                ],
                'chat' => [
                    'id' => 123456789,
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'username' => 'testuser',
                    'type' => 'private'
                ],
                'date' => time(),
                'text' => '/start'
            ]
        ];
        
        try {
            $response = Http::timeout(10)->post($webhookUrl, $testMessage);
            
            if ($response->successful()) {
                $this->info('✅ Webhook test successful!');
                $this->line('Response: ' . $response->body());
                return 0;
            } else {
                $this->error('❌ Webhook test failed');
                $this->line('Status: ' . $response->status());
                $this->line('Response: ' . $response->body());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}