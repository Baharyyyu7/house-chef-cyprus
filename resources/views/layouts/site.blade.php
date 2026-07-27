<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>{{ $title ?? 'House Chef Cyprus' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{dark: localStorage.theme === 'dark'}" x-init="$watch('dark', value => {document.documentElement.classList.toggle('dark', value); localStorage.theme = value ? 'dark' : 'light'})" :class="dark ? 'bg-stone-950 text-stone-100' : 'bg-stone-50 text-stone-900'" class="antialiased">
<header class="sticky top-0 z-40 border-b border-orange-100 bg-stone-50/95 backdrop-blur dark:border-stone-700 dark:bg-stone-950/95"><nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4"><a href="{{ route('home') }}" class="text-xl font-black tracking-tight text-orange-700">House Chef <span class="text-stone-700 dark:text-stone-100">Cyprus</span></a><div class="flex items-center gap-4 text-sm font-semibold"><a href="{{ route('recipes.index') }}">Tarifler</a><button @click="dark = !dark" class="rounded-full border border-stone-300 px-3 py-1 dark:border-stone-600" x-text="dark ? '☀ Açık' : '☾ Koyu'"></button>@auth <a href="{{ route('pantry.index') }}">Dolabım</a><a href="{{ route('favorites.index') }}">Favoriler</a><a href="{{ route('dashboard') }}">Hesabım</a>@if(auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}">Yönetim</a>@endif @else <a href="{{ route('login') }}">Giriş</a><a class="rounded-full bg-orange-700 px-4 py-2 text-white" href="{{ route('register') }}">Üye ol</a>@endauth</div></nav></header>
<main>@if(session('status'))<div class="mx-auto mt-5 max-w-7xl rounded-xl bg-emerald-100 px-4 py-3 text-emerald-800">{{ session('status') }}</div>@endif{{ $slot }}</main>
<footer class="mt-20 border-t border-stone-200 py-10 text-center text-sm text-stone-500">© {{ date('Y') }} House Chef Cyprus · Özgün demo içerik</footer>
</body></html>
