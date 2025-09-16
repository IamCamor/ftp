<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TestTelegramBotCommands extends Command
{
    protected $signature = 'telegram:test-commands';
    protected $description = 'Test Telegram bot commands';

    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle()
    {
        $this->info('Testing Telegram bot commands...');
        $this->line('');
        
        $commands = [
            'start' => 'Start the bot',
            'help' => 'Show available commands',
            'stats' => 'Get current statistics',
            'users' => 'Get user statistics',
            'catches' => 'Get catch statistics',
            'payments' => 'Get payment statistics',
            'points' => 'Get fishing points statistics',
            'status' => 'Get application status'
        ];
        
        foreach ($commands as $command => $description) {
            $this->info("Testing command: /{$command}");
            $this->line("Description: {$description}");
            
            try {
                $response = $this->telegramService->handleCommand($command);
                $this->line("Response: " . substr($response, 0, 100) . (strlen($response) > 100 ? '...' : ''));
                $this->line('✅ Command handled successfully');
            } catch (\Exception $e) {
                $this->line('❌ Command failed: ' . $e->getMessage());
            }
            
            $this->line('');
        }
        
        $this->info('🎉 All command tests completed!');
        
        return 0;
    }
}