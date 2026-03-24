<?php

return [
    /*
    |--------------------------------------------------------------------------
    | BOUNDLY Framework Configuration
    |--------------------------------------------------------------------------
    */

    'locale' => env('BOUNDLY_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Discovery Paths
    |--------------------------------------------------------------------------
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
    'api_prefix' => env('BOUNDLY_API_PREFIX', 'api'),

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    | Set to true to force scanning even if a cache file exists.
    | Useful during development (or set APP_ENV=local).
    */
    'disable_cache' => env('BOUNDLY_DISABLE_CACHE', app()->environment('local')),

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    | Default Laravel auth guard used by the ResourceAuthorize middleware
    | when an entity declares #[Authorize] without an explicit guard.
    */
    'auth' => [
        'default_guard' => env('BOUNDLY_AUTH_GUARD', 'sanctum'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'default_per_page' => env('BOUNDLY_PER_PAGE', 15),
        'max_per_page' => env('BOUNDLY_MAX_PER_PAGE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    | Global rate limiting configuration for the API.
    | Can be overridden per-entity using #[RateLimit] attribute.
    */
    'rate_limit' => [
        'enabled' => env('BOUNDLY_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('BOUNDLY_RATE_LIMIT_MAX_ATTEMPTS', 60),
        'decay_minutes' => env('BOUNDLY_RATE_LIMIT_DECAY_MINUTES', 1),
        'prefix' => env('BOUNDLY_RATE_LIMIT_PREFIX', 'api'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    | HTTP security headers to protect against common vulnerabilities.
    | All headers are enabled by default with secure defaults.
    */
    'security' => [
        'enabled' => env('BOUNDLY_SECURITY_ENABLED', true),
        'force_https' => env('BOUNDLY_SECURITY_FORCE_HTTPS', false),

        'headers' => [
            'x_frame_options' => true,
            'x_content_type_options' => true,
            'x_xss_protection' => true,
            'strict_transport_security' => true,
            'referrer_policy' => true,
            'permissions_policy' => true,
            'content_security_policy' => false,
        ],

        'hsts' => [
            'max_age' => 31536000,
            'include_subdomains' => true,
            'preload' => false,
        ],

        'referrer_policy' => 'strict-origin-when-cross-origin',

        'csp' => [
            'allow_inline_styles' => false,
            'allowed_domains' => [],
        ],

        'max_request_size' => env('BOUNDLY_MAX_REQUEST_SIZE', '1M'),
        'max_upload_size' => env('BOUNDLY_MAX_UPLOAD_SIZE', '10M'),
    ],

    /*
    |--------------------------------------------------------------------------
    | CORS (Cross-Origin Resource Sharing)
    |--------------------------------------------------------------------------
    | Configure which origins can access your API from browsers.
    | WARNING: Never use '*' in production with credentials.
    */
    'cors' => [
        'enabled' => env('BOUNDLY_CORS_ENABLED', false),
        'allowed_origins' => ['*'],
        'allowed_origins_patterns' => [],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-Api-Key'],
        'exposed_headers' => ['X-RateLimit-Limit', 'X-RateLimit-Remaining', 'X-Total-Count'],
        'max_age' => 3600,
        'supports_credentials' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Sanitization
    |--------------------------------------------------------------------------
    | Automatic sanitization of user input to prevent XSS and injection.
    */
    'sanitization' => [
        'enabled' => env('BOUNDLY_SANITIZATION_ENABLED', false),
        'strip_html' => true,
        'strip_scripts' => true,
        'escape_sql_wildcards' => true,
        'allowed_tags' => '<b><i><p><br><ul><ol><li><strong><em><u>',
    ],

    /*
    |--------------------------------------------------------------------------
    | Brute Force Protection
    |--------------------------------------------------------------------------
    | Protects against brute force attacks on authentication endpoints.
    */
    'brute_force' => [
        'enabled' => env('BOUNDLY_BRUTE_FORCE_ENABLED', true),
        'max_attempts' => env('BOUNDLY_BRUTE_FORCE_MAX_ATTEMPTS', 5),
        'decay_minutes' => env('BOUNDLY_BRUTE_FORCE_DECAY_MINUTES', 15),
        'lockout_multiplier' => 2,
        'max_lockouts' => 3,
        'track_by' => 'email',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Logging
    |--------------------------------------------------------------------------
    | Configure security event logging for audit trails and threat detection.
    | Logs are written to the specified channel with structured context.
    */
    'security_logging' => [
        'enabled' => env('BOUNDLY_SECURITY_LOGGING_ENABLED', true),
        'channel' => env('BOUNDLY_SECURITY_LOGGING_CHANNEL', 'single'),
        'excluded_events' => [],
        'log_auth_success' => env('BOUNDLY_LOG_AUTH_SUCCESS', false),
        'log_suspicious_input' => true,
        'log_brute_force' => true,
        'log_api_keys' => true,
    ],
];
