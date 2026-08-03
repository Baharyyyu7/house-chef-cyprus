@props(['title' => 'House Chef Cyprus', 'metaDescription' => "Kıbrıs'tan ilham alan tarifler, malzeme dolabı ve dengeli beslenme rehberi.", 'structuredData' => null, 'canonicalUrl' => null, 'socialImage' => null, 'socialImageWidth' => null, 'socialImageHeight' => null, 'socialImageAlt' => null, 'robots' => null])
<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#ff6b00">
    <meta name="description" content="{{ $metaDescription }}">
    @if(app()->environment('production') && filled(config('integrations.google.site_verification')))
        <meta name="google-site-verification" content="{{ config('integrations.google.site_verification') }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl ?: url()->current() }}">
    @php($isPrivateSurface = request()->routeIs('admin.*', 'login', 'register', 'password.*', 'verification.*', 'profile', 'dashboard'))
    @if ($robots || $isPrivateSurface)
        <meta name="robots" content="{{ $robots ?: 'noindex,nofollow' }}">
    @endif
    @unless ($isPrivateSurface || str_contains((string) $robots, 'noindex'))
        <meta property="og:type" content="{{ request()->routeIs('blog.show') ? 'article' : 'website' }}">
        <meta property="og:locale" content="tr_TR">
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
        <script type="application/ld+json">{"\u0040context":"https://schema.org","\u0040graph":[{"\u0040type":"Organization","name":"House Chef Cyprus","url":"{{ url('/') }}","logo":"{{ url(config('brand.assets.mark_light')) }}"},{"\u0040type":"WebSite","name":"House Chef Cyprus","url":"{{ url('/') }}","inLanguage":"tr","publisher":{"\u0040type":"Organization","name":"House Chef Cyprus","logo":"{{ url(config('brand.assets.mark_light')) }}"},"potentialAction":{"\u0040type":"SearchAction","target":"{{ route('recipes.index') }}?q={search_term_string}","query-input":"required name=search_term_string"}}]}</script>
    @endif
    <link rel="icon" type="image/svg+xml" href="{{ asset(config('brand.assets.favicon')) }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset(config('brand.assets.favicon_32')) }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset(config('brand.assets.apple_touch_icon')) }}">
    <link rel="manifest" href="{{ asset('brand/site.webmanifest') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <title>{{ $title }}</title>
    <script>if (localStorage.theme === 'dark') document.documentElement.classList.add('dark');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @unless($isPrivateSurface)<x-google-integrations />@endunless
