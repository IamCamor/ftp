<?php

return [
    'limits' => [
        'user' => [
            'max_photos' => 10,
            'max_videos' => 0,
            'max_media_total' => 10,
            'video_enabled' => false,
        ],
        'pro' => [
            'max_photos' => 20,
            'max_videos' => 5,
            'max_media_total' => 25,
            'video_enabled' => true,
        ],
        'premium' => [
            'max_photos' => 20,
            'max_videos' => 5,
            'max_media_total' => 25,
            'video_enabled' => true,
        ],
        'admin' => [
            'max_photos' => 50,
            'max_videos' => 10,
            'max_media_total' => 60,
            'video_enabled' => true,
        ],
    ],
    
    'file_types' => [
        'photos' => ['jpg', 'jpeg', 'png', 'webp'],
        'videos' => ['mp4', 'mov', 'avi', 'webm'],
    ],
    
    'max_file_size' => [
        'photo' => 10 * 1024 * 1024, // 10MB
        'video' => 100 * 1024 * 1024, // 100MB
    ],
    
    'storage' => [
        'disk' => env('MEDIA_DISK', 'public'),
        'path' => [
            'photos' => 'catches/photos',
            'videos' => 'catches/videos',
        ],
    ],
    
    'thumbnails' => [
        'enabled' => true,
        'sizes' => [
            'small' => [150, 150],
            'medium' => [400, 400],
            'large' => [800, 800],
        ],
    ],
    
    'video_processing' => [
        'enabled' => true,
        'generate_thumbnails' => true,
        'thumbnail_times' => [1, 5, 10], // секунды для создания превью
    ],
];
