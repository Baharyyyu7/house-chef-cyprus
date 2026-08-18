<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public language rollout
    |--------------------------------------------------------------------------
    |
    | A locale becomes selectable only after its public content and legal copy
    | have been reviewed. This prevents a translated interface from presenting
    | Turkish editorial content as if it were a complete foreign-language site.
    | Enabling a locale later does not require a route or database migration.
    |
    */
    'default' => env('APP_LOCALE', 'tr'),

    'supported' => [
        'tr' => [
            'flag' => '🇹🇷',
            'label' => 'Türkçe',
            'native_label' => 'Türkçe',
            'html_locale' => 'tr',
            'og_locale' => 'tr_TR',
            'direction' => 'ltr',
            'enabled' => true,
        ],
        'en' => [
            'flag' => '🇬🇧',
            'label' => 'English',
            'native_label' => 'English',
            'html_locale' => 'en',
            'og_locale' => 'en_US',
            'direction' => 'ltr',
            'enabled' => false,
        ],
        'de' => [
            'flag' => '🇩🇪',
            'label' => 'German',
            'native_label' => 'Deutsch',
            'html_locale' => 'de',
            'og_locale' => 'de_DE',
            'direction' => 'ltr',
            'enabled' => false,
        ],
        'fa' => [
            'flag' => '🇮🇷',
            'label' => 'Persian',
            'native_label' => 'فارسی',
            'html_locale' => 'fa',
            'og_locale' => 'fa_IR',
            'direction' => 'rtl',
            'enabled' => false,
        ],
    ],
];
