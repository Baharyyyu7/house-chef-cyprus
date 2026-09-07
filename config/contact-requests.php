<?php

declare(strict_types=1);

return [
    'recipient' => env('CONTACT_REQUEST_RECIPIENT', 'info@housechefcyprus.com'),
    'retention_days' => (int) env('CONTACT_REQUEST_RETENTION_DAYS', 730),
    'subjects' => [
        'general_feedback' => 'Genel Geri Bildirim',
        'recipe_error' => 'Tarif Hatası',
        'blog_content' => 'Blog İçeriği',
        'technical_issue' => 'Teknik Sorun',
        'copyright_notice' => 'Telif Bildirimi',
        'privacy' => 'Gizlilik ve Kişisel Veriler',
        'account_issue' => 'Hesap Sorunu',
        'partnership' => 'İş Birliği',
        'advertising' => 'Reklam ve Sponsorluk',
        'menu_content' => 'Menü ve Mutfak İçeriği İş Birliği',
        'hospitality' => 'Otel ve Villa İş Birliği',
        'referral_partner' => 'Restoran ve Şef Yönlendirme Ortaklığı',
        'affiliate_partner' => 'Satış Ortaklığı (Affiliate)',
        'business_listing' => 'İşletme Profili ve İlan',
        'other' => 'Diğer',
    ],
    'commercial_subjects' => ['partnership', 'advertising', 'referral_partner', 'affiliate_partner', 'business_listing', 'menu_content', 'hospitality'],
    'statuses' => [
        'new' => 'Yeni', 'in_review' => 'İnceleniyor', 'waiting_reply' => 'Yanıt Bekliyor',
        'answered' => 'Yanıtlandı', 'resolved' => 'Çözüldü', 'spam' => 'Spam', 'archived' => 'Arşivlendi',
    ],
    'priorities' => ['low' => 'Düşük', 'normal' => 'Normal', 'high' => 'Yüksek', 'critical' => 'Kritik'],
    'captcha' => [
        'enabled' => (bool) env('CONTACT_CAPTCHA_ENABLED', false),
        'site_key' => env('CONTACT_TURNSTILE_SITE_KEY'),
        'secret' => env('CONTACT_TURNSTILE_SECRET'),
    ],
];
