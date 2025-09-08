<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CatchRecord;
use App\Models\Point;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Get admin dashboard statistics.
     */
    public function dashboard(): JsonResponse
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'active' => User::active()->count(),
                'blocked' => User::blocked()->count(),
                'new_today' => User::whereDate('created_at', today())->count(),
            ],
            'catches' => [
                'total' => CatchRecord::count(),
                'active' => CatchRecord::active()->count(),
                'blocked' => CatchRecord::blocked()->count(),
                'new_today' => CatchRecord::whereDate('created_at', today())->count(),
            ],
            'points' => [
                'total' => Point::count(),
                'active' => Point::active()->count(),
                'blocked' => Point::blocked()->count(),
                'new_today' => Point::whereDate('created_at', today())->count(),
            ],
            'reports' => [
                'total' => Report::count(),
                'pending' => Report::pending()->count(),
                'resolved' => Report::resolved()->count(),
                'new_today' => Report::whereDate('created_at', today())->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get recent activity.
     */
    public function recentActivity(): JsonResponse
    {
        $recentUsers = User::latest()->limit(5)->get(['id', 'name', 'username', 'email', 'created_at', 'is_blocked']);
        $recentCatches = CatchRecord::with(['user:id,name,username', 'point:id,name'])
            ->latest()
            ->limit(5)
            ->get(['id', 'user_id', 'point_id', 'fish_type', 'weight', 'created_at', 'is_blocked']);
        $recentReports = Report::with(['reporter:id,name,username', 'reportable'])
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'recent_users' => $recentUsers,
                'recent_catches' => $recentCatches,
                'recent_reports' => $recentReports,
            ]
        ]);
    }
}
