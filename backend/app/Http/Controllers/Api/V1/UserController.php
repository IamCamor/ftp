<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::select(['id', 'name', 'username', 'email', 'photo_url', 'role', 'is_premium', 'crown_icon_url', 'is_online', 'last_seen_at', 'created_at'])
            ->withCount(['catchRecords', 'followers', 'following'])
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $user->loadCount(['catchRecords', 'followers', 'following']);
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Get current user profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadCount(['catchRecords', 'followers', 'following']);
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }
}
