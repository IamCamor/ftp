<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class AIController extends Controller
{
    private AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Analyze image content
     */
    public function analyzeImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|image|max:10240', // 10MB max
            'content_type' => 'string|in:catch,profile,place'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 400);
        }

        try {
            $file = $request->file('image');
            $contentType = $request->input('content_type', 'catch');
            
            // Store file temporarily
            $tempPath = $file->store('temp/ai-analysis');
            $fullPath = Storage::path($tempPath);
            
            // Analyze image
            $analysis = $this->aiService->analyzeImage($fullPath, $contentType);
            
            // Clean up temp file
            Storage::delete($tempPath);
            
            return response()->json([
                'success' => true,
                'analysis' => $analysis
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Analysis failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Moderate content
     */
    public function moderateContent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|image|max:10240',
            'content_type' => 'string|in:catch,profile,place'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 400);
        }

        try {
            $file = $request->file('image');
            $contentType = $request->input('content_type', 'catch');
            
            // Store file temporarily
            $tempPath = $file->store('temp/ai-moderation');
            $fullPath = Storage::path($tempPath);
            
            // Moderate content
            $moderation = $this->aiService->moderateContent($fullPath, $contentType);
            
            // Clean up temp file
            Storage::delete($tempPath);
            
            return response()->json([
                'success' => true,
                'moderation' => $moderation
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Moderation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate alt text
     */
    public function generateAltText(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|image|max:10240',
            'context' => 'string|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 400);
        }

        try {
            $file = $request->file('image');
            $context = $request->input('context');
            
            // Store file temporarily
            $tempPath = $file->store('temp/ai-alt-text');
            $fullPath = Storage::path($tempPath);
            
            // Generate alt text
            $altText = $this->aiService->generateAltText($fullPath, $context);
            
            // Clean up temp file
            Storage::delete($tempPath);
            
            return response()->json([
                'success' => true,
                'alt_text' => $altText
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Alt text generation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detect fish species
     */
    public function detectFishSpecies(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|image|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 400);
        }

        try {
            $file = $request->file('image');
            
            // Store file temporarily
            $tempPath = $file->store('temp/ai-species');
            $fullPath = Storage::path($tempPath);
            
            // Detect species
            $detection = $this->aiService->detectFishSpecies($fullPath);
            
            // Clean up temp file
            Storage::delete($tempPath);
            
            return response()->json([
                'success' => true,
                'detection' => $detection
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Species detection failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check AI service health
     */
    public function health(): JsonResponse
    {
        $isAvailable = $this->aiService->isAvailable();
        
        return response()->json([
            'ai_service_available' => $isAvailable,
            'status' => $isAvailable ? 'healthy' : 'unavailable'
        ]);
    }

    /**
     * Get AI service configuration
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'enabled' => config('ai.enabled'),
            'service_url' => config('ai.service_url'),
            'timeout' => config('ai.timeout'),
            'moderation' => config('ai.moderation'),
            'image_analysis' => config('ai.image_analysis'),
            'content_types' => config('ai.content_types')
        ]);
    }
}

