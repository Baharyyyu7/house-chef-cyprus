@props(['post', 'ratio' => '4/3'])

@php
    $cover = app(\App\Services\Blog\CoverArtService::class);
    $palette = $cover->palette($post);
    $overline = $cover->overline($post);
    // 1200x900 tuval; başlık 84 punto, kenar boşluğu 88 => kullanılabilir genişlik 1024.
    $lines = $cover->titleLines($post, 84, 1024);
    $lineHeight = 104;
    $blockHeight = count($lines) * $lineHeight;
    $startY = 470 - ($blockHeight / 2) + 74;
@endphp

<svg class="h-full w-full" viewBox="0 0 1200 900" role="img" aria-label="{{ $post->title }}" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="hc-cover-{{ $post->id }}" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="{{ $palette['bg'] }}"/>
            <stop offset="100%" stop-color="{{ $palette['bg'] }}" stop-opacity="0.86"/>
        </linearGradient>
    </defs>

    <rect width="1200" height="900" fill="url(#hc-cover-{{ $post->id }})"/>
    {{-- Sağ üstte yumuşak vurgu: kapaklar tek renk bloğu gibi durmasın. --}}
    <circle cx="1090" cy="120" r="260" fill="{{ $palette['accent'] }}" opacity="0.13"/>
    <circle cx="1160" cy="60" r="120" fill="{{ $palette['accent'] }}" opacity="0.1"/>

    <text x="88" y="150" fill="{{ $palette['accent'] }}" font-family="Manrope, ui-sans-serif, system-ui, sans-serif" font-size="30" font-weight="800" letter-spacing="4.5">{{ $overline }}</text>

    @foreach($lines as $i => $line)
        <text x="88" y="{{ $startY + ($i * $lineHeight) }}" fill="{{ $palette['ink'] }}" font-family="Manrope, ui-sans-serif, system-ui, sans-serif" font-size="84" font-weight="800" letter-spacing="-2.6">{{ $line }}</text>
    @endforeach

    <rect x="88" y="790" width="72" height="5" rx="2.5" fill="{{ $palette['accent'] }}"/>
    <text x="88" y="838" fill="{{ $palette['ink'] }}" opacity="0.62" font-family="Manrope, ui-sans-serif, system-ui, sans-serif" font-size="26" font-weight="700" letter-spacing="2.4">HOUSE CHEF CYPRUS</text>
</svg>
