<?php

$origins = array_filter([
    env('FRONTEND_URL'),
    ...explode(',', (string) env('CORS_ALLOWED_ORIGINS')),
]);

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_unique(array_map('trim', $origins))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => (int) env('CORS_MAX_AGE'),
    'supports_credentials' => false,
];
