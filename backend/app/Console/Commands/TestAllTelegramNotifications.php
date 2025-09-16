<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TestAllTelegramNotifications extends Command
{
    protected $signature = 'telegram:test-all {chat_id}';
    protected $description = 'Test all Telegram notification types';

    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle()
    {
        $chatId = $this->argument('chat_id');
        
        $this->info("Testing all Telegram notifications to chat ID: {$chatId}");
        $this->line('');
        
        // Test 1: Catch notification
        $this->info('1. Testing catch notification...');
        $catchData = [
            'user_name' => 'Алексей Рыбаков',
            'username' => 'alex_fisher',
            'fish_type' => 'Окунь',
            'weight' => '1.8',
            'length' => '35',
            'location' => 'Река Быстрая',
            'time' => now()->format('Y-m-d H:i:s')
        ];
        
        $success = $this->telegramService->sendCatchNotification($catchData, $chatId);
        $this->line($success ? '✅ Catch notification sent' : '❌ Catch notification failed');
        $this->line('');
        
        // Test 2: User registration notification
        $this->info('2. Testing user registration notification...');
        $userData = [
            'name' => 'Мария Соколова',
            'username' => 'maria_fisher',
            'email' => 'maria@example.com',
            'role' => 'user',
            'time' => now()->format('Y-m-d H:i:s')
        ];
        
        $success = $this->telegramService->sendUserRegistrationNotification($userData, $chatId);
        $this->line($success ? '✅ User registration notification sent' : '❌ User registration notification failed');
        $this->line('');
        
        // Test 3: Bonus notification
        $this->info('3. Testing bonus notification...');
        $bonusData = [
            'user_name' => 'Дмитрий Козлов',
            'username' => 'dmitry_fisher',
            'action' => 'Добавление улова',
            'amount' => '15',
            'balance' => '250',
            'time' => now()->format('Y-m-d H:i:s')
        ];
        
        $success = $this->telegramService->sendBonusNotification($bonusData, $chatId);
        $this->line($success ? '✅ Bonus notification sent' : '❌ Bonus notification failed');
        $this->line('');
        
        // Test 4: Point notification
        $this->info('4. Testing point notification...');
        $pointData = [
            'user_name' => 'Елена Волкова',
            'username' => 'elena_fisher',
            'title' => 'Тайное озеро',
            'location' => '55.7558, 37.6176',
            'privacy' => 'Приватное',
            'time' => now()->format('Y-m-d H:i:s')
        ];
        
        $success = $this->telegramService->sendPointNotification($pointData, $chatId);
        $this->line($success ? '✅ Point notification sent' : '❌ Point notification failed');
        $this->line('');
        
        // Test 5: Payment notification
        $this->info('5. Testing payment notification...');
        $paymentData = [
            'user_name' => 'Сергей Морозов',
            'username' => 'sergey_fisher',
            'amount' => '299',
            'currency' => 'RUB',
            'payment_type' => 'Pro подписка',
            'status' => 'Завершен',
            'time' => now()->format('Y-m-d H:i:s')
        ];
        
        $success = $this->telegramService->sendPaymentNotification($paymentData, $chatId);
        $this->line($success ? '✅ Payment notification sent' : '❌ Payment notification failed');
        $this->line('');
        
        $this->info('🎉 All notification tests completed!');
        
        return 0;
    }
}