<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportManagementController extends Controller
{
    /**
     * Get all reports with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Report::with([
            'reporter:id,name,username',
            'reviewer:id,name,username',
            'reportable'
        ]);

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('reason')) {
            $query->where('reason', $request->reason);
        }

        if ($request->has('reportable_type')) {
            $query->where('reportable_type', $request->reportable_type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('reporter', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        $reports = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }

    /**
     * Get report details.
     */
    public function show(Report $report): JsonResponse
    {
        $report->load([
            'reporter:id,name,username,email',
            'reviewer:id,name,username',
            'reportable'
        ]);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Review report.
     */
    public function review(Request $request, Report $report): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:reviewed,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $admin = $request->user();

        $report->update([
            'status' => $request->status,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        $statusLabels = [
            'reviewed' => 'рассмотрена',
            'resolved' => 'решена',
            'dismissed' => 'отклонена',
        ];

        $action = $statusLabels[$request->status] ?? $request->status;

        return response()->json([
            'success' => true,
            'message' => "Жалоба {$action}",
            'data' => $report->fresh(['reviewer'])
        ]);
    }

    /**
     * Bulk review reports.
     */
    public function bulkReview(Request $request): JsonResponse
    {
        $request->validate([
            'report_ids' => 'required|array|min:1',
            'report_ids.*' => 'integer|exists:reports,id',
            'status' => 'required|in:reviewed,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $admin = $request->user();

        $updated = Report::whereIn('id', $request->report_ids)
            ->update([
                'status' => $request->status,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'admin_notes' => $request->admin_notes,
            ]);

        $statusLabels = [
            'reviewed' => 'рассмотрены',
            'resolved' => 'решены',
            'dismissed' => 'отклонены',
        ];

        $action = $statusLabels[$request->status] ?? $request->status;

        return response()->json([
            'success' => true,
            'message' => "{$updated} жалоб {$action}",
            'data' => ['updated_count' => $updated]
        ]);
    }

    /**
     * Get report statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => Report::count(),
            'pending' => Report::pending()->count(),
            'reviewed' => Report::reviewed()->count(),
            'resolved' => Report::resolved()->count(),
            'dismissed' => Report::where('status', 'dismissed')->count(),
            'by_reason' => Report::selectRaw('reason, COUNT(*) as count')
                ->groupBy('reason')
                ->pluck('count', 'reason'),
            'by_type' => Report::selectRaw('reportable_type, COUNT(*) as count')
                ->groupBy('reportable_type')
                ->pluck('count', 'reportable_type'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
