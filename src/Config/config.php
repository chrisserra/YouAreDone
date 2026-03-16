<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => getenv('APP_NAME') ?: 'YouAreDone.org',
        'env' => getenv('APP_ENV') ?: 'production',
        'url' => rtrim(getenv('APP_URL') ?: 'https://youaredone.org', '/'),
        'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL),
    ],
];