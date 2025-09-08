<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    /**
     * Follow a user.
     */
    public function follow(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = Auth::user();
        $targetUser = User::findOrFail($request->user_id);

        if ($user->id === $targetUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя подписаться на самого себя'
            ], 400);
        }

        $result = $user->follow($targetUser);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Вы уже подписаны на этого пользователя'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Вы подписались на пользователя',
            'data' => [
                'following' => true,
                'followers_count' => $targetUser->fresh()->followers_count,
            ]
        ]);
    }

    /**
     * Unfollow a user.
     */
    public function unfollow(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = Auth::user();
        $targetUser = User::findOrFail($request->user_id);

        $result = $user->unfollow($targetUser);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не подписаны на этого пользователя'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Вы отписались от пользователя',
            'data' => [
                'following' => false,
                'followers_count' => $targetUser->fresh()->followers_count,
            ]
        ]);
    }

    /**
     * Toggle follow status.
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = Auth::user();
        $targetUser = User::findOrFail($request->user_id);

        if ($user->id === $targetUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя подписаться на самого себя'
            ], 400);
        }

        $result = $user->toggleFollow($targetUser);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['following'] ? 'Вы подписались на пользователя' : 'Вы отписались от пользователя',
            'data' => [
                'following' => $result['following'],
                'followers_count' => $targetUser->fresh()->followers_count,
            ]
        ]);
    }

    /**
     * Get user's followers.
     */
    public function followers(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = $user->followers();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%");
            });
        }

        $followers = $query->orderBy('follows.created_at', 'desc')
                          ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $followers
        ]);
    }

    /**
     * Get users that user is following.
     */
    public function following(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = $user->following();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%");
            });
        }

        $following = $query->orderBy('follows.created_at', 'desc')
                          ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $following
        ]);
    }

    /**
     * Check if current user is following another user.
     */
    public function isFollowing(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();
        $isFollowing = $currentUser->isFollowing($user);

        return response()->json([
            'success' => true,
            'data' => [
                'following' => $isFollowing,
                'followers_count' => $user->followers_count,
                'following_count' => $user->following_count,
            ]
        ]);
    }

    /**
     * Get follow suggestions.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $user = Auth::user();
        $limit = $request->get('limit', 10);

        // Get users that current user is not following
        $followingIds = $user->following()->pluck('users.id')->toArray();
        $followingIds[] = $user->id; // Exclude self

        $suggestions = User::whereNotIn('id', $followingIds)
                          ->where('is_blocked', false)
                          ->orderBy('followers_count', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }

    /**
     * Get mutual follows.
     */
    public function mutual(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $currentUser = Auth::user();
        $limit = $request->get('limit', 10);

        // Get users that both current user and target user follow
        $currentFollowing = $currentUser->following()->pluck('users.id')->toArray();
        $targetFollowing = $user->following()->pluck('users.id')->toArray();
        
        $mutualIds = array_intersect($currentFollowing, $targetFollowing);

        $mutual = User::whereIn('id', $mutualIds)
                     ->where('is_blocked', false)
                     ->orderBy('name')
                     ->limit($limit)
                     ->get();

        return response()->json([
            'success' => true,
            'data' => $mutual
        ]);
    }
}
