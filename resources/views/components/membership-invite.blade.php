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
            @if (config('social_login.providers.google.enabled'))
                <a class="hc-btn hc-btn-primary mt-5 flex w-full items-center justify-center gap-2.5 !py-3 font-extrabold shadow-md transition-transform hover:scale-[1.01]" href="{{ route('social.redirect', 'google') }}" rel="nofollow">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3l5.7-5.7C34 6.1 29.3 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-2.6-.2-5.2-.4-3.9z"/>
                        <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
                        <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/>
                        <path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C42 35 44 30 44 24c0-2.6-.2-5.2-.4-3.9z"/>
                    </svg>
                    <span>Google ile Tek Tıkla Bağlan</span>
                </a>
                <p class="hc-caption mt-3 text-center" style="color: var(--hc-text-muted)">
                    veya <a class="font-bold underline underline-offset-2" href="{{ route('register') }}">e-posta ile üye ol</a> · <a class="font-bold underline underline-offset-2" href="{{ route('login') }}">Giriş yap</a>
                </p>
            @else
                <a class="hc-btn hc-btn-primary mt-5 w-full" href="{{ route('register') }}">Ücretsiz üye ol</a>
                <p class="hc-caption mt-3 text-center" style="color: var(--hc-text-muted)">
                    Zaten üye misiniz? <a class="font-bold underline underline-offset-2" href="{{ route('login') }}">Giriş yapın</a>
                </p>
            @endif
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
