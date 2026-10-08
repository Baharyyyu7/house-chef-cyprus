{{--
    og:locale:alternate etiketleri.

    Bu dosya eskiden hreflang <link> etiketlerini de basıyordu. Basmıyor artık:
    site.blade.php zaten LocaleUrlService::alternates() kümesini basıyor ve iki
    küme yan yana durduğunda her sayfa hreflang'i iki kez bildiriyordu. Google
    çelişkili bir hreflang kümesini kısmen değil tamamen yok sayar, yani iki
    doğru küme bir arada sıfır küme eder.

    Daha kötüsü buradaki küme yalnız "dil açık mı" diye bakıyordu; alternates()
    ise "bu içeriğin o dilde çevirisi var mı" diye de bakar. İkisi çelişince
    tarif ve blog sayfalarında olmayan çevirilere hreflang verilmiş oluyordu.

    Tek kaynak alternates(); burada yalnız onun kapsamadığı og etiketi kalıyor.
    og:locale:alternate da aynı kümeden türer. 8 Ekim 2026 denetimi: her
    sayfa altı dilin hepsini alternatif ilan ediyordu, sayfanın o dilde
    olup olmadığına bakmadan.
--}}
@php
    $mevcutHtmlDili = config('locales.supported.'.app()->getLocale().'.html_locale', app()->getLocale());
    $ogDilleri = collect(request()->attributes->get('hc_hreflang_kumesi') ?? app(\App\Services\Seo\LocaleUrlService::class)->alternates(request(), request()->attributes->get('hc_seo_content')))
        ->pluck('hreflang')
        ->reject(fn (string $dil): bool => $dil === 'x-default' || $dil === $mevcutHtmlDili)
        ->map(fn (string $dil): string => (string) (collect(config('locales.supported', []))->firstWhere('html_locale', $dil)['og_locale'] ?? $dil))
        ->values();
@endphp

@foreach ($ogDilleri as $ogDili)
    <meta property="og:locale:alternate" content="{{ $ogDili }}">
@endforeach
