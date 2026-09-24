@props([
    'variant' => 'auto',
    'size' => 'md',
    'context' => 'header',
    'loading' => null,
    'linked' => true,
    'compact' => false,
])

@php
    $sizes = config('brand.sizes');
    $width = $sizes[$size] ?? $sizes['md'];
    $loading = $loading ?? ($context === 'header' ? 'eager' : 'lazy');
    $assets = config('brand.assets');
    $light = asset($compact ? $assets['mark_light'] : $assets['logo_light']);
    $dark = asset($compact ? $assets['mark_dark'] : $assets['logo_dark']);
    $imageClass = 'hc-brand-logo__image max-w-full object-contain';
    $imageStyle = 'width: '.$width.'px; height: auto;';
    $alt = config('brand.name');
@endphp

@if ($linked)
    <a href="{{ app()->getLocale() === 'en' ? route('en.home') : route('home') }}" {{ $attributes->merge(['class' => 'hc-brand-logo inline-flex shrink-0 items-center']) }} aria-label="{{ $alt }} {{ app()->getLocale() === 'en' ? 'home' : 'ana sayfa' }}">
@else
    <span {{ $attributes->merge(['class' => 'hc-brand-logo inline-flex shrink-0 items-center']) }}>
@endif
    @if ($variant === 'auto')
        <img src="{{ $light }}" width="{{ $compact ? 1024 : 908 }}" height="{{ $compact ? 1024 : 233 }}" class="{{ $imageClass }} dark:hidden" style="{{ $imageStyle }}" alt="{{ $alt }}" loading="{{ $loading }}" @if($loading === 'eager') fetchpriority="high" @endif>
        <img src="{{ $dark }}" width="{{ $compact ? 1024 : 908 }}" height="{{ $compact ? 1024 : 233 }}" class="{{ $imageClass }} hidden dark:block" style="{{ $imageStyle }}" alt="{{ $alt }}" loading="{{ $loading }}" @if($loading === 'eager') fetchpriority="high" @endif>
    @else
        <img src="{{ $variant === 'dark' ? $dark : $light }}" width="{{ $compact ? 1024 : 908 }}" height="{{ $compact ? 1024 : 233 }}" class="{{ $imageClass }}" style="{{ $imageStyle }}" alt="{{ $alt }}" loading="{{ $loading }}" @if($loading === 'eager') fetchpriority="high" @endif>
    @endif
@if ($linked)
    </a>
@else
    </span>
@endif
