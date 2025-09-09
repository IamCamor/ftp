<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDailyTelegramStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:daily-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily statistics to Telegram';

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
            $this->info('Sending daily statistics to Telegram...');
            
            $success = $this->telegramService->sendDailyStatistics();
            
            if ($success) {
                $this->info('Daily statistics sent successfully!');
                Log::info('Daily Telegram statistics sent successfully');
                return 0;
            } else {
                $this->error('Failed to send daily statistics');
                Log::error('Failed to send daily Telegram statistics');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Error sending daily statistics: ' . $e->getMessage());
            Log::error('Error sending daily Telegram statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
