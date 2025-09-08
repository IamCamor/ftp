<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatchRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CatchManagementController extends Controller
{
    /**
     * Get all catches with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CatchRecord::with(['user:id,name,username', 'point:id,name', 'blockedBy:id,name,username', 'editedByAdmin:id,name,username']);

        // Filters
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'blocked') {
                $query->blocked();
            } elseif ($request->status === 'edited_by_admin') {
                $query->editedByAdmin();
            }
        }

        if ($request->has('fish_type')) {
            $query->where('fish_type', 'like', "%{$request->fish_type}%");
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fish_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        $catches = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $catches
        ]);
    }

    /**
     * Get catch details.
     */
    public function show(CatchRecord $catch): JsonResponse
    {
        $catch->load([
            'user:id,name,username,email',
            'point:id,name,latitude,longitude',
            'blockedBy:id,name,username',
            'editedByAdmin:id,name,username',
            'likes.user:id,name,username',
            'comments.user:id,name,username',
            'reports.reporter:id,name,username'
        ]);

        return response()->json([
            'success' => true,
            'data' => $catch
        ]);
    }

    /**
     * Block/unblock catch.
     */
    public function toggleBlock(Request $request, CatchRecord $catch): JsonResponse
    {
        $request->validate([
            'block_reason' => 'required_if:is_blocked,true|string|max:500',
        ]);

        $admin = $request->user();

        $catch->update([
            'is_blocked' => !$catch->is_blocked,
            'blocked_at' => !$catch->is_blocked ? now() : null,
            'block_reason' => $request->block_reason,
            'blocked_by' => $admin->id,
        ]);

        $action = $catch->is_blocked ? 'заблокирован' : 'разблокирован';

        return response()->json([
            'success' => true,
            'message' => "Улов {$action}",
            'data' => $catch->fresh(['blockedBy'])
        ]);
    }

    /**
     * Update catch information.
     */
    public function update(Request $request, CatchRecord $catch): JsonResponse
    {
        $request->validate([
            'fish_type' => 'sometimes|string|max:255',
            'weight' => 'sometimes|numeric|min:0|max:1000',
            'length' => 'sometimes|numeric|min:0|max:500',
            'bait' => 'sometimes|string|max:255',
            'weather' => 'sometimes|string|max:50',
            'temperature' => 'sometimes|integer|min:-50|max:50',
            'description' => 'sometimes|string|max:2000',
            'is_public' => 'sometimes|boolean',
        ]);

        $admin = $request->user();

        $catch->update(array_merge(
            $request->only([
                'fish_type', 'weight', 'length', 'bait', 'weather', 
                'temperature', 'description', 'is_public'
            ]),
            [
                'is_edited_by_admin' => true,
                'edited_by_admin_at' => now(),
                'edited_by_admin_id' => $admin->id,
            ]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Информация об улове обновлена',
            'data' => $catch->fresh(['editedByAdmin'])
        ]);
    }

    /**
     * Delete catch.
     */
    public function destroy(CatchRecord $catch): JsonResponse
    {
        $catch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Улов удален'
        ]);
    }
}
