@props(['item', 'width' => 400])
@php
    $placeholder = $item->is_placeholder || str_starts_with((string) $item->path, 'fallbacks/');
    $localMissing = filled($item->path) && $item->disk === 'public' && ! \Illuminate\Support\Facades\Storage::disk('public')->exists($item->path);
    $url = ! $placeholder && ! $localMissing ? $item->responsiveUrl($width) : null;
    $label = $placeholder ? 'Yer tutucu · gerçek fotoğraf yok' : ($item->visibility === 'private' ? 'Özel dosya' : 'Görsel dosyası bulunamadı');
@endphp
<div class="h-full w-full" x-data="{ failed: false }">
    @if($url)
        <img x-show="!failed" x-on:error="failed = true" class="h-full w-full object-cover" src="{{ $url }}" alt="{{ $item->alt_text }}" loading="lazy">
        <div x-cloak x-show="failed" class="hc-media-preview h-full w-full"><span><x-ui.icon name="image" size="28" />Görsel yüklenemedi</span></div>
    @else
        <div class="hc-media-preview h-full w-full"><span><x-ui.icon name="image" size="28" />{{ $label }}</span></div>
    @endif
</div>
