@props(['variant' => 'light', 'compact' => false])

@php($isDark = $variant === 'dark')
<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }} aria-label="House Chef Cyprus ana sayfa">
    <svg viewBox="0 0 48 48" class="h-10 w-10 shrink-0" aria-hidden="true" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M10 23.5C10 16.6 15.5 11 22.4 11c5.2 0 9.7 3.2 11.5 7.7 2.4.6 4.1 2.8 4.1 5.4 0 3.1-2.5 5.7-5.7 5.7H15.7A5.7 5.7 0 0 1 10 23.5Z" class="{{ $isDark ? 'fill-[#fbf7ee]' : 'fill-forest-700' }}"/>
        <path d="M14 36.5 24 26l10 10.5v5H14v-5Z" class="{{ $isDark ? 'fill-gold-400' : 'fill-gold-400' }}"/>
        <path d="M20 41.5v-6h8v6M17 22.5l-3-4M31 22.5l3-4" stroke="{{ $isDark ? '#153224' : '#fffdf8' }}" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M38 11.5c-3.5 2.8-4.4 6.4-4.2 9.3 3.2-.2 6.5-1.6 8.2-4.8" stroke="#b98427" stroke-width="2.2" stroke-linecap="round"/>
    </svg>
    @unless($compact)
        <span class="leading-none {{ $isDark ? 'text-[#fbf7ee]' : 'text-forest-900' }}"><span class="block font-display text-xl font-semibold">House Chef</span><span class="mt-1 block text-[9px] font-bold uppercase tracking-[.35em] text-gold-500">Cyprus</span></span>
    @endunless
</a>
