<?php

namespace App\Listeners;

use App\Events\CommentAdded;
use App\Services\BonusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AwardCommentBonus implements ShouldQueue
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
    public function handle(CommentAdded $event): void
    {
        try {
            $comment = $event->comment;
            $user = $comment->user;

            // Check if user can receive bonus for this action
            if (!$this->bonusService->canPerformAction($user, 'comment_added')) {
                Log::info('User cannot receive comment bonus due to rate limiting', [
                    'user_id' => $user->id,
                    'comment_id' => $comment->id
                ]);
                return;
            }

            // Award bonus
            $transaction = $this->bonusService->awardCommentBonus($user, $comment);

            Log::info('Comment bonus awarded', [
                'user_id' => $user->id,
                'comment_id' => $comment->id,
                'bonus_amount' => $transaction->amount,
                'transaction_id' => $transaction->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to award comment bonus', [
                'error' => $e->getMessage(),
                'comment_id' => $event->comment->id
            ]);
        }
    }
}
