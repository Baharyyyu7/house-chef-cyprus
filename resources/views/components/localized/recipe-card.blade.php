@props(['translation'])

@php
    $recipe = $translation->recipe;
    $media = $recipe?->media->first(fn ($item) => $item->isApprovedRealMedia());
    $isSyntheticMedia = (bool) $media?->generated_by_ai;
    $url = route($translation->locale.'.recipes.show', $translation->slug);
@endphp

<article {{ $attributes->merge(['class' => 'hc-v3-card hc-v3-card--interactive group relative flex w-full min-w-0 min-h-[25rem] flex-col overflow-hidden']) }} lang="{{ $translation->locale }}">
    <a class="relative block aspect-[1.16] overflow-hidden bg-stone-100 dark:bg-stone-900" tabindex="-1" aria-hidden="true" href="{{ $url }}">
        @if($media)
            <picture>
                @if($media->responsiveSrcset([400, 800], 'avif'))<source type="image/avif" srcset="{{ $media->responsiveSrcset([400, 800], 'avif') }}" sizes="(min-width: 640px) 400px, 100vw">@endif
                @if($media->responsiveSrcset([400, 800], 'webp'))<source type="image/webp" srcset="{{ $media->responsiveSrcset([400, 800], 'webp') }}" sizes="(min-width: 640px) 400px, 100vw">@endif
                <img class="h-full w-full object-cover transition duration-500 group-hover:scale-105 motion-reduce:transform-none" src="{{ $media->responsiveUrl(800) }}" srcset="{{ $media->responsiveSrcset([400, 800]) }}" sizes="(min-width: 640px) 400px, 100vw" alt="{{ $isSyntheticMedia ? 'AI-generated image: ' : '' }}{{ $translation->image_alt ?: $translation->title }}" width="800" height="600" loading="lazy">
            </picture>
            <span class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/5 to-transparent"></span>
        @else
            <div class="flex h-full w-full flex-col items-center justify-center gap-3 border-b border-dashed p-6 text-center" style="border-color:var(--hc-border-subtle)">
                <span class="grid h-12 w-12 place-items-center rounded-full bg-orange-100 text-2xl text-orange-800 dark:bg-orange-500/15 dark:text-orange-200">⌁</span>
                <span class="text-sm font-extrabold" style="color:var(--hc-text-primary)">Dish photo in review</span>
            </div>
        @endif
        <span class="absolute left-3 top-3 hc-v3-badge {{ $media ? 'border-white/20 bg-black/35 text-white backdrop-blur' : '' }}">{{ $translation->cuisine_label ?: 'Home cooking' }}</span>
        @if($media && $isSyntheticMedia)<span class="absolute right-3 top-3 hc-v3-badge border-white/20 bg-black/35 text-white backdrop-blur">AI image</span>@endif
        <span class="absolute bottom-3 left-3 text-xs font-extrabold {{ $media ? 'text-white' : 'text-stone-700 dark:text-stone-300' }}">{{ $translation->totalMinutes() }} min · serves {{ $translation->servingCount() }}</span>
    </a>
    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-center justify-between gap-3 text-xs font-bold" style="color: var(--hc-text-muted)"><span>{{ $translation->category_label ?: 'Recipe' }}</span><span>{{ $translation->difficulty_label }}</span></div>
        <h3 class="mt-3 hc-heading-3 line-clamp-2"><a class="before:absolute before:inset-0 focus-visible:rounded" href="{{ $url }}">{{ $translation->title }}</a></h3>
        <p class="hc-body-sm mt-3 line-clamp-3" style="color: var(--hc-text-secondary)">{{ $translation->excerpt }}</p>
        <div class="mt-auto flex items-center justify-end pt-5"><span class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" aria-hidden="true">View recipe →</span></div>
    </div>
</article>
