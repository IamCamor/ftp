<?php

namespace App\Services;

use App\Models\User;
use App\Models\BonusTransaction;
use App\Models\CatchRecord;
use App\Models\Point;
use App\Models\CatchComment;
use App\Models\CatchLike;
use App\Events\BonusAwarded;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BonusService
{
    /**
     * Award bonus to user for specific action
     */
    public function awardBonus(
        User $user,
        string $action,
        int $amount,
        ?string $description = null,
        array $metadata = [],
        ?User $relatedUser = null,
        ?CatchRecord $relatedCatch = null,
        ?Point $relatedPoint = null,
        ?CatchComment $relatedComment = null,
        ?CatchLike $relatedLike = null
    ): BonusTransaction {
        return DB::transaction(function () use (
            $user, $action, $amount, $description, $metadata,
            $relatedUser, $relatedCatch, $relatedPoint, $relatedComment, $relatedLike
        ) {
            // Create bonus transaction
            $transaction = BonusTransaction::create([
                'user_id' => $user->id,
                'type' => BonusTransaction::TYPE_EARNED,
                'action' => $action,
                'amount' => $amount,
                'description' => $description,
                'metadata' => $metadata,
                'related_user_id' => $relatedUser?->id,
                'related_catch_id' => $relatedCatch?->id,
                'related_point_id' => $relatedPoint?->id,
                'related_comment_id' => $relatedComment?->id,
                'related_like_id' => $relatedLike?->id,
            ]);

            // Update user's bonus balance
            $user->increment('bonus_balance', $amount);

            // Fire event for notifications
            event(new BonusAwarded($transaction));

            Log::info('Bonus awarded', [
                'user_id' => $user->id,
                'action' => $action,
                'amount' => $amount,
                'transaction_id' => $transaction->id
            ]);

            return $transaction;
        });
    }

    /**
     * Spend bonus from user
     */
    public function spendBonus(
        User $user,
        string $action,
        int $amount,
        ?string $description = null,
        array $metadata = []
    ): BonusTransaction {
        return DB::transaction(function () use ($user, $action, $amount, $description, $metadata) {
            // Check if user has enough bonus balance
            if ($user->bonus_balance < $amount) {
                throw new \Exception('Insufficient bonus balance');
            }

            // Create bonus transaction
            $transaction = BonusTransaction::create([
                'user_id' => $user->id,
                'type' => BonusTransaction::TYPE_SPENT,
                'action' => $action,
                'amount' => -$amount, // Negative for spent
                'description' => $description,
                'metadata' => $metadata,
            ]);

            // Update user's bonus balance
            $user->decrement('bonus_balance', $amount);

            Log::info('Bonus spent', [
                'user_id' => $user->id,
                'action' => $action,
                'amount' => $amount,
                'transaction_id' => $transaction->id
            ]);

            return $transaction;
        });
    }

    /**
     * Award bonus for adding a friend
     */
    public function awardFriendBonus(User $user, User $friend): BonusTransaction
    {
        return $this->awardBonus(
            user: $user,
            action: BonusTransaction::ACTION_FRIEND_ADDED,
            amount: BonusTransaction::BONUS_FRIEND_ADDED,
            description: "Добавлен друг: {$friend->name}",
            relatedUser: $friend
        );
    }

    /**
     * Award bonus for recording a catch
     */
    public function awardCatchBonus(User $user, CatchRecord $catch): BonusTransaction
    {
        return $this->awardBonus(
            user: $user,
            action: BonusTransaction::ACTION_CATCH_RECORDED,
            amount: BonusTransaction::BONUS_CATCH_RECORDED,
            description: "Записан улов: {$catch->fish_type} ({$catch->weight} кг)",
            relatedCatch: $catch
        );
    }

    /**
     * Award bonus for creating a point
     */
    public function awardPointBonus(User $user, Point $point): BonusTransaction
    {
        return $this->awardBonus(
            user: $user,
            action: BonusTransaction::ACTION_POINT_CREATED,
            amount: BonusTransaction::BONUS_POINT_CREATED,
            description: "Создана точка: {$point->title}",
            relatedPoint: $point
        );
    }

    /**
     * Award bonus for adding a comment
     */
    public function awardCommentBonus(User $user, CatchComment $comment): BonusTransaction
    {
        return $this->awardBonus(
            user: $user,
            action: BonusTransaction::ACTION_COMMENT_ADDED,
            amount: BonusTransaction::BONUS_COMMENT_ADDED,
            description: "Добавлен комментарий к улову",
            relatedComment: $comment
        );
    }

    /**
     * Award bonus for giving a like
     */
    public function awardLikeBonus(User $user, CatchLike $like): BonusTransaction
    {
        return $this->awardBonus(
            user: $user,
            action: BonusTransaction::ACTION_LIKE_GIVEN,
            amount: BonusTransaction::BONUS_LIKE_GIVEN,
            description: "Поставлен лайк улову",
            relatedLike: $like
        );
    }

    /**
     * Get user's bonus statistics
     */
    public function getUserBonusStats(User $user): array
    {
        $transactions = BonusTransaction::where('user_id', $user->id)->get();

        $stats = [
            'total_earned' => $transactions->where('type', BonusTransaction::TYPE_EARNED)->sum('amount'),
            'total_spent' => abs($transactions->where('type', BonusTransaction::TYPE_SPENT)->sum('amount')),
            'current_balance' => $user->bonus_balance,
            'transactions_count' => $transactions->count(),
            'by_action' => [
                'friends_added' => $transactions->where('action', BonusTransaction::ACTION_FRIEND_ADDED)->count(),
                'catches_recorded' => $transactions->where('action', BonusTransaction::ACTION_CATCH_RECORDED)->count(),
                'points_created' => $transactions->where('action', BonusTransaction::ACTION_POINT_CREATED)->count(),
                'comments_added' => $transactions->where('action', BonusTransaction::ACTION_COMMENT_ADDED)->count(),
                'likes_given' => $transactions->where('action', BonusTransaction::ACTION_LIKE_GIVEN)->count(),
            ],
            'recent_transactions' => $transactions->sortByDesc('created_at')->take(10)->values()
        ];

        return $stats;
    }

    /**
     * Get global bonus statistics
     */
    public function getGlobalBonusStats(): array
    {
        $totalUsers = User::count();
        $totalTransactions = BonusTransaction::count();
        $totalBonusesAwarded = BonusTransaction::where('type', BonusTransaction::TYPE_EARNED)->sum('amount');
        $totalBonusesSpent = abs(BonusTransaction::where('type', BonusTransaction::TYPE_SPENT)->sum('amount'));

        $topEarners = User::withSum('bonusTransactions as total_earned', 'amount')
            ->whereHas('bonusTransactions', function ($query) {
                $query->where('type', BonusTransaction::TYPE_EARNED);
            })
            ->orderByDesc('total_earned')
            ->take(10)
            ->get();

        $actionStats = BonusTransaction::select('action')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(amount) as total_amount')
            ->where('type', BonusTransaction::TYPE_EARNED)
            ->groupBy('action')
            ->get();

        return [
            'total_users' => $totalUsers,
            'total_transactions' => $totalTransactions,
            'total_bonuses_awarded' => $totalBonusesAwarded,
            'total_bonuses_spent' => $totalBonusesSpent,
            'average_bonus_per_user' => $totalUsers > 0 ? round($totalBonusesAwarded / $totalUsers, 2) : 0,
            'top_earners' => $topEarners,
            'action_statistics' => $actionStats,
        ];
    }

    /**
     * Check if user can perform action (rate limiting)
     */
    public function canPerformAction(User $user, string $action): bool
    {
        // Check if user has already earned bonus for this action recently
        $recentTransaction = BonusTransaction::where('user_id', $user->id)
            ->where('action', $action)
            ->where('created_at', '>=', now()->subMinutes(5)) // 5 minutes cooldown
            ->exists();

        return !$recentTransaction;
    }

    /**
     * Get bonus amount for action
     */
    public function getBonusAmount(string $action): int
    {
        return match ($action) {
            BonusTransaction::ACTION_FRIEND_ADDED => BonusTransaction::BONUS_FRIEND_ADDED,
            BonusTransaction::ACTION_CATCH_RECORDED => BonusTransaction::BONUS_CATCH_RECORDED,
            BonusTransaction::ACTION_POINT_CREATED => BonusTransaction::BONUS_POINT_CREATED,
            BonusTransaction::ACTION_COMMENT_ADDED => BonusTransaction::BONUS_COMMENT_ADDED,
            BonusTransaction::ACTION_LIKE_GIVEN => BonusTransaction::BONUS_LIKE_GIVEN,
            default => 0,
        };
    }
}
