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
];
