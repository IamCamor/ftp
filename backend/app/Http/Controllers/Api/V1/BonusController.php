<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\BonusService;
use App\Models\BonusTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BonusController extends Controller
{
    private BonusService $bonusService;

    public function __construct(BonusService $bonusService)
    {
        $this->bonusService = $bonusService;
    }

    /**
     * Get user's bonus balance and statistics
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->bonusService->getUserBonusStats($user);

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $user->bonus_balance,
                'statistics' => $stats
            ]
        ]);
    }

    /**
     * Get user's bonus transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 20);
        $type = $request->get('type'); // 'earned', 'spent', 'all'
        $action = $request->get('action');

        $query = BonusTransaction::where('user_id', $user->id)
            ->with(['relatedUser', 'relatedCatch', 'relatedPoint', 'relatedComment', 'relatedLike'])
            ->orderBy('created_at', 'desc');

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        if ($action) {
            $query->where('action', $action);
        }

        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Get bonus amounts for different actions
     */
    public function amounts(): JsonResponse
    {
        $amounts = [
            'friend_added' => BonusTransaction::BONUS_FRIEND_ADDED,
            'catch_recorded' => BonusTransaction::BONUS_CATCH_RECORDED,
            'point_created' => BonusTransaction::BONUS_POINT_CREATED,
            'comment_added' => BonusTransaction::BONUS_COMMENT_ADDED,
            'like_given' => BonusTransaction::BONUS_LIKE_GIVEN,
        ];

        return response()->json([
            'success' => true,
            'data' => $amounts
        ]);
    }

    /**
     * Get user's bonus statistics by action
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = Auth::user();
        $action = $request->get('action');

        if ($action) {
            $transactions = BonusTransaction::where('user_id', $user->id)
                ->where('action', $action)
                ->where('type', BonusTransaction::TYPE_EARNED)
                ->get();

            $stats = [
                'action' => $action,
                'count' => $transactions->count(),
                'total_bonus' => $transactions->sum('amount'),
                'last_earned' => $transactions->sortByDesc('created_at')->first()?->created_at,
            ];
        } else {
            $stats = $this->bonusService->getUserBonusStats($user);
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get leaderboard for bonus earners
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $period = $request->get('period', 'all'); // 'all', 'month', 'week'

        $query = \App\Models\User::withSum('bonusTransactions as total_earned', 'amount')
            ->whereHas('bonusTransactions', function ($q) use ($period) {
                $q->where('type', BonusTransaction::TYPE_EARNED);
                
                if ($period === 'month') {
                    $q->where('created_at', '>=', now()->subMonth());
                } elseif ($period === 'week') {
                    $q->where('created_at', '>=', now()->subWeek());
                }
            })
            ->orderByDesc('total_earned')
            ->limit($limit);

        $leaderboard = $query->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'total_earned' => $user->total_earned ?? 0,
                'current_balance' => $user->bonus_balance,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'leaderboard' => $leaderboard,
                'period' => $period,
                'user_position' => $this->getUserPosition(Auth::id(), $period)
            ]
        ]);
    }

    /**
     * Get user's position in leaderboard
     */
    private function getUserPosition(int $userId, string $period): ?int
    {
        $query = \App\Models\User::withSum('bonusTransactions as total_earned', 'amount')
            ->whereHas('bonusTransactions', function ($q) use ($period) {
                $q->where('type', BonusTransaction::TYPE_EARNED);
                
                if ($period === 'month') {
                    $q->where('created_at', '>=', now()->subMonth());
                } elseif ($period === 'week') {
                    $q->where('created_at', '>=', now()->subWeek());
                }
            })
            ->orderByDesc('total_earned');

        $users = $query->get();
        $position = $users->search(function ($user) use ($userId) {
            return $user->id === $userId;
        });

        return $position !== false ? $position + 1 : null;
    }

    /**
     * Get global bonus statistics (admin only)
     */
    public function globalStats(): JsonResponse
    {
        $this->authorize('admin');

        $stats = $this->bonusService->getGlobalBonusStats();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Award bonus manually (admin only)
     */
    public function award(Request $request): JsonResponse
    {
        $this->authorize('admin');

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'action' => 'required|string',
            'amount' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        
        $transaction = $this->bonusService->awardBonus(
            user: $user,
            action: $request->action,
            amount: $request->amount,
            description: $request->description
        );

        return response()->json([
            'success' => true,
            'message' => 'Bonus awarded successfully',
            'data' => $transaction
        ]);
    }

    /**
     * Spend bonus (for purchases)
     */
    public function spend(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string',
            'amount' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $user = Auth::user();

        try {
            $transaction = $this->bonusService->spendBonus(
                user: $user,
                action: $request->action,
                amount: $request->amount,
                description: $request->description
            );

            return response()->json([
                'success' => true,
                'message' => 'Bonus spent successfully',
                'data' => [
                    'transaction' => $transaction,
                    'new_balance' => $user->fresh()->bonus_balance
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}