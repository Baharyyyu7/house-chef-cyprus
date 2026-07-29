<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#153224"><link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=playfair-display:500,600,700&display=swap" rel="stylesheet" />
    <title>{{ config('app.name', 'House Chef Cyprus') }}</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{dark: localStorage.theme === 'dark', menuOpen: false}" x-init="document.documentElement.classList.toggle('dark', dark); $watch('dark', value => { document.documentElement.classList.toggle('dark', value); localStorage.theme = value ? 'dark' : 'light' })" class="min-h-screen antialiased">
    <livewire:layout.navigation />
    @if (isset($header))<header class="border-b border-stone-200 bg-white/60 dark:border-stone-800 dark:bg-stone-900/40"><div class="hc-container py-6">{{ $header }}</div></header>@endif
    <main class="hc-page">@if(session('status'))<div class="hc-alert hc-alert-success mb-6">{{ session('status') }}</div>@endif{{ $slot }}</main>@livewireScripts
</body>
</html>
