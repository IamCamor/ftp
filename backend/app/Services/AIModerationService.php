<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AIModerationService
{
    private string $defaultProvider;
    private array $providers;
    private array $config;

    public function __construct()
    {
        $this->config = config('ai_moderation', []);
        $this->defaultProvider = $this->config['default_provider'] ?? 'yandexgpt';
        $this->providers = $this->config['providers'] ?? [];
    }

    /**
     * Moderate text content
     */
    public function moderateText(string $text, string $contentType = 'comment'): array
    {
        if (!$this->isModerationEnabled($contentType)) {
            return $this->createApprovedResult('Moderation disabled for this content type');
        }

        $cacheKey = $this->getCacheKey('text', $text, $contentType);
        
        if ($this->isCachingEnabled()) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        try {
            $result = $this->callAIProvider($text, $contentType, 'text');
            
            if ($this->isCachingEnabled()) {
                Cache::put($cacheKey, $result, $this->getCacheTTL());
            }

            $this->logModerationResult($contentType, 'text', $result);
            return $result;
        } catch (\Exception $e) {
            Log::error('AI moderation failed', [
                'content_type' => $contentType,
                'error' => $e->getMessage(),
                'text_preview' => substr($text, 0, 100)
            ]);

            return $this->handleModerationFailure($contentType);
        }
    }

    /**
     * Moderate image content
     */
    public function moderateImage(string $imagePath, string $contentType = 'catch_photos'): array
    {
        if (!$this->isModerationEnabled($contentType)) {
            return $this->createApprovedResult('Moderation disabled for this content type');
        }

        $cacheKey = $this->getCacheKey('image', $imagePath, $contentType);
        
        if ($this->isCachingEnabled()) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        try {
            $result = $this->callAIProvider($imagePath, $contentType, 'image');
            
            if ($this->isCachingEnabled()) {
                Cache::put($cacheKey, $result, $this->getCacheTTL());
            }

            $this->logModerationResult($contentType, 'image', $result);
            return $result;
        } catch (\Exception $e) {
            Log::error('AI image moderation failed', [
                'content_type' => $contentType,
                'error' => $e->getMessage(),
                'image_path' => $imagePath
            ]);

            return $this->handleModerationFailure($contentType);
        }
    }

    /**
     * Call AI provider for moderation
     */
    private function callAIProvider(string $content, string $contentType, string $contentFormat): array
    {
        $provider = $this->getProviderForContentType($contentType);
        $providerConfig = $this->providers[$provider] ?? null;

        if (!$providerConfig || !($providerConfig['enabled'] ?? false)) {
            throw new \Exception("Provider {$provider} is not enabled or configured");
        }

        $this->checkRateLimit($provider);

        switch ($provider) {
            case 'yandexgpt':
                return $this->callYandexGPT($content, $contentType, $contentFormat, $providerConfig);
            case 'gigachat':
                return $this->callGigaChat($content, $contentType, $contentFormat, $providerConfig);
            case 'chatgpt':
                return $this->callChatGPT($content, $contentType, $contentFormat, $providerConfig);
            case 'deepseek':
                return $this->callDeepSeek($content, $contentType, $contentFormat, $providerConfig);
            default:
                throw new \Exception("Unsupported provider: {$provider}");
        }
    }

    /**
     * Call YandexGPT API
     */
    private function callYandexGPT(string $content, string $contentType, string $contentFormat, array $config): array
    {
        $prompt = $this->getPromptForContentType($contentType);
        
        if ($contentFormat === 'image') {
            // For images, we need to convert to base64
            $imageData = base64_encode(Storage::get($content));
            $prompt = "Analyze this image: data:image/jpeg;base64,{$imageData}. {$prompt}";
        } else {
            $prompt = "Text to moderate: {$content}. {$prompt}";
        }

        $response = Http::timeout($config['timeout'] ?? 30)
            ->withHeaders([
                'Authorization' => 'Api-Key ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ])
            ->post('https://llm.api.cloud.yandex.net/foundationModels/v1/completion', [
                'modelUri' => "gpt://{$config['folder_id']}/{$config['model']}",
                'completionOptions' => [
                    'temperature' => $config['temperature'] ?? 0.1,
                    'maxTokens' => $config['max_tokens'] ?? 1000,
                ],
                'messages' => [
                    [
                        'role' => 'user',
                        'text' => $prompt,
                    ],
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception("YandexGPT API error: " . $response->body());
        }

        $result = $response->json();
        $responseText = $result['result']['alternatives'][0]['message']['text'] ?? '';

        return $this->parseModerationResponse($responseText);
    }

    /**
     * Call GigaChat API
     */
    private function callGigaChat(string $content, string $contentType, string $contentFormat, array $config): array
    {
        $prompt = $this->getPromptForContentType($contentType);
        
        if ($contentFormat === 'image') {
            $imageData = base64_encode(Storage::get($content));
            $prompt = "Analyze this image: data:image/jpeg;base64,{$imageData}. {$prompt}";
        } else {
            $prompt = "Text to moderate: {$content}. {$prompt}";
        }

        $response = Http::timeout($config['timeout'] ?? 30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ])
            ->post('https://gigachat.devices.sberbank.ru/api/v1/chat/completions', [
                'model' => $config['model'] ?? 'GigaChat',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => $config['temperature'] ?? 0.1,
                'max_tokens' => $config['max_tokens'] ?? 1000,
            ]);

        if (!$response->successful()) {
            throw new \Exception("GigaChat API error: " . $response->body());
        }

        $result = $response->json();
        $responseText = $result['choices'][0]['message']['content'] ?? '';

        return $this->parseModerationResponse($responseText);
    }

    /**
     * Call ChatGPT API
     */
    private function callChatGPT(string $content, string $contentType, string $contentFormat, array $config): array
    {
        $prompt = $this->getPromptForContentType($contentType);
        
        if ($contentFormat === 'image') {
            $imageData = base64_encode(Storage::get($content));
            $prompt = "Analyze this image: data:image/jpeg;base64,{$imageData}. {$prompt}";
        } else {
            $prompt = "Text to moderate: {$content}. {$prompt}";
        }

        $response = Http::timeout($config['timeout'] ?? 30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $config['model'] ?? 'gpt-4',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => $config['temperature'] ?? 0.1,
                'max_tokens' => $config['max_tokens'] ?? 1000,
            ]);

        if (!$response->successful()) {
            throw new \Exception("ChatGPT API error: " . $response->body());
        }

        $result = $response->json();
        $responseText = $result['choices'][0]['message']['content'] ?? '';

        return $this->parseModerationResponse($responseText);
    }

    /**
     * Call DeepSeek API
     */
    private function callDeepSeek(string $content, string $contentType, string $contentFormat, array $config): array
    {
        $prompt = $this->getPromptForContentType($contentType);
        
        if ($contentFormat === 'image') {
            $imageData = base64_encode(Storage::get($content));
            $prompt = "Analyze this image: data:image/jpeg;base64,{$imageData}. {$prompt}";
        } else {
            $prompt = "Text to moderate: {$content}. {$prompt}";
        }

        $response = Http::timeout($config['timeout'] ?? 30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.deepseek.com/v1/chat/completions', [
                'model' => $config['model'] ?? 'deepseek-chat',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => $config['temperature'] ?? 0.1,
                'max_tokens' => $config['max_tokens'] ?? 1000,
            ]);

        if (!$response->successful()) {
            throw new \Exception("DeepSeek API error: " . $response->body());
        }

        $result = $response->json();
        $responseText = $result['choices'][0]['message']['content'] ?? '';

        return $this->parseModerationResponse($responseText);
    }

    /**
     * Parse AI response into moderation result
     */
    private function parseModerationResponse(string $response): array
    {
        try {
            // Try to extract JSON from response
            $jsonStart = strpos($response, '{');
            $jsonEnd = strrpos($response, '}');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
                $data = json_decode($jsonString, true);
                
                if ($data && isset($data['approved'])) {
                    return [
                        'approved' => (bool) $data['approved'],
                        'confidence' => (float) ($data['confidence'] ?? 0.5),
                        'reason' => $data['reason'] ?? 'AI moderation result',
                        'categories' => $data['categories'] ?? [],
                        'raw_response' => $response,
                    ];
                }
            }

            // Fallback: try to determine from text
            $approved = !preg_match('/\b(reject|inappropriate|violation|offensive|spam)\b/i', $response);
            $confidence = $approved ? 0.7 : 0.8;

            return [
                'approved' => $approved,
                'confidence' => $confidence,
                'reason' => 'Parsed from text response',
                'categories' => [],
                'raw_response' => $response,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to parse AI moderation response', [
                'response' => $response,
                'error' => $e->getMessage()
            ]);

            return $this->createPendingReviewResult('Failed to parse AI response');
        }
    }

    /**
     * Get provider for content type
     */
    private function getProviderForContentType(string $contentType): string
    {
        $contentTypes = $this->config['content_types'] ?? [];
        return $contentTypes[$contentType]['provider'] ?? $this->defaultProvider;
    }

    /**
     * Get prompt for content type
     */
    private function getPromptForContentType(string $contentType): string
    {
        $contentTypes = $this->config['content_types'] ?? [];
        $promptKey = $contentTypes[$contentType]['prompt'] ?? 'comment_moderation';
        
        return $this->config['prompts'][$promptKey] ?? 'Moderate this content for inappropriate material.';
    }

    /**
     * Check if moderation is enabled for content type
     */
    private function isModerationEnabled(string $contentType): bool
    {
        if (!($this->config['enabled'] ?? true)) {
            return false;
        }

        $contentTypes = $this->config['content_types'] ?? [];
        return $contentTypes[$contentType]['enabled'] ?? false;
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit(string $provider): void
    {
        $rateLimiting = $this->config['rate_limiting'] ?? [];
        
        if (!($rateLimiting['enabled'] ?? true)) {
            return;
        }

        $key = "ai_moderation_rate_limit_{$provider}_" . now()->format('Y-m-d-H-i');
        $count = Cache::get($key, 0);
        
        $maxPerMinute = $rateLimiting['max_requests_per_minute'] ?? 60;
        
        if ($count >= $maxPerMinute) {
            throw new \Exception("Rate limit exceeded for provider {$provider}");
        }

        Cache::increment($key);
        Cache::expire($key, 60);
    }

    /**
     * Check if caching is enabled
     */
    private function isCachingEnabled(): bool
    {
        return $this->config['caching']['enabled'] ?? true;
    }

    /**
     * Get cache TTL
     */
    private function getCacheTTL(): int
    {
        return $this->config['caching']['ttl'] ?? 3600;
    }

    /**
     * Generate cache key
     */
    private function getCacheKey(string $type, string $content, string $contentType): string
    {
        return "ai_moderation_{$type}_" . md5($content . $contentType);
    }

    /**
     * Log moderation result
     */
    private function logModerationResult(string $contentType, string $format, array $result): void
    {
        if (!($this->config['logging']['enabled'] ?? true)) {
            return;
        }

        $logLevel = $this->config['logging']['log_level'] ?? 'info';
        
        Log::log($logLevel, 'AI moderation completed', [
            'content_type' => $contentType,
            'format' => $format,
            'approved' => $result['approved'],
            'confidence' => $result['confidence'],
            'reason' => $result['reason'],
            'categories' => $result['categories'],
        ]);
    }

    /**
     * Handle moderation failure
     */
    private function handleModerationFailure(string $contentType): array
    {
        $fallback = $this->config['fallback']['on_failure'] ?? 'manual_review';
        
        return match ($fallback) {
            'approve' => $this->createApprovedResult('AI moderation failed, auto-approved'),
            'reject' => $this->createRejectedResult('AI moderation failed, auto-rejected'),
            default => $this->createPendingReviewResult('AI moderation failed, pending manual review'),
        };
    }

    /**
     * Create approved result
     */
    private function createApprovedResult(string $reason): array
    {
        return [
            'approved' => true,
            'confidence' => 1.0,
            'reason' => $reason,
            'categories' => [],
            'raw_response' => null,
        ];
    }

    /**
     * Create rejected result
     */
    private function createRejectedResult(string $reason): array
    {
        return [
            'approved' => false,
            'confidence' => 1.0,
            'reason' => $reason,
            'categories' => ['moderation_failure'],
            'raw_response' => null,
        ];
    }

    /**
     * Create pending review result
     */
    private function createPendingReviewResult(string $reason): array
    {
        return [
            'approved' => false,
            'confidence' => 0.5,
            'reason' => $reason,
            'categories' => ['pending_review'],
            'raw_response' => null,
        ];
    }

    /**
     * Get moderation statistics
     */
    public function getModerationStatistics(): array
    {
        // This would typically query the database for moderation statistics
        return [
            'total_moderated' => 0,
            'approved_count' => 0,
            'rejected_count' => 0,
            'pending_review_count' => 0,
            'by_provider' => [],
            'by_content_type' => [],
        ];
    }

    /**
     * Clear moderation cache
     */
    public function clearCache(): void
    {
        if ($this->isCachingEnabled()) {
            Cache::flush();
        }
    }
}
