<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the AI service integration
    |
    */

    'service_url' => env('AI_SERVICE_URL', 'http://localhost:8001'),
    'timeout' => env('AI_SERVICE_TIMEOUT', 30),
    'enabled' => env('AI_SERVICE_ENABLED', true),
    
    /*
    |--------------------------------------------------------------------------
    | Content Moderation Settings
    |--------------------------------------------------------------------------
    |
    | Settings for content moderation thresholds
    |
    */
    
    'moderation' => [
        'min_confidence' => env('AI_MIN_CONFIDENCE', 0.7),
        'auto_approve_threshold' => env('AI_AUTO_APPROVE_THRESHOLD', 0.9),
        'auto_reject_threshold' => env('AI_AUTO_REJECT_THRESHOLD', 0.3),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Image Analysis Settings
    |--------------------------------------------------------------------------
    |
    | Settings for image analysis
    |
    */
    
    'image_analysis' => [
        'max_file_size' => env('AI_MAX_FILE_SIZE', 10485760), // 10MB
        'allowed_formats' => ['jpg', 'jpeg', 'png', 'webp'],
        'min_dimensions' => ['width' => 50, 'height' => 50],
        'max_dimensions' => ['width' => 5000, 'height' => 5000],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Content Types
    |--------------------------------------------------------------------------
    |
    | Different content types and their moderation rules
    |
    */
    
    'content_types' => [
        'catch' => [
            'required_categories' => ['water', 'nature'],
            'forbidden_categories' => ['explicit'],
            'min_confidence' => 0.6,
        ],
        'profile' => [
            'required_categories' => ['person'],
            'forbidden_categories' => ['explicit'],
            'min_confidence' => 0.7,
        ],
        'place' => [
            'required_categories' => ['nature'],
            'forbidden_categories' => ['explicit'],
            'min_confidence' => 0.5,
        ],
    ],
];