</head>
<body x-data="{ dark: localStorage.theme === 'dark', menuOpen: false, adminMenuOpen: false, adminSidebarCollapsed: false, adminSidebarQuery: '', scrolled: false }"
    x-init="document.documentElement.classList.toggle('dark', dark); $watch('dark', value => { document.documentElement.classList.toggle('dark', value); localStorage.theme = value ? 'dark' : 'light'; window.dispatchEvent(new CustomEvent('hc-theme-changed')); }); $watch('adminMenuOpen', value => document.body.classList.toggle('overflow-hidden', value))"
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
                            <button @click="dark = !dark" class="hc-v3-btn hc-v3-btn--outline px-3 py-2" :aria-label="dark ? 'Açık temaya geç' : 'Koyu temaya geç'"><span x-show="dark"><x-ui.icon name="sun" size="18" /></span><span x-show="!dark"><x-ui.icon name="moon" size="18" /></span></button>
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
        <header @scroll.window="scrolled = window.scrollY > 16" :class="{ 'is-scrolled': scrolled }" class="hc-public-header sticky top-0 z-40 border-b backdrop-blur-xl transition-all duration-300">
            <nav :class="scrolled ? 'h-[4.25rem]' : 'h-[5.25rem]'" class="hc-container flex items-center justify-between gap-4 transition-all duration-300" aria-label="Ana menü">
                <x-brand-logo class="scale-105" />
                <div class="hidden items-center gap-6 text-sm font-bold xl:flex">
                    <a @class(['nav-link', 'nav-link-active' => request()->routeIs('home')]) href="{{ route('home') }}">Ana Sayfa</a>
                    <a @class(['nav-link', 'nav-link-active' => request()->routeIs('recipes.*')]) href="{{ route('recipes.index') }}">Tarifler</a>
                    <a @class(['nav-link', 'nav-link-active' => request()->routeIs('blog.*')]) href="{{ route('blog.index') }}">Blog</a>
                    @auth
                        <a @class(['nav-link', 'nav-link-active' => request()->routeIs('ingredients.explore')]) href="{{ route('ingredients.explore') }}">Ne Pişirebilirim?</a>
                        <a @class(['nav-link', 'nav-link-active' => request()->routeIs('ai-kitchen.*')]) href="{{ route('ai-kitchen.index') }}">AI Mutfak</a>
                        <a @class(['nav-link', 'nav-link-active' => request()->routeIs('pantry.*')]) href="{{ route('pantry.index') }}">Dolabım</a>
                    @endauth
                </div>
                <div class="hidden items-center gap-2 xl:flex">
                    <button @click="dark = !dark" class="hc-theme-button" :aria-label="dark ? 'Açık temaya geç' : 'Koyu temaya geç'" :title="dark ? 'Açık temaya geç' : 'Koyu temaya geç'"><span x-show="dark"><x-ui.icon name="sun" size="20" /></span><span x-show="!dark"><x-ui.icon name="moon" size="20" /></span></button>
                    @auth
                        <a href="{{ route('favorites.index') }}" class="hc-icon-button" aria-label="Favoriler"><x-ui.icon name="heart" size="20" /></a>
                        @if (auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}" class="hc-btn hc-btn-outline">Yönetim</a>@endif
                        <a href="{{ route('dashboard') }}" class="inline-flex h-11 items-center gap-2 rounded-2xl bg-charcoal-900 py-2 pl-2 pr-4 text-sm font-extrabold text-white transition hover:bg-orange-600"><span class="grid h-7 w-7 place-items-center rounded-xl bg-orange-500 text-xs">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span> Hesabım</a>
                    @else
                        <a href="{{ route('login') }}" class="hc-btn hc-btn-outline">Giriş Yap</a>
                        <a href="{{ route('register') }}" class="hc-btn hc-btn-gold">Üye Ol</a>
                    @endauth
                </div>
                <div class="flex items-center gap-2 xl:hidden">
                    <button @click="dark = !dark" class="hc-theme-button !h-10 !w-10 !rounded-xl" :aria-label="dark ? 'Açık temaya geç' : 'Koyu temaya geç'" :title="dark ? 'Açık temaya geç' : 'Koyu temaya geç'"><span x-show="dark"><x-ui.icon name="sun" size="18" /></span><span x-show="!dark"><x-ui.icon name="moon" size="18" /></span></button>
                    <button @click="menuOpen = !menuOpen" class="grid h-10 w-10 place-items-center rounded-xl bg-charcoal-900 text-white" :aria-expanded="menuOpen" aria-controls="mobile-nav" aria-label="Menüyü aç"><x-ui.icon name="menu" size="20" /></button>
                </div>
            </nav>
            <div id="mobile-nav" x-cloak x-show="menuOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-init="$watch('menuOpen', open => { if (open) { $nextTick(() => $refs.publicDrawer.querySelector('[data-public-drawer-close]')?.focus()) } })" @keydown.tab.prevent="const items = [...$el.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex=\'-1\'])')].filter(item => !item.hidden); const current = items.indexOf(document.activeElement); const next = $event.shiftKey ? (current <= 0 ? items.length - 1 : current - 1) : (current === items.length - 1 ? 0 : current + 1); items[next]?.focus()" class="hc-mobile-drawer border-t px-5 py-5 shadow-xl xl:hidden" role="dialog" aria-modal="true" aria-label="Ana menü">
                <div x-ref="publicDrawer" class="mx-auto max-w-7xl">
                    <div class="mb-3 flex justify-end"><button data-public-drawer-close @click="menuOpen = false" class="hc-icon-button !h-10 !w-10 !rounded-xl" aria-label="Menüyü kapat"><x-ui.icon name="close" size="18" /></button></div>
                <div class="grid gap-2 text-sm font-semibold">
                    <a class="mobile-nav-link" href="{{ route('home') }}">Ana Sayfa</a>
                    <a class="mobile-nav-link" href="{{ route('recipes.index') }}">Tarifler</a>
                    <a class="mobile-nav-link" href="{{ route('blog.index') }}">Blog</a>
                    @auth
                        <a class="mobile-nav-link" href="{{ route('ingredients.explore') }}">Ne Pişirebilirim?</a>
                        <a class="mobile-nav-link" href="{{ route('ai-kitchen.index') }}">AI Mutfak</a>
                        <a class="mobile-nav-link" href="{{ route('pantry.index') }}">Dolabım</a>
                        <a class="mobile-nav-link" href="{{ route('favorites.index') }}">Favoriler</a>
                        <a class="mobile-nav-link" href="{{ route('dashboard') }}">Hesabım</a>
                        @if(auth()->user()->isAdmin())<a class="mobile-nav-link" href="{{ route('admin.dashboard') }}">Yönetim</a>@endif
                        <form method="post" action="{{ route('logout') }}">@csrf<button class="mobile-nav-link w-full text-left" type="submit">Çıkış yap</button></form>
                    @else
                        <a class="mobile-nav-link" href="{{ route('login') }}">Giriş Yap</a>
                        <a class="mobile-nav-link text-gold-700" href="{{ route('register') }}">Üye Ol</a>
                    @endauth
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
            <div class="hc-container grid gap-10 py-14 md:grid-cols-[1.35fr_.8fr_.8fr_1fr]">
                <div><x-brand-logo variant="dark" /><p class="hc-footer-muted mt-5 max-w-sm text-sm leading-7">Kıbrıs sıcaklığını modern mutfak deneyimiyle bir araya getiriyoruz. Her tarif, daha keyifli bir ev sofrası için.</p><p class="hc-footer-muted mt-6 text-xs leading-5">Sosyal kanallar yalnız doğrulanmış kurumsal hesaplar yayına alındığında burada yer alır.</p></div>
                <div><p class="text-sm font-extrabold text-orange-300">Keşfet</p><div class="hc-footer-muted mt-4 grid gap-3 text-sm"><a class="hover:text-orange-300" href="{{ route('recipes.index') }}">Tarifler</a><a class="hover:text-orange-300" href="{{ route('blog.index') }}">Blog</a>@auth<a class="hover:text-orange-300" href="{{ route('ingredients.explore') }}">Ne Pişirebilirim?</a><a class="hover:text-orange-300" href="{{ route('pantry.index') }}">Malzeme Dolabım</a>@endauth</div></div>
                <div><p class="text-sm font-extrabold text-orange-300">Platform</p><div class="hc-footer-muted mt-4 grid gap-3 text-sm"><a class="hover:text-orange-300" href="{{ route('information.show', 'hakkimizda') }}">Hakkımızda</a><a class="hover:text-orange-300" href="{{ route('information.show', 'nasil-calisir') }}">Nasıl çalışır?</a><a class="hover:text-orange-300" href="{{ route('information.show', 'sss') }}">Sık sorulan sorular</a><a class="hover:text-orange-300" href="{{ route('information.show', 'tarif-dogrulama-politikasi') }}">Tarif doğrulama</a><a class="hover:text-orange-300" href="{{ route('legal.center') }}">Yasal bilgilendirme</a><a class="hover:text-orange-300" href="{{ route('legal.center') }}#gorsel-ve-yapay-zeka-politikasi">Görsel ve medya</a><a class="hover:text-orange-300" href="{{ route('legal.center') }}#icerik-ve-kaynak-politikasi">Kaynak politikası</a><a class="hover:text-orange-300" href="{{ route('legal.center') }}#gizlilik-ve-kisisel-veriler">Gizlilik</a><a class="hover:text-orange-300" href="{{ route('legal.center') }}#kullanim-kosullari">Koşullar</a><a class="hover:text-orange-300" href="{{ route('legal.center') }}#cerez-politikasi">Çerezler</a><a class="hover:text-orange-300" href="{{ route('legal.center') }}#iletisim-ve-geri-bildirim">İletişim</a></div></div>
                <div><p class="text-sm font-extrabold text-orange-300">Bülten</p><p class="hc-footer-muted mt-4 text-sm leading-6">Yeni tarifler ve mutfak ilhamı için bizi takipte kalın.</p><a href="{{ route('register') }}" class="hc-btn hc-btn-primary mt-5">Ücretsiz üye ol</a></div>
            </div>
            <div class="border-t border-white/10"><div class="hc-container hc-footer-muted flex flex-col gap-2 py-5 text-xs sm:flex-row sm:items-center sm:justify-between"><span>© {{ date('Y') }} House Chef Cyprus</span><span>Özgün editoryal içerik · Gizlilik odaklı platform</span></div></div>
        </footer>
        <x-analytics-consent />
    @endif
    @livewireScripts
</body>
</html>
