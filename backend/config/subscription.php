<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for subscription system
    | including pricing, payment methods, and feature settings.
    |
    */

    'pro' => [
        'price_rub' => 199,
        'price_bonus' => 1990,
        'duration_days' => 30,
        'features' => [
            'unlimited_catches' => true,
            'advanced_statistics' => true,
            'priority_support' => true,
            'ad_free' => true,
        ],
    ],

    'premium' => [
        'price_rub' => 499,
        'price_bonus' => 4990,
        'duration_days' => 30,
        'crown_icon_url' => 'https://cdn.fishtrackpro.ru/icons/crown.svg',
        'features' => [
            'unlimited_catches' => true,
            'advanced_statistics' => true,
            'priority_support' => true,
            'ad_free' => true,
            'create_points' => true,
            'manage_points' => true,
            'create_groups' => true,
            'moderate_groups' => true,
            'priority_search' => true,
            'crown_badge' => true,
        ],
    ],

    'payment_methods' => [
        'yandex_pay' => [
            'name' => 'Яндекс.Платежи',
            'enabled' => true,
            'icon' => 'https://cdn.fishtrackpro.ru/icons/yandex-pay.svg',
        ],
        'sber_pay' => [
            'name' => 'Сбербанк',
            'enabled' => true,
            'icon' => 'https://cdn.fishtrackpro.ru/icons/sber-pay.svg',
        ],
        'apple_pay' => [
            'name' => 'Apple Pay',
            'enabled' => true,
            'icon' => 'https://cdn.fishtrackpro.ru/icons/apple-pay.svg',
        ],
        'google_pay' => [
            'name' => 'Google Pay',
            'enabled' => true,
            'icon' => 'https://cdn.fishtrackpro.ru/icons/google-pay.svg',
        ],
        'bonuses' => [
            'name' => 'Бонусы',
            'enabled' => true,
            'icon' => 'https://cdn.fishtrackpro.ru/icons/bonus.svg',
        ],
    ],

    'bonus_system' => [
        'earn_per_catch' => 10,
        'earn_per_like' => 1,
        'earn_per_comment' => 2,
        'earn_per_share' => 5,
        'earn_per_daily_login' => 5,
        'max_daily_earnings' => 100,
    ],

    'trial' => [
        'enabled' => true,
        'duration_days' => 7,
        'types' => ['pro', 'premium'],
    ],

    'auto_renewal' => [
        'enabled' => true,
        'reminder_days' => [7, 3, 1],
    ],

    'refund' => [
        'enabled' => true,
        'max_days' => 14,
        'partial_refund' => true,
    ],
];
