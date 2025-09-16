<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TestTelegramNotifications extends Command
{
    protected $signature = 'telegram:test-notifications {chat_id}';
    protected $description = 'Test Telegram notifications by sending sample messages';

    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle()
    {
        $chatId = $this->argument('chat_id');
        
        $this->info("Testing Telegram notifications with chat ID: {$chatId}");
        
        // Test catch notification
        $this->info("Testing catch notification...");
        $catchData = [
            'user_name' => 'Test User',
            'username' => 'testuser',
            'fish_type' => 'Щука',
            'weight' => '2.5',
            'length' => '45',
            'location' => 'Озеро Светлое',
            'time' => now()->format('Y-m-d H:i:s')
        ];
        
        $success = $this->telegramService->sendCatchNotification($catchData, $chatId);
        if ($success) {
            $this->info('✅ Catch notification sent successfully!');
        } else {
            $this->error('❌ Failed to send catch notification');
        }
        
        // Test user registration notification
        $this->info("Testing user registration notification...");
        $userData = [
            'name' => 'New User',
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'role' => 'user',
            'time' => now()->format('Y-m-d H:i:s')
        ];
        
        $success = $this->telegramService->sendUserRegistrationNotification($userData, $chatId);
        if ($success) {
            $this->info('✅ User registration notification sent successfully!');
        } else {
            $this->error('❌ Failed to send user registration notification');
        }
        
        // Test bonus notification
        $this->info("Testing bonus notification...");
        $bonusData = [
            'user_name' => 'Test User',
            'username' => 'testuser',
            'action' => 'Добавление улова',
            'amount' => '10',
            'balance' => '150',
            'time' => now()->format('Y-m-d H:i:s')
        ];
        
        $success = $this->telegramService->sendBonusNotification($bonusData, $chatId);
        if ($success) {
            $this->info('✅ Bonus notification sent successfully!');
        } else {
            $this->error('❌ Failed to send bonus notification');
        }
        
        return 0;
    }
}