<?php

declare(strict_types=1);

return [
    'locale' => env('SEO_LOCALE', 'tr'),
    'country' => env('SEO_COUNTRY', 'CY'),
    'auto_publish' => (bool) env('SEO_CONTENT_AUTO_PUBLISH', false),
    'content_automation_enabled' => (bool) env('CONTENT_AUTOMATION_ENABLED', true),
    'max_drafts_per_day' => (int) env('CONTENT_MAX_DRAFTS_PER_DAY', 5),
    'max_refresh_drafts_per_day' => (int) env('CONTENT_MAX_REFRESH_DRAFTS_PER_DAY', 5),
    'max_social_drafts_per_day' => (int) env('CONTENT_MAX_SOCIAL_DRAFTS_PER_DAY', 10),
    'audit' => [
        'max_internal_links_per_document' => 8,
        'duplicate_title_limit' => 70,
        'duplicate_description_limit' => 160,
    ],
    'search_console' => [
        'enabled' => (bool) env('GOOGLE_SEARCH_CONSOLE_ENABLED', false),
        'site_url' => env('GOOGLE_SEARCH_CONSOLE_SITE_URL'),
        'timezone' => env('GOOGLE_SEARCH_CONSOLE_TIMEZONE', 'Europe/Nicosia'),
    ],
    'indexnow' => [
        'enabled' => (bool) env('INDEXNOW_ENABLED', false),
        'endpoint' => env('INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow'),
        'batch_size' => (int) env('INDEXNOW_BATCH_SIZE', 100),
    ],
    'social' => [
        'x_enabled' => (bool) env('X_API_ENABLED', false),
        'x_auto_post' => (bool) env('X_AUTO_POST', false),
    ],
];
