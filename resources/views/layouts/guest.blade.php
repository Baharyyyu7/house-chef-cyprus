<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('locales.supported.'.app()->getLocale().'.direction', 'ltr') }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ff6b00">
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset(config('brand.assets.favicon_32')) }}"><link rel="icon" type="image/png" sizes="16x16" href="{{ asset(config('brand.assets.favicon_16')) }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset(config('brand.assets.apple_touch_icon')) }}">
    <link rel="manifest" href="{{ asset('brand/site.webmanifest') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <title>House Chef Cyprus · {{ __('ux.secure_login') }}</title>
    <script>if (localStorage.theme === 'dark') document.documentElement.classList.add('dark');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ dark: localStorage.theme === 'dark' }" x-init="$watch('dark', value => { document.documentElement.classList.toggle('dark', value); localStorage.theme = value ? 'dark' : 'light' })" class="min-h-screen font-sans text-[var(--hc-text-primary)] antialiased bg-[var(--hc-bg)]">
    {{-- İki sütun: solda form, sağda kahraman görsel ve gerçek özellikler.
         Referans tasarımdaki kullanıcı yorumları bilerek yok - uydurma yorum
         yazmak, olmayan bir memnuniyeti varmış gibi göstermek olurdu. Yerine
         sitenin gerçekten yaptığı işler duruyor. --}}
    <div class="hc-giris">
        <main class="hc-giris__sutun">
            <div class="hc-giris__icerik">
                <div class="mb-8 flex items-center justify-between">
                    <x-brand-logo context="auth" size="lg" class="transition hover:scale-[1.02] motion-reduce:transition-none" />
                    <x-theme-switch />
                </div>
                <section class="hc-v3-card p-6 shadow-xl sm:p-8">
                    {{ $slot }}
                </section>
                <div class="mt-8 flex flex-col items-center gap-2 text-center text-xs text-[var(--hc-text-muted)]">
                    <span>© {{ date('Y') }} House Chef Cyprus · {{ __('ux.flavors') }}</span>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('home') }}" class="hover:text-orange-600 dark:hover:text-orange-300">{{ __('nav.home') }}</a>
                        <span>·</span>
                        <a href="{{ route('legal.center') }}" class="hover:text-orange-600 dark:hover:text-orange-300">{{ __('ux.privacy_terms') }}</a>
                    </div>
                </div>
            </div>
        </main>

        <aside class="hc-giris__gorsel" aria-hidden="true">
            <picture>
                {{-- ?v=2: Cloudflare kenarinda `.avif` uzantisi taninmadan once
                     onbellege alinmis text/plain kopyalar var; surum sorgusu
                     taze bir girdi acar. Tarifler sayfasi da ayni sekilde. --}}
                <source type="image/avif" srcset="{{ asset('images/house-chef-hero-v2-1440.avif').'?v=2' }}">
                <source type="image/webp" srcset="{{ asset('images/house-chef-hero-v2-1440.webp') }}">
                <img src="{{ asset('images/house-chef-hero-v2-1440.webp') }}" alt="House Chef Cyprus Mutfak" loading="lazy" decoding="async">
            </picture>
            <div class="hc-giris__perde"></div>
            <div class="hc-giris__kartlar">
                @foreach ([
                    [__('ux.pantry_title'), __('ux.pantry_intro')],
                    [__('ux.measure_title'), __('ux.measure_intro')],
                ] as [$baslik, $metin])
                    <div class="hc-giris__kart">
                        <strong>{{ $baslik }}</strong>
                        <span>{{ $metin }}</span>
                    </div>
                @endforeach
            </div>
        </aside>
    </div>
    @livewireScripts
</body>
</html>
