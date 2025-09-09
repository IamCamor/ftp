<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Telegram bot notifications and statistics
    |
    */

    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'chat_id' => env('TELEGRAM_CHAT_ID'),
    'admin_chat_id' => env('TELEGRAM_ADMIN_CHAT_ID', env('TELEGRAM_CHAT_ID')),

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable different types of notifications
    |
    */

    'features' => [
        'deployment_notifications' => env('TELEGRAM_DEPLOYMENT_NOTIFICATIONS', true),
        'user_registration_notifications' => env('TELEGRAM_USER_REGISTRATION_NOTIFICATIONS', true),
        'catch_notifications' => env('TELEGRAM_CATCH_NOTIFICATIONS', true),
        'payment_notifications' => env('TELEGRAM_PAYMENT_NOTIFICATIONS', true),
        'point_notifications' => env('TELEGRAM_POINT_NOTIFICATIONS', true),
        'daily_statistics' => env('TELEGRAM_DAILY_STATISTICS', true),
        'error_notifications' => env('TELEGRAM_ERROR_NOTIFICATIONS', true),
        'bonus_notifications' => env('TELEGRAM_BONUS_NOTIFICATIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Settings for different types of notifications
    |
    */

    'notifications' => [
        'deployment' => [
            'enabled' => true,
            'template' => "🚀 *Deployment Update*\n\n" .
                         "Repository: {repository}\n" .
                         "Branch: {branch}\n" .
                         "Commit: {commit_message}\n" .
                         "Author: {author}\n" .
                         "Status: {status}\n" .
                         "Time: {time}",
        ],
        'user_registration' => [
            'enabled' => true,
            'template' => "👤 *New User Registration*\n\n" .
                         "Name: {name}\n" .
                         "Username: {username}\n" .
                         "Email: {email}\n" .
                         "Role: {role}\n" .
                         "Time: {time}",
        ],
        'catch' => [
            'enabled' => true,
            'template' => "🎣 *New Catch Recorded*\n\n" .
                         "User: {user_name} (@{username})\n" .
                         "Fish: {fish_type}\n" .
                         "Weight: {weight} kg\n" .
                         "Length: {length} cm\n" .
                         "Location: {location}\n" .
                         "Time: {time}",
        ],
        'payment' => [
            'enabled' => true,
            'template' => "💳 *New Payment*\n\n" .
                         "User: {user_name} (@{username})\n" .
                         "Amount: {amount} {currency}\n" .
                         "Type: {payment_type}\n" .
                         "Status: {status}\n" .
                         "Time: {time}",
        ],
        'point' => [
            'enabled' => true,
            'template' => "📍 *New Fishing Point*\n\n" .
                         "User: {user_name} (@{username})\n" .
                         "Title: {title}\n" .
                         "Location: {location}\n" .
                         "Privacy: {privacy}\n" .
                         "Time: {time}",
        ],
        'error' => [
            'enabled' => true,
            'template' => "❌ *Application Error*\n\n" .
                         "Error: {error_message}\n" .
                         "File: {file}:{line}\n" .
                         "User: {user_info}\n" .
                         "Time: {time}",
        ],
        'bonus' => [
            'enabled' => true,
            'template' => "🎁 *Бонус начислен!*\n\n" .
                         "Пользователь: {user_name} (@{username})\n" .
                         "Действие: {action}\n" .
                         "Бонус: +{amount} 🪙\n" .
                         "Баланс: {balance} 🪙\n" .
                         "Время: {time}",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Daily Statistics Template
    |--------------------------------------------------------------------------
    |
    | Template for daily statistics report
    |
    */

    'daily_stats_template' => "📊 *Daily Statistics - {date}*\n\n" .
                             "👥 *Users:*\n" .
                             "• New registrations: {new_users}\n" .
                             "• Total users: {total_users}\n" .
                             "• Active users: {active_users}\n\n" .
                             "🎣 *Catches:*\n" .
                             "• New catches: {new_catches}\n" .
                             "• Total catches: {total_catches}\n" .
                             "• Total weight: {total_weight} kg\n\n" .
                             "📍 *Points:*\n" .
                             "• New points: {new_points}\n" .
                             "• Total points: {total_points}\n\n" .
                             "💳 *Payments:*\n" .
                             "• New payments: {new_payments}\n" .
                             "• Total revenue: {total_revenue} {currency}\n\n" .
                             "📈 *Growth:*\n" .
                             "• Users growth: {users_growth}%\n" .
                             "• Catches growth: {catches_growth}%\n" .
                             "• Revenue growth: {revenue_growth}%",

    /*
    |--------------------------------------------------------------------------
    | Bot Commands
    |--------------------------------------------------------------------------
    |
    | Available bot commands
    |
    */

    'commands' => [
        'start' => 'Start the bot and show welcome message',
        'help' => 'Show available commands',
        'stats' => 'Get current statistics',
        'users' => 'Get user statistics',
        'catches' => 'Get catch statistics',
        'payments' => 'Get payment statistics',
        'points' => 'Get fishing points statistics',
        'status' => 'Get application status',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting for notifications to prevent spam
    |
    */

    'rate_limiting' => [
        'enabled' => true,
        'max_notifications_per_minute' => 10,
        'max_notifications_per_hour' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    |
    | Settings for Telegram webhook (if using webhook instead of polling)
    |
    */

    'webhook' => [
        'enabled' => env('TELEGRAM_WEBHOOK_ENABLED', false),
        'url' => env('TELEGRAM_WEBHOOK_URL'),
        'secret_token' => env('TELEGRAM_WEBHOOK_SECRET'),
    ],
];
