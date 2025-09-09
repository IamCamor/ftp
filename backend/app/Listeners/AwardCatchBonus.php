<?php

namespace App\Listeners;

use App\Events\CatchRecorded;
use App\Services\BonusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AwardCatchBonus implements ShouldQueue
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
    public function handle(CatchRecorded $event): void
    {
        try {
            $catch = $event->catch;
            $user = $catch->user;

            // Check if user can receive bonus for this action
            if (!$this->bonusService->canPerformAction($user, 'catch_recorded')) {
                Log::info('User cannot receive catch bonus due to rate limiting', [
                    'user_id' => $user->id,
                    'catch_id' => $catch->id
                ]);
                return;
            }

            // Award bonus
            $transaction = $this->bonusService->awardCatchBonus($user, $catch);

            Log::info('Catch bonus awarded', [
                'user_id' => $user->id,
                'catch_id' => $catch->id,
                'bonus_amount' => $transaction->amount,
                'transaction_id' => $transaction->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to award catch bonus', [
                'error' => $e->getMessage(),
                'catch_id' => $event->catch->id
            ]);
        }
    }
}
