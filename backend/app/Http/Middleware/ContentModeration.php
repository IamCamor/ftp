<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Events\ContentModerationRequested;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ContentModeration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only process successful responses
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // Check if AI moderation is enabled
        if (!config('ai_moderation.enabled', true)) {
            return $response;
        }

        // Process moderation based on route
        $this->processModeration($request, $response);

        return $response;
    }

    /**
     * Process moderation based on request
     */
    private function processModeration(Request $request, Response $response): void
    {
        $routeName = $request->route()?->getName();
        $responseData = json_decode($response->getContent(), true);

        if (!$responseData || !isset($responseData['data'])) {
            return;
        }

        try {
            match ($routeName) {
                'api.v1.catches.store' => $this->moderateCatch($responseData['data']),
                'api.v1.comments.store' => $this->moderateComment($responseData['data']),
                'api.v1.points.store' => $this->moderatePoint($responseData['data']),
                default => null
            };
        } catch (\Exception $e) {
            Log::error('Content moderation middleware error', [
                'route' => $routeName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Moderate catch content
     */
    private function moderateCatch(array $catchData): void
    {
        $catchId = $catchData['id'] ?? null;
        if (!$catchId) {
            return;
        }

        // Moderate catch description
        if (!empty($catchData['description'])) {
            event(new ContentModerationRequested(
                'catch_descriptions',
                (string) $catchId,
                $catchData['description'],
                'text',
                $catchData['user_id'] ?? null
            ));
        }

        // Moderate catch photos
        if (!empty($catchData['photos'])) {
            foreach ($catchData['photos'] as $photo) {
                if (isset($photo['path'])) {
                    event(new ContentModerationRequested(
                        'catch_photos',
                        (string) $catchId,
                        $photo['path'],
                        'image',
                        $catchData['user_id'] ?? null
                    ));
                }
            }
        }
    }

    /**
     * Moderate comment content
     */
    private function moderateComment(array $commentData): void
    {
        $commentId = $commentData['id'] ?? null;
        if (!$commentId || empty($commentData['body'])) {
            return;
        }

        event(new ContentModerationRequested(
            'catch_comments',
            (string) $commentId,
            $commentData['body'],
            'text',
            $commentData['user_id'] ?? null
        ));
    }

    /**
     * Moderate point content
     */
    private function moderatePoint(array $pointData): void
    {
        $pointId = $pointData['id'] ?? null;
        if (!$pointId) {
            return;
        }

        // Moderate point description
        if (!empty($pointData['description'])) {
            event(new ContentModerationRequested(
                'point_descriptions',
                (string) $pointId,
                $pointData['description'],
                'text',
                $pointData['user_id'] ?? null
            ));
        }

        // Moderate point photos
        if (!empty($pointData['photos'])) {
            foreach ($pointData['photos'] as $photo) {
                if (isset($photo['path'])) {
                    event(new ContentModerationRequested(
                        'point_photos',
                        (string) $pointId,
                        $photo['path'],
                        'image',
                        $pointData['user_id'] ?? null
                    ));
                }
            }
        }
    }
}