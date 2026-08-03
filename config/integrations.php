<?php

declare(strict_types=1);

return [
    'google' => [
        'site_verification' => env('GOOGLE_SITE_VERIFICATION'),
        'search_console' => [
            'enabled' => (bool) env('GOOGLE_SEARCH_CONSOLE_ENABLED', false),
            'property' => env('GOOGLE_SEARCH_CONSOLE_PROPERTY'),
        ],
        'analytics' => [
            'enabled' => (bool) env('GOOGLE_ANALYTICS_ENABLED', false),
            'measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID'),
            'debug' => (bool) env('GOOGLE_ANALYTICS_DEBUG', false),
            'consent_mode' => (bool) env('GOOGLE_CONSENT_MODE_ENABLED', true),
        ],
        'tag_manager' => [
            'enabled' => (bool) env('GOOGLE_TAG_MANAGER_ENABLED', false),
            'container_id' => env('GOOGLE_TAG_MANAGER_CONTAINER_ID'),
        ],
        'auth' => [
            'enabled' => (bool) env('GOOGLE_AUTH_ENABLED', false),
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
        ],
        'recaptcha' => [
            'enabled' => (bool) env('GOOGLE_RECAPTCHA_ENABLED', false),
            'site_key' => env('GOOGLE_RECAPTCHA_SITE_KEY'),
            'secret_key' => env('GOOGLE_RECAPTCHA_SECRET_KEY'),
        ],
    ],
    'captcha_provider' => env('CAPTCHA_PROVIDER', 'turnstile'),
];
