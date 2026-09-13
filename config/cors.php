<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        rtrim(env('FRONTEND_URL', 'https://instadrop.uk'), '/'),
        'https://www.instadrop.uk',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 600,
    'supports_credentials' => false,
];
