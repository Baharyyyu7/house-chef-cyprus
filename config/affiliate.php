<?php

declare(strict_types=1);

return [
    'enabled' => env('AFFILIATE_ENABLED', true),

    'default_partner' => env('AFFILIATE_DEFAULT_PARTNER', 'amazon'),

    'partners' => [
        'amazon' => [
            'tag' => env('AFFILIATE_AMAZON_TAG', 'housechefcypr-21'),
            'base_search_url' => 'https://www.amazon.com/s?k=',
        ],
        'trendyol' => [
            'tag' => env('AFFILIATE_TRENDYOL_TAG', 'housechefcyprus'),
            'base_search_url' => 'https://www.trendyol.com/sr?q=',
        ],
    ],

    'utm' => [
        'source' => 'housechefcyprus',
        'medium' => 'affiliate',
        'campaign' => 'culinary_gear',
    ],

    /**
     * Reçeteler ve bloglar için önerilen doğrulanmış mutfak ekipmanları kataloğu.
     */
    'items' => [
        'dokum-tava' => [
            'name' => 'Ağır Döküm Demir Izgara Tavası (Cast Iron Skillet)',
            'desc' => 'Hellim peynirini yapışmadan, dışı çıtır içi yumuşak ızgaralamak ve et mühürlemek için vazgeçilmez.',
            'search_query' => 'cast iron skillet pre seasoned',
            'category' => 'tava-tencere',
            'emoji' => '🍳',
        ],
        'toprak-guvec' => [
            'name' => 'Geleneksel Toprak Fırın Güveci (Clay Pot)',
            'desc' => 'Kleftiko ve ağır ateşte pişen Kıbrıs et yahnisinde etin kendi suyunda lokum gibi olmasını sağlar.',
            'search_query' => 'traditional clay cooking pot with lid',
            'category' => 'tava-tencere',
            'emoji' => '🍲',
        ],
        'sef-bicagi' => [
            'name' => 'Dövme Çelik Profesyonel Şef Bıçağı (Chef Knife)',
            'desc' => 'Sebzeleri jülyen ve brunoise doğramak, et dilimlemek için yüksek karbonlu dengeli mutfak bıçağı.',
            'search_query' => 'japanese high carbon steel chef knife 8 inch',
            'category' => 'kesme-hazirlik',
            'emoji' => '🔪',
        ],
        'dijital-terazi' => [
            'name' => 'Hassas Dijital Mutfak Terazisi (1g - 5kg)',
            'desc' => 'Ekmek, çörek, mayalı hamur işleri ve pastacılık tariflerinde gramı gramına tutarlı sonuçlar.',
            'search_query' => 'digital kitchen scale grams precision',
            'category' => 'olcum',
            'emoji' => '⚖️',
        ],
        'et-termometresi' => [
            'name' => 'Anlık Okumalı Dijital Et & Fırın Termometresi',
            'desc' => 'Tandır, rosto ve kuzu but pişirirken iç sıcaklığı ölçerek etin kurumasını kesin olarak önler.',
            'search_query' => 'instant read digital meat thermometer waterproof',
            'category' => 'olcum',
            'emoji' => '🌡️',
        ],
        'peynir-mayasi' => [
            'name' => 'Doğal Sıvı Peynir Mayası ve Organik Süzme Tülbenti',
            'desc' => 'Evde geleneksel Kıbrıs hellimi, lor ve anari peyniri yapmak için gerekli enzim ve süzme bezi seti.',
            'search_query' => 'liquid microbial rennet and organic cheesecloth',
            'category' => 'peynir-yapimi',
            'emoji' => '🧀',
        ],
        'zeytinyagi-spreyi' => [
            'name' => 'Paslanmaz Çelik Zeytinyağı Püskürtücü & Sprey Şişe',
            'desc' => 'Salatalarda, meze tabaklarında ve fırın sebzelerinde sızma zeytinyağını eşit ve tasarruflu dağıtır.',
            'search_query' => 'olive oil sprayer dispenser glass bottle',
            'category' => 'sos-sunum',
            'emoji' => '🫒',
        ],
    ],
];
