@props(['variant' => 'light', 'compact' => false])

<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'hc-brand-logo '.($variant === 'dark' ? 'hc-brand-logo--inverse ' : '').'inline-flex items-center gap-3']) }} aria-label="House Chef Cyprus ana sayfa">
    <svg viewBox="0 0 52 52" class="h-11 w-11 shrink-0" aria-hidden="true" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="2" y="2" width="48" height="48" rx="15" fill="var(--hc-logo-surface)"/>
        <path d="M13.5 16v20M13.5 26h10V16M23.5 26v10M31 16v20M31 16h8v9h-8M31 28h8v8h-8" stroke="var(--hc-logo-mark)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M36.5 10.5c2.35 1.9 3.65 4.35 3.65 7.15" stroke="var(--hc-accent)" stroke-width="2.45" stroke-linecap="round"/>
    </svg>
    @unless($compact)
        <span class="hc-brand-logo__wordmark leading-none"><span class="block text-[1.1rem] font-extrabold tracking-[-.06em]">House Chef</span><span class="mt-1 block text-[9px] font-extrabold uppercase tracking-[.34em] text-orange-500">Cyprus</span></span>
    @endunless
</a>
