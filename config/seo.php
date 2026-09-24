<?php

declare(strict_types=1);

return [
    'locale' => env('SEO_LOCALE', 'tr'),
    'country' => env('SEO_COUNTRY', 'CY'),
    'indexing_enabled' => (bool) env('SEO_INDEXING_ENABLED', true),
    'auto_publish' => (bool) env('SEO_CONTENT_AUTO_PUBLISH', false),
    'content_automation_enabled' => (bool) env('CONTENT_AUTOMATION_ENABLED', true),
    'max_drafts_per_day' => (int) env('CONTENT_MAX_DRAFTS_PER_DAY', 5),
    'max_refresh_drafts_per_day' => (int) env('CONTENT_MAX_REFRESH_DRAFTS_PER_DAY', 5),
    'max_social_drafts_per_day' => (int) env('CONTENT_MAX_SOCIAL_DRAFTS_PER_DAY', 10),
    'audit' => [
        'max_internal_links_per_document' => 8,
        'duplicate_title_limit' => 70,
        'duplicate_description_limit' => 160,
        'minimum_article_characters' => (int) env('SEO_MINIMUM_ARTICLE_CHARACTERS', 300),
        'minimum_recipe_description_characters' => (int) env('SEO_MINIMUM_RECIPE_DESCRIPTION_CHARACTERS', 120),
        'minimum_zero_result_searches' => (int) env('SEO_MINIMUM_ZERO_RESULT_SEARCHES', 5),
    ],
    'search_console' => [
        'client_id' => env('GOOGLE_SEARCH_CONSOLE_CLIENT_ID', env('GOOGLE_CLIENT_ID')),
        'client_secret' => env('GOOGLE_SEARCH_CONSOLE_CLIENT_SECRET', env('GOOGLE_CLIENT_SECRET')),
        'refresh_token' => env('GOOGLE_SEARCH_CONSOLE_REFRESH_TOKEN'),
        'enabled' => (bool) env('GOOGLE_SEARCH_CONSOLE_ENABLED', env('SEARCH_CONSOLE_ENABLED', false)),
        'site_url' => env('GOOGLE_SEARCH_CONSOLE_SITE_URL', env('SEARCH_CONSOLE_SITE_URL')),
        'timezone' => env('GOOGLE_SEARCH_CONSOLE_TIMEZONE', env('SEARCH_CONSOLE_TIMEZONE', 'Europe/Nicosia')),
    ],
    'indexnow' => [
        'enabled' => (bool) env('INDEXNOW_ENABLED', true),
        'endpoint' => env('INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow'),
        'batch_size' => (int) env('INDEXNOW_BATCH_SIZE', 100),
    ],
    'social' => [
        'x_enabled' => (bool) env('X_API_ENABLED', env('X_DISTRIBUTION_ENABLED', false)),
        'x_auto_post' => (bool) env('X_AUTO_POST', false),
    ],
    'geo_targeting' => [
        'locale' => 'tr-TR',
        'country' => 'CY',
        'region' => 'TR',
        'geo_coordinates' => [
            'latitude' => 35.1856,
            'longitude' => 33.3823,
        ],
        'city' => 'Nicosia',
        'timezone' => 'Europe/Nicosia',
    ],
    'hreflang' => [
        'enabled' => true,
        'default_locale' => 'tr',
        'locales' => ['tr-TR', 'en-GB', 'de-DE', 'fa-IR', 'pl-PL', 'ru-RU', 'pt-PT'],
        'x_default' => '/tr/',
        'include_self' => true,
    ],
    // Distribb SEO Platform
    'distribb' => [
        'enabled' => (bool) env('DISTRIBB_API_ENABLED', false),
        'api_key' => env('DISTRIBB_API_KEY'),
        'platform_url' => env('DISTRIBB_PLATFORM_URL', 'https://api.distribb.io'),
        'onboarding_url' => env('DISTRIBB_ONBOARDING_URL', 'https://distribb.io/onboarding'),
        'auto_publish' => (bool) env('DISTRIBB_AUTO_PUBLISH', false),
        'backlink_exchange' => true,
        'keyword_region' => env('DISTRIBB_KEYWORD_REGION', 'CY'),
        'content_pillars' => ['https://housechefcyprus.com/tr/kibris-mutlagi', 'https://housechefcyprus.com/tr/mediterranean-recipes'],
    ],
];