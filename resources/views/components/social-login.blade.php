@props(['label' => null, 'dividerAfter' => false, 'dividerText' => null])

@php
    /**
     * Sosyal giriş düğmeleri.
     *
     * Yalnız yapılandırılmış sağlayıcı basılır. Anahtarı olmayan bir düğme
     * göstermek, tıklayan herkesi hata sayfasına götürmekten başka bir işe
     * yaramaz - mağazada dosyası olmayan ürünü gizlememizle aynı kural.
     *
     * Apple bilerek yok: Apple ile giriş yıllık ücretli bir geliştirici
     * hesabı istiyor ve bu kurulumda her şey ücretsiz olacak.
     */
    $saglayicilar = collect(config('social_login.providers', []))
        ->filter(fn (array $s): bool => ($s['enabled'] ?? false) === true);
@endphp

@if ($saglayicilar->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'hc-sosyal']) }}>
        @if (! $dividerAfter)
            <div class="hc-sosyal__ayrac"><span>{{ $label ?? __('ux.social') }}</span></div>
        @endif

        <div class="hc-sosyal__dugmeler">
            @foreach ($saglayicilar as $kod => $saglayici)
                <a class="hc-sosyal__dugme" href="{{ route('social.redirect', $kod) }}" rel="nofollow">
                    @if ($kod === 'google')
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 48 48" aria-hidden="true">
                            <path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3l5.7-5.7C34 6.1 29.3 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-2.6-.2-5.2-.4-3.9z"/>
                            <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
                            <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/>
                            <path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C42 35 44 30 44 24c0-2.6-.2-5.2-.4-3.9z"/>
                        </svg>
                    @elseif ($kod === 'github')
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 .5A11.5 11.5 0 0 0 .5 12a11.5 11.5 0 0 0 7.9 10.9c.6.1.8-.2.8-.5v-2c-3.2.7-3.9-1.5-3.9-1.5-.5-1.4-1.3-1.7-1.3-1.7-1-.7.1-.7.1-.7 1.1.1 1.7 1.2 1.7 1.2 1 1.7 2.7 1.2 3.3.9.1-.7.4-1.2.7-1.5-2.6-.3-5.3-1.3-5.3-5.7 0-1.3.5-2.3 1.2-3.1-.1-.3-.5-1.5.1-3.1 0 0 1-.3 3.2 1.2a11 11 0 0 1 5.8 0c2.2-1.5 3.2-1.2 3.2-1.2.6 1.6.2 2.8.1 3.1.8.8 1.2 1.8 1.2 3.1 0 4.4-2.7 5.4-5.3 5.7.4.4.8 1.1.8 2.2v3.3c0 .3.2.6.8.5A11.5 11.5 0 0 0 23.5 12 11.5 11.5 0 0 0 12 .5z"/>
                        </svg>
                    @endif
                    <span>{{ __('ux.provider', ['provider' => $saglayici['label']]) }}</span>
                </a>
            @endforeach
        </div>

        @if ($dividerAfter)
            <div class="hc-sosyal__ayrac"><span>{{ $dividerText ?? 'veya e-posta ile' }}</span></div>
        @endif
    </div>
@endif
