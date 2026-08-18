{{--
    Tarif ve yazı sayfalarındaki tek dönüşüm yüzeyi. En çok trafik alan
    sayfalarda hiçbir üyelik teklifi yoktu.

    Burada yalnız kodda karşılığı olan şeyler sayılıyor ve karşılık rota
    olarak aranıyor. Tam liste fiyatlandırma sayfasında kalır; kart onu
    tekrarlamak yerine oraya bağlanır.

    Üyeye hiçbir şey gösterilmez: ödeme yapmış birine satış yapılmaz.
--}}

@php
    $uye = auth()->check();
    $premium = $uye && auth()->user()->isPremium();

    // Yalnız gerçekten var olan yollar teklif edilir.
    $ucretsizFaydalar = collect([
        ['pantry.index', 'Malzeme dolabı', 'Evdekileri gir, yapabileceğin tarifleri gör.'],
        ['favorites.index', 'Favoriler', 'Beğendiklerini kendi listende topla.'],
        ['ingredients.explore', 'Elindekilerle eşleştirme', 'Eksikleri ve alternatifleri açıkça gör.'],
    ])->filter(fn ($f) => Route::has($f[0]));
@endphp

@if (! $premium)
    <section class="hc-v3-card p-5 sm:p-6" aria-labelledby="uyelik-daveti-baslik">
        @if (! $uye)
            <p class="hc-overline" style="color: var(--hc-text-muted)">Ücretsiz hesap</p>
            <h2 id="uyelik-daveti-baslik" class="hc-heading-3 mt-2">Bu tarifi kaydedin.</h2>
            <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">
                Ücretsiz hesap, mutfağınızı siteye tanıtır.
            </p>
            <ul class="mt-4 grid gap-3">
                @foreach ($ucretsizFaydalar as [$rota, $baslik, $aciklama])
                    <li class="flex gap-3">
                        <span class="mt-0.5 shrink-0 text-orange-500" aria-hidden="true">✓</span>
                        <span>
                            <span class="block text-sm font-bold" style="color: var(--hc-text-primary)">{{ $baslik }}</span>
                            <span class="hc-caption block" style="color: var(--hc-text-secondary)">{{ $aciklama }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
            <a class="hc-btn hc-btn-primary mt-5 w-full" href="{{ route('register') }}">Ücretsiz üye ol</a>
            <p class="hc-caption mt-3 text-center" style="color: var(--hc-text-muted)">
                Zaten üye misiniz? <a class="font-bold underline underline-offset-2" href="{{ route('login') }}">Giriş yapın</a>
            </p>
        @elseif (Route::has('pricing'))
            <p class="hc-overline" style="color: var(--hc-text-muted)">Premium</p>
            <h2 id="uyelik-daveti-baslik" class="hc-heading-3 mt-2">Reklamsız pişirin.</h2>
            <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">
                Premium üyelikte sitede hiç reklam görünmez ve AI Mutfak sınırsız açılır.
            </p>
            <a class="hc-btn hc-btn-primary mt-5 w-full" href="{{ route('pricing') }}">Planları gör</a>
        @endif
    </section>
@endif
