<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PointManagementController extends Controller
{
    /**
     * Get all points with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Point::with(['user:id,name,username', 'blockedBy:id,name,username', 'editedByAdmin:id,name,username']);

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

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        $points = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $points
        ]);
    }

    /**
     * Get point details.
     */
    public function show(Point $point): JsonResponse
    {
        $point->load([
            'user:id,name,username,email',
            'blockedBy:id,name,username',
            'editedByAdmin:id,name,username',
            'catchRecords' => function ($query) {
                $query->latest()->limit(10);
            },
            'reports.reporter:id,name,username'
        ]);

        return response()->json([
            'success' => true,
            'data' => $point
        ]);
    }

    /**
     * Block/unblock point.
     */
    public function toggleBlock(Request $request, Point $point): JsonResponse
    {
        $request->validate([
            'block_reason' => 'required_if:is_blocked,true|string|max:500',
        ]);

        $admin = $request->user();

        $point->update([
            'is_blocked' => !$point->is_blocked,
            'blocked_at' => !$point->is_blocked ? now() : null,
            'block_reason' => $request->block_reason,
            'blocked_by' => $admin->id,
        ]);

        $action = $point->is_blocked ? 'заблокирована' : 'разблокирована';

        return response()->json([
            'success' => true,
            'message' => "Точка {$action}",
            'data' => $point->fresh(['blockedBy'])
        ]);
    }

    /**
     * Update point information.
     */
    public function update(Request $request, Point $point): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:2000',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'type' => 'sometimes|string|max:50',
            'is_public' => 'sometimes|boolean',
            'rating' => 'sometimes|numeric|between:0,5',
        ]);

        $admin = $request->user();

        $point->update(array_merge(
            $request->only([
                'name', 'description', 'latitude', 'longitude', 
                'type', 'is_public', 'rating'
            ]),
            [
                'is_edited_by_admin' => true,
                'edited_by_admin_at' => now(),
                'edited_by_admin_id' => $admin->id,
            ]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Информация о точке обновлена',
            'data' => $point->fresh(['editedByAdmin'])
        ]);
    }

    /**
     * Delete point.
     */
    public function destroy(Point $point): JsonResponse
    {
        $point->delete();

        return response()->json([
            'success' => true,
            'message' => 'Точка удалена'
        ]);
    }
}
