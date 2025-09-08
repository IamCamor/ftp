<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OnlineStatusController extends Controller
{
    /**
     * Update user's online status.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'is_online' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $isOnline = $request->get('is_online', true);

        $user->updateOnlineStatus($isOnline);

        return response()->json([
            'success' => true,
            'message' => 'Статус обновлен',
            'data' => [
                'is_online' => $user->fresh()->is_online,
                'last_seen_at' => $user->fresh()->last_seen_at,
            ]
        ]);
    }

    /**
     * Get online users.
     */
    public function online(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1|max:1000',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $query = User::online()
                    ->where('is_blocked', false)
                    ->select(['id', 'name', 'username', 'photo_url', 'role', 'is_premium', 'crown_icon_url', 'last_seen_at']);

        // Filter by location if provided
        if ($request->latitude && $request->longitude) {
            $radius = $request->get('radius', 50); // Default 50km
            $query->whereHas('catchRecords.point', function ($q) use ($request, $radius) {
                $q->whereRaw(
                    "ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?)) <= ?",
                    [$request->longitude, $request->latitude, $radius * 1000]
                );
            });
        }

        $limit = $request->get('limit', 20);
        $onlineUsers = $query->orderBy('last_seen_at', 'desc')
                           ->limit($limit)
                           ->get();

        return response()->json([
            'success' => true,
            'data' => $onlineUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'photo_url' => $user->photo_url,
                    'role' => $user->role,
                    'is_premium' => $user->is_premium,
                    'crown_icon_url' => $user->getCrownIconUrl(),
                    'last_seen_at' => $user->last_seen_at,
                    'is_online' => $user->is_online,
                ];
            })
        ]);
    }

    /**
     * Get recently active users.
     */
    public function recentlyActive(Request $request): JsonResponse
    {
        $request->validate([
            'minutes' => 'nullable|integer|min:1|max:1440', // Max 24 hours
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $minutes = $request->get('minutes', 30);
        $limit = $request->get('limit', 20);

        $users = User::recentlyActive($minutes)
                    ->where('is_blocked', false)
                    ->select(['id', 'name', 'username', 'photo_url', 'role', 'is_premium', 'crown_icon_url', 'last_seen_at', 'is_online'])
                    ->orderBy('last_seen_at', 'desc')
                    ->limit($limit)
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'photo_url' => $user->photo_url,
                    'role' => $user->role,
                    'is_premium' => $user->is_premium,
                    'crown_icon_url' => $user->getCrownIconUrl(),
                    'last_seen_at' => $user->last_seen_at,
                    'is_online' => $user->is_online,
                ];
            })
        ]);
    }

    /**
     * Get user's current status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'is_online' => $user->is_online,
                'last_seen_at' => $user->last_seen_at,
            ]
        ]);
    }

    /**
     * Mark user as offline.
     */
    public function offline(): JsonResponse
    {
        $user = Auth::user();
        $user->updateOnlineStatus(false);

        return response()->json([
            'success' => true,
            'message' => 'Пользователь отмечен как офлайн',
            'data' => [
                'is_online' => false,
                'last_seen_at' => $user->fresh()->last_seen_at,
            ]
        ]);
    }

    /**
     * Get online users count.
     */
    public function count(): JsonResponse
    {
        $count = User::online()->where('is_blocked', false)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'online_count' => $count,
            ]
        ]);
    }
}
