<?php

declare(strict_types=1);

return [
    'name' => 'House Chef Cyprus',
    'assets' => [
        // Not: eski .svg dosyaları aslında vektör değildi, içlerine base64
        // PNG gömülmüştü; ikisi birlikte her sayfada 160 KB yer kaplıyordu.
        // Aynı görüntü, gösterildiği ölçüde webp olarak 15 KB.
        'logo_light' => 'brand/logo-light.webp',
        'logo_dark' => 'brand/logo-dark.webp',
        'logo_light_2x' => 'brand/logo-light@2x.webp',
        'logo_dark_2x' => 'brand/logo-dark@2x.webp',
        'logo_light_fallback' => 'brand/logo-light@2x.png',
        'logo_dark_fallback' => 'brand/logo-dark@2x.png',
        'logo_light_png' => 'brand/house-chef-cyprus-logo-on-light.png',
        'logo_dark_png' => 'brand/house-chef-cyprus-logo-on-dark.png',
        'mark_light' => 'brand/mark-light.webp',
        'mark_light_2x' => 'brand/mark-light@2x.webp',
        'mark_dark_2x' => 'brand/mark-dark@2x.webp',
        'mark_light_fallback' => 'brand/mark-light@2x.png',
        'mark_dark_fallback' => 'brand/mark-dark@2x.png',
        'mark_dark' => 'brand/mark-dark.webp',
        'favicon_16' => 'brand/favicon-16x16.png',
        'favicon_32' => 'brand/favicon-32x32.png',
        'apple_touch_icon' => 'brand/apple-touch-icon.png',
        'android_chrome_192' => 'brand/android-chrome-192x192.png',
        'android_chrome_512' => 'brand/android-chrome-512x512.png',
        'og_default' => 'brand/og-default.jpg',
    ],
    'sizes' => [
        'xs' => 40,
        'sm' => 120,
        'md' => 164,
        'lg' => 200,
        'xl' => 240,
    ],
];
