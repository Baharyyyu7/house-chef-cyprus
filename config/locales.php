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

    /*
    |--------------------------------------------------------------------------
    | Localized path segments
    |--------------------------------------------------------------------------
    |
    | English pages lived under the Turkish path (/en/tarifler/...). An English
    | searcher and an English-language crawler both read the path; "recipes" is
    | the word they know. Only the first segment is translated: the recipe slug
    | is the dish's own name and stays as it is.
    |
    | The Turkish segment remains the internal route name. This map is the
    | public spelling per locale; requests to the Turkish spelling under a
    | foreign prefix are redirected to the localized one.
    |
    */
    'path_aliases' => [
        'en' => ['tarifler' => 'recipes', 'mutfaklar' => 'cuisines'],
        'de' => ['tarifler' => 'rezepte', 'mutfaklar' => 'kuechen'],
        'ru' => ['tarifler' => 'recepty', 'mutfaklar' => 'kuhni'],
        'pl' => ['tarifler' => 'przepisy', 'mutfaklar' => 'kuchnie'],
        'pt' => ['tarifler' => 'receitas', 'mutfaklar' => 'cozinhas'],
        'fa' => ['tarifler' => 'recipes', 'mutfaklar' => 'cuisines'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dil sürümü olan rotalar
    |--------------------------------------------------------------------------
    |
    | Yalnız bu rotaların yabancı dil adresi vardır. route() bunlara dil
    | önekini ve yol yazımını ekler; hreflang kümesi yalnız bunlar için
    | kurulur; listede olmayan bir sayfanın önekli adresi (/en/hakkimizda)
    | Türkçe sürüme kalıcı yönlendirilir. Önceden liste SetLocale içinde
    | gömülüydü ve hreflang ile site haritası onu bilmiyordu: 74 Türkçe
    | statik sayfa yedi dilde ayrı dil sürümü gibi ilan ediliyordu.
    |
    | Bir sayfanın gerçek çevirisi geldiğinde buraya eklenir.
    |
    */
    'localized_routes' => [
        'home', 'recipes.*', 'blog.*', 'cuisines.*', 'categories.*',
        'ingredients.explore', 'ingredients.hub.*', 'collections.sure*', 'collections.zorluk*',
        'login', 'register', 'password.request', 'password.reset', 'password.confirm',
        'verification.notice', 'pricing',
    ],

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
        'pt' => [
            'flag' => '🇵🇹',
            'label' => 'Portuguese',
            'native_label' => 'Português',
            'html_locale' => 'pt',
            'og_locale' => 'pt_PT',
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
