@props(['variant' => 'light', 'compact' => false])

@php($darkSurface = $variant === 'dark')
<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }} aria-label="House Chef Cyprus ana sayfa">
    <svg viewBox="0 0 52 52" class="h-11 w-11 shrink-0" aria-hidden="true" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="2" y="2" width="48" height="48" rx="16" fill="{{ $darkSurface ? '#fff8f0' : '#171717' }}"/>
        <path d="M14 16v20M14 26h10V16M24 26v10M31 16v20M31 16h8v9h-8M31 28h8v8h-8" stroke="{{ $darkSurface ? '#171717' : '#fff8f0' }}" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M36.8 10.5c2.5 2 3.8 4.6 3.8 7.5" stroke="#f97316" stroke-width="2.6" stroke-linecap="round"/>
    </svg>
    @unless($compact)
        <span class="leading-none {{ $darkSurface ? 'text-[#fff8f0]' : 'text-[#171717] dark:text-[#fff8f0]' }}"><span class="block text-[1.1rem] font-extrabold tracking-[-.06em]">House Chef</span><span class="mt-1 block text-[9px] font-extrabold uppercase tracking-[.34em] text-orange-500">Cyprus</span></span>
    @endunless
</a>
