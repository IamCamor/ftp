<?php

namespace App\Listeners;

use App\Events\PointCreated;
use App\Services\BonusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AwardPointBonus implements ShouldQueue
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
    public function handle(PointCreated $event): void
    {
        try {
            $point = $event->point;
            $user = $point->user;

            // Check if user can receive bonus for this action
            if (!$this->bonusService->canPerformAction($user, 'point_created')) {
                Log::info('User cannot receive point bonus due to rate limiting', [
                    'user_id' => $user->id,
                    'point_id' => $point->id
                ]);
                return;
            }

            // Award bonus
            $transaction = $this->bonusService->awardPointBonus($user, $point);

            Log::info('Point bonus awarded', [
                'user_id' => $user->id,
                'point_id' => $point->id,
                'bonus_amount' => $transaction->amount,
                'transaction_id' => $transaction->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to award point bonus', [
                'error' => $e->getMessage(),
                'point_id' => $event->point->id
            ]);
        }
    }
}
