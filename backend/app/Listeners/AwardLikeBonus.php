<?php

namespace App\Listeners;

use App\Events\LikeGiven;
use App\Services\BonusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AwardLikeBonus implements ShouldQueue
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
    public function handle(LikeGiven $event): void
    {
        try {
            $like = $event->like;
            $user = $like->user;

            // Check if user can receive bonus for this action
            if (!$this->bonusService->canPerformAction($user, 'like_given')) {
                Log::info('User cannot receive like bonus due to rate limiting', [
                    'user_id' => $user->id,
                    'like_id' => $like->id
                ]);
                return;
            }

            // Award bonus
            $transaction = $this->bonusService->awardLikeBonus($user, $like);

            Log::info('Like bonus awarded', [
                'user_id' => $user->id,
                'like_id' => $like->id,
                'bonus_amount' => $transaction->amount,
                'transaction_id' => $transaction->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to award like bonus', [
                'error' => $e->getMessage(),
                'like_id' => $event->like->id
            ]);
        }
    }
}
