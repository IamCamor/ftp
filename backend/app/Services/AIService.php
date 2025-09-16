<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class AIService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('ai.service_url', 'http://localhost:8001');
        $this->timeout = config('ai.timeout', 30);
    }

    /**
     * Analyze image content for safety and categorization
     */
    public function analyzeImage(string $imagePath, string $contentType = 'catch'): array
    {
        try {
            $imageData = $this->encodeImageToBase64($imagePath);
            
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/analyze", [
                    'image_data' => $imageData,
                    'content_type' => $contentType
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('AI Service analyze failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return $this->getDefaultAnalysis($contentType);

        } catch (Exception $e) {
            Log::error('AI Service analyze error', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath
            ]);

            return $this->getDefaultAnalysis($contentType);
        }
    }

    /**
     * Moderate content based on image analysis
     */
    public function moderateContent(string $imagePath, string $contentType = 'catch'): array
    {
        try {
            $imageData = $this->encodeImageToBase64($imagePath);
            
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/moderate", [
                    'image_data' => $imageData,
                    'content_type' => $contentType
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('AI Service moderate failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return $this->getDefaultModeration($contentType);

        } catch (Exception $e) {
            Log::error('AI Service moderate error', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath
            ]);

            return $this->getDefaultModeration($contentType);
        }
    }

    /**
     * Generate alt text for accessibility
     */
    public function generateAltText(string $imagePath, ?string $context = null): string
    {
        try {
            $imageData = $this->encodeImageToBase64($imagePath);
            
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/alt-text", [
                    'image_data' => $imageData,
                    'context' => $context
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['alt_text'] ?? 'Image';
            }

            Log::error('AI Service alt-text failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return 'Image';

        } catch (Exception $e) {
            Log::error('AI Service alt-text error', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath
            ]);

            return 'Image';
        }
    }

    /**
     * Detect fish species in the image
     */
    public function detectFishSpecies(string $imagePath): array
    {
        try {
            $imageData = $this->encodeImageToBase64($imagePath);
            
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/detect-species", [
                    'image_data' => $imageData
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('AI Service detect-species failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return $this->getDefaultSpeciesDetection();

        } catch (Exception $e) {
            Log::error('AI Service detect-species error', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath
            ]);

            return $this->getDefaultSpeciesDetection();
        }
    }

    /**
     * Check if AI service is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (Exception $e) {
            Log::warning('AI Service not available', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Encode image file to base64
     */
    private function encodeImageToBase64(string $imagePath): string
    {
        if (!file_exists($imagePath)) {
            throw new Exception("Image file not found: {$imagePath}");
        }

        $imageData = file_get_contents($imagePath);
        return base64_encode($imageData);
    }

    /**
     * Get default analysis when AI service is unavailable
     */
    private function getDefaultAnalysis(string $contentType): array
    {
        return [
            'is_safe' => true,
            'confidence' => 0.5,
            'categories' => [],
            'warnings' => ['AI analysis unavailable'],
            'metadata' => [],
            'content_type' => $contentType
        ];
    }

    /**
     * Get default moderation when AI service is unavailable
     */
    private function getDefaultModeration(string $contentType): array
    {
        return [
            'approved' => true,
            'confidence' => 0.5,
            'reason' => 'Content approved (AI unavailable)',
            'categories' => [],
            'warnings' => ['AI moderation unavailable'],
            'content_type' => $contentType,
            'metadata' => []
        ];
    }

    /**
     * Get default species detection when AI service is unavailable
     */
    private function getDefaultSpeciesDetection(): array
    {
        return [
            'species' => 'Unknown',
            'confidence' => 0.1,
            'alternatives' => [],
            'metadata' => []
        ];
    }
}

