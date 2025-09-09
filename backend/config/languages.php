<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | Top 15 languages by number of speakers worldwide
    |
    */

    'supported' => [
        'en' => [
            'name' => 'English',
            'native_name' => 'English',
            'flag' => '🇺🇸',
            'rtl' => false,
            'enabled' => true,
        ],
        'zh' => [
            'name' => 'Chinese (Simplified)',
            'native_name' => '中文',
            'flag' => '🇨🇳',
            'rtl' => false,
            'enabled' => true,
        ],
        'hi' => [
            'name' => 'Hindi',
            'native_name' => 'हिन्दी',
            'flag' => '🇮🇳',
            'rtl' => false,
            'enabled' => true,
        ],
        'es' => [
            'name' => 'Spanish',
            'native_name' => 'Español',
            'flag' => '🇪🇸',
            'rtl' => false,
            'enabled' => true,
        ],
        'fr' => [
            'name' => 'French',
            'native_name' => 'Français',
            'flag' => '🇫🇷',
            'rtl' => false,
            'enabled' => true,
        ],
        'ar' => [
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'flag' => '🇸🇦',
            'rtl' => true,
            'enabled' => true,
        ],
        'bn' => [
            'name' => 'Bengali',
            'native_name' => 'বাংলা',
            'flag' => '🇧🇩',
            'rtl' => false,
            'enabled' => true,
        ],
        'pt' => [
            'name' => 'Portuguese',
            'native_name' => 'Português',
            'flag' => '🇵🇹',
            'rtl' => false,
            'enabled' => true,
        ],
        'ru' => [
            'name' => 'Russian',
            'native_name' => 'Русский',
            'flag' => '🇷🇺',
            'rtl' => false,
            'enabled' => true,
        ],
        'ja' => [
            'name' => 'Japanese',
            'native_name' => '日本語',
            'flag' => '🇯🇵',
            'rtl' => false,
            'enabled' => true,
        ],
        'de' => [
            'name' => 'German',
            'native_name' => 'Deutsch',
            'flag' => '🇩🇪',
            'rtl' => false,
            'enabled' => true,
        ],
        'ko' => [
            'name' => 'Korean',
            'native_name' => '한국어',
            'flag' => '🇰🇷',
            'rtl' => false,
            'enabled' => true,
        ],
        'tr' => [
            'name' => 'Turkish',
            'native_name' => 'Türkçe',
            'flag' => '🇹🇷',
            'rtl' => false,
            'enabled' => true,
        ],
        'vi' => [
            'name' => 'Vietnamese',
            'native_name' => 'Tiếng Việt',
            'flag' => '🇻🇳',
            'rtl' => false,
            'enabled' => true,
        ],
        'it' => [
            'name' => 'Italian',
            'native_name' => 'Italiano',
            'flag' => '🇮🇹',
            'rtl' => false,
            'enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | The default language for the application
    |
    */

    'default' => env('APP_DEFAULT_LANGUAGE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Language
    |--------------------------------------------------------------------------
    |
    | The fallback language when translation is missing
    |
    */

    'fallback' => env('APP_FALLBACK_LANGUAGE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Auto Detection
    |--------------------------------------------------------------------------
    |
    | Enable automatic language detection from browser headers
    |
    */

    'auto_detect' => env('APP_AUTO_DETECT_LANGUAGE', true),

    /*
    |--------------------------------------------------------------------------
    | Language Detection Sources
    |--------------------------------------------------------------------------
    |
    | Priority order for language detection
    |
    */

    'detection_sources' => [
        'user_preference',  // User's saved preference
        'url_parameter',    // ?lang=en
        'session',          // Session stored language
        'browser_header',   // Accept-Language header
        'default',          // Application default
    ],

    /*
    |--------------------------------------------------------------------------
    | Language Storage
    |--------------------------------------------------------------------------
    |
    | How to store user's language preference
    |
    */

    'storage' => [
        'user_preference' => true,  // Store in user profile
        'session' => true,          // Store in session
        'cookie' => true,           // Store in cookie
    ],

    /*
    |--------------------------------------------------------------------------
    | RTL Support
    |--------------------------------------------------------------------------
    |
    | Enable right-to-left language support
    |
    */

    'rtl_support' => true,

    /*
    |--------------------------------------------------------------------------
    | Language Switcher
    |--------------------------------------------------------------------------
    |
    | Configuration for language switcher component
    |
    */

    'switcher' => [
        'show_flags' => true,
        'show_native_names' => true,
        'show_english_names' => false,
        'group_by_region' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Management
    |--------------------------------------------------------------------------
    |
    | Configuration for translation management
    |
    */

    'translation' => [
        'auto_generate' => false,        // Auto-generate missing translations
        'fallback_to_key' => true,       // Fallback to translation key
        'cache_translations' => true,    // Cache translations
        'cache_ttl' => 3600,            // Cache TTL in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Language Regions
    |--------------------------------------------------------------------------
    |
    | Group languages by regions for better organization
    |
    */

    'regions' => [
        'europe' => ['en', 'es', 'fr', 'de', 'ru', 'it', 'pt'],
        'asia' => ['zh', 'hi', 'ja', 'ko', 'bn', 'vi'],
        'middle_east' => ['ar', 'tr'],
        'americas' => ['en', 'es', 'pt'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Browser Language Mapping
    |--------------------------------------------------------------------------
    |
    | Map browser language codes to application language codes
    |
    */

    'browser_mapping' => [
        'zh-cn' => 'zh',
        'zh-tw' => 'zh',
        'zh-hans' => 'zh',
        'zh-hant' => 'zh',
        'pt-br' => 'pt',
        'pt-pt' => 'pt',
        'en-us' => 'en',
        'en-gb' => 'en',
        'en-au' => 'en',
        'es-es' => 'es',
        'es-mx' => 'es',
        'fr-fr' => 'fr',
        'fr-ca' => 'fr',
        'de-de' => 'de',
        'de-at' => 'de',
        'ru-ru' => 'ru',
        'ja-jp' => 'ja',
        'ko-kr' => 'ko',
        'ar-sa' => 'ar',
        'ar-eg' => 'ar',
        'hi-in' => 'hi',
        'bn-bd' => 'bn',
        'tr-tr' => 'tr',
        'vi-vn' => 'vi',
        'it-it' => 'it',
    ],
];
