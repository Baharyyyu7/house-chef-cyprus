<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#153224">
    <meta name="description" content="Kıbrıs'tan ilham alan tarifler, malzeme dolabı ve dengeli beslenme rehberi.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=playfair-display:500,600,700&display=swap" rel="stylesheet" />
    <title>{{ $title ?? 'House Chef Cyprus' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ dark: localStorage.theme === 'dark', menuOpen: false, adminMenuOpen: false }" x-init="document.documentElement.classList.toggle('dark', dark); $watch('dark', value => { document.documentElement.classList.toggle('dark', value); localStorage.theme = value ? 'dark' : 'light' })" class="min-h-screen antialiased">
    @if (request()->routeIs('admin.*'))
        <div class="min-h-screen bg-paper dark:bg-stone-950 lg:grid lg:grid-cols-[17.5rem_minmax(0,1fr)]">
            <aside class="fixed inset-y-0 left-0 z-50 hidden w-[17.5rem] bg-forest-900 lg:block">
                <x-admin-sidebar />
            </aside>

            <div class="lg:col-start-2">
                <header class="sticky top-0 z-30 border-b border-stone-200/80 bg-paper/95 backdrop-blur dark:border-stone-800 dark:bg-stone-950/95">
                    <div class="flex h-17 items-center justify-between px-4 sm:px-7">
                        <button @click="adminMenuOpen = true" class="hc-btn hc-btn-outline px-3 py-2 lg:hidden" aria-label="Yönetim menüsünü aç">☰</button>
                        <div>
                            <p class="hc-kicker">House Chef Cyprus</p>
                            <p class="text-sm font-semibold text-forest-900 dark:text-cream">Yönetim merkezi</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="dark = !dark" class="hc-btn hc-btn-outline px-3 py-2" :aria-label="dark ? 'Açık temaya geç' : 'Koyu temaya geç'" x-text="dark ? '☀' : '☾'"></button>
                            <a href="{{ route('profile') }}" class="hidden text-sm font-semibold text-forest-800 hover:text-gold-600 sm:inline">{{ auth()->user()->name }}</a>
                        </div>
                    </div>
                </header>

                <div x-cloak x-show="adminMenuOpen" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Yönetim menüsü">
                    <div @click="adminMenuOpen = false" class="absolute inset-0 bg-forest-950/60"></div>
                    <aside x-show="adminMenuOpen" x-transition class="relative h-full w-72 bg-forest-900 shadow-2xl">
                        <button @click="adminMenuOpen = false" class="absolute right-3 top-3 rounded-lg p-2 text-white hover:bg-white/10" aria-label="Menüyü kapat">✕</button>
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
        <header class="sticky top-0 z-40 border-b border-forest-900/15 bg-[#fbf7ee]/95 backdrop-blur dark:border-white/10 dark:bg-forest-950/95">
            <nav class="hc-container flex h-19 items-center justify-between gap-4" aria-label="Ana menü">
                <x-brand-logo />
                <div class="hidden items-center gap-6 text-sm font-semibold text-forest-950 dark:text-cream lg:flex">
                    <a @class(['nav-link', 'nav-link-active' => request()->routeIs('home')]) href="{{ route('home') }}">Ana Sayfa</a>
                    <a @class(['nav-link', 'nav-link-active' => request()->routeIs('recipes.*')]) href="{{ route('recipes.index') }}">Tarifler</a>
                    @auth
                        <a @class(['nav-link', 'nav-link-active' => request()->routeIs('ingredients.explore')]) href="{{ route('ingredients.explore') }}">Ne Pişirebilirim?</a>
                        <a @class(['nav-link', 'nav-link-active' => request()->routeIs('pantry.*')]) href="{{ route('pantry.index') }}">Dolabım</a>
                    @endauth
                </div>
                <div class="hidden items-center gap-2 lg:flex">
                    <button @click="dark = !dark" class="hc-btn hc-btn-outline px-3 py-2" :aria-label="dark ? 'Açık temaya geç' : 'Koyu temaya geç'" x-text="dark ? '☀' : '☾'"></button>
                    @auth
                        <a href="{{ route('favorites.index') }}" class="hc-btn hc-btn-outline">Favoriler</a>
                        @if (auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}" class="hc-btn hc-btn-gold">Yönetim</a>@endif
                        <a href="{{ route('dashboard') }}" class="hc-btn hc-btn-primary">Hesabım</a>
                    @else
                        <a href="{{ route('login') }}" class="hc-btn hc-btn-outline">Giriş Yap</a>
                        <a href="{{ route('register') }}" class="hc-btn hc-btn-gold">Üye Ol</a>
                    @endauth
                </div>
                <div class="flex items-center gap-2 lg:hidden">
                    <button @click="dark = !dark" class="rounded-lg p-2 text-forest-900 dark:text-cream" aria-label="Tema değiştir" x-text="dark ? '☀' : '☾'"></button>
                    <button @click="menuOpen = !menuOpen" class="rounded-lg p-2 text-forest-900 dark:text-cream" :aria-expanded="menuOpen" aria-controls="mobile-nav" aria-label="Menüyü aç">☰</button>
                </div>
            </nav>
            <div id="mobile-nav" x-cloak x-show="menuOpen" x-transition class="border-t border-forest-900/10 bg-paper px-5 py-4 dark:border-white/10 dark:bg-forest-950 lg:hidden">
                <div class="mx-auto grid max-w-7xl gap-2 text-sm font-semibold">
                    <a class="mobile-nav-link" href="{{ route('home') }}">Ana Sayfa</a>
                    <a class="mobile-nav-link" href="{{ route('recipes.index') }}">Tarifler</a>
                    @auth
                        <a class="mobile-nav-link" href="{{ route('ingredients.explore') }}">Ne Pişirebilirim?</a>
                        <a class="mobile-nav-link" href="{{ route('pantry.index') }}">Dolabım</a>
                        <a class="mobile-nav-link" href="{{ route('favorites.index') }}">Favoriler</a>
                        <a class="mobile-nav-link" href="{{ route('dashboard') }}">Hesabım</a>
                        @if(auth()->user()->isAdmin())<a class="mobile-nav-link" href="{{ route('admin.dashboard') }}">Yönetim</a>@endif
                    @else
                        <a class="mobile-nav-link" href="{{ route('login') }}">Giriş Yap</a>
                        <a class="mobile-nav-link text-gold-700" href="{{ route('register') }}">Üye Ol</a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            @if (session('status'))
                <div class="hc-container pt-5"><div class="hc-alert hc-alert-success" role="status">{{ session('status') }}</div></div>
            @endif
            @if ($errors->any())
                <div class="hc-container pt-5"><div class="hc-alert hc-alert-error" role="alert">{{ $errors->first() }}</div></div>
            @endif
            {{ $slot }}
        </main>

        <footer class="mt-20 border-t border-forest-900/10 bg-forest-950 text-[#e7efe7]">
            <div class="hc-container grid gap-8 py-12 md:grid-cols-[1.3fr_1fr_1fr]">
                <div><x-brand-logo variant="dark" /><p class="mt-4 max-w-sm text-sm leading-6 text-[#c8d7ca]">Kıbrıs'tan ilham alan tarifleri, malzemeleri ve dengeli beslenme bilgisini aynı mutfakta buluşturuyoruz.</p></div>
                <div><p class="text-sm font-bold text-gold-300">Keşfet</p><div class="mt-3 grid gap-2 text-sm"><a href="{{ route('recipes.index') }}">Tarifler</a>@auth<a href="{{ route('ingredients.explore') }}">Ne Pişirebilirim?</a><a href="{{ route('pantry.index') }}">Malzeme Dolabım</a>@endauth</div></div>
                <div><p class="text-sm font-bold text-gold-300">House Chef Cyprus</p><p class="mt-3 text-sm leading-6 text-[#c8d7ca]">Özgün demo içerik. Sağlık ve alerjen bilgilerini kişisel durumunuza göre değerlendirin.</p></div>
            </div>
            <div class="border-t border-white/10 py-4 text-center text-xs text-[#aebfac]">© {{ date('Y') }} House Chef Cyprus</div>
        </footer>
    @endif
    @livewireScripts
</body>
</html>
