@props(['title' => 'House Chef Cyprus', 'metaDescription' => "Kıbrıs'tan ilham alan tarifler, malzeme dolabı ve dengeli beslenme rehberi.", 'structuredData' => null, 'canonicalUrl' => null, 'socialImage' => null, 'socialImageWidth' => null, 'socialImageHeight' => null, 'socialImageAlt' => null, 'robots' => null, 'livewire' => false, 'seoContent' => null])
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
    @foreach (app(\App\Services\Seo\LocaleUrlService::class)->alternates(request(), $seoContent) as $alternatif)
        <link rel="alternate" hreflang="{{ $alternatif['hreflang'] }}" href="{{ $alternatif['href'] }}">
    @endforeach
    @php($isPrivateSurface = request()->routeIs('admin.*', 'login', 'register', 'password.*', 'verification.*', 'profile', 'dashboard'))
    @if ($robots || $isPrivateSurface)
        @php($effectiveRobots = $isPrivateSurface || str_contains((string) $robots, 'noindex') ? ($robots ?: 'noindex,nofollow') : ($robots ? $robots.', max-image-preview:large' : 'index,follow, max-image-preview:large'))
        <meta name="robots" content="{{ $effectiveRobots }}">
    @endif
    @unless ($isPrivateSurface || str_contains((string) $robots, 'noindex'))
        <meta name="geo.region" content="CY">
        <meta name="geo.placename" content="Cyprus">
        <meta name="geo.position" content="35.1856;33.3823">
        <meta name="ICBM" content="35.1856, 33.3823">
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
        <link rel="alternate" type="application/rss+xml" title="House Chef Cyprus (Türkçe)" href="{{ route('feed') }}">
        <link rel="alternate" type="application/rss+xml" title="House Chef Cyprus (English)" href="{{ route('feed.en') }}">
        <link rel="search" type="application/opensearchdescription+xml" title="House Chef Cyprus (TR)" href="{{ route('opensearch') }}">
        <link rel="search" type="application/opensearchdescription+xml" title="House Chef Cyprus (EN)" href="{{ route('opensearch.en') }}">
        <link rel="llms-txt" type="text/markdown" title="House Chef Cyprus AI LLM Corpus (TR)" href="{{ route('llms.txt') }}">
        <link rel="llms-txt-en" type="text/markdown" title="House Chef Cyprus AI LLM Corpus (EN)" href="{{ route('llms.txt.en') }}">
        @if($socialImageAlt)<meta name="twitter:image:alt" content="{{ $socialImageAlt }}">@endif
    @endunless
    @if($structuredData)<script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script>@endif
    @unless($isPrivateSurface || str_contains((string) $robots, 'noindex'))
        @php($existingSchemaTypes = collect($structuredData['@graph'] ?? ($structuredData ? [$structuredData] : []))->pluck('@type')->all())
        @php($siteSchema = app(\App\Services\Seo\SeoSchemaService::class)->webSite())
        @php($siteSchema['@graph'] = array_values(array_filter($siteSchema['@graph'], static fn (array $node): bool => ! in_array($node['@type'], $existingSchemaTypes, true))))
        @if($siteSchema['@graph'] !== [])
            <script type="application/ld+json">{!! json_encode($siteSchema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script>
        @endif
    @endunless
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
    <link rel="preload" as="style" href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&family=inter:400,500,600,700&display=swap">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&family=inter:400,500,600,700&display=swap" rel="stylesheet" media="print" onload="this.media='all'" />
    <noscript>
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&family=inter:400,500,600,700&display=swap" />
    </noscript>
    <style>
        /* Responsive CSS media queries for mobile/tablet/desktop */
        @media (max-width: 639px) { .hc-hide-mobile { display: none !important; } }
        @media (min-width: 640px) and (max-width: 1023px) { .hc-hide-tablet { display: none !important; } }
        @media (min-width: 1024px) { .hc-hide-desktop { display: none !important; } }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

        // Emniyet ağı: Gözlemci beklenmedik bir nedenle tetiklenmezse
        // içerik görünmez kalmasın; yüklemeden kısa bir süre sonra kalan her blok açılır.
        // Kaydırma sırasında getBoundingClientRect çağrılmaz, forced reflow ve scroll kasması önlenir.
        function emniyetAc() {
            var kalan = document.querySelectorAll('[data-reveal]:not(.is-revealed)');
            for (var i = 0; i < kalan.length; i++) {
                kalan[i].classList.add('is-revealed');
            }
        }

        if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', kur); } else { kur(); }
        document.addEventListener('livewire:navigated', kur);
        window.addEventListener('load', function () { setTimeout(emniyetAc, 1500); });

        // Yapiskan baslik durumu artik kaydirma olayina bagli degil.
        //
        // Onceki hali her kaydirma olayinda Alpine ifadesini calistiriyordu.
        // Otomasyonda sayfa kaydirildiktan sonra pencereye hicbir kaydirma
        // olayi ulasmiyordu ve baslik hero ustu saydam stilinde kaliyordu:
        // acik zeminin ustunde beyaz yazi, okunmuyordu.
        //
        // Gozcu ogesi bu sinifi tamamen kaldiriyor. Tarayici kesisimi kendisi
        // hesapliyor; olay dinleyicisi, es zamanli yerlesim okumasi ve kare
        // basina is yok. Kaydirma kabinin pencere olup olmamasi da onemsiz.
        function gozcuyuKur() {
            var gozcu = document.createElement('div');
            gozcu.setAttribute('aria-hidden', 'true');
            gozcu.style.cssText = 'position:absolute;top:0;left:0;width:1px;height:48px;pointer-events:none';
            document.body.prepend(gozcu);

            var basligiGuncelle = function (kaydirildi) {
                var govde = document.body;

                if (window.Alpine && govde._x_dataStack) {
                    window.Alpine.$data(govde).scrolled = kaydirildi;

                    return;
                }

                // Alpine henuz baslamadiysa sinifi dogrudan basiyoruz;
                // baslik saydam kalmaktansa erken dogru gorunsun.
                var baslik = document.querySelector('header.hc-public-header');

                if (baslik) { baslik.classList.toggle('is-scrolled', kaydirildi); }
            };

            new IntersectionObserver(function (girisler) {
                basligiGuncelle(!girisler[0].isIntersecting);
            }, { threshold: 0 }).observe(gozcu);
        }

        // Bu betik <head> icinde kosuyor: document.body henuz yok. Ilk
        // yazisimda bunu atladim ve gozcu hic eklenmedi, baslik da saydam
        // kaldi.
        if (document.body) {
            gozcuyuKur();
        } else {
            document.addEventListener('DOMContentLoaded', gozcuyuKur, { once: true });
        }
    })();
    </script>
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
        <a href="#admin-main-content" class="hc-skip-link">İçeriğe geç</a>
        {{-- Izgara sutunlari ve kenar cubugu genisligi yalniz Alpine
             baglamasindan geliyordu. Alpine calismadigi anda sutun tanimi hic
             olusmuyor, sabit konumlu kenar cubugu da icerigin ustune biniyordu:
             panel okunamaz hale geliyordu. Bunu yasadik.

             Acik hal artik statik siniftan geliyor; Alpine yalnizca daraltilmis
             hali ekliyor. Alpine gec kalirsa ya da hic gelmezse panel dogru
             genislikte, kullanilabilir duruyor. --}}
        <div class="admin-shell min-h-screen lg:grid lg:grid-cols-[17.5rem_minmax(0,1fr)]" :class="adminSidebarCollapsed && 'lg:!grid-cols-[5.25rem_minmax(0,1fr)]'">
            <aside class="admin-shell__sidebar fixed inset-y-0 left-0 z-50 hidden w-[17.5rem] lg:block" :class="adminSidebarCollapsed && '!w-[5.25rem]'">
                <x-admin-sidebar />
            </aside>

            <div class="admin-shell__content min-w-0 lg:col-start-2">
                <header class="admin-topbar sticky top-0 z-30">
                    <div class="flex min-h-[4.5rem] items-center justify-between gap-3 px-4 sm:px-7">
                        <button x-ref="adminMenuTrigger" @click="adminMenuOpen = true" :aria-expanded="adminMenuOpen" aria-controls="admin-mobile-menu" class="hc-v3-btn hc-v3-btn--outline px-3 py-2 lg:hidden" aria-label="Yönetim menüsünü aç"><x-ui.icon name="menu" size="20" /></button>
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

                <div id="admin-mobile-menu" x-cloak x-show="adminMenuOpen" x-transition.opacity class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Yönetim menüsü" @click.self="adminMenuOpen = false">
                    <div class="absolute inset-0 bg-black/60" @click="adminMenuOpen = false" aria-hidden="true"></div>
                    <aside x-data="{ adminSidebarCollapsed: false }" x-ref="adminDrawer" x-show="adminMenuOpen" x-transition:enter="transition ease-out duration-220" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-160" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" x-init="$watch('adminMenuOpen', open => { $nextTick(() => open ? $refs.adminDrawer.querySelector('[data-drawer-close]')?.focus() : $refs.adminMenuTrigger?.focus()) })" @keydown.tab.prevent="const items = [...$el.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex=\'-1\'])')].filter(item => item.getClientRects().length > 0 && getComputedStyle(item).visibility !== 'hidden'); const current = items.indexOf(document.activeElement); const next = $event.shiftKey ? (current <= 0 ? items.length - 1 : current - 1) : (current === items.length - 1 ? 0 : current + 1); items[next]?.focus()" class="admin-shell__sidebar relative h-full w-[18rem] max-w-[86vw] shadow-2xl">
                        <button data-drawer-close @click="adminMenuOpen = false" class="absolute right-3 top-3 rounded-lg p-2 text-white hover:bg-white/10" aria-label="Menüyü kapat"><x-ui.icon name="close" size="20" /></button>
                        <x-admin-sidebar mobile />
                    </aside>
                </div>

                <main id="admin-main-content" tabindex="-1" class="hc-page">
                    <x-admin.workspace-tabs />
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
        @include('layouts.partials.public-header')

        <main id="main-content" tabindex="-1">
            @php($isHeroPage = request()->routeIs('home', 'recipes.show'))
            @if (session('status'))
                <div @class([
                    'hc-container pt-5' => ! $isHeroPage,
                    'hc-container pointer-events-none fixed inset-x-0 top-[var(--hc-header-h)] z-50 pt-4' => $isHeroPage,
                ])>
                    <div class="hc-alert hc-alert-success pointer-events-auto shadow-lg" role="status">{{ session('status') }}</div>
                </div>
            @endif
            @if (isset($errors) && $errors->any())
                <div @class([
                    'hc-container pt-5' => ! $isHeroPage,
                    'hc-container pointer-events-none fixed inset-x-0 top-[var(--hc-header-h)] z-50 pt-4' => $isHeroPage,
                ])>
                    <div class="hc-alert hc-alert-error pointer-events-auto shadow-lg" role="alert">{{ $errors->first() }}</div>
                </div>
            @endif
            {{ $slot }}
        </main>

        @include('layouts.partials.public-footer')
        <x-analytics-consent />
        <x-floating-quick-quote />
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
