<?php

namespace App\Listeners;

use App\Events\FriendAdded;
use App\Services\BonusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AwardFriendBonus implements ShouldQueue
{
    use InteractsWithQueue;

    private BonusService $bonusService;

    /**
     * Create the event listener.
     */
    public function __construct(BonusService $bonusService)
    {
        $this->bonusService = $bonusService;
    }

    /**
     * Handle the event.
     */
    public function handle(FriendAdded $event): void
    {
        try {
            $user = $event->user;
            $friend = $event->friend;

            // Check if user can receive bonus for this action
            if (!$this->bonusService->canPerformAction($user, 'friend_added')) {
                Log::info('User cannot receive friend bonus due to rate limiting', [
                    'user_id' => $user->id,
                    'friend_id' => $friend->id
                ]);
                return;
            }

            // Award bonus
            $transaction = $this->bonusService->awardFriendBonus($user, $friend);

            Log::info('Friend bonus awarded', [
                'user_id' => $user->id,
                'friend_id' => $friend->id,
                'bonus_amount' => $transaction->amount,
                'transaction_id' => $transaction->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to award friend bonus', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id,
                'friend_id' => $event->friend->id
            ]);
        }
    }
}
