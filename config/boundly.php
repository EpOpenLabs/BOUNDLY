<?php

return [
    /*
    |--------------------------------------------------------------------------
    | BOUNDLY Infrastructure Configuration
    |--------------------------------------------------------------------------
    |
    | This file allows you to configure the behavior of the metadata engine
    | for your Domain and Infrastructure layers.
    |
    */

    'locale' => env('BOUNDLY_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Discovery Paths
    |--------------------------------------------------------------------------
    |
    | Paths where the engine will scan for Entities, Traits, and Actions.
    */
    'paths' => [
        'domain' => base_path('Domain'),
        'application' => base_path('Application'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Prefix
    |--------------------------------------------------------------------------
    */
    'api_prefix' => 'api',

    /*
    |--------------------------------------------------------------------------
    | Health Checks
    |--------------------------------------------------------------------------
    |
    | Configure which services to check and health check behavior.
    |
    */
    'health' => [
        'enabled' => true,
        'timeout' => 5,
        'services' => [
            'database' => true,
            'cache' => true,
            'queue' => true,
            'storage' => true,
        ],
        'custom' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'version' => '1.0.0',
        'channel' => 'single',
        'request_logger' => [
            'enabled' => true,
            'channel' => 'single',
            'exclude_paths' => ['health', 'up'],
        ],
        'audit' => [
            'enabled' => true,
            'channel' => 'single',
            'events' => ['created', 'updated', 'deleted', 'accessed'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Timeouts
    |--------------------------------------------------------------------------
    |
    | Configure query timeouts for different database operations.
    | Timeouts are in seconds.
    |
    */
    'database_timeouts' => [
        'default' => 30,
        'operations' => [
            'select' => 30,
            'insert' => 30,
            'update' => 30,
            'delete' => 30,
            'bulk' => 60,
            'migration' => 300,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'response' => [
            'enabled' => false,
            'store' => 'file',
            'ttl' => 60,
            'exclude_paths' => ['api/health', 'api/*/health'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Access Control
    |--------------------------------------------------------------------------
    */
    'ip_access' => [
        'enabled' => false,
        'default_action' => 'deny',
        'whitelist' => [],
        'blacklist' => [],
        'cache_store' => 'file',
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Signing (HMAC)
    |--------------------------------------------------------------------------
    */
    'security' => [
        'request_signing' => [
            'enabled' => false,
            'algorithm' => 'sha256',
            'secret_key' => env('REQUEST_SIGNING_SECRET', ''),
            'timestamp_tolerance' => 300,
        ],
        'tier_throttling' => [
            'enabled' => true,
            'cache_store' => 'file',
            'tiers' => [
                'free' => [
                    'requests_per_minute' => 60,
                    'requests_per_hour' => 1000,
                    'requests_per_day' => 10000,
                ],
                'basic' => [
                    'requests_per_minute' => 300,
                    'requests_per_hour' => 5000,
                    'requests_per_day' => 50000,
                ],
                'pro' => [
                    'requests_per_minute' => 1000,
                    'requests_per_hour' => 20000,
                    'requests_per_day' => 200000,
                ],
                'enterprise' => [
                    'requests_per_minute' => 5000,
                    'requests_per_hour' => 100000,
                    'requests_per_day' => 1000000,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring (Hooks)
    |--------------------------------------------------------------------------
    |
    | Configure external monitoring integrations.
    | BOUNDLY provides hooks - you connect your preferred provider.
    | Supported: 'sentry', 'bugsnag', 'grafana', null
    |
    */
    'monitoring' => [
        'enabled' => false,
        'provider' => null,
        'api_key' => env('MONITORING_API_KEY'),
    ],
];
