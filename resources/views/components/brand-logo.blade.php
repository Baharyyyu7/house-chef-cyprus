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
    $width = (int) ($sizes[$size] ?? $sizes['md']);
    $height = $compact ? $width : (int) max(1, round($width * 44 / 172));
    $loading = $loading ?? ($context === 'header' ? 'eager' : 'lazy');
    $assets = config('brand.assets');
    $light = asset($compact ? $assets['mark_light'] : $assets['logo_light']);
    $dark = asset($compact ? $assets['mark_dark'] : $assets['logo_dark']);
    // Retina için ikinci bir kaynak; tarayıcı ekranına göre hangisini
    // indireceğine kendi karar verir, ikisini birden indirmez.
    $lightSrcset = $light.' 1x, '.asset($assets[$compact ? 'mark_light_2x' : 'logo_light_2x']).' 2x';
    $darkSrcset = $dark.' 1x, '.asset($assets[$compact ? 'mark_dark_2x' : 'logo_dark_2x']).' 2x';
    $imageClass = 'hc-brand-logo__image max-w-full object-contain';
    $imageStyle = 'width: '.$width.'px; height: '.$height.'px; aspect-ratio: '.$width.'/'.$height.';';
    $alt = config('brand.name');
@endphp

@if ($linked)
    <a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'hc-brand-logo inline-flex shrink-0 items-center']) }} aria-label="{{ $alt }} ana sayfa">
@else
    <span {{ $attributes->merge(['class' => 'hc-brand-logo inline-flex shrink-0 items-center']) }}>
@endif
    @if ($variant === 'auto')
        <img src="{{ $light }}" srcset="{{ $lightSrcset }}" width="{{ $width }}" height="{{ $height }}" class="{{ $imageClass }} dark:hidden" style="{{ $imageStyle }}" alt="{{ $alt }}" loading="{{ $loading }}">
        <img src="{{ $dark }}" srcset="{{ $darkSrcset }}" width="{{ $width }}" height="{{ $height }}" class="{{ $imageClass }} hidden dark:block" style="{{ $imageStyle }}" alt="{{ $alt }}" loading="{{ $loading }}">
    @else
        <img src="{{ $variant === 'dark' ? $dark : $light }}" srcset="{{ $variant === 'dark' ? $darkSrcset : $lightSrcset }}" width="{{ $width }}" height="{{ $height }}" class="{{ $imageClass }}" style="{{ $imageStyle }}" alt="{{ $alt }}" loading="{{ $loading }}">
    @endif
@if ($linked)
    </a>
@else
    </span>
@endif
