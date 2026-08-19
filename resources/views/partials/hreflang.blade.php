{{--
    hreflang etiketleri.

    Codex'in i18n paketinden alındı ama kendi yapılandırmasıyla değil: paket
    config('localization.*') okuyor, bu projede çalışan yapılandırma
    config('locales.*'). İkisini yan yana koymak aynı işi yapan iki sistem
    demek olurdu, o yüzden mevcut olana bağlandı.

    Yalnız açık diller yazılır. Henüz yayına girmemiş bir dili hreflang ile
    Google'a bildirmek, olmayan bir sayfayı vaat etmek olur; Google o adresi
    tarar, 404 alır ve güveni düşer.
--}}
@php
    $diller = collect(config('locales.supported', []))
        ->filter(fn (array $dil): bool => ($dil['enabled'] ?? false) === true);

    $varsayilan = config('locales.default', 'tr');
    $mevcutDil = app()->getLocale();

    // Adres dilden bağımsız olmalı: /tarifler her dilde aynı yolu gösterir,
    // dil öneki eklendiğinde bu fonksiyon tek yerden güncellenir.
    $adres = function (string $kod) use ($varsayilan): string {
        $yol = trim(request()->path(), '/');
        $yol = $yol === '/' ? '' : $yol;

        return $kod === $varsayilan ? url($yol) : url($kod.'/'.$yol);
    };
@endphp

@if ($diller->count() > 1)
    <link rel="alternate" hreflang="x-default" href="{{ $adres($varsayilan) }}">
    @foreach ($diller as $kod => $dil)
        <link rel="alternate" hreflang="{{ $dil['html_locale'] ?? $kod }}" href="{{ $adres((string) $kod) }}">
    @endforeach
    @foreach ($diller as $kod => $dil)
        @continue($kod === $mevcutDil)
        <meta property="og:locale:alternate" content="{{ $dil['og_locale'] ?? $kod }}">
    @endforeach
@endif
