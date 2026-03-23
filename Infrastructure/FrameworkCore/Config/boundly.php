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
        'domain'      => base_path('Domain'),
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
        'max_per_page'     => env('BOUNDLY_MAX_PER_PAGE', 100),
    ],
];
