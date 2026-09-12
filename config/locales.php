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
            'enabled' => true,
        ],
        'de' => [
            'flag' => '🇩🇪',
            'label' => 'German',
            'native_label' => 'Deutsch',
            'html_locale' => 'de',
            'og_locale' => 'de_DE',
            'direction' => 'ltr',
            'enabled' => true,
        ],
        'fa' => [
            'flag' => '🇮🇷',
            'label' => 'Persian',
            'native_label' => 'فارسی',
            'html_locale' => 'fa',
            'og_locale' => 'fa_IR',
            'direction' => 'rtl',
            'enabled' => true,
        ],
        'pl' => [
            'flag' => '🇵🇱',
            'label' => 'Polish',
            'native_label' => 'Polski',
            'html_locale' => 'pl',
            'og_locale' => 'pl_PL',
            'direction' => 'ltr',
            'enabled' => true,
        ],
        'ru' => [
            'flag' => '🇷🇺',
            'label' => 'Russian',
            'native_label' => 'Русский',
            'html_locale' => 'ru',
            'og_locale' => 'ru_RU',
            'direction' => 'ltr',
            'enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | URL öneki
    |--------------------------------------------------------------------------
    |
    | Varsayılan dil kök adreste yaşar; diğerleri kendi önekinde. Dil çerezle
    | seçildiği sürece bütün diller aynı URL'i paylaşıyordu ve arama motoru
    | tek bir sürüm indeksleyebiliyordu: Googlebot çerez taşımaz, bu yüzden
    | Türkçe dışındaki hiçbir sayfayı hiç görmedi. hreflang de kurulamıyordu,
    | çünkü hreflang farklı URL'leri birbirine bağlar.
    |
    | Önek yalnız ETKİN diller için üretilir. Kapalı bir dile URL açmak,
    | Türkçe içeriği yabancı dil sayfası gibi göstermek olurdu; çok sayıda
    | zayıf dil sayfası site genelindeki kalite sinyalini aşağı çeker.
    |
    */
    'default_has_prefix' => false,
];
