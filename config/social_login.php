<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Sosyal giriş sağlayıcıları
    |--------------------------------------------------------------------------
    |
    | Yalnız ücretsiz sağlayıcılar var. Apple bilerek yok: Apple ile giriş
    | yıllık ücretli bir geliştirici hesabı istiyor.
    |
    | Bir sağlayıcı ancak istemci kimliği ve gizli anahtarı tanımlıysa açılır.
    | Anahtarsız bir düğme göstermek, tıklayan herkesi hata sayfasına
    | götürmekten başka bir işe yaramaz.
    |
    */
    'providers' => [
        'google' => [
            'label' => 'Google',
            'enabled' => env('GOOGLE_CLIENT_ID') !== null && env('GOOGLE_CLIENT_SECRET') !== null,
        ],
        'github' => [
            'label' => 'GitHub',
            'enabled' => env('GITHUB_CLIENT_ID') !== null && env('GITHUB_CLIENT_SECRET') !== null,
        ],
    ],
];
