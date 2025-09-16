<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CatchRecord;
use App\Models\CatchReport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CatchReportController extends Controller
{
    /**
     * Report a catch.
     */
    public function store(Request $request, $catchId): JsonResponse
    {
        try {
            $user = Auth::guard('api')->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $catch = CatchRecord::findOrFail($catchId);
            
            // Check if user is trying to report their own catch
            if ($catch->user_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot report your own catch'
                ], 403);
            }

            // Check if user has already reported this catch
            $existingReport = CatchReport::where('catch_id', $catchId)
                ->where('user_id', $user->id)
                ->first();
                
            if ($existingReport) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reported this catch'
                ], 409);
            }

            $validated = $request->validate([
                'category' => 'required|string|in:spam,advertisement,fraud',
                'description' => 'required|string|max:1000|min:10',
            ]);

            DB::beginTransaction();
            
            try {
                // Create the report
                $report = CatchReport::create([
                    'catch_id' => $catchId,
                    'user_id' => $user->id,
                    'category' => $validated['category'],
                    'description' => $validated['description'],
                    'status' => 'pending',
                ]);

                // Check if this is the 3rd report and hide the catch
                $reportsCount = CatchReport::where('catch_id', $catchId)->count();
                
                if ($reportsCount >= 3) {
                    // The catch will be automatically hidden by the scope in queries
                    // We can add a flag or notification here if needed
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Report submitted successfully',
                    'data' => [
                        'id' => $report->id,
                        'category' => $report->category,
                        'category_label' => $report->category_label,
                        'description' => $report->description,
                        'status' => $report->status,
                        'status_label' => $report->status_label,
                        'created_at' => $report->created_at,
                        'reports_count' => $reportsCount,
                        'catch_hidden' => $reportsCount >= 3,
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reports for a specific catch (admin only).
     */
    public function index($catchId): JsonResponse
    {
        try {
            $user = Auth::guard('api')->user();
            
            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin access required'
                ], 403);
            }

            $catch = CatchRecord::findOrFail($catchId);
            
            $reports = CatchReport::where('catch_id', $catchId)
                ->with(['user:id,name,username', 'reviewer:id,name,username'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'catch' => [
                        'id' => $catch->id,
                        'species' => $catch->species,
                        'user' => [
                            'id' => $catch->user->id,
                            'name' => $catch->user->name,
                            'username' => $catch->user->username,
                        ],
                    ],
                    'reports' => $reports->map(function ($report) {
                        return [
                            'id' => $report->id,
                            'category' => $report->category,
                            'category_label' => $report->category_label,
                            'description' => $report->description,
                            'status' => $report->status,
                            'status_label' => $report->status_label,
                            'user' => [
                                'id' => $report->user->id,
                                'name' => $report->user->name,
                                'username' => $report->user->username,
                            ],
                            'reviewer' => $report->reviewer ? [
                                'id' => $report->reviewer->id,
                                'name' => $report->reviewer->name,
                                'username' => $report->reviewer->username,
                            ] : null,
                            'admin_notes' => $report->admin_notes,
                            'created_at' => $report->created_at,
                            'reviewed_at' => $report->reviewed_at,
                        ];
                    }),
                    'total_reports' => $reports->count(),
                    'is_hidden' => $catch->isHiddenByReports(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get reports',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report categories.
     */
    public function categories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['value' => 'spam', 'label' => 'Спам'],
                ['value' => 'advertisement', 'label' => 'Реклама'],
                ['value' => 'fraud', 'label' => 'Обман'],
            ]
        ]);
    }
}