<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FishingSession;
use App\Models\BiometricData;
use App\Models\CatchRecord;
use App\Services\BiometricService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WatchController extends Controller
{
    private BiometricService $biometricService;

    public function __construct(BiometricService $biometricService)
    {
        $this->biometricService = $biometricService;
    }

    public function startSession(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "point_id" => "nullable|exists:points,id",
            "watch_hand" => "required|in:casting,reeling",
            "biometric_tracking" => "boolean",
            "gps_tracking" => "boolean",
            "mood_tracking" => "boolean",
            "name" => "nullable|string|max:255",
            "description" => "nullable|string|max:1000",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation failed",
                "errors" => $validator->errors()
            ], 422);
        }

        try {
            $session = FishingSession::create([
                "user_id" => Auth::id(),
                "point_id" => $request->point_id,
                "name" => $request->name,
                "description" => $request->description,
                "started_at" => now(),
                "status" => "active",
                "watch_hand" => $request->watch_hand,
                "biometric_tracking" => $request->biometric_tracking ?? true,
                "gps_tracking" => $request->gps_tracking ?? true,
                "mood_tracking" => $request->mood_tracking ?? true,
                "start_latitude" => $request->start_latitude,
                "start_longitude" => $request->start_longitude,
            ]);

            return response()->json([
                "success" => true,
                "message" => "Fishing session started successfully",
                "data" => [
                    "session_id" => $session->id,
                    "started_at" => $session->started_at->toISOString(),
                    "watch_hand" => $session->watch_hand,
                    "watch_hand_description" => $session->watch_hand_description,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Failed to start fishing session",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function recordBiometricData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "session_id" => "required|exists:fishing_sessions,id",
            "heart_rate" => "nullable|integer|min:30|max:220",
            "hrv" => "nullable|numeric|min:0|max:200",
            "stress_level" => "nullable|numeric|min:0|max:100",
            "temperature" => "nullable|numeric|min:30|max:45",
            "steps" => "nullable|integer|min:0",
            "calories_burned" => "nullable|numeric|min:0",
            "activity_level" => "nullable|numeric|min:0|max:100",
            "acceleration_x" => "nullable|numeric",
            "acceleration_y" => "nullable|numeric",
            "acceleration_z" => "nullable|numeric",
            "gyroscope_x" => "nullable|numeric",
            "gyroscope_y" => "nullable|numeric",
            "gyroscope_z" => "nullable|numeric",
            "casts_count" => "nullable|integer|min:0",
            "reels_count" => "nullable|integer|min:0",
            "reels_meters" => "nullable|numeric|min:0",
            "recorded_at" => "nullable|date",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation failed",
                "errors" => $validator->errors()
            ], 422);
        }

        try {
            $session = FishingSession::where("id", $request->session_id)
                ->where("user_id", Auth::id())
                ->where("status", "active")
                ->first();

            if (!$session) {
                return response()->json([
                    "success" => false,
                    "message" => "Session not found or not active"
                ], 404);
            }

            $recentCatches = $session->catchRecords()
                ->where("created_at", ">=", now()->subMinutes(30))
                ->count();

            $timeSinceLastCatch = null;
            $lastCatch = $session->catchRecords()->latest()->first();
            if ($lastCatch) {
                $timeSinceLastCatch = $lastCatch->created_at->diffInMinutes(now());
            }

            $biometricData = $this->biometricService->processBiometricData(
                array_merge($request->all(), [
                    "recent_catches" => $recentCatches,
                    "time_since_last_catch" => $timeSinceLastCatch,
                ]),
                Auth::id(),
                $session->id
            );

            return response()->json([
                "success" => true,
                "message" => "Biometric data recorded successfully",
                "data" => [
                    "biometric_id" => $biometricData->id,
                    "mood_index" => $biometricData->mood_index,
                    "mood_emoji" => $biometricData->mood_emoji,
                    "mood_description" => $biometricData->mood_description,
                    "stress_level" => $biometricData->stress_level,
                    "stress_description" => $biometricData->stress_description,
                    "heart_rate" => $biometricData->heart_rate,
                    "heart_rate_zone" => $biometricData->heart_rate_zone,
                    "heart_rate_zone_description" => $biometricData->heart_rate_zone_description,
                    "recorded_at" => $biometricData->recorded_at->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Failed to record biometric data",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function recordCatch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "session_id" => "required|exists:fishing_sessions,id",
            "fish_species" => "required|string|max:255",
            "weight" => "nullable|numeric|min:0|max:1000",
            "length" => "nullable|numeric|min:0|max:500",
            "latitude" => "nullable|numeric",
            "longitude" => "nullable|numeric",
            "notes" => "nullable|string|max:1000",
            "heart_rate" => "nullable|integer|min:30|max:220",
            "hrv" => "nullable|numeric|min:0|max:200",
            "stress_level" => "nullable|numeric|min:0|max:100",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation failed",
                "errors" => $validator->errors()
            ], 422);
        }

        try {
            $session = FishingSession::where("id", $request->session_id)
                ->where("user_id", Auth::id())
                ->where("status", "active")
                ->first();

            if (!$session) {
                return response()->json([
                    "success" => false,
                    "message" => "Session not found or not active"
                ], 404);
            }

            $catch = CatchRecord::create([
                "user_id" => Auth::id(),
                "point_id" => $session->point_id,
                "fish_species" => $request->fish_species,
                "weight" => $request->weight,
                "length" => $request->length,
                "latitude" => $request->latitude,
                "longitude" => $request->longitude,
                "notes" => $request->notes,
                "caught_at" => now(),
                "is_public" => true,
            ]);

            $biometricData = $this->biometricService->processBiometricData(
                [
                    "heart_rate" => $request->heart_rate,
                    "hrv" => $request->hrv,
                    "stress_level" => $request->stress_level,
                    "recent_catches" => 1,
                    "time_since_last_catch" => 0,
                    "recorded_at" => now(),
                ],
                Auth::id(),
                $session->id,
                $catch->id
            );

            $session->increment("catches_count");
            if ($request->weight) {
                $session->increment("total_weight", $request->weight);
            }

            return response()->json([
                "success" => true,
                "message" => "Catch recorded successfully",
                "data" => [
                    "catch_id" => $catch->id,
                    "fish_species" => $catch->fish_species,
                    "weight" => $catch->weight,
                    "length" => $catch->length,
                    "caught_at" => $catch->caught_at->toISOString(),
                    "biometric_data" => [
                        "mood_index" => $biometricData->mood_index,
                        "mood_emoji" => $biometricData->mood_emoji,
                        "mood_description" => $biometricData->mood_description,
                        "heart_rate" => $biometricData->heart_rate,
                        "stress_level" => $biometricData->stress_level,
                    ],
                    "session_stats" => [
                        "catches_count" => $session->catches_count,
                        "total_weight" => $session->total_weight,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Failed to record catch",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function getSessionStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "session_id" => "required|exists:fishing_sessions,id",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation failed",
                "errors" => $validator->errors()
            ], 422);
        }

        try {
            $session = FishingSession::where("id", $request->session_id)
                ->where("user_id", Auth::id())
                ->first();

            if (!$session) {
                return response()->json([
                    "success" => false,
                    "message" => "Session not found"
                ], 404);
            }

            $latestBiometric = $session->latestBiometricData;

            return response()->json([
                "success" => true,
                "data" => [
                    "session_id" => $session->id,
                    "status" => $session->status,
                    "status_description" => $session->status_description,
                    "duration" => $session->duration,
                    "duration_human" => $session->duration_human,
                    "watch_hand" => $session->watch_hand,
                    "watch_hand_description" => $session->watch_hand_description,
                    "total_casts" => $session->total_casts,
                    "total_reels" => $session->total_reels,
                    "total_reels_meters" => $session->total_reels_meters,
                    "catches_count" => $session->catches_count,
                    "total_weight" => $session->total_weight,
                    "casts_per_hour" => $session->casts_per_hour,
                    "reels_per_hour" => $session->reels_per_hour,
                    "meters_per_hour" => $session->meters_per_hour,
                    "latest_biometric" => $latestBiometric ? [
                        "mood_index" => $latestBiometric->mood_index,
                        "mood_emoji" => $latestBiometric->mood_emoji,
                        "mood_description" => $latestBiometric->mood_description,
                        "heart_rate" => $latestBiometric->heart_rate,
                        "heart_rate_zone" => $latestBiometric->heart_rate_zone,
                        "heart_rate_zone_description" => $latestBiometric->heart_rate_zone_description,
                        "stress_level" => $latestBiometric->stress_level,
                        "stress_description" => $latestBiometric->stress_description,
                        "recorded_at" => $latestBiometric->recorded_at->toISOString(),
                    ] : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Failed to get session status",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function getUserBiometricStats(Request $request): JsonResponse
    {
        $days = $request->get("days", 30);
        
        try {
            $stats = $this->biometricService->getUserBiometricStats(Auth::id(), $days);

            return response()->json([
                "success" => true,
                "data" => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Failed to get biometric statistics",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
