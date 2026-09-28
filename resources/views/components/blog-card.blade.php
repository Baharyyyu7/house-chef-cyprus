@props(['post'])

@php
    $media = app(\App\Services\Media\PublicMediaEligibilityService::class)
        ->heroOrFirstEligible($post->heroMedia, $post->media);
    $kategori = $post->category?->name ?? 'Mutfak notu';
    $yapayZeka = (bool) $media?->generated_by_ai;
@endphp

{{-- Blog kartları anasayfada tarif kartlarının yanında duruyor ama onlarla
     aynı davranmıyordu: --interactive yoktu (tarif kartı imleçle kalkıyor,
     blog kartı donuk kalıyordu), görsel oranı 16/9'a karşı 4/3'tü ve kart
     başına aynı hedefe üç ayrı bağlantı vardı - klavyeyle bölümü geçmek
     üç kart için dokuz durak sürüyordu.

     Yapı artık recipe-card-v3 ile aynı: görsel bağlantısı odak sırasından
     çıkarılıyor, başlık bağlantısı before:inset-0 ile tüm kartı hedef
     yapıyor, alttaki bağlantı da onun üstünde duruyor. --}}
<article {{ $attributes->merge(['class' => 'hc-v3-card hc-v3-card--interactive group relative flex w-full min-w-0 flex-col overflow-hidden']) }}>
    <a class="relative block aspect-[4/3] overflow-hidden bg-stone-100 dark:bg-stone-900" tabindex="-1" aria-hidden="true" href="{{ route('blog.show', $post) }}">
        @if($media?->publicUrl())
            <picture>
                @if($media->responsiveSrcset([400, 800], 'avif'))<source type="image/avif" srcset="{{ $media->responsiveSrcset([400, 800], 'avif') }}" sizes="(min-width: 768px) 400px, 100vw">@endif
                @if($media->responsiveSrcset([400, 800], 'webp'))<source type="image/webp" srcset="{{ $media->responsiveSrcset([400, 800], 'webp') }}" sizes="(min-width: 768px) 400px, 100vw">@endif
                <img class="h-full w-full object-cover transition duration-500 group-hover:scale-105 motion-reduce:transform-none" src="{{ $media->responsiveUrl(800) ?: $media->publicUrl() }}" srcset="{{ $media->responsiveSrcset([400, 800]) }}" sizes="(min-width: 768px) 400px, 100vw" alt="{{ $yapayZeka ? __('blog.ai_image_alt_prefix') : '' }}{{ $media->alt_text }}" width="800" height="600" loading="lazy">
            </picture>
            <span class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/5 to-transparent"></span>
        @else
            <div class="flex h-full w-full flex-col items-center justify-center gap-3 border-b border-dashed p-6 text-center" style="border-color:var(--hc-border-subtle)" aria-hidden="true">
                <span class="grid h-12 w-12 place-items-center rounded-full bg-orange-100 text-2xl text-orange-800 dark:bg-orange-500/15 dark:text-orange-200">✎</span>
                <span class="text-sm font-extrabold" style="color:var(--hc-text-primary)">{{ __('blog.cover_pending') }}</span>
                <span class="max-w-52 text-xs leading-5" style="color:var(--hc-text-muted)">{{ __('blog.cover_pending_note') }}</span>
            </div>
        @endif
        <span class="absolute left-3 top-3 hc-v3-badge {{ $media?->publicUrl() ? 'border-white/20 bg-black/35 text-white backdrop-blur' : '' }}">{{ $kategori }}</span>
        @if($media?->publicUrl() && $yapayZeka)<span class="absolute right-3 top-3 hc-v3-badge border-white/20 bg-black/35 text-white backdrop-blur">{{ __('blog.ai_image_badge') }}</span>@endif
    </a>
    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-center justify-between gap-3 text-xs font-bold" style="color:var(--hc-text-muted)">
            <span>{{ $kategori }}</span>
            @if($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('j F Y') }}</time>@endif
        </div>
        <h3 class="mt-2 text-base font-extrabold leading-snug line-clamp-2" style="color:var(--hc-text-primary)">
            <a class="before:absolute before:inset-0 focus-visible:rounded" href="{{ route('blog.show', $post) }}">{{ $post->title }}</a>
        </h3>
        <p class="hc-body-sm mt-2 line-clamp-2" style="color:var(--hc-text-secondary)">{{ $post->excerpt }}</p>
        <div class="mt-auto flex items-center justify-between gap-3 pt-4">
            <span class="hc-v3-badge">Mutfak notu</span>
            <a class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm relative z-10 font-bold" href="{{ route('blog.show', $post) }}">{{ __('blog.read_post') }} <span aria-hidden="true">→</span></a>
        </div>
    </div>
</article>
