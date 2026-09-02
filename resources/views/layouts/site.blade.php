@props(['title' => 'House Chef Cyprus', 'metaDescription' => "Kıbrıs'tan ilham alan tarifler, malzeme dolabı ve dengeli beslenme rehberi.", 'structuredData' => null, 'canonicalUrl' => null, 'socialImage' => null, 'socialImageWidth' => null, 'socialImageHeight' => null, 'socialImageAlt' => null, 'robots' => null, 'livewire' => false])
@php($activeLanguage = config('locales.supported.'.app()->getLocale(), config('locales.supported.'.config('locales.default', 'tr'), [])))
<!DOCTYPE html>
<html lang="{{ $activeLanguage['html_locale'] ?? 'tr' }}" dir="{{ $activeLanguage['direction'] ?? 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#ff6b00">
    <meta name="description" content="{{ $metaDescription }}">
    @if(app()->environment('production') && filled(config('integrations.google.site_verification')))
        <meta name="google-site-verification" content="{{ config('integrations.google.site_verification') }}">
    @endif
    @if(app()->environment('production') && config('integrations.google.adsense.verification_meta_enabled') && preg_match('/^ca-pub-\\d+$/', (string) config('integrations.google.adsense.client_id')))
        <meta name="google-adsense-account" content="{{ config('integrations.google.adsense.client_id') }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl ?: url()->current() }}">
    {{-- hreflang yalnız birden çok dil ETKİN olduğunda basılır. Kapalı bir
         dile hreflang vermek, Türkçe içeriği yabancı dil sayfası gibi
         göstermek olurdu. Küme kendini de içerir: kendine gönderme yapmayan
         hreflang'i Google tamamen yok sayar. --}}
    @foreach (app(\App\Services\Seo\LocaleUrlService::class)->alternates(request()) as $alternatif)
        <link rel="alternate" hreflang="{{ $alternatif['hreflang'] }}" href="{{ $alternatif['href'] }}">
    @endforeach
    @php($isPrivateSurface = request()->routeIs('admin.*', 'login', 'register', 'password.*', 'verification.*', 'profile', 'dashboard'))
    @if ($robots || $isPrivateSurface)
        <meta name="robots" content="{{ $robots ?: 'noindex,nofollow' }}">
    @endif
    @unless ($isPrivateSurface || str_contains((string) $robots, 'noindex'))
        <meta property="og:type" content="{{ request()->routeIs('blog.show') ? 'article' : 'website' }}">
        <meta property="og:locale" content="{{ $activeLanguage['og_locale'] ?? 'tr_TR' }}">
        @include('partials.hreflang')
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:url" content="{{ $canonicalUrl ?: url()->current() }}">
        <meta property="og:image" content="{{ $socialImage ?: url(config('brand.assets.og_default')) }}">
        @if($socialImage)
            @if($socialImageWidth)<meta property="og:image:width" content="{{ $socialImageWidth }}">@endif
            @if($socialImageHeight)<meta property="og:image:height" content="{{ $socialImageHeight }}">@endif
            @if($socialImageAlt)<meta property="og:image:alt" content="{{ $socialImageAlt }}">@endif
        @endif
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $title }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">
        <meta name="twitter:image" content="{{ $socialImage ?: url(config('brand.assets.og_default')) }}">
        @if($socialImageAlt)<meta name="twitter:image:alt" content="{{ $socialImageAlt }}">@endif
    @endunless
    @if($structuredData)<script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script>@endif
    @if(!request()->routeIs('admin.*', 'login', 'register', 'password.*', 'verification.*', 'profile', 'dashboard'))
        <script type="application/ld+json">{"\u0040context":"https://schema.org","\u0040graph":[{"\u0040type":"Organization","name":"House Chef Cyprus","url":"{{ url('/') }}","logo":"{{ url(config('brand.assets.mark_light')) }}"},{"\u0040type":"WebSite","name":"House Chef Cyprus","url":"{{ url('/') }}","inLanguage":"{{ $activeLanguage['html_locale'] ?? 'tr' }}","publisher":{"\u0040type":"Organization","name":"House Chef Cyprus","logo":"{{ url(config('brand.assets.mark_light')) }}"},"potentialAction":{"\u0040type":"SearchAction","target":"{{ route('recipes.index') }}?q={search_term_string}","query-input":"required name=search_term_string"}}]}</script>
    @endif
    {{-- favicon.svg 77 KB'lık bir kabuktu: içinde gömülü PNG vardı ve
         tarayıcı onu 16 pikselde göstermek için indiriyordu. --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset(config('brand.assets.favicon_32')) }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset(config('brand.assets.favicon_16')) }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset(config('brand.assets.apple_touch_icon')) }}">
    <link rel="manifest" href="{{ asset('brand/site.webmanifest') }}">
    {{-- İki preconnect gerekiyor: stil dosyası normal istekle, yazı tipi
         dosyaları CORS ile çekilir ve ikincisi ayrı bir bağlantı açar. --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <title>{{ $title }}</title>
    <script>if (localStorage.theme === 'dark') document.documentElement.classList.add('dark');</script>
    <script>
    /**
     * Tema değişimi.
     *
     * Eskiden sayfadaki her elemana (body *) aynı anda geçiş veriliyordu,
     * gölgeler dâhil. Binlerce elemanı aynı karede animasyonlamak takılmaya
     * yol açıyordu; "smooth değil" hissinin sebebi buydu.
     *
     * View Transitions destekleyen tarayıcıda tarayıcı, boyanmış kareyi tek
     * bir kompozit katman olarak çapraz geçirir: eleman sayısı ne olursa
     * olsun maliyet aynı. Desteklemeyen tarayıcıda dar kapsamlı bir sınıf
     * devreye girer. Hareket kısıtlıysa geçiş yok, doğrudan değişir.
     */
    window.hcTemaDegistir = function (uygula) {
        var kisitli = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (kisitli) { uygula(); return; }

        if (typeof document.startViewTransition === 'function') {
            document.startViewTransition(uygula);

            return;
        }

        var kok = document.documentElement;
        kok.classList.add('hc-theme-transitioning');
        uygula();
        clearTimeout(window.hcThemeTransitionTimer);
        window.hcThemeTransitionTimer = setTimeout(function () {
            kok.classList.remove('hc-theme-transitioning');
        }, 420);
    };
    </script>
    {{-- Kırık medya koruması: yüklenemeyen görseller alt metnini ekrana düşürmesin, nötr yüzeyle değişsin. --}}
    <script>
    (function () {
        var FALLBACK = "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%20800%20600'%3E%3Crect%20width='800'%20height='600'%20fill='%23e0ddda'/%3E%3Cg%20fill='none'%20stroke='%23a8a29e'%20stroke-width='9'%3E%3Ccircle%20cx='400'%20cy='300'%20r='104'/%3E%3Ccircle%20cx='400'%20cy='300'%20r='52'/%3E%3C/g%3E%3C/svg%3E";
        function settle(img) { img.classList.remove('hc-media-loading'); }
        function fail(img) {
            if (img.dataset.hcMediaFailed) { return; }
            img.dataset.hcMediaFailed = '1';
            var parent = img.parentElement;
            if (parent && parent.tagName === 'PICTURE') {
                var sources = parent.querySelectorAll('source');
                for (var i = 0; i < sources.length; i++) { sources[i].parentNode.removeChild(sources[i]); }
            }
            img.removeAttribute('srcset');
            img.removeAttribute('sizes');
            img.setAttribute('alt', '');
            img.setAttribute('aria-hidden', 'true');
            settle(img);
            img.classList.add('hc-media-fallback');
            img.src = FALLBACK;
        }
        document.addEventListener('error', function (event) {
            var target = event.target;
            if (target && target.tagName === 'IMG') { fail(target); }
        }, true);
        document.addEventListener('load', function (event) {
            var target = event.target;
            if (target && target.tagName === 'IMG') { settle(target); }
        }, true);
        function prime() {
            var images = document.images;
            for (var i = 0; i < images.length; i++) {
                var img = images[i];
                if (img.dataset.hcMediaFailed) { continue; }
                if (!img.complete) { img.classList.add('hc-media-loading'); continue; }
                // currentSrc bos ise tarayici henuz bir kaynak secmemistir.
                // Cok kaynakli gorsellerde img bu ara durumda complete=true
                // ve naturalWidth=0 gorunuyor; burada hata varsaymak saglam
                // gorseli yer tutucuyla degistiriyordu.
                //
                // Gercek hatalar zaten error olayiyla yakalaniyor; prime()
                // yalnizca dinleyici baglanmadan once biten yuklemeleri
                // toparliyor ve onlarda currentSrc dolu olur.
                if (img.naturalWidth === 0 && img.currentSrc) { fail(img); } else { settle(img); }
            }
        }
        if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', prime); } else { prime(); }
        document.addEventListener('livewire:navigated', prime);
    })();
    </script>
    {{-- Yapışkan başlık yüksekliğini ölç: bölüm bağlantıları hedefi çubukların altında bırakmasın. --}}
    <script>
    (function () {
        function measure() {
            var root = document.documentElement;
            var header = document.querySelector('.hc-public-header') || document.querySelector('body > header');
            var nav = document.querySelector('.hc-sticky-under-header');
            if (header) { root.style.setProperty('--hc-header-h', Math.round(header.getBoundingClientRect().height) + 'px'); }
            // Cerez seridi ekranin altina sabitleniyor ve panelin altinda
            // kalan baglanti gercekten tiklanamiyor. Serit bir engel degil
            // (sarmalayici pointer-events:none) ama panelin kapladigi alan
            // engel. Sayfanin altina serit yuksekligi kadar bosluk birakmak,
            // seridin niyetini bozmadan orten alani sifirliyor.
            var serit = document.querySelector('.hc-consent__panel');
            root.style.setProperty('--hc-consent-h', serit ? Math.round(serit.getBoundingClientRect().height) + 24 + 'px' : '0px');
            root.style.setProperty('--hc-section-nav-h', nav ? Math.round(nav.getBoundingClientRect().height) + 'px' : '0px');
        }
        function watch() {
            measure();
            if (typeof ResizeObserver === 'function') {
                var header = document.querySelector('.hc-public-header') || document.querySelector('body > header');
                if (header) { new ResizeObserver(measure).observe(header); }
            }
        }
        if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', watch); } else { watch(); }
        window.addEventListener('resize', measure);
        document.addEventListener('livewire:navigated', measure);
    })();
    </script>
    <script>
    (function () {
        // Kaydırıldıkça açılma. Başlangıç durumu buradan eklenir: betik
        // çalışmazsa hiçbir şey gizlenmez, sayfa olduğu gibi görünür.
        var azalt = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (azalt || typeof IntersectionObserver !== 'function') { return; }

        var gozlemci = new IntersectionObserver(function (girisler) {
            girisler.forEach(function (giris) {
                if (!giris.isIntersecting) { return; }
                giris.target.classList.add('is-revealed');
                gozlemci.unobserve(giris.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.06 });

        function kur() {
            document.querySelectorAll('[data-reveal-group]').forEach(function (grup) {
                var cocuklar = Array.prototype.slice.call(grup.children);
                cocuklar.forEach(function (cocuk, sira) {
                    if (cocuk.hasAttribute('data-reveal')) { return; }
                    cocuk.setAttribute('data-reveal', '');
                    // Kademeli gecikme yalnız ilk satırlar için: uzun listede
                    // sona kalan kart saniyelerce beklemesin.
                    cocuk.style.transitionDelay = Math.min(sira, 5) * 55 + 'ms';
                    gozlemci.observe(cocuk);
                });
            });
            document.querySelectorAll('[data-reveal]:not(.is-revealed)').forEach(function (el) {
                gozlemci.observe(el);
            });
        }

        // Gizleme yalnız bu sınıf varken geçerli. Betik buraya kadar gelmediyse
        // hiçbir blok gizlenmez; sayfa efektsiz ama eksiksiz görünür.
        document.documentElement.classList.add('hc-reveal-on');

        // Gözlemci beklenmedik bir nedenle tetiklenmezse içerik görünmez
        // kalmasın: kısa bir süre sonra kalan her blok açılır.
        //
        // Bu ağ her kaydırma olayında yeni bir rAF kuyruğa alıyordu ve
        // hepsiniAc tüm belgeyi sorgulayıp her öğe için getBoundingClientRect
        // çağırıyordu. 15.000 piksellik anasayfada kaydırma ana iş
        // parçacığını kilitliyordu: yüzlerce sıraya girmiş çağrı, her biri
        // zorunlu yeniden yerleşim. Şimdi kare başına en fazla bir çağrı var
        // ve iş bitince dinleyici kendini söküyor - ağın görevi zaten
        // gözlemci tetiklenmezse devreye girmek.
        var kareBekliyor = false;

        function hepsiniAc() {
            kareBekliyor = false;

            var kalan = document.querySelectorAll('[data-reveal]:not(.is-revealed)');

            for (var i = 0; i < kalan.length; i++) {
                if (kalan[i].getBoundingClientRect().top < window.innerHeight * 1.5) {
                    kalan[i].classList.add('is-revealed');
                }
            }

            if (document.querySelector('[data-reveal]:not(.is-revealed)') === null) {
                window.removeEventListener('scroll', kaydirmada);
            }
        }

        function kaydirmada() {
            if (kareBekliyor) { return; }

            kareBekliyor = true;
            window.requestAnimationFrame(hepsiniAc);
        }

        if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', kur); } else { kur(); }
        document.addEventListener('livewire:navigated', kur);
        window.addEventListener('load', function () { setTimeout(hepsiniAc, 1200); });
        window.addEventListener('scroll', kaydirmada, { passive: true });
    })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @unless($isPrivateSurface)<x-google-integrations />@endunless
</head>
<body data-alpine-owner="{{ $livewire ? 'livewire' : 'app' }}" x-data="{ dark: localStorage.theme === 'dark', menuOpen: false, adminMenuOpen: false, adminSidebarCollapsed: localStorage.hcAdminSidebarCollapsed === 'true', adminSidebarQuery: '', scrolled: false }"
    x-init="document.documentElement.classList.toggle('dark', dark); $watch('dark', value => { document.documentElement.classList.toggle('dark', value); localStorage.theme = value ? 'dark' : 'light'; window.dispatchEvent(new CustomEvent('hc-theme-changed')); }); $watch('adminSidebarCollapsed', value => localStorage.hcAdminSidebarCollapsed = value); $watch('adminMenuOpen', value => document.body.classList.toggle('overflow-hidden', value)); $watch('menuOpen', value => document.body.classList.toggle('overflow-hidden', value))"
    @keydown.escape.window="adminMenuOpen = false; menuOpen = false"
    class="min-h-screen antialiased">
    @if(!$isPrivateSurface && request()->cookie('hc_analytics_consent') === 'granted' && config('integrations.google.tag_manager.enabled') && preg_match('/^GTM-[A-Z0-9]+$/', (string) config('integrations.google.tag_manager.container_id')))
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ urlencode((string) config('integrations.google.tag_manager.container_id')) }}" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>
    @endif
    @if (request()->routeIs('admin.*'))
        <div class="admin-shell min-h-screen lg:grid" :class="adminSidebarCollapsed ? 'lg:grid-cols-[5.25rem_minmax(0,1fr)]' : 'lg:grid-cols-[17.5rem_minmax(0,1fr)]'">
            <aside class="admin-shell__sidebar fixed inset-y-0 left-0 z-50 hidden lg:block" :class="adminSidebarCollapsed ? 'w-[5.25rem]' : 'w-[17.5rem]'">
                <x-admin-sidebar />
            </aside>

            <div class="admin-shell__content min-w-0 lg:col-start-2">
                <header class="admin-topbar sticky top-0 z-30">
                    <div class="flex min-h-[4.5rem] items-center justify-between gap-3 px-4 sm:px-7">
                        <button @click="adminMenuOpen = true" class="hc-v3-btn hc-v3-btn--outline px-3 py-2 lg:hidden" aria-label="Yönetim menüsünü aç"><x-ui.icon name="menu" size="20" /></button>
                        <div class="min-w-0 flex-1">
                            <nav aria-label="Breadcrumb" class="hc-admin-breadcrumb"><span>Yönetim</span><span aria-hidden="true">/</span><span aria-current="page">{{ $title ?? 'Genel Bakış' }}</span></nav>
                            <p class="truncate text-sm font-extrabold" style="color:var(--hc-text-primary)">{{ $title ?? 'Genel Bakış' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if (Route::has('admin.recipes.create'))
                                <a href="{{ route('admin.recipes.create') }}" class="hc-v3-btn hc-v3-btn--primary hidden sm:inline-flex"><x-ui.icon name="plus" size="16" />Yeni tarif</a>
                            @endif
                            <x-locale-switcher />
                            <x-theme-switch />
                            <a href="{{ route('profile') }}" class="admin-topbar__user hidden sm:inline-flex" aria-label="Profil ayarları">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</a>
                        </div>
                    </div>
                </header>

                <div x-cloak x-show="adminMenuOpen" x-transition.opacity class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Yönetim menüsü" @click.self="adminMenuOpen = false">
                    <div class="absolute inset-0 bg-black/60" aria-hidden="true"></div>
                    <aside x-ref="adminDrawer" x-show="adminMenuOpen" x-transition:enter="transition ease-out duration-220" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-160" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" x-init="$watch('adminMenuOpen', open => { if (open) { $nextTick(() => $refs.adminDrawer.querySelector('[data-drawer-close]')?.focus()) } })" @keydown.tab.prevent="const items = [...$el.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex=\'-1\'])')].filter(item => !item.hidden); const current = items.indexOf(document.activeElement); const next = $event.shiftKey ? (current <= 0 ? items.length - 1 : current - 1) : (current === items.length - 1 ? 0 : current + 1); items[next]?.focus()" class="admin-shell__sidebar relative h-full w-[18rem] max-w-[86vw] shadow-2xl">
                        <button data-drawer-close @click="adminMenuOpen = false" class="absolute right-3 top-3 rounded-lg p-2 text-white hover:bg-white/10" aria-label="Menüyü kapat"><x-ui.icon name="close" size="20" /></button>
                        <x-admin-sidebar />
                    </aside>
                </div>

                <main class="hc-page">
                    @if (session('status'))
                        <div class="hc-alert hc-alert-success mb-6" role="status">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="hc-alert hc-alert-error mb-6" role="alert">{{ $errors->first() }}</div>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>
    @else
        <a href="#main-content" class="hc-skip-link">İçeriğe geç</a>
        {{-- Koyu açılışı olan sayfalarda hero başlığın altından başlar. Sayfa
             tepesindeyken çubuk kendi zeminini basmaz; görüntünün üstünde
             saydam durur ve içeriğini koyu zemine göre okutur. --}}
        <header @scroll.window.passive="if ((window.scrollY > 16) !== scrolled) scrolled = window.scrollY > 16" :class="{ 'is-scrolled': scrolled }" @class([
            'hc-public-header sticky top-0 z-40 transition-all duration-300',
            'hc-header-over-hero' => request()->routeIs('home', 'recipes.show'),
        ])>
            <nav :class="scrolled ? 'h-[4.25rem]' : 'h-[5.25rem]'" class="hc-container flex items-center justify-between gap-4 transition-all duration-300" aria-label="Ana menü">
                <x-brand-logo class="scale-105" />
                {{-- Menüde sekiz üst düzey bağlantı vardı ve sığmadığı için
                     "Ana / Sayfa", "Ne / Pişirebilirim?", "AI / Mutfak" diye
                     iki satıra kırılıyordu. Herkesin gittiği yerler üstte
                     kaldı; üyeye özel araçlar tek bir başlık altına indi.
                     "Ana Sayfa" kalktı çünkü logo zaten oraya götürüyor. --}}
                <div class="hidden items-center gap-7 text-sm font-bold xl:flex">
                    <a @class(['nav-link whitespace-nowrap', 'nav-link-active' => request()->routeIs('recipes.*')]) href="{{ route('recipes.index') }}" @if(request()->routeIs('recipes.*')) aria-current="page" @endif>Tarifler</a>
                    <a @class(['nav-link whitespace-nowrap', 'nav-link-active' => request()->routeIs('blog.*')]) href="{{ route('blog.index') }}" @if(request()->routeIs('blog.*')) aria-current="page" @endif>Blog</a>

                    @auth
                        @php($mutfakRotalari = collect([
                            ['ingredients.explore', 'Ne pişirebilirim?', 'Elindekilerle eşleştir'],
                            ['ai-kitchen.index', 'AI Mutfak', 'Menü planla, artanları değerlendir'],
                            ['pantry.index', 'Dolabım', 'Malzemelerini güncel tut'],
                            ['favorites.index', 'Favorilerim', 'Beğendiğin tarifler'],
                            ['collections.index', 'Koleksiyonlarım', 'Kendi gruplarını kur'],
                        ])->filter(fn ($r) => Route::has($r[0])))

                        <div class="relative" x-data="{ acik: false }" @keydown.escape.window="acik = false">
                            <button type="button" @click="acik = !acik" :aria-expanded="acik" aria-haspopup="menu"
                                    @class(['nav-link inline-flex items-center gap-1 whitespace-nowrap', 'nav-link-active' => request()->routeIs('ingredients.explore', 'ai-kitchen.*', 'pantry.*', 'favorites.*', 'collections.*')])>
                                Mutfağım
                                <span class="text-[.6rem] transition-transform duration-300" :class="acik ? 'rotate-180' : ''" aria-hidden="true">▾</span>
                            </button>

                            <div x-cloak x-show="acik" @click="acik = false" class="fixed inset-0 z-40" aria-hidden="true"></div>

                            <div x-cloak x-show="acik"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1 scale-[.97]"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 -translate-y-1 scale-[.97]"
                                 class="hc-user-menu absolute left-1/2 z-50 mt-3 w-[20rem] -translate-x-1/2 origin-top overflow-hidden rounded-3xl p-1.5"
                                 role="menu" aria-label="Mutfağım">
                                @foreach ($mutfakRotalari as [$rota, $etiket, $aciklama])
                                    <a href="{{ route($rota) }}" class="hc-user-menu__item !items-start" role="menuitem">
                                        <span class="min-w-0">
                                            <span class="block">{{ $etiket }}</span>
                                            <span class="hc-caption block font-normal" style="color: var(--hc-text-secondary)">{{ $aciklama }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endauth

                    @if (Route::has('pricing'))
                        <a @class(['nav-link whitespace-nowrap text-amber-600 dark:text-amber-400', 'nav-link-active' => request()->routeIs('pricing')]) href="{{ route('pricing') }}" @if(request()->routeIs('pricing')) aria-current="page" @endif>Premium</a>
                    @endif
                    @if (Route::has('shop.index'))
                        <a @class(['nav-link whitespace-nowrap', 'nav-link-active' => request()->routeIs('shop.*')]) href="{{ route('shop.index') }}" @if(request()->routeIs('shop.*')) aria-current="page" @endif>Mağaza</a>
                    @endif
                </div>

                <div class="hidden items-center gap-2 xl:flex">
                    <x-locale-switcher />
                    <x-theme-switch class="hc-nav-control" />
                    @auth
                        @if (auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}" class="hc-btn hc-btn-outline hc-nav-control">Yönetim</a>@endif
                        <x-user-menu />
                    @else
                        <a href="{{ route('login') }}" class="hc-btn hc-btn-outline hc-nav-control">Giriş Yap</a>
                        <a href="{{ route('register') }}" class="hc-btn hc-btn-gold hc-nav-control">Üye Ol</a>
                    @endauth
                </div>
                <div class="flex items-center gap-2 xl:hidden">
                    {{-- 375px'te logo + dil + tema + menu toplami 404px ediyordu:
                         menu dugmesi ekranin disinda kaliyor ve sayfa yatay
                         kaymadigi icin hata gorunmuyordu. Telefondan hicbir
                         gezinme baglantisina ulasilamiyordu. Dar ekranda dil
                         secici cubuktan cikip cekmeceye giriyor. --}}
                    <div class="hidden sm:block"><x-locale-switcher /></div>
                    <x-theme-switch class="hc-nav-control" />
                    <button @click="menuOpen = !menuOpen" class="grid h-10 w-10 place-items-center rounded-xl bg-charcoal-900 text-white" :aria-expanded="menuOpen" aria-controls="mobile-nav" aria-label="Menüyü aç"><x-ui.icon name="menu" size="20" /></button>
                </div>
            </nav>
            {{-- Çekmece kendini modal ilan ediyor; o hâlde arkası gerçekten
                 kararmalı ve sayfa altında kaymamalı. Perdeye dokunmak kapatır. --}}
            <div x-cloak x-show="menuOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="menuOpen = false" class="hc-drawer-scrim fixed inset-0 -z-10 xl:hidden" aria-hidden="true"></div>
            {{-- Giriş aşağı kayarak olur; çıkış aynı yolu geri izler. --}}
            <div id="mobile-nav" x-cloak x-show="menuOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-y-3 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="-translate-y-3 opacity-0" @keydown.escape.window="menuOpen = false" x-init="$watch('menuOpen', open => { if (open) { $nextTick(() => $refs.publicDrawer.querySelector('[data-public-drawer-close]')?.focus()) } })" @keydown.tab.prevent="const items = [...$el.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex=\'-1\'])')].filter(item => !item.hidden); const current = items.indexOf(document.activeElement); const next = $event.shiftKey ? (current <= 0 ? items.length - 1 : current - 1) : (current === items.length - 1 ? 0 : current + 1); items[next]?.focus()" class="hc-mobile-drawer border-t px-5 py-5 shadow-xl xl:hidden" role="dialog" aria-modal="true" aria-label="Ana menü">
                <div x-ref="publicDrawer" class="mx-auto max-w-7xl">
                    <div class="mb-3 flex justify-end"><button data-public-drawer-close @click="menuOpen = false" class="hc-icon-button !h-10 !w-10 !rounded-xl" aria-label="Menüyü kapat"><x-ui.icon name="close" size="18" /></button></div>
                <div class="grid gap-2 text-sm font-semibold">
                    <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('home')]) href="{{ route('home') }}" @click="menuOpen = false" @if(request()->routeIs('home')) aria-current="page" @endif>Ana Sayfa</a>
                    <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('recipes.*')]) href="{{ route('recipes.index') }}" @click="menuOpen = false" @if(request()->routeIs('recipes.*')) aria-current="page" @endif>Tarifler</a>
                    <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('blog.*')]) href="{{ route('blog.index') }}" @click="menuOpen = false" @if(request()->routeIs('blog.*')) aria-current="page" @endif>Blog</a>
                    @if (Route::has('pricing'))
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('pricing'), 'text-amber-600 dark:text-amber-400']) href="{{ route('pricing') }}" @click="menuOpen = false" @if(request()->routeIs('pricing')) aria-current="page" @endif>👑 Premium</a>
                    @endif
                    {{-- Mağaza masaüstü menüsünde vardı, çekmecede yoktu: telefondan
                         gelen ziyaretçi dijital ürünleri hiç göremiyordu. --}}
                    @if (Route::has('shop.index'))
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('shop.*')]) href="{{ route('shop.index') }}" @click="menuOpen = false" @if(request()->routeIs('shop.*')) aria-current="page" @endif>Mağaza</a>
                    @endif
                    @auth
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('ingredients.explore')]) href="{{ route('ingredients.explore') }}" @click="menuOpen = false" @if(request()->routeIs('ingredients.explore')) aria-current="page" @endif>Ne Pişirebilirim?</a>
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('ai-kitchen.*')]) href="{{ route('ai-kitchen.index') }}" @click="menuOpen = false" @if(request()->routeIs('ai-kitchen.*')) aria-current="page" @endif>AI Mutfak</a>
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('pantry.*')]) href="{{ route('pantry.index') }}" @click="menuOpen = false" @if(request()->routeIs('pantry.*')) aria-current="page" @endif>Dolabım</a>
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('favorites.*')]) href="{{ route('favorites.index') }}" @click="menuOpen = false" @if(request()->routeIs('favorites.*')) aria-current="page" @endif>Favoriler</a>
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('dashboard', 'profile')]) href="{{ route('dashboard') }}" @click="menuOpen = false" @if(request()->routeIs('dashboard', 'profile')) aria-current="page" @endif>Hesabım</a>
                        @if(auth()->user()->isAdmin())<a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('admin.*')]) href="{{ route('admin.dashboard') }}" @click="menuOpen = false" @if(request()->routeIs('admin.*')) aria-current="page" @endif>Yönetim</a>@endif
                        <form method="post" action="{{ route('logout') }}">@csrf<button class="mobile-nav-link w-full text-left" type="submit">Çıkış yap</button></form>
                    @else
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('login')]) href="{{ route('login') }}" @click="menuOpen = false" @if(request()->routeIs('login')) aria-current="page" @endif>Giriş Yap</a>
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('register'), 'text-gold-700 dark:text-gold-400']) href="{{ route('register') }}" @click="menuOpen = false" @if(request()->routeIs('register')) aria-current="page" @endif>Üye Ol</a>
                    @endauth
                </div>

                {{-- Cubuktan cikan dil secicinin karsiligi: dar ekranda tek
                     dil kontrolu burasi. --}}
                <div class="mt-5 border-t pt-5 sm:hidden">
                    <p class="hc-caption mb-3 text-charcoal-700 dark:text-charcoal-300">Dil</p>
                    <x-locale-switcher />
                </div>
                </div>
            </div>
        </header>

        <main id="main-content" tabindex="-1">
            @if (session('status'))
                <div class="hc-container pt-5"><div class="hc-alert hc-alert-success" role="status">{{ session('status') }}</div></div>
            @endif
            @if (isset($errors) && $errors->any())
                <div class="hc-container pt-5"><div class="hc-alert hc-alert-error" role="alert">{{ $errors->first() }}</div></div>
            @endif
            {{ $slot }}
        </main>

        <footer class="hc-footer mt-20">
            <div class="hc-container grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-[1.3fr_.75fr_.85fr_.85fr_1fr]">
                <div><x-brand-logo variant="dark" /><p class="hc-footer-muted mt-5 max-w-sm text-sm leading-7">Kıbrıs sıcaklığını modern mutfak deneyimiyle bir araya getiriyoruz. Her tarif, daha keyifli bir ev sofrası için.</p><p class="hc-footer-muted mt-6 text-xs leading-5">Sosyal kanallar yalnız doğrulanmış kurumsal hesaplar yayına alındığında burada yer alır.</p></div>
                <div><p class="text-sm font-extrabold text-orange-300">Keşfet</p><div class="hc-footer-muted mt-4 grid gap-3 text-sm [&>a]:inline-flex [&>a]:min-h-[24px] [&>a]:items-center"><a class="hover:text-orange-300" href="{{ route('recipes.index') }}">Tarifler</a><a class="hover:text-orange-300" href="{{ route('blog.index') }}">Blog</a><a class="hover:text-orange-300" href="{{ route('cuisines.index') }}">Mutfaklar</a><a class="hover:text-orange-300" href="{{ route('categories.index') }}">Kategoriler</a><a class="hover:text-orange-300" href="{{ route('ingredients.hub.index') }}">Malzemeler</a><a class="hover:text-orange-300" href="{{ route('collections.sure.index') }}">Süreye göre</a><a class="hover:text-orange-300" href="{{ route('collections.zorluk.index') }}">Zorluğa göre</a><a class="hover:text-orange-300" href="{{ route('ingredients.explore') }}">Ne Pişirebilirim?</a>@auth<a class="hover:text-orange-300" href="{{ route('pantry.index') }}">Malzeme Dolabım</a>@endauth</div></div>
                {{-- On bir baglanti tek dar sutunun icinde iki sutuna sikistirilmisti;
                     "Sik sorulan / sorular" ve "Yasal / bilgilendirme" diye
                     kiriliyordu. Iki ayri baslik, her biri tek sutun. --}}
                <div>
                    <p class="text-sm font-extrabold text-orange-300">Platform</p>
                    <div class="hc-footer-muted mt-4 grid gap-3 text-sm [&>a]:inline-flex [&>a]:min-h-[24px] [&>a]:items-center">
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'hakkimizda') }}">Hakkımızda</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'nasil-calisir') }}">Nasıl çalışır?</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'sss') }}">Sıkça sorulanlar</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'tarif-dogrulama-politikasi') }}">Tarif doğrulama</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'editorial-ilkeler') }}">Editoryal ilkeler</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'hata-bildir') }}">Hata bildir</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('contact.index') }}">İletişim</a>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-extrabold text-orange-300">Yasal</p>
                    <div class="hc-footer-muted mt-4 grid gap-3 text-sm [&>a]:inline-flex [&>a]:min-h-[24px] [&>a]:items-center">
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('legal.center') }}#gizlilik-ve-kisisel-veriler">Gizlilik</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('legal.center') }}#kullanim-kosullari">Kullanım koşulları</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('legal.center') }}#cerez-politikasi">Çerezler</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('legal.center') }}#gorsel-ve-yapay-zeka-politikasi">Görsel politikası</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('legal.center') }}#icerik-ve-kaynak-politikasi">Kaynak politikası</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'besin-degeri-aciklamasi') }}">Besin değeri açıklaması</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'alerjen-uyarisi') }}">Alerjen uyarısı</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'gorsel-ve-medya-politikasi') }}">Görsel ve medya</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'icerik-duzeltme-politikasi') }}">İçerik düzeltme</a>
                    </div>
                </div>
                <div><p class="text-sm font-extrabold text-orange-300">Bülten</p><p class="hc-footer-muted mt-4 text-sm leading-6">Yeni tarifler ve mutfak ilhamı için bizi takipte kalın.</p><a href="{{ route('register') }}" class="hc-btn hc-btn-primary mt-5">Ücretsiz üye ol</a></div>
            </div>
            <div class="border-t border-white/10"><div class="hc-container hc-footer-muted flex flex-col gap-2 py-5 text-xs sm:flex-row sm:items-center sm:justify-between"><span>© {{ date('Y') }} House Chef Cyprus</span><span>Özgün editoryal içerik · Gizlilik odaklı platform</span></div></div>
        </footer>
        <x-analytics-consent />
    @endif
    {{-- @livewireScripts kosulsuzdu: her genel sayfa 161 KB Livewire
         betigini indirip calistiriyordu, oysa anasayfada, tarif detayinda ve
         blogda tek bir wire:id yok. Bu layout'u kullanan sayfalardan yalniz
         profile.blade.php Livewire bileseni bariniyor.

         Ilk denemem @stack/@push'tu ve tam test paketinde sizdi: profil
         sayfasi yiginına ittigi icerik ayni surecte sonraki isteklerde de
         basiliyordu. Uretimde LiteSpeed sureci yeniden kullaniyor, yani bu
         yalniz bir test tuhafligi degildi. Prop paylasilan durum tasimiyor. --}}
    @if ($livewire)
        @livewireScripts
    @endif
</body>
</html>
