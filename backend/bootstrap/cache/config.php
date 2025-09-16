<?php return array (
  'mail' => 
  array (
    'default' => 'smtp',
    'mailers' => 
    array (
      'smtp' => 
      array (
        'transport' => 'smtp',
        'scheme' => NULL,
        'url' => NULL,
        'host' => 'mailpit',
        'port' => '1025',
        'username' => NULL,
        'password' => NULL,
        'timeout' => NULL,
        'local_domain' => 'api.fishtrackpro.ru',
      ),
      'ses' => 
      array (
        'transport' => 'ses',
      ),
      'postmark' => 
      array (
        'transport' => 'postmark',
      ),
      'resend' => 
      array (
        'transport' => 'resend',
      ),
      'sendmail' => 
      array (
        'transport' => 'sendmail',
        'path' => '/usr/sbin/sendmail -bs -i',
      ),
      'log' => 
      array (
        'transport' => 'log',
        'channel' => NULL,
      ),
      'array' => 
      array (
        'transport' => 'array',
      ),
      'failover' => 
      array (
        'transport' => 'failover',
        'mailers' => 
        array (
          0 => 'smtp',
          1 => 'log',
        ),
        'retry_after' => 60,
      ),
      'roundrobin' => 
      array (
        'transport' => 'roundrobin',
        'mailers' => 
        array (
          0 => 'ses',
          1 => 'postmark',
        ),
        'retry_after' => 60,
      ),
    ),
    'from' => 
    array (
      'address' => 'hello@example.com',
      'name' => 'FishTrackPro',
    ),
    'markdown' => 
    array (
      'theme' => 'default',
      'paths' => 
      array (
        0 => '/var/www/ftp/backend/resources/views/vendor/mail',
      ),
    ),
  ),
  'concurrency' => 
  array (
    'default' => 'process',
  ),
  'queue' => 
  array (
    'default' => 'redis',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 0,
        'after_commit' => false,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => '',
        'secret' => '',
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'default',
        'suffix' => NULL,
        'region' => 'us-east-1',
        'after_commit' => false,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
        'after_commit' => false,
      ),
    ),
    'batching' => 
    array (
      'database' => 'mysql',
      'table' => 'job_batches',
    ),
    'failed' => 
    array (
      'driver' => 'database-uuids',
      'database' => 'mysql',
      'table' => 'failed_jobs',
    ),
  ),
  'broadcasting' => 
  array (
    'default' => 'null',
    'connections' => 
    array (
      'reverb' => 
      array (
        'driver' => 'reverb',
        'key' => NULL,
        'secret' => NULL,
        'app_id' => NULL,
        'options' => 
        array (
          'host' => NULL,
          'port' => 443,
          'scheme' => 'https',
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => '',
        'secret' => '',
        'app_id' => '',
        'options' => 
        array (
          'cluster' => 'mt1',
          'host' => 'api-mt1.pusher.com',
          'port' => '443',
          'scheme' => 'https',
          'encrypted' => true,
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'ably' => 
      array (
        'driver' => 'ably',
        'key' => NULL,
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
      'null' => 
      array (
        'driver' => 'null',
      ),
    ),
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => 12,
      'verify' => true,
      'limit' => NULL,
    ),
    'argon' => 
    array (
      'memory' => 65536,
      'threads' => 1,
      'time' => 4,
      'verify' => true,
    ),
    'rehash_on_login' => true,
  ),
  'ai' => 
  array (
    'service_url' => 'http://localhost:8001',
    'timeout' => 30,
    'enabled' => true,
    'moderation' => 
    array (
      'min_confidence' => 0.7,
      'auto_approve_threshold' => 0.9,
      'auto_reject_threshold' => 0.3,
    ),
    'image_analysis' => 
    array (
      'max_file_size' => 10485760,
      'allowed_formats' => 
      array (
        0 => 'jpg',
        1 => 'jpeg',
        2 => 'png',
        3 => 'webp',
      ),
      'min_dimensions' => 
      array (
        'width' => 50,
        'height' => 50,
      ),
      'max_dimensions' => 
      array (
        'width' => 5000,
        'height' => 5000,
      ),
    ),
    'content_types' => 
    array (
      'catch' => 
      array (
        'required_categories' => 
        array (
          0 => 'water',
          1 => 'nature',
        ),
        'forbidden_categories' => 
        array (
          0 => 'explicit',
        ),
        'min_confidence' => 0.6,
      ),
      'profile' => 
      array (
        'required_categories' => 
        array (
          0 => 'person',
        ),
        'forbidden_categories' => 
        array (
          0 => 'explicit',
        ),
        'min_confidence' => 0.7,
      ),
      'place' => 
      array (
        'required_categories' => 
        array (
          0 => 'nature',
        ),
        'forbidden_categories' => 
        array (
          0 => 'explicit',
        ),
        'min_confidence' => 0.5,
      ),
    ),
  ),
  'ai_moderation' => 
  array (
    'enabled' => true,
    'features' => 
    array (
      'photo_moderation' => true,
      'comment_moderation' => true,
      'catch_moderation' => true,
      'point_moderation' => true,
      'auto_approve' => false,
      'auto_reject' => false,
      'manual_review' => true,
    ),
    'providers' => 
    array (
      'yandexgpt' => 
      array (
        'enabled' => false,
        'api_key' => NULL,
        'folder_id' => NULL,
        'model' => 'yandexgpt',
        'temperature' => 0.1,
        'max_tokens' => 1000,
        'timeout' => 30,
      ),
      'gigachat' => 
      array (
        'enabled' => false,
        'api_key' => NULL,
        'model' => 'GigaChat',
        'temperature' => 0.1,
        'max_tokens' => 1000,
        'timeout' => 30,
      ),
      'chatgpt' => 
      array (
        'enabled' => false,
        'api_key' => NULL,
        'model' => 'gpt-4',
        'temperature' => 0.1,
        'max_tokens' => 1000,
        'timeout' => 30,
      ),
      'deepseek' => 
      array (
        'enabled' => false,
        'api_key' => NULL,
        'model' => 'deepseek-chat',
        'temperature' => 0.1,
        'max_tokens' => 1000,
        'timeout' => 30,
      ),
    ),
    'default_provider' => 'yandexgpt',
    'rules' => 
    array (
      'photo' => 
      array (
        'max_file_size' => 10485760,
        'allowed_formats' => 
        array (
          0 => 'jpg',
          1 => 'jpeg',
          2 => 'png',
          3 => 'webp',
        ),
        'min_resolution' => 
        array (
          0 => 100,
          1 => 100,
        ),
        'max_resolution' => 
        array (
          0 => 4096,
          1 => 4096,
        ),
        'content_categories' => 
        array (
          0 => 'explicit_content',
          1 => 'violence',
          2 => 'hate_speech',
          3 => 'spam',
          4 => 'inappropriate',
        ),
      ),
      'text' => 
      array (
        'max_length' => 10000,
        'min_length' => 1,
        'content_categories' => 
        array (
          0 => 'hate_speech',
          1 => 'harassment',
          2 => 'spam',
          3 => 'inappropriate',
          4 => 'offensive',
          5 => 'adult_content',
          6 => 'violence',
          7 => 'illegal_activities',
        ),
      ),
    ),
    'prompts' => 
    array (
      'photo_moderation' => 'Analyze this image for inappropriate content. Check for: explicit content, violence, hate speech, spam, or any content that violates community guidelines. Respond with JSON: {"approved": true/false, "confidence": 0.0-1.0, "reason": "explanation", "categories": ["category1", "category2"]}',
      'comment_moderation' => 'Moderate this text comment for inappropriate content. Check for: hate speech, harassment, spam, offensive language, adult content, violence, or illegal activities. Respond with JSON: {"approved": true/false, "confidence": 0.0-1.0, "reason": "explanation", "categories": ["category1", "category2"]}',
      'catch_moderation' => 'Moderate this fishing catch description for inappropriate content. Check for: hate speech, harassment, spam, offensive language, or any content that violates community guidelines. Respond with JSON: {"approved": true/false, "confidence": 0.0-1.0, "reason": "explanation", "categories": ["category1", "category2"]}',
      'point_moderation' => 'Moderate this fishing point description for inappropriate content. Check for: hate speech, harassment, spam, offensive language, or any content that violates community guidelines. Respond with JSON: {"approved": true/false, "confidence": 0.0-1.0, "reason": "explanation", "categories": ["category1", "category2"]}',
    ),
    'thresholds' => 
    array (
      'auto_approve_confidence' => 0.9,
      'auto_reject_confidence' => 0.8,
      'manual_review_confidence' => 0.7,
    ),
    'actions' => 
    array (
      'approved' => 
      array (
        'status' => 'approved',
        'notify_user' => false,
        'log_action' => true,
      ),
      'rejected' => 
      array (
        'status' => 'rejected',
        'notify_user' => true,
        'log_action' => true,
        'hide_content' => true,
      ),
      'pending_review' => 
      array (
        'status' => 'pending_review',
        'notify_user' => true,
        'log_action' => true,
        'hide_content' => false,
      ),
    ),
    'rate_limiting' => 
    array (
      'enabled' => true,
      'max_requests_per_minute' => 60,
      'max_requests_per_hour' => 1000,
      'max_requests_per_day' => 10000,
    ),
    'caching' => 
    array (
      'enabled' => true,
      'ttl' => 3600,
      'store' => 'redis',
    ),
    'logging' => 
    array (
      'enabled' => true,
      'log_level' => 'info',
      'log_failed_requests' => true,
      'log_successful_requests' => false,
    ),
    'notifications' => 
    array (
      'enabled' => true,
      'notify_admins' => true,
      'notify_users' => true,
      'telegram_notifications' => true,
    ),
    'fallback' => 
    array (
      'on_failure' => 'manual_review',
      'retry_attempts' => 3,
      'retry_delay' => 5,
    ),
    'content_types' => 
    array (
      'catch_photos' => 
      array (
        'enabled' => true,
        'provider' => 'yandexgpt',
        'prompt' => 'photo_moderation',
      ),
      'catch_comments' => 
      array (
        'enabled' => true,
        'provider' => 'yandexgpt',
        'prompt' => 'comment_moderation',
      ),
      'catch_descriptions' => 
      array (
        'enabled' => true,
        'provider' => 'yandexgpt',
        'prompt' => 'catch_moderation',
      ),
      'point_descriptions' => 
      array (
        'enabled' => true,
        'provider' => 'yandexgpt',
        'prompt' => 'point_moderation',
      ),
      'point_comments' => 
      array (
        'enabled' => true,
        'provider' => 'yandexgpt',
        'prompt' => 'comment_moderation',
      ),
      'user_bio' => 
      array (
        'enabled' => false,
        'provider' => 'yandexgpt',
        'prompt' => 'comment_moderation',
      ),
    ),
  ),
  'app' => 
  array (
    'name' => 'FishTrackPro',
    'env' => 'production',
    'debug' => false,
    'url' => 'https://api.fishtrackpro.ru',
    'frontend_url' => 'https://www.fishtrackpro.ru',
    'asset_url' => NULL,
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'cipher' => 'AES-256-CBC',
    'key' => 'base64:CpO3pThRgEc9xakfZIPpTuj7rSwv9Tgaw5oiHJZJT44=',
    'previous_keys' => 
    array (
    ),
    'maintenance' => 
    array (
      'driver' => 'file',
    ),
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Concurrency\\ConcurrencyServiceProvider',
      6 => 'Illuminate\\Cookie\\CookieServiceProvider',
      7 => 'Illuminate\\Database\\DatabaseServiceProvider',
      8 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      9 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      10 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      11 => 'Illuminate\\Hashing\\HashServiceProvider',
      12 => 'Illuminate\\Mail\\MailServiceProvider',
      13 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      14 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      15 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      16 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      17 => 'Illuminate\\Queue\\QueueServiceProvider',
      18 => 'Illuminate\\Redis\\RedisServiceProvider',
      19 => 'Illuminate\\Session\\SessionServiceProvider',
      20 => 'Illuminate\\Translation\\TranslationServiceProvider',
      21 => 'Illuminate\\Validation\\ValidationServiceProvider',
      22 => 'Illuminate\\View\\ViewServiceProvider',
      23 => 'SocialiteProviders\\Manager\\ServiceProvider',
      24 => 'App\\Providers\\AppServiceProvider',
      25 => 'App\\Providers\\AuthServiceProvider',
      26 => 'App\\Providers\\EventServiceProvider',
      27 => 'App\\Providers\\RouteServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Arr' => 'Illuminate\\Support\\Arr',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Benchmark' => 'Illuminate\\Support\\Benchmark',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Concurrency' => 'Illuminate\\Support\\Facades\\Concurrency',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Context' => 'Illuminate\\Support\\Facades\\Context',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'Date' => 'Illuminate\\Support\\Facades\\Date',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Http' => 'Illuminate\\Support\\Facades\\Http',
      'Js' => 'Illuminate\\Support\\Js',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Number' => 'Illuminate\\Support\\Number',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Process' => 'Illuminate\\Support\\Facades\\Process',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schedule' => 'Illuminate\\Support\\Facades\\Schedule',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'Str' => 'Illuminate\\Support\\Str',
      'Uri' => 'Illuminate\\Support\\Uri',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Vite' => 'Illuminate\\Support\\Facades\\Vite',
    ),
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'api' => 
      array (
        'driver' => 'jwt',
        'provider' => 'users',
      ),
      'sanctum' => 
      array (
        'driver' => 'sanctum',
        'provider' => NULL,
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\User',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
      ),
    ),
    'password_timeout' => 10800,
  ),
  'cache' => 
  array (
    'default' => 'file',
    'stores' => 
    array (
      'array' => 
      array (
        'driver' => 'array',
        'serialize' => false,
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'cache',
        'lock_connection' => NULL,
        'lock_table' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => '/var/www/ftp/backend/storage/framework/cache/data',
        'lock_path' => '/var/www/ftp/backend/storage/framework/cache/data',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => 'localhost',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
      ),
      'dynamodb' => 
      array (
        'driver' => 'dynamodb',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'table' => 'cache',
        'endpoint' => NULL,
      ),
      'octane' => 
      array (
        'driver' => 'octane',
      ),
    ),
    'prefix' => 'fishtrackpro_cache_',
  ),
  'cors' => 
  array (
    'paths' => 
    array (
      0 => 'api/*',
      1 => 'sanctum/csrf-cookie',
    ),
    'allowed_methods' => 
    array (
      0 => '*',
    ),
    'allowed_origins' => 
    array (
      0 => '*',
    ),
    'allowed_origins_patterns' => 
    array (
    ),
    'allowed_headers' => 
    array (
      0 => '*',
    ),
    'exposed_headers' => 
    array (
    ),
    'max_age' => 0,
    'supports_credentials' => true,
  ),
  'database' => 
  array (
    'default' => 'mysql',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'url' => NULL,
        'database' => 'fishtrackpro',
        'prefix' => '',
        'foreign_key_constraints' => true,
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'url' => NULL,
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'fishtrackpro',
        'username' => 'root',
        'password' => 'FishTrack2024!',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'mariadb' => 
      array (
        'driver' => 'mariadb',
        'url' => NULL,
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'fishtrackpro',
        'username' => 'root',
        'password' => 'FishTrack2024!',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'url' => NULL,
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'fishtrackpro',
        'username' => 'root',
        'password' => 'FishTrack2024!',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode' => 'prefer',
      ),
      'sqlsrv' => 
      array (
        'driver' => 'sqlsrv',
        'url' => NULL,
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'fishtrackpro',
        'username' => 'root',
        'password' => 'FishTrack2024!',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
      ),
    ),
    'migrations' => 'migrations',
    'redis' => 
    array (
      'client' => 'phpredis',
      'options' => 
      array (
        'cluster' => 'redis',
        'prefix' => 'fishtrackpro_database_',
      ),
      'default' => 
      array (
        'url' => NULL,
        'host' => 'localhost',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '0',
      ),
      'cache' => 
      array (
        'url' => NULL,
        'host' => 'localhost',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '1',
      ),
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => '/var/www/ftp/backend/storage/app',
        'throw' => false,
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => '/var/www/ftp/backend/storage/app/public',
        'url' => 'https://api.fishtrackpro.ru/storage',
        'visibility' => 'public',
        'throw' => false,
      ),
      's3' => 
      array (
        'driver' => 's3',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'bucket' => '',
        'url' => NULL,
        'endpoint' => NULL,
        'use_path_style_endpoint' => false,
        'throw' => false,
      ),
    ),
    'links' => 
    array (
      '/var/www/ftp/backend/public/storage' => '/var/www/ftp/backend/storage/app/public',
    ),
  ),
  'jwt' => 
  array (
    'secret' => 'j7PmuczwjzxhSLDGL7CepaeHkuQfZHeYjDkQpiWXs0GRlDOLXMkWoNAryjBCoVO7',
    'keys' => 
    array (
      'public' => NULL,
      'private' => NULL,
      'passphrase' => NULL,
    ),
    'ttl' => '43200',
    'refresh_ttl' => 20160,
    'algo' => 'HS256',
    'required_claims' => 
    array (
      0 => 'iss',
      1 => 'iat',
      2 => 'exp',
      3 => 'nbf',
      4 => 'sub',
      5 => 'jti',
    ),
    'persistent_claims' => 
    array (
    ),
    'lock_subject' => true,
    'leeway' => 0,
    'blacklist_enabled' => true,
    'blacklist_grace_period' => 0,
    'decrypt_cookies' => false,
    'providers' => 
    array (
      'jwt' => 'Tymon\\JWTAuth\\Providers\\JWT\\Lcobucci',
      'auth' => 'Tymon\\JWTAuth\\Providers\\Auth\\Illuminate',
      'storage' => 'Tymon\\JWTAuth\\Providers\\Storage\\Illuminate',
    ),
  ),
  'l5-swagger' => 
  array (
    'default' => 'default',
    'documentations' => 
    array (
      'default' => 
      array (
        'api' => 
        array (
          'title' => 'FishTrackPro API Documentation',
          'description' => 'API для приложения FishTrackPro - социальной сети для рыболовов',
          'version' => '1.0.0',
          'contact' => 
          array (
            'name' => 'FishTrackPro Support',
            'email' => 'support@fishtrackpro.ru',
            'url' => 'https://fishtrackpro.ru',
          ),
          'license' => 
          array (
            'name' => 'MIT',
            'url' => 'https://opensource.org/licenses/MIT',
          ),
        ),
        'routes' => 
        array (
          'api' => 'api/documentation',
        ),
        'paths' => 
        array (
          'use_absolute_path' => true,
          'swagger_ui_assets_path' => 'vendor/swagger-api/swagger-ui/dist/',
          'docs_json' => 'api-docs.json',
          'docs_yaml' => 'api-docs.yaml',
          'format_to_use_for_docs' => 'json',
          'annotations' => 
          array (
            0 => '/var/www/ftp/backend/app',
          ),
        ),
      ),
    ),
    'defaults' => 
    array (
      'routes' => 
      array (
        'docs' => 'docs',
        'oauth2_callback' => 'api/oauth2-callback',
        'middleware' => 
        array (
          'api' => 
          array (
          ),
          'asset' => 
          array (
          ),
          'docs' => 
          array (
          ),
          'oauth2_callback' => 
          array (
          ),
        ),
        'group_options' => 
        array (
        ),
      ),
      'paths' => 
      array (
        'docs' => '/var/www/ftp/backend/storage/api-docs',
        'views' => '/var/www/ftp/backend/resources/views/vendor/l5-swagger',
        'base' => NULL,
        'excludes' => 
        array (
        ),
      ),
      'scanOptions' => 
      array (
        'default_processors_configuration' => 
        array (
        ),
        'analyser' => NULL,
        'analysis' => NULL,
        'processors' => 
        array (
        ),
        'pattern' => NULL,
        'exclude' => 
        array (
        ),
        'open_api_spec_version' => '3.0.0',
      ),
      'securityDefinitions' => 
      array (
        'securitySchemes' => 
        array (
          'jwt' => 
          array (
            'type' => 'http',
            'description' => 'JWT Bearer token для аутентификации',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
          ),
        ),
        'security' => 
        array (
          0 => 
          array (
            'jwt' => 
            array (
            ),
          ),
        ),
      ),
      'generate_always' => true,
      'generate_yaml_copy' => false,
      'proxy' => false,
      'additional_config_url' => NULL,
      'operations_sort' => NULL,
      'validator_url' => NULL,
      'ui' => 
      array (
        'display' => 
        array (
          'dark_mode' => false,
          'doc_expansion' => 'none',
          'filter' => true,
        ),
        'authorization' => 
        array (
          'persist_authorization' => false,
          'oauth2' => 
          array (
            'use_pkce_with_authorization_code_grant' => false,
          ),
        ),
      ),
      'constants' => 
      array (
        'L5_SWAGGER_CONST_HOST' => 'http://my-default-host.com',
      ),
    ),
  ),
  'languages' => 
  array (
    'supported' => 
    array (
      'en' => 
      array (
        'name' => 'English',
        'native_name' => 'English',
        'flag' => '🇺🇸',
        'rtl' => false,
        'enabled' => true,
      ),
      'zh' => 
      array (
        'name' => 'Chinese (Simplified)',
        'native_name' => '中文',
        'flag' => '🇨🇳',
        'rtl' => false,
        'enabled' => true,
      ),
      'hi' => 
      array (
        'name' => 'Hindi',
        'native_name' => 'हिन्दी',
        'flag' => '🇮🇳',
        'rtl' => false,
        'enabled' => true,
      ),
      'es' => 
      array (
        'name' => 'Spanish',
        'native_name' => 'Español',
        'flag' => '🇪🇸',
        'rtl' => false,
        'enabled' => true,
      ),
      'fr' => 
      array (
        'name' => 'French',
        'native_name' => 'Français',
        'flag' => '🇫🇷',
        'rtl' => false,
        'enabled' => true,
      ),
      'ar' => 
      array (
        'name' => 'Arabic',
        'native_name' => 'العربية',
        'flag' => '🇸🇦',
        'rtl' => true,
        'enabled' => true,
      ),
      'bn' => 
      array (
        'name' => 'Bengali',
        'native_name' => 'বাংলা',
        'flag' => '🇧🇩',
        'rtl' => false,
        'enabled' => true,
      ),
      'pt' => 
      array (
        'name' => 'Portuguese',
        'native_name' => 'Português',
        'flag' => '🇵🇹',
        'rtl' => false,
        'enabled' => true,
      ),
      'ru' => 
      array (
        'name' => 'Russian',
        'native_name' => 'Русский',
        'flag' => '🇷🇺',
        'rtl' => false,
        'enabled' => true,
      ),
      'ja' => 
      array (
        'name' => 'Japanese',
        'native_name' => '日本語',
        'flag' => '🇯🇵',
        'rtl' => false,
        'enabled' => true,
      ),
      'de' => 
      array (
        'name' => 'German',
        'native_name' => 'Deutsch',
        'flag' => '🇩🇪',
        'rtl' => false,
        'enabled' => true,
      ),
      'ko' => 
      array (
        'name' => 'Korean',
        'native_name' => '한국어',
        'flag' => '🇰🇷',
        'rtl' => false,
        'enabled' => true,
      ),
      'tr' => 
      array (
        'name' => 'Turkish',
        'native_name' => 'Türkçe',
        'flag' => '🇹🇷',
        'rtl' => false,
        'enabled' => true,
      ),
      'vi' => 
      array (
        'name' => 'Vietnamese',
        'native_name' => 'Tiếng Việt',
        'flag' => '🇻🇳',
        'rtl' => false,
        'enabled' => true,
      ),
      'it' => 
      array (
        'name' => 'Italian',
        'native_name' => 'Italiano',
        'flag' => '🇮🇹',
        'rtl' => false,
        'enabled' => true,
      ),
    ),
    'default' => 'en',
    'fallback' => 'en',
    'auto_detect' => true,
    'detection_sources' => 
    array (
      0 => 'user_preference',
      1 => 'url_parameter',
      2 => 'session',
      3 => 'browser_header',
      4 => 'default',
    ),
    'storage' => 
    array (
      'user_preference' => true,
      'session' => true,
      'cookie' => true,
    ),
    'rtl_support' => true,
    'switcher' => 
    array (
      'show_flags' => true,
      'show_native_names' => true,
      'show_english_names' => false,
      'group_by_region' => false,
    ),
    'translation' => 
    array (
      'auto_generate' => false,
      'fallback_to_key' => true,
      'cache_translations' => true,
      'cache_ttl' => 3600,
    ),
    'regions' => 
    array (
      'europe' => 
      array (
        0 => 'en',
        1 => 'es',
        2 => 'fr',
        3 => 'de',
        4 => 'ru',
        5 => 'it',
        6 => 'pt',
      ),
      'asia' => 
      array (
        0 => 'zh',
        1 => 'hi',
        2 => 'ja',
        3 => 'ko',
        4 => 'bn',
        5 => 'vi',
      ),
      'middle_east' => 
      array (
        0 => 'ar',
        1 => 'tr',
      ),
      'americas' => 
      array (
        0 => 'en',
        1 => 'es',
        2 => 'pt',
      ),
    ),
    'browser_mapping' => 
    array (
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
    ),
  ),
  'logging' => 
  array (
    'default' => 'stack',
    'deprecations' => 
    array (
      'channel' => NULL,
      'trace' => false,
    ),
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'single',
        ),
        'ignore_exceptions' => false,
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => '/var/www/ftp/backend/storage/logs/laravel.log',
        'level' => 'debug',
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => '/var/www/ftp/backend/storage/logs/laravel.log',
        'level' => 'debug',
        'days' => 14,
      ),
      'slack' => 
      array (
        'driver' => 'slack',
        'url' => NULL,
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'debug',
      ),
      'papertrail' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\SyslogUdpHandler',
        'handler_with' => 
        array (
          'host' => NULL,
          'port' => NULL,
        ),
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'formatter' => NULL,
        'with' => 
        array (
          'stream' => 'php://stderr',
        ),
      ),
      'syslog' => 
      array (
        'driver' => 'syslog',
        'level' => 'debug',
      ),
      'errorlog' => 
      array (
        'driver' => 'errorlog',
        'level' => 'debug',
      ),
      'null' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\NullHandler',
      ),
      'emergency' => 
      array (
        'path' => '/var/www/ftp/backend/storage/logs/laravel.log',
      ),
    ),
  ),
  'media' => 
  array (
    'limits' => 
    array (
      'user' => 
      array (
        'max_photos' => 10,
        'max_videos' => 0,
        'max_media_total' => 10,
        'video_enabled' => false,
      ),
      'pro' => 
      array (
        'max_photos' => 20,
        'max_videos' => 5,
        'max_media_total' => 25,
        'video_enabled' => true,
      ),
      'premium' => 
      array (
        'max_photos' => 20,
        'max_videos' => 5,
        'max_media_total' => 25,
        'video_enabled' => true,
      ),
      'admin' => 
      array (
        'max_photos' => 50,
        'max_videos' => 10,
        'max_media_total' => 60,
        'video_enabled' => true,
      ),
    ),
    'file_types' => 
    array (
      'photos' => 
      array (
        0 => 'jpg',
        1 => 'jpeg',
        2 => 'png',
        3 => 'webp',
      ),
      'videos' => 
      array (
        0 => 'mp4',
        1 => 'mov',
        2 => 'avi',
        3 => 'webm',
      ),
    ),
    'max_file_size' => 
    array (
      'photo' => 10485760,
      'video' => 104857600,
    ),
    'storage' => 
    array (
      'disk' => 'public',
      'path' => 
      array (
        'photos' => 'catches/photos',
        'videos' => 'catches/videos',
      ),
    ),
    'thumbnails' => 
    array (
      'enabled' => true,
      'sizes' => 
      array (
        'small' => 
        array (
          0 => 150,
          1 => 150,
        ),
        'medium' => 
        array (
          0 => 400,
          1 => 400,
        ),
        'large' => 
        array (
          0 => 800,
          1 => 800,
        ),
      ),
    ),
    'video_processing' => 
    array (
      'enabled' => true,
      'generate_thumbnails' => true,
      'thumbnail_times' => 
      array (
        0 => 1,
        1 => 5,
        2 => 10,
      ),
    ),
  ),
  'services' => 
  array (
    'postmark' => 
    array (
      'token' => NULL,
    ),
    'resend' => 
    array (
      'key' => NULL,
    ),
    'ses' => 
    array (
      'key' => '',
      'secret' => '',
      'region' => 'us-east-1',
    ),
    'slack' => 
    array (
      'notifications' => 
      array (
        'bot_user_oauth_token' => NULL,
        'channel' => NULL,
      ),
    ),
    'google' => 
    array (
      'client_id' => '834068999091-ab2p29q5oo5a1m7bfk6tgm40nv6h97qr.apps.googleusercontent.com',
      'client_secret' => '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',
      'redirect' => 'https://api.fishtrackpro.ru/auth/google/callback',
    ),
    'yandex' => 
    array (
      'client_id' => 'c95ab933113241c28307546afd523640',
      'client_secret' => 'd9fd27ef9218461ea58ee77129deaa85',
      'redirect' => 'https://api.fishtrackpro.ru/auth/yandex/callback',
    ),
    'telegram' => 
    array (
      'bot_token' => '8280208098:AAGD4yxyauYY4Q5BBqa-95MyMDDDkjpCdos',
      'bot_username' => 'fishtrackpro_bot',
      'redirect' => 'https://api.fishtrackpro.ru/auth/telegram/callback',
    ),
  ),
  'session' => 
  array (
    'driver' => 'file',
    'lifetime' => '120',
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => '/var/www/ftp/backend/storage/framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'fishtrackpro_session',
    'path' => '/',
    'domain' => NULL,
    'secure' => NULL,
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
  ),
  'subscription' => 
  array (
    'pro' => 
    array (
      'price_rub' => 199,
      'price_bonus' => 1990,
      'duration_days' => 30,
      'features' => 
      array (
        'unlimited_catches' => true,
        'advanced_statistics' => true,
        'priority_support' => true,
        'ad_free' => true,
      ),
    ),
    'premium' => 
    array (
      'price_rub' => 499,
      'price_bonus' => 4990,
      'duration_days' => 30,
      'crown_icon_url' => 'https://cdn.fishtrackpro.ru/icons/crown.svg',
      'features' => 
      array (
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
      ),
    ),
    'payment_methods' => 
    array (
      'yandex_pay' => 
      array (
        'name' => 'Яндекс.Платежи',
        'enabled' => true,
        'icon' => 'https://cdn.fishtrackpro.ru/icons/yandex-pay.svg',
      ),
      'sber_pay' => 
      array (
        'name' => 'Сбербанк',
        'enabled' => true,
        'icon' => 'https://cdn.fishtrackpro.ru/icons/sber-pay.svg',
      ),
      'apple_pay' => 
      array (
        'name' => 'Apple Pay',
        'enabled' => true,
        'icon' => 'https://cdn.fishtrackpro.ru/icons/apple-pay.svg',
      ),
      'google_pay' => 
      array (
        'name' => 'Google Pay',
        'enabled' => true,
        'icon' => 'https://cdn.fishtrackpro.ru/icons/google-pay.svg',
      ),
      'bonuses' => 
      array (
        'name' => 'Бонусы',
        'enabled' => true,
        'icon' => 'https://cdn.fishtrackpro.ru/icons/bonus.svg',
      ),
    ),
    'bonus_system' => 
    array (
      'earn_per_catch' => 10,
      'earn_per_like' => 1,
      'earn_per_comment' => 2,
      'earn_per_share' => 5,
      'earn_per_daily_login' => 5,
      'max_daily_earnings' => 100,
    ),
    'trial' => 
    array (
      'enabled' => true,
      'duration_days' => 7,
      'types' => 
      array (
        0 => 'pro',
        1 => 'premium',
      ),
    ),
    'auto_renewal' => 
    array (
      'enabled' => true,
      'reminder_days' => 
      array (
        0 => 7,
        1 => 3,
        2 => 1,
      ),
    ),
    'refund' => 
    array (
      'enabled' => true,
      'max_days' => 14,
      'partial_refund' => true,
    ),
  ),
  'telegram' => 
  array (
    'bot_token' => '8280208098:AAGD4yxyauYY4Q5BBqa-95MyMDDDkjpCdos',
    'chat_id' => '98658233',
    'admin_chat_id' => '98658233',
    'features' => 
    array (
      'deployment_notifications' => true,
      'user_registration_notifications' => true,
      'catch_notifications' => true,
      'payment_notifications' => true,
      'point_notifications' => true,
      'daily_statistics' => true,
      'error_notifications' => true,
      'bonus_notifications' => true,
    ),
    'notifications' => 
    array (
      'deployment' => 
      array (
        'enabled' => true,
        'template' => '🚀 *Deployment Update*

Repository: {repository}
Branch: {branch}
Commit: {commit_message}
Author: {author}
Status: {status}
Time: {time}',
      ),
      'user_registration' => 
      array (
        'enabled' => true,
        'template' => '👤 *New User Registration*

Name: {name}
Username: {username}
Email: {email}
Role: {role}
Time: {time}',
      ),
      'catch' => 
      array (
        'enabled' => true,
        'template' => '🎣 <b>New Catch Recorded</b>

User: {user_name} (@{username})
Fish: {fish_type}
Weight: {weight} kg
Length: {length} cm
Location: {location}
Time: {time}',
      ),
      'payment' => 
      array (
        'enabled' => true,
        'template' => '💳 *New Payment*

User: {user_name} (@{username})
Amount: {amount} {currency}
Type: {payment_type}
Status: {status}
Time: {time}',
      ),
      'point' => 
      array (
        'enabled' => true,
        'template' => '📍 *New Fishing Point*

User: {user_name} (@{username})
Title: {title}
Location: {location}
Privacy: {privacy}
Time: {time}',
      ),
      'error' => 
      array (
        'enabled' => true,
        'template' => '❌ *Application Error*

Error: {error_message}
File: {file}:{line}
User: {user_info}
Time: {time}',
      ),
      'bonus' => 
      array (
        'enabled' => true,
        'template' => '🎁 *Бонус начислен!*

Пользователь: {user_name} (@{username})
Действие: {action}
Бонус: +{amount} 🪙
Баланс: {balance} 🪙
Время: {time}',
      ),
    ),
    'daily_stats_template' => '📊 *Daily Statistics - {date}*

👥 *Users:*
• New registrations: {new_users}
• Total users: {total_users}
• Active users: {active_users}

🎣 *Catches:*
• New catches: {new_catches}
• Total catches: {total_catches}
• Total weight: {total_weight} kg

📍 *Points:*
• New points: {new_points}
• Total points: {total_points}

💳 *Payments:*
• New payments: {new_payments}
• Total revenue: {total_revenue} {currency}

📈 *Growth:*
• Users growth: {users_growth}%
• Catches growth: {catches_growth}%
• Revenue growth: {revenue_growth}%',
    'commands' => 
    array (
      'start' => 'Start the bot and show welcome message',
      'help' => 'Show available commands',
      'stats' => 'Get current statistics',
      'users' => 'Get user statistics',
      'catches' => 'Get catch statistics',
      'payments' => 'Get payment statistics',
      'points' => 'Get fishing points statistics',
      'status' => 'Get application status',
    ),
    'rate_limiting' => 
    array (
      'enabled' => true,
      'max_notifications_per_minute' => 10,
      'max_notifications_per_hour' => 100,
    ),
    'webhook' => 
    array (
      'enabled' => true,
      'url' => 'https://api.fishtrackpro.ru/api/telegram/webhook',
      'secret_token' => '',
    ),
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => '/var/www/ftp/backend/resources/views',
    ),
    'compiled' => '/var/www/ftp/backend/storage/framework/views',
  ),
  'sanctum' => 
  array (
    'stateful' => 
    array (
      0 => 'localhost',
      1 => 'localhost:3000',
      2 => '127.0.0.1',
      3 => '127.0.0.1:8000',
      4 => '::1',
      5 => 'api.fishtrackpro.ru',
    ),
    'guard' => 
    array (
      0 => 'web',
    ),
    'expiration' => NULL,
    'token_prefix' => '',
    'middleware' => 
    array (
      'authenticate_session' => 'Laravel\\Sanctum\\Http\\Middleware\\AuthenticateSession',
      'encrypt_cookies' => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
      'validate_csrf_token' => 'Illuminate\\Foundation\\Http\\Middleware\\ValidateCsrfToken',
    ),
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'alias' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
  ),
);
