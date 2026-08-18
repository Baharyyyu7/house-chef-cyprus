<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
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
    <title>House Chef Cyprus · Güvenli Giriş</title>
    <script>if (localStorage.theme === 'dark') document.documentElement.classList.add('dark');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ dark: localStorage.theme === 'dark' }" x-init="$watch('dark', value => { document.documentElement.classList.toggle('dark', value); localStorage.theme = value ? 'dark' : 'light' })" class="min-h-screen font-sans text-[var(--hc-text-primary)] antialiased bg-[var(--hc-bg)]">
    <main class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-4 py-10 sm:px-6">
        <div class="mb-8 flex items-center justify-between">
            <x-brand-logo context="auth" size="lg" class="transition hover:scale-[1.02] motion-reduce:transition-none" />
            <x-theme-switch />
        </div>
        <section class="hc-v3-card p-6 shadow-xl sm:p-8">
            {{ $slot }}
        </section>
        <div class="mt-8 flex flex-col items-center gap-2 text-center text-xs text-[var(--hc-text-muted)]">
            <span>© {{ date('Y') }} House Chef Cyprus · Kıbrıs ve Akdeniz Lezzetleri</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="hover:text-orange-600 dark:hover:text-orange-300">Ana Sayfa</a>
                <span>·</span>
                <a href="{{ route('legal.center') }}" class="hover:text-orange-600 dark:hover:text-orange-300">Gizlilik &amp; Koşullar</a>
            </div>
        </div>
    </main>
    @livewireScripts
</body>
</html>
