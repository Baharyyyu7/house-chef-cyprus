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
--}}
@php
    $mevcutDil = app()->getLocale();
    $diller = collect(config('locales.supported', []))
        ->filter(fn (array $dil): bool => ($dil['enabled'] ?? false) === true);
@endphp

@if ($diller->count() > 1)
    @foreach ($diller as $kod => $dil)
        @continue($kod === $mevcutDil)
        <meta property="og:locale:alternate" content="{{ $dil['og_locale'] ?? $kod }}">
    @endforeach
@endif
