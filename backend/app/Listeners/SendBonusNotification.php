<?php

namespace App\Listeners;

use App\Events\BonusAwarded;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendBonusNotification implements ShouldQueue
{
    use InteractsWithQueue;

    private TelegramService $telegramService;

    /**
     * Create the event listener.
     */
    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle the event.
     */
    public function handle(BonusAwarded $event): void
    {
        try {
            $transaction = $event->transaction;
            $user = $transaction->user;
            
            // Only send notifications for significant bonuses
            if ($transaction->amount < 50) {
                return;
            }

            $data = [
                '{user_name}' => $user->name,
                '{username}' => $user->username,
                '{action}' => $transaction->action_description,
                '{amount}' => $transaction->amount,
                '{balance}' => $user->bonus_balance,
            ];

            $message = $this->formatBonusMessage($data);
            $this->telegramService->sendMessage($message, config('telegram.chat_id'));
        } catch (\Exception $e) {
            Log::error('Failed to send bonus notification', [
                'error' => $e->getMessage(),
                'transaction_id' => $event->transaction->id
            ]);
        }
    }

    /**
     * Format bonus notification message
     */
    private function formatBonusMessage(array $data): string
    {
        return "🎁 *Бонус начислен!*\n\n" .
               "Пользователь: {$data['{user_name}']} (@{$data['{username}']})\n" .
               "Действие: {$data['{action}']}\n" .
               "Бонус: +{$data['{amount}']} 🪙\n" .
               "Баланс: {$data['{balance}']} 🪙\n" .
               "Время: " . now()->format('Y-m-d H:i:s');
    }
}
