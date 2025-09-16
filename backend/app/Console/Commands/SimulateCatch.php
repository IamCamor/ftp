<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class SimulateCatch extends Command
{
    protected $signature = 'telegram:simulate-catch {chat_id}';
    protected $description = 'Simulate a new catch and send Telegram notification';

    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle()
    {
        $chatId = $this->argument('chat_id');
        
        $this->info("Simulating new catch notification to chat ID: {$chatId}");
        
        // Simulate catch data
        $catchData = [
            'user_name' => 'Иван Петров',
            'username' => 'ivan_petrov',
            'fish_type' => 'Щука',
            'weight' => '3.2',
            'length' => '52',
            'location' => 'Озеро Светлое',
            'time' => now()->format('Y-m-d H:i:s')
        ];
        
        $this->info('Catch data:');
        $this->line('User: ' . $catchData['user_name'] . ' (@' . $catchData['username'] . ')');
        $this->line('Fish: ' . $catchData['fish_type']);
        $this->line('Weight: ' . $catchData['weight'] . ' kg');
        $this->line('Length: ' . $catchData['length'] . ' cm');
        $this->line('Location: ' . $catchData['location']);
        $this->line('Time: ' . $catchData['time']);
        
        // Send notification
        $success = $this->telegramService->sendCatchNotification($catchData, $chatId);
        
        if ($success) {
            $this->info('✅ Catch notification sent successfully!');
            return 0;
        } else {
            $this->error('❌ Failed to send catch notification');
            return 1;
        }
    }
}