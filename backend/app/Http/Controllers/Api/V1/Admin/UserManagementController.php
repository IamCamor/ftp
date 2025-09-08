<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Get all users with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Filters
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'blocked') {
                $query->blocked();
            }
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->with(['blockedBy:id,name,username'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get user details.
     */
    public function show(User $user): JsonResponse
    {
        $user->load([
            'blockedBy:id,name,username',
            'catchRecords' => function ($query) {
                $query->latest()->limit(10);
            },
            'points' => function ($query) {
                $query->latest()->limit(10);
            },
            'reports' => function ($query) {
                $query->latest()->limit(5);
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Block/unblock user.
     */
    public function toggleBlock(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'block_reason' => 'required_if:is_blocked,true|string|max:500',
        ]);

        $admin = $request->user();

        if ($user->id === $admin->id) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя заблокировать самого себя'
            ], 400);
        }

        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя заблокировать администратора'
            ], 400);
        }

        $user->update([
            'is_blocked' => !$user->is_blocked,
            'blocked_at' => !$user->is_blocked ? now() : null,
            'block_reason' => $request->block_reason,
            'blocked_by' => $admin->id,
        ]);

        $action = $user->is_blocked ? 'заблокирован' : 'разблокирован';

        return response()->json([
            'success' => true,
            'message' => "Пользователь {$action}",
            'data' => $user->fresh(['blockedBy'])
        ]);
    }

    /**
     * Update user information.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'sometimes|in:user,admin',
            'bio' => 'sometimes|string|max:1000',
            'location' => 'sometimes|string|max:255',
        ]);

        $user->update($request->only([
            'name', 'username', 'email', 'role', 'bio', 'location'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Информация о пользователе обновлена',
            'data' => $user
        ]);
    }

    /**
     * Delete user.
     */
    public function destroy(User $user): JsonResponse
    {
        $admin = request()->user();

        if ($user->id === $admin->id) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя удалить самого себя'
            ], 400);
        }

        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя удалить администратора'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Пользователь удален'
        ]);
    }
}
