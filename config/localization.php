<?php

declare(strict_types=1);

return [
    // Turkish stays at the site root so existing indexed URLs never move.
    'default' => 'tr',

    // Every additional locale lives under its own path prefix (/en/...).
    'locales' => [
        'tr' => ['name' => 'Türkçe', 'native' => 'Türkçe', 'short' => 'TR', 'prefix' => '', 'hreflang' => 'tr', 'og_locale' => 'tr_TR'],
        'en' => ['name' => 'English', 'native' => 'English', 'short' => 'EN', 'prefix' => 'en', 'hreflang' => 'en', 'og_locale' => 'en_GB'],
    ],

    // Searchers whose language has no dedicated edition get the English one.
    'x_default' => env('LOCALIZATION_X_DEFAULT', 'en'),
];
