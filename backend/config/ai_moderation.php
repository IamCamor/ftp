<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Moderation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI-powered content moderation
    |
    */

    'enabled' => env('AI_MODERATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable different types of AI moderation
    |
    */

    'features' => [
        'photo_moderation' => env('AI_PHOTO_MODERATION', true),
        'comment_moderation' => env('AI_COMMENT_MODERATION', true),
        'catch_moderation' => env('AI_CATCH_MODERATION', true),
        'point_moderation' => env('AI_POINT_MODERATION', true),
        'auto_approve' => env('AI_AUTO_APPROVE', false),
        'auto_reject' => env('AI_AUTO_REJECT', false),
        'manual_review' => env('AI_MANUAL_REVIEW', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for different AI providers
    |
    */

    'providers' => [
        'yandexgpt' => [
            'enabled' => env('YANDEX_GPT_ENABLED', false),
            'api_key' => env('YANDEX_GPT_API_KEY'),
            'folder_id' => env('YANDEX_GPT_FOLDER_ID'),
            'model' => env('YANDEX_GPT_MODEL', 'yandexgpt'),
            'temperature' => env('YANDEX_GPT_TEMPERATURE', 0.1),
            'max_tokens' => env('YANDEX_GPT_MAX_TOKENS', 1000),
            'timeout' => env('YANDEX_GPT_TIMEOUT', 30),
        ],
        'gigachat' => [
            'enabled' => env('GIGACHAT_ENABLED', false),
            'api_key' => env('GIGACHAT_API_KEY'),
            'model' => env('GIGACHAT_MODEL', 'GigaChat'),
            'temperature' => env('GIGACHAT_TEMPERATURE', 0.1),
            'max_tokens' => env('GIGACHAT_MAX_TOKENS', 1000),
            'timeout' => env('GIGACHAT_TIMEOUT', 30),
        ],
        'chatgpt' => [
            'enabled' => env('CHATGPT_ENABLED', false),
            'api_key' => env('CHATGPT_API_KEY'),
            'model' => env('CHATGPT_MODEL', 'gpt-4'),
            'temperature' => env('CHATGPT_TEMPERATURE', 0.1),
            'max_tokens' => env('CHATGPT_MAX_TOKENS', 1000),
            'timeout' => env('CHATGPT_TIMEOUT', 30),
        ],
        'deepseek' => [
            'enabled' => env('DEEPSEEK_ENABLED', false),
            'api_key' => env('DEEPSEEK_API_KEY'),
            'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'temperature' => env('DEEPSEEK_TEMPERATURE', 0.1),
            'max_tokens' => env('DEEPSEEK_MAX_TOKENS', 1000),
            'timeout' => env('DEEPSEEK_TIMEOUT', 30),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    |
    | The default AI provider to use for moderation
    |
    */

    'default_provider' => env('AI_DEFAULT_PROVIDER', 'yandexgpt'),

    /*
    |--------------------------------------------------------------------------
    | Moderation Rules
    |--------------------------------------------------------------------------
    |
    | Rules and thresholds for content moderation
    |
    */

    'rules' => [
        'photo' => [
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'allowed_formats' => ['jpg', 'jpeg', 'png', 'webp'],
            'min_resolution' => [100, 100],
            'max_resolution' => [4096, 4096],
            'content_categories' => [
                'explicit_content',
                'violence',
                'hate_speech',
                'spam',
                'inappropriate',
            ],
        ],
        'text' => [
            'max_length' => 10000,
            'min_length' => 1,
            'content_categories' => [
                'hate_speech',
                'harassment',
                'spam',
                'inappropriate',
                'offensive',
                'adult_content',
                'violence',
                'illegal_activities',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Moderation Prompts
    |--------------------------------------------------------------------------
    |
    | Prompts for different types of content moderation
    |
    */

    'prompts' => [
        'photo_moderation' => 'Analyze this image for inappropriate content. Check for: explicit content, violence, hate speech, spam, or any content that violates community guidelines. Respond with JSON: {"approved": true/false, "confidence": 0.0-1.0, "reason": "explanation", "categories": ["category1", "category2"]}',
        
        'comment_moderation' => 'Moderate this text comment for inappropriate content. Check for: hate speech, harassment, spam, offensive language, adult content, violence, or illegal activities. Respond with JSON: {"approved": true/false, "confidence": 0.0-1.0, "reason": "explanation", "categories": ["category1", "category2"]}',
        
        'catch_moderation' => 'Moderate this fishing catch description for inappropriate content. Check for: hate speech, harassment, spam, offensive language, or any content that violates community guidelines. Respond with JSON: {"approved": true/false, "confidence": 0.0-1.0, "reason": "explanation", "categories": ["category1", "category2"]}',
        
        'point_moderation' => 'Moderate this fishing point description for inappropriate content. Check for: hate speech, harassment, spam, offensive language, or any content that violates community guidelines. Respond with JSON: {"approved": true/false, "confidence": 0.0-1.0, "reason": "explanation", "categories": ["category1", "category2"]}',
    ],

    /*
    |--------------------------------------------------------------------------
    | Moderation Thresholds
    |--------------------------------------------------------------------------
    |
    | Confidence thresholds for automatic decisions
    |
    */

    'thresholds' => [
        'auto_approve_confidence' => env('AI_AUTO_APPROVE_CONFIDENCE', 0.9),
        'auto_reject_confidence' => env('AI_AUTO_REJECT_CONFIDENCE', 0.8),
        'manual_review_confidence' => env('AI_MANUAL_REVIEW_CONFIDENCE', 0.7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Moderation Actions
    |--------------------------------------------------------------------------
    |
    | Actions to take based on moderation results
    |
    */

    'actions' => [
        'approved' => [
            'status' => 'approved',
            'notify_user' => false,
            'log_action' => true,
        ],
        'rejected' => [
            'status' => 'rejected',
            'notify_user' => true,
            'log_action' => true,
            'hide_content' => true,
        ],
        'pending_review' => [
            'status' => 'pending_review',
            'notify_user' => true,
            'log_action' => true,
            'hide_content' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting for AI moderation requests
    |
    */

    'rate_limiting' => [
        'enabled' => env('AI_RATE_LIMITING_ENABLED', true),
        'max_requests_per_minute' => env('AI_MAX_REQUESTS_PER_MINUTE', 60),
        'max_requests_per_hour' => env('AI_MAX_REQUESTS_PER_HOUR', 1000),
        'max_requests_per_day' => env('AI_MAX_REQUESTS_PER_DAY', 10000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Caching configuration for moderation results
    |
    */

    'caching' => [
        'enabled' => env('AI_CACHING_ENABLED', true),
        'ttl' => env('AI_CACHE_TTL', 3600), // 1 hour
        'store' => env('AI_CACHE_STORE', 'redis'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Logging configuration for moderation activities
    |
    */

    'logging' => [
        'enabled' => env('AI_LOGGING_ENABLED', true),
        'log_level' => env('AI_LOG_LEVEL', 'info'),
        'log_failed_requests' => env('AI_LOG_FAILED_REQUESTS', true),
        'log_successful_requests' => env('AI_LOG_SUCCESSFUL_REQUESTS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Notification settings for moderation events
    |
    */

    'notifications' => [
        'enabled' => env('AI_NOTIFICATIONS_ENABLED', true),
        'notify_admins' => env('AI_NOTIFY_ADMINS', true),
        'notify_users' => env('AI_NOTIFY_USERS', true),
        'telegram_notifications' => env('AI_TELEGRAM_NOTIFICATIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Behavior
    |--------------------------------------------------------------------------
    |
    | What to do when AI moderation fails
    |
    */

    'fallback' => [
        'on_failure' => env('AI_FALLBACK_ON_FAILURE', 'manual_review'), // 'approve', 'reject', 'manual_review'
        'retry_attempts' => env('AI_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('AI_RETRY_DELAY', 5), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Types
    |--------------------------------------------------------------------------
    |
    | Types of content that can be moderated
    |
    */

    'content_types' => [
        'catch_photos' => [
            'enabled' => true,
            'provider' => 'yandexgpt',
            'prompt' => 'photo_moderation',
        ],
        'catch_comments' => [
            'enabled' => true,
            'provider' => 'yandexgpt',
            'prompt' => 'comment_moderation',
        ],
        'catch_descriptions' => [
            'enabled' => true,
            'provider' => 'yandexgpt',
            'prompt' => 'catch_moderation',
        ],
        'point_descriptions' => [
            'enabled' => true,
            'provider' => 'yandexgpt',
            'prompt' => 'point_moderation',
        ],
        'point_comments' => [
            'enabled' => true,
            'provider' => 'yandexgpt',
            'prompt' => 'comment_moderation',
        ],
        'user_bio' => [
            'enabled' => false,
            'provider' => 'yandexgpt',
            'prompt' => 'comment_moderation',
        ],
    ],
];
