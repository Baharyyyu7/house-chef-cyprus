@props(['recipe', 'showFavorite' => true, 'heading' => 'h2'])

{{-- Kart basligi h2'ye sabitti. Arsiv sayfasinda dogru (h1'in hemen
     altinda), ama bir bolumun icinde yanlis: tarif detayindaki "Benzer
     tarifler" kartlari sayfanin kendi "Malzemeler" ve "Yapim adimlari"
     basliklariyla ayni duzeyde duruyordu. Basliklarla gezinen biri
     "Humus"u bu tarifin bir bolumu saniyordu. --}}

@php
    $displayMedia = app(\App\Services\Media\RecipeDisplayMediaService::class)->resolve($recipe);
    $media = $displayMedia['media'];
    $hasApprovedMedia = $displayMedia['has_specific_media'];
    $alt = $displayMedia['alt'];
    $isSyntheticMedia = $displayMedia['is_synthetic'];
    // Rozet kayıt ve görsel incelemesinin yanında besin ve alerjen verisinin
    // de tam olmasını ister; karar tarif başına önbellekte tutulur.
    $dogrulandi = $hasApprovedMedia && $recipe->verification_status === 'verified'
        && app(\App\Services\RecipeDataCompletenessService::class)->tamamOnbellekli($recipe);
@endphp

<article {{ $attributes->merge(['class' => 'hc-v3-card hc-v3-card--interactive group relative flex w-full min-w-0 flex-col overflow-hidden']) }}>
    <a class="relative block aspect-[4/3] overflow-hidden bg-stone-100 dark:bg-stone-900" tabindex="-1" aria-hidden="true" href="{{ route('recipes.show', $recipe) }}">
        @if($hasApprovedMedia && $media)
            <picture>
                @if($media->responsiveSrcset([400, 800], 'avif'))<source type="image/avif" srcset="{{ $media->responsiveSrcset([400, 800], 'avif') }}" sizes="(min-width: 640px) 400px, 100vw">@endif
                @if($media->responsiveSrcset([400, 800], 'webp'))<source type="image/webp" srcset="{{ $media->responsiveSrcset([400, 800], 'webp') }}" sizes="(min-width: 640px) 400px, 100vw">@endif
                <img class="h-full w-full object-cover transition duration-500 group-hover:scale-105 motion-reduce:transform-none" src="{{ $media->responsiveUrl(800) }}" srcset="{{ $media->responsiveSrcset([400, 800]) }}" sizes="(min-width: 640px) 400px, 100vw" alt="{{ $isSyntheticMedia ? __('card.ai_image_alt_prefix') : '' }}{{ $alt }}" width="800" height="600" loading="lazy">
            </picture>
        @else
            <img class="h-full w-full object-cover transition duration-500 group-hover:scale-105 motion-reduce:transform-none" src="{{ $displayMedia['card_url'] }}" alt="{{ $alt }}" width="800" height="600" loading="lazy">
        @endif
        <span class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/5 to-transparent"></span>
        <span class="absolute left-3 top-3 hc-v3-badge border-white/20 bg-black/35 text-white backdrop-blur">{{ $recipe->cuisine ? taxonomy_name($recipe->cuisine) : __('recipe.home_cooking') }}</span>
        @if($hasApprovedMedia && $isSyntheticMedia)<span class="absolute right-3 top-3 hc-v3-badge border-white/20 bg-black/35 text-white backdrop-blur">{{ __('card.ai_image') }}</span>@endif
        <span class="absolute bottom-3 left-3 text-xs font-extrabold text-white">{{ $recipe->totalMinutes() }} {{ __('card.min') }} · {{ $recipe->servings }} {{ __('card.servings') }}</span>
    </a>
    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-center justify-between gap-3 text-xs font-bold" style="color: var(--hc-text-muted)"><span>{{ $recipe->category ? taxonomy_name($recipe->category) : __('recipe.recipe') }}</span><span>{{ $recipe->zorlukEtiketi() }}</span></div>
        <{{ $heading }} class="mt-2 text-base font-extrabold leading-snug line-clamp-2" style="color: var(--hc-text-primary)"><a class="before:absolute before:inset-0 focus-visible:rounded" href="{{ route('recipes.show', $recipe) }}">{{ $recipe->translated('title') }}</a></{{ $heading }}>
        <p class="hc-body-sm mt-2 line-clamp-2" style="color: var(--hc-text-secondary)">{{ $recipe->translated('excerpt') }}</p>
        <div class="mt-3 flex flex-wrap gap-2">@foreach($recipe->dietaryTags->take(2) as $tag)<span class="hc-v3-badge hc-v3-badge--success">{{ $tag->name ?? str($tag->code)->replace('_', ' ')->title() }}</span>@endforeach</div>
        <div class="mt-auto flex items-center justify-between gap-3 pt-4">
            <span class="hc-v3-badge {{ $dogrulandi ? 'hc-v3-badge--success' : 'hc-v3-badge--warning' }}"><span aria-hidden="true">{{ $dogrulandi ? '✓' : 'ⓘ' }}</span> {{ $dogrulandi ? __('card.verified') : __('card.under_review') }}</span>
            <div class="flex items-center gap-1">
                {{-- Favori "Mutfağım" özelliğine ait; bayrak kapalıyken rota 404 verir. --}}
                @auth
                    @if($showFavorite && app(\App\Services\SiteFeatureService::class)->enabled('member_kitchen'))
                        @php($favoriteIds = once(fn () => auth()->user()->favoriteRecipes()->pluck('recipes.id')->flip()->all()))
                        @php($isFavorite = isset($favoriteIds[$recipe->id]))
                        <form method="post" action="{{ route('favorites.toggle', $recipe) }}" class="relative z-10">@csrf<button class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" type="submit" aria-pressed="{{ $isFavorite ? 'true' : 'false' }}" aria-label="{{ $isFavorite ? __('card.remove_from_favorites_aria', ['title' => $recipe->translated('title')]) : __('card.add_to_favorites_aria', ['title' => $recipe->translated('title')]) }}">{{ $isFavorite ? '♥' : '♡' }}<span class="sr-only">{{ $isFavorite ? __('card.remove_from_favorites') : __('card.add_to_favorites') }}</span></button></form>
                    @endif
                @endauth
                <a class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm font-bold" href="{{ route('recipes.show', $recipe) }}">{{ __('card.view_recipe') }} <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </div>
</article>
