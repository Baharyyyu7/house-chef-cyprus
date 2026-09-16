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
        /*
         * The publisher identifier is public, but ad delivery is deliberately
         * opt-in. A simple analytics preference is not an EEA ad-consent
         * mechanism, so production must keep this disabled until a
         * Google-certified CMP is configured and tested.
         */
        'adsense' => [
            'enabled' => (bool) env('GOOGLE_ADSENSE_ENABLED', false),
            'client_id' => env('GOOGLE_ADSENSE_CLIENT_ID'),
            'verification_meta_enabled' => (bool) env('GOOGLE_ADSENSE_VERIFICATION_META_ENABLED', false),
            'certified_cmp_enabled' => (bool) env('GOOGLE_CERTIFIED_CMP_ENABLED', false),

            /*
             * Onayı kimin topladığı.
             *
             * 'custom'  : kendi bandımız sorar, hc_advertising_consent çerezi
             *             karar verir. Varsayılan ve bugüne kadarki davranış.
             * 'google'  : Google'ın kendi sertifikalı CMP'si (Privacy &
             *             messaging) sorar. Bu CMP AdSense betiğinin içinden
             *             gelir, dolayısıyla betik onaydan ÖNCE yüklenmek
             *             zorundadır; yoksa mesaj hiç görünmez ve onay hiç
             *             oluşmaz. Betik Consent Mode v2 varsayılanları
             *             "denied" iken yüklenir ve TCF sinyali gelene kadar
             *             kişiselleştirilmiş reklam isteği atmaz.
             *
             * 'google' seçmeden önce AdSense panelinde Gizlilik ve mesajlaşma
             * altında AEA mesajının yayında olması gerekir.
             */
            'cmp_provider' => env('GOOGLE_ADSENSE_CMP', 'custom'),
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
    'affiliate' => [
        'enabled' => (bool) env('AFFILIATE_ENABLED', true),
        'amazon' => [
            'tag' => env('AMAZON_AFFILIATE_TAG', 'housechefcyprus-21'),
            'base_url' => 'https://www.amazon.com.tr',
        ],
        'trendyol' => [
            'partner_id' => env('TRENDYOL_AFFILIATE_ID', ''),
            'base_url' => 'https://www.trendyol.com',
        ],
    ],
    'captcha_provider' => env('CAPTCHA_PROVIDER', 'turnstile'),
];
