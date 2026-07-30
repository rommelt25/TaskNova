<?php

$origins = array_filter([
    env('FRONTEND_URL'),
    ...explode(',', (string) env('CORS_ALLOWED_ORIGINS')),
]);

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_unique(array_map('trim', $origins))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => (int) env('CORS_MAX_AGE'),
    'supports_credentials' => filter_var(
        env('CORS_SUPPORTS_CREDENTIALS'),
        FILTER_VALIDATE_BOOL
    ),
];
