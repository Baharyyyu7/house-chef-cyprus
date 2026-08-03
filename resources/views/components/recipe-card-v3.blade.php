@props(['recipe', 'showFavorite' => true])

@php
    $media = $recipe->media->first(fn ($item) => $item->isApprovedRealMedia());
    $hasApprovedMedia = $media !== null;
    $alt = $media?->alt_text ?: '';
@endphp

<article {{ $attributes->merge(['class' => 'hc-v3-card hc-v3-card--interactive group flex min-h-[27rem] flex-col overflow-hidden']) }}>
    <a class="relative block aspect-[1.16] overflow-hidden bg-stone-100 dark:bg-stone-900" href="{{ route('recipes.show', $recipe) }}" aria-label="{{ $recipe->title }} tarifini incele">
        @if($hasApprovedMedia)
            <picture>
                @if($media->responsiveSrcset([400, 800], 'avif'))<source type="image/avif" srcset="{{ $media->responsiveSrcset([400, 800], 'avif') }}" sizes="(min-width: 640px) 400px, 100vw">@endif
                @if($media->responsiveSrcset([400, 800], 'webp'))<source type="image/webp" srcset="{{ $media->responsiveSrcset([400, 800], 'webp') }}" sizes="(min-width: 640px) 400px, 100vw">@endif
                <img class="h-full w-full object-cover transition duration-500 group-hover:scale-105 motion-reduce:transform-none" src="{{ $media->responsiveUrl(800) }}" srcset="{{ $media->responsiveSrcset([400, 800]) }}" sizes="(min-width: 640px) 400px, 100vw" alt="{{ $alt }}" width="800" height="600" loading="lazy">
            </picture>
        @else
            <div class="flex h-full w-full flex-col items-center justify-center gap-3 border-b border-dashed p-6 text-center" style="border-color:var(--hc-border-subtle)" aria-hidden="true">
                <span class="grid h-12 w-12 place-items-center rounded-full bg-orange-100 text-2xl text-orange-800 dark:bg-orange-500/15 dark:text-orange-200">⌁</span>
                <span class="text-sm font-extrabold" style="color:var(--hc-text-primary)">Yemek fotoğrafı hazırlanıyor</span>
                <span class="max-w-52 text-xs leading-5" style="color:var(--hc-text-muted)">Onaylı, tarife özel görsel bulunana kadar fotoğraf gösterilmez.</span>
            </div>
        @endif
        @if($hasApprovedMedia)<span class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/5 to-transparent"></span>@endif
        <span class="absolute left-3 top-3 hc-v3-badge {{ $hasApprovedMedia ? 'border-white/20 bg-black/35 text-white backdrop-blur' : '' }}">{{ $recipe->cuisine?->name ?? 'Ev mutfağı' }}</span>
        <span class="absolute bottom-3 left-3 text-xs font-extrabold {{ $hasApprovedMedia ? 'text-white' : 'text-stone-700 dark:text-stone-300' }}">{{ $recipe->totalMinutes() }} dk · {{ $recipe->servings }} porsiyon</span>
    </a>
    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-center justify-between gap-3 text-xs font-bold" style="color: var(--hc-text-muted)"><span>{{ $recipe->category?->name ?? 'Tarif' }}</span><span class="capitalize">{{ $recipe->difficulty }}</span></div>
        <h2 class="mt-3 hc-heading-3 line-clamp-2"><a class="focus-visible:rounded" href="{{ route('recipes.show', $recipe) }}">{{ $recipe->title }}</a></h2>
        <p class="hc-body-sm mt-3 line-clamp-3" style="color: var(--hc-text-secondary)">{{ $recipe->excerpt }}</p>
        <div class="mt-4 flex flex-wrap gap-2">@foreach($recipe->dietaryTags->take(2) as $tag)<span class="hc-v3-badge hc-v3-badge--success">{{ $tag->name ?? str($tag->code)->replace('_', ' ')->title() }}</span>@endforeach</div>
        <div class="mt-auto flex items-center justify-between gap-3 pt-5">
            <span class="hc-v3-badge {{ $hasApprovedMedia && $recipe->verification_status === 'verified' ? 'hc-v3-badge--success' : 'hc-v3-badge--warning' }}"><span aria-hidden="true">{{ $hasApprovedMedia && $recipe->verification_status === 'verified' ? '✓' : 'ⓘ' }}</span> {{ $hasApprovedMedia && $recipe->verification_status === 'verified' ? 'Doğrulandı' : 'İnceleme sürüyor' }}</span>
            <div class="flex items-center gap-1">
                @auth
                    @if($showFavorite)
                        <form method="post" action="{{ route('favorites.toggle', $recipe) }}" class="relative z-10">@csrf<button class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" type="submit" aria-label="{{ $recipe->title }} tarifini favorilere ekle">♡<span class="sr-only">Favorilere ekle</span></button></form>
                    @endif
                @endauth
                <a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="{{ route('recipes.show', $recipe) }}">İncele <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </div>
</article>
