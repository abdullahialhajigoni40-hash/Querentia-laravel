<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Services Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI service providers used in the application.
    | API keys should be stored in your .env file.
    |
    */

    'deepseek' => [
        'key' => env('DEEPSEEK_API_KEY'),
        'endpoint' => env('DEEPSEEK_ENDPOINT', 'https://api.deepseek.com/v1/chat/completions'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
        'max_tokens' => env('DEEPSEEK_MAX_TOKENS', 4000),
        'temperature' => env('DEEPSEEK_TEMPERATURE', 0.7),
        'timeout' => env('DEEPSEEK_TIMEOUT', 120),
        'retry_attempts' => env('DEEPSEEK_RETRY_ATTEMPTS', 3),
        'cost_per_1k_tokens' => env('DEEPSEEK_COST_PER_1K_TOKENS', 0.0014),
        'streaming' => env('DEEPSEEK_STREAMING', true),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'endpoint' => env('OPENAI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
        'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 4000),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
        'timeout' => env('OPENAI_TIMEOUT', 120),
        'retry_attempts' => env('OPENAI_RETRY_ATTEMPTS', 3),
        'cost_per_1k_tokens' => env('OPENAI_COST_PER_1K_TOKENS', 0.03),
        'streaming' => env('OPENAI_STREAMING', true),
    ],

    'gemini' => [
        'key' => env('GOOGLE_GEMINI_API_KEY'),
        'endpoint' => env('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent'),
        'model' => env('GEMINI_MODEL', 'gemini-pro'),
        'max_tokens' => env('GEMINI_MAX_TOKENS', 4000),
        'temperature' => env('GEMINI_TEMPERATURE', 0.7),
        'timeout' => env('GEMINI_TIMEOUT', 120),
        'retry_attempts' => env('GEMINI_RETRY_ATTEMPTS', 3),
        'cost_per_1k_tokens' => env('GEMINI_COST_PER_1K_TOKENS', 0.00125),
        'streaming' => env('GEMINI_STREAMING', true),
    ],

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'endpoint' => env('ANTHROPIC_ENDPOINT', 'https://api.anthropic.com/v1/messages'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-opus-20240229'),
        'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 4000),
        'temperature' => env('ANTHROPIC_TEMPERATURE', 0.7),
        'timeout' => env('ANTHROPIC_TIMEOUT', 120),
        'retry_attempts' => env('ANTHROPIC_RETRY_ATTEMPTS', 3),
        'cost_per_1k_tokens' => env('ANTHROPIC_COST_PER_1K_TOKENS', 0.015),
        'streaming' => env('ANTHROPIC_STREAMING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Services
    |--------------------------------------------------------------------------
    */

    'paystack' => [
        'key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret' => env('PAYSTACK_SECRET_KEY'),
        'payment_url' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
        'callback_url' => env('PAYSTACK_CALLBACK_URL'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Storage Services
    |--------------------------------------------------------------------------
    */

    'aws' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    ],

    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
        'secure' => env('CLOUDINARY_SECURE', true),
    ],

    'cloudflare' => [
        'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
        'api_token' => env('CLOUDFLARE_API_TOKEN'),
        'bucket' => env('CLOUDFLARE_BUCKET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics & Monitoring Services
    |--------------------------------------------------------------------------
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'analytics' => [
            'tracking_id' => env('GOOGLE_ANALYTICS_TRACKING_ID'),
            'view_id' => env('GOOGLE_ANALYTICS_VIEW_ID'),
        ],
    ],

    'sentry' => [
        'dsn' => env('SENTRY_LARAVEL_DSN'),
        'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 1.0),
        'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE', 1.0),
    ],

    'logrocket' => [
        'app_id' => env('LOGROCKET_APP_ID'),
    ],

    'mixpanel' => [
        'token' => env('MIXPANEL_TOKEN'),
        'enable' => env('MIXPANEL_ENABLE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Authentication Services
    |--------------------------------------------------------------------------
    */

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('TWITTER_REDIRECT_URI'),
        'oauth' => [
            1 => env('TWITTER_OAUTH_1_CLIENT_ID'),
            2 => env('TWITTER_OAUTH_2_CLIENT_ID'),
        ],
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => env('LINKEDIN_REDIRECT_URI'),
    ],

    'google-oauth' => [
        'client_id' => env('GOOGLE_OAUTH_CLIENT_ID'),
        'client_secret' => env('GOOGLE_OAUTH_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_OAUTH_REDIRECT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Services
    |--------------------------------------------------------------------------
    */

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
    ],

    'mailjet' => [
        'key' => env('MAILJET_APIKEY'),
        'secret' => env('MAILJET_APISECRET'),
        'transactional' => [
            'call' => true,
            'options' => [
                'url' => 'api.mailjet.com',
                'version' => 'v3.1',
                'call' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Services
    |--------------------------------------------------------------------------
    */

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
        'verify_sid' => env('TWILIO_VERIFY_SID'),
    ],

    'termii' => [
        'api_key' => env('TERMII_API_KEY'),
        'sender_id' => env('TERMII_SENDER_ID'),
        'channel' => env('TERMII_CHANNEL', 'generic'),
    ],

    'africastalking' => [
        'username' => env('AFRICASTALKING_USERNAME'),
        'api_key' => env('AFRICASTALKING_API_KEY'),
        'from' => env('AFRICASTALKING_FROM'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache & Queue Services
    |--------------------------------------------------------------------------
    */

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', 'laravel_database_'),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
        'queue' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_QUEUE_DB', '2'),
        ],
        'session' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_SESSION_DB', '3'),
        ],
    ],

    'horizon' => [
        'domain' => env('HORIZON_DOMAIN'),
        'path' => env('HORIZON_PATH', 'horizon'),
        'use' => 'default',
        'prefix' => env('HORIZON_PREFIX', 'horizon:'),
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Services
    |--------------------------------------------------------------------------
    */

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID'),
        'secret' => env('ALGOLIA_SECRET'),
    ],

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            'users' => [
                'filterableAttributes' => ['id', 'name', 'email'],
                'sortableAttributes' => ['name', 'created_at'],
            ],
            'journals' => [
                'filterableAttributes' => ['id', 'title', 'area_of_study', 'status', 'user_id'],
                'sortableAttributes' => ['title', 'created_at', 'updated_at'],
                'searchableAttributes' => ['title', 'abstract', 'area_of_study', 'content'],
            ],
        ],
    ],

    'typesense' => [
        'api_key' => env('TYPESENSE_API_KEY'),
        'nodes' => [
            [
                'host' => env('TYPESENSE_HOST', 'localhost'),
                'port' => env('TYPESENSE_PORT', 8108),
                'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
            ],
        ],
        'nearest_node' => [
            'host' => env('TYPESENSE_HOST', 'localhost'),
            'port' => env('TYPESENSE_PORT', 8108),
            'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
        ],
        'connection_timeout_seconds' => 2,
        'healthcheck_interval_seconds' => 30,
        'num_retries' => 3,
        'retry_interval_seconds' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | CDN & Asset Services
    |--------------------------------------------------------------------------
    */

    'bunny' => [
        'api_key' => env('BUNNY_API_KEY'),
        'storage_zone' => env('BUNNY_STORAGE_ZONE'),
        'cdn_hostname' => env('BUNNY_CDN_HOSTNAME'),
        'pull_zone' => env('BUNNY_PULL_ZONE'),
    ],

    'cloudflare-cdn' => [
        'api_key' => env('CLOUDFLARE_CDN_API_KEY'),
        'email' => env('CLOUDFLARE_CDN_EMAIL'),
        'account_id' => env('CLOUDFLARE_CDN_ACCOUNT_ID'),
        'zone_id' => env('CLOUDFLARE_CDN_ZONE_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Services
    |--------------------------------------------------------------------------
    */

    'fcm' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'private_key_id' => env('FIREBASE_PRIVATE_KEY_ID'),
        'private_key' => env('FIREBASE_PRIVATE_KEY'),
        'client_email' => env('FIREBASE_CLIENT_EMAIL'),
        'client_id' => env('FIREBASE_CLIENT_ID'),
        'client_x509_cert_url' => env('FIREBASE_CLIENT_X509_CERT_URL'),
    ],

    'apn' => [
        'key_id' => env('APN_KEY_ID'),
        'team_id' => env('APN_TEAM_ID'),
        'app_bundle_id' => env('APN_APP_BUNDLE_ID'),
        'private_key_content' => env('APN_PRIVATE_KEY_CONTENT'),
        'production' => env('APN_PRODUCTION', false),
    ],

    'pusher' => [
        'beams_instance_id' => env('PUSHER_BEAMS_INSTANCE_ID'),
        'beams_secret_key' => env('PUSHER_BEAMS_SECRET_KEY'),
    ],

    'one_signal' => [
        'app_id' => env('ONE_SIGNAL_APP_ID'),
        'rest_api_key' => env('ONE_SIGNAL_REST_API_KEY'),
        'user_auth_key' => env('ONE_SIGNAL_USER_AUTH_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development & Debugging Services
    |--------------------------------------------------------------------------
    */

    'telescope' => [
        'domain' => env('TELESCOPE_DOMAIN'),
        'path' => env('TELESCOPE_PATH', 'telescope'),
        'driver' => env('TELESCOPE_DRIVER', 'database'),
        'storage' => [
            'database' => [
                'connection' => env('TELESCOPE_DB_CONNECTION', 'mysql'),
                'chunk' => 1000,
            ],
        ],
        'enabled' => env('TELESCOPE_ENABLED', true),
    ],

    'clockwork' => [
        'enable' => env('CLOCKWORK_ENABLE', false),
        'web' => env('CLOCKWORK_WEB', true),
        'api' => env('CLOCKWORK_API', '/__clockwork/'),
        'storage' => env('CLOCKWORK_STORAGE', 'files'),
        'storage_files_path' => env('CLOCKWORK_STORAGE_FILES_PATH', storage_path('clockwork')),
        'storage_files_compress' => env('CLOCKWORK_STORAGE_FILES_COMPRESS', false),
        'storage_sql_database' => env('CLOCKWORK_STORAGE_SQL_DATABASE', storage_path('clockwork.sqlite')),
        'authenticate' => env('CLOCKWORK_AUTHENTICATE', false),
    ],

    'debugbar' => [
        'enabled' => env('DEBUGBAR_ENABLED', false),
        'storage' => [
            'enabled'    => true,
            'driver'     => 'file',
            'path'       => storage_path('debugbar'),
            'connection' => null,
            'provider'   => '',
        ],
    ],

];