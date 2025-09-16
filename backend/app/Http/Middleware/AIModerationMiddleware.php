<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AIService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIModerationMiddleware
{
    private AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $contentType = 'catch')
    {
        // Only process if AI service is enabled and available
        if (!config('ai.enabled') || !$this->aiService->isAvailable()) {
            return $next($request);
        }

        // Check if request has image files
        if (!$request->hasFile('image') && !$request->hasFile('images')) {
            return $next($request);
        }

        try {
            // Process single image
            if ($request->hasFile('image')) {
                $this->moderateImage($request->file('image'), $contentType);
            }

            // Process multiple images
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                if (is_array($images)) {
                    foreach ($images as $image) {
                        $this->moderateImage($image, $contentType);
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('AI Moderation Middleware error', [
                'error' => $e->getMessage(),
                'content_type' => $contentType
            ]);

            // Continue with request even if moderation fails
            return $next($request);
        }

        return $next($request);
    }

    /**
     * Moderate a single image
     */
    private function moderateImage($image, string $contentType): void
    {
        try {
            // Store file temporarily
            $tempPath = $image->store('temp/ai-moderation');
            $fullPath = Storage::path($tempPath);

            // Moderate content
            $moderation = $this->aiService->moderateContent($fullPath, $contentType);

            // Check if content should be rejected
            if (!$moderation['approved']) {
                // Clean up temp file
                Storage::delete($tempPath);

                throw new \Exception('Content rejected by AI moderation: ' . $moderation['reason']);
            }

            // Log moderation results
            Log::info('AI Moderation result', [
                'content_type' => $contentType,
                'approved' => $moderation['approved'],
                'confidence' => $moderation['confidence'],
                'categories' => $moderation['categories'],
                'warnings' => $moderation['warnings']
            ]);

            // Clean up temp file
            Storage::delete($tempPath);

        } catch (\Exception $e) {
            Log::error('Error moderating image', [
                'error' => $e->getMessage(),
                'content_type' => $contentType
            ]);
            throw $e;
        }
    }
}

