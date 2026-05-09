<?php

return [
    // Add these API configurations to your existing modularous.php config
    'prefix' => env('MODULAROUS_API_PREFIX', 'api/v1'),
    'domain' => env('MODULAROUS_API_DOMAIN'),
    'middlewares' => [
        'language',
        'api',
        'throttle:api',
    ],
    'public_middlewares' => [],
    'auth_middlewares' => [
        'auth:sanctum',
    ],
    'routes' => [], // Additional API resource routes to merge with default (index, store, show, update, destroy)
    'versioning' => [
        'enabled' => true,
        'default_version' => 'v1',
        'header' => 'API-Version',
    ],
    'pagination' => [
        'default_per_page' => env('MODULAROUS_API_PAGINATION_DEFAULT_PER_PAGE', 15),
        'max_per_page' => env('MODULAROUS_API_PAGINATION_MAX_PER_PAGE', 100),
    ],
    'rate_limiting' => [
        'enabled' => env('MODULAROUS_API_RATE_LIMITING_ENABLED', true),
        'per_minute' => env('MODULAROUS_API_RATE_LIMITING_PER_MINUTE', 60),
        'per_hour' => env('MODULAROUS_API_RATE_LIMITING_PER_HOUR', 1000),
        'blocking_time' => env('MODULAROUS_API_RATE_LIMITING_BLOCKING_TIME', 3600), // 1 hour
        'blocking_maximum_attempts' => env('MODULAROUS_API_RATE_LIMITING_BLOCKING_MAXIMUM_ATTEMPTS', 250), // 250 attempts
        'blocking_time_threshold' => env('MODULAROUS_API_RATE_LIMITING_BLOCKING_TIME_THRESHOLD', 300), // 5 minutes
    ],
    'cors' => [
        'enabled' => true,
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['*'],
    ],
    'response' => [
        'wrap_in_data' => env('MODULAROUS_API_RESPONSE_WRAP_IN_DATA', true),
        'include_meta' => env('MODULAROUS_API_RESPONSE_INCLUDE_META', true),
        'include_links' => env('MODULAROUS_API_RESPONSE_INCLUDE_LINKS', true),
    ],
    'features' => [
        'filtering' => env('MODULAROUS_API_FEATURES_FILTERING', true),
        'sorting' => env('MODULAROUS_API_FEATURES_SORTING', true),
        'searching' => env('MODULAROUS_API_FEATURES_SEARCHING', true),
        'including' => env('MODULAROUS_API_FEATURES_INCLUDING', true),
        'field_selection' => true,
    ],
];
