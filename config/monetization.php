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
    'payments_enabled' => env(
        'PAYMENTS_ENABLED',
        env('MONETIZATION_PAYMENTS_ENABLED', false),
    ),

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
    /*
     * Her yuva kendi biçimini taşır. AdSense'te oluşturulan birim hangi
     * biçimdeyse sayfada da o özniteliklerle basılmalı; yanlış öznitelikle
     * basılan birim ya hiç dolmaz ya da bozuk görünür.
     *
     * Varsayılanlar AdSense hesabında oluşturulmuş gerçek birimlerdir
     * (pub-1614195335767159). Ortam değişkeniyle ezilebilir; yer tutucu
     * kimlik kalırsa ad-slot bileşeni o yuvayı hiç basmaz.
     */
    'slots' => [
        // Yazı içi: makale metninin arasına girer.
        'in_article' => [
            'id' => env('AD_SLOT_IN_ARTICLE', '6007812595'),
            'format' => 'in_article',
        ],
        // Feed içi: liste ve ızgara akışlarının arasına girer.
        'in_feed' => [
            'id' => env('AD_SLOT_IN_FEED', '4500802572'),
            'format' => 'in_feed',
            'layout_key' => env('AD_SLOT_IN_FEED_LAYOUT_KEY', '-gw-3+1f-3d+2z'),
        ],
        // Multiplex: yazının sonunda içerik önerisi ızgarası.
        'multiplex' => [
            'id' => env('AD_SLOT_MULTIPLEX', '4694730924'),
            'format' => 'multiplex',
        ],
        // Görüntülü: duyarlı, genel amaçlı birim.
        'display' => [
            'id' => env('AD_SLOT_DISPLAY', '7904132011'),
            'format' => 'display',
        ],
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
