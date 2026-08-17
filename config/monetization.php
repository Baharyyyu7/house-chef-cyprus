<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ödeme Altyapısı Ana Anahtarı
    |--------------------------------------------------------------------------
    |
    | Gerçek sağlayıcı ve doğrulanmış webhook akışı hazır olmadan hiçbir ücretli
    | hak verilmez. Production ortamında da açıkça etkinleştirilmesi gerekir.
    |
    */
    'payments_enabled' => env('MONETIZATION_PAYMENTS_ENABLED', false),

    'tip_max_amount' => env('MONETIZATION_TIP_MAX_AMOUNT', 250),

    'affiliate_allowed_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('AFFILIATE_ALLOWED_HOSTS', '')),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Google AdSense & Reklam Ağı Ayarları
    |--------------------------------------------------------------------------
    */
    /*
     | Yayıncı kimliği ve reklamın açık olup olmadığı burada değil,
     | config/integrations.php içindeki google.adsense bloğunda tutulur:
     | orası onay çerezine ve sertifikalı CMP şartına bağlıdır. Aynı bilgiyi
     | iki yerde tutmak, birini kapatıp diğerini açık bırakmayı kolaylaştırır.
     */

    /*
    |--------------------------------------------------------------------------
    | Reklam Yuvaları (Slot IDs)
    |--------------------------------------------------------------------------
    */
    'slots' => [
        'recipe_header' => env('AD_SLOT_RECIPE_HEADER', '1234567890'),
        'recipe_mid' => env('AD_SLOT_RECIPE_MID', '1234567891'),
        'recipe_bottom' => env('AD_SLOT_RECIPE_BOTTOM', '1234567892'),
        'blog_mid' => env('AD_SLOT_BLOG_MID', '1234567893'),
        'blog_bottom' => env('AD_SLOT_BLOG_BOTTOM', '1234567894'),
        'sidebar' => env('AD_SLOT_SIDEBAR', '1234567895'),
        'home_mid' => env('AD_SLOT_HOME_MID', '1234567896'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fiyatlandırma & Premium Paketler
    |--------------------------------------------------------------------------
    */
    'plans' => [
        'monthly' => [
            'id' => 'monthly',
            'name' => 'House Chef Premium (Aylık)',
            'price' => 4.99,
            'currency' => 'EUR',
            'symbol' => '€',
            'interval' => 'ay',
            'stripe_price_id' => env('STRIPE_PRICE_MONTHLY', ''),
            'features' => [
                'Sınırsız AI Mutfak & Tarif Asistanı',
                'Haftalık Otomatik Menü Planlama',
                'Akıllı Alışveriş Listesi Oluşturma',
                'Tüm Reklamları Kaldırma (Sıfır Reklam)',
                'Gelişmiş Makro & Mikro Besin Değerleri',
                'Kişiselleştirilmiş Alerjen & Diyet Filtresi',
                'Sınırsız Tarif Koleksiyonu Oluşturma',
            ],
        ],
        'yearly' => [
            'id' => 'yearly',
            'name' => 'House Chef Premium (Yıllık)',
            'price' => 39.99,
            'currency' => 'EUR',
            'symbol' => '€',
            'interval' => 'yıl',
            'discount_badge' => '%33 Tasarruf',
            'stripe_price_id' => env('STRIPE_PRICE_YEARLY', ''),
            'features' => [
                'Tüm Aylık Plan Özellikleri',
                '2 Ay Bedava Kullanım Avantajı',
                'Özel Dijital Kıbrıs Yemek E-Kitabı',
                'Öncelikli Şef Desteği & Yeni Özelliklere Erken Erişim',
            ],
        ],
    ],
];
