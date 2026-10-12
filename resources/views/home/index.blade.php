{{-- Homepage: editorial hero, pantry matching, collections, trust and close. --}}
@php($siteFeatures = app(\App\Services\SiteFeatureService::class))
<section class="hc-hero relative isolate -mt-[var(--hc-header-h)] overflow-hidden">
    <div class="hc-hero__ground" aria-hidden="true"></div>
    <div class="hc-hero__grain" aria-hidden="true"></div>
    <div class="hc-container relative flex min-h-[44rem] flex-col justify-between gap-12 pb-8 pt-[calc(var(--hc-header-h)+4.5rem)] sm:min-h-[48rem] sm:pb-10 lg:min-h-[min(52rem,100svh)]">
        <div @class(['hc-hero__layout', 'hc-hero__layout--match' => ! empty($homeMatchShowcase)])>
        <div class="hc-hero__copy">
            <h1 class="hc-hero__title hc-animate-fade-up">{{ __('home.hero_title') }}</h1>
            <p class="hc-hero__lede hc-animate-fade-up">{{ __('home.hero_subtitle', ['count' => number_format((int) ($homeRecipeCount ?? 0), 0, ',', '.')]) }}</p>
            <div class="hc-hero__actions hc-animate-fade-up"><a class="hc-hero__cta hc-btn-primary" href="{{ $siteFeatures->enabled('explore') ? '#hero-ingredient' : route('recipes.index') }}"><span>{{ $siteFeatures->enabled('explore') ? __('home.hero_cta') : __('home.browse_archive') }}</span><x-ui.icon name="arrow-right" size="18" /></a>@if ($siteFeatures->enabled('explore'))<a class="hc-hero__link hc-hero-secondary" href="{{ route('recipes.index') }}"><span>{{ __('home.browse_archive') }}</span><x-ui.icon name="arrow-right" size="16" /></a>@endif</div>
            <p class="hc-hero__proof hc-animate-fade-up">{{ __('home.editorial_verified_only') }}</p>
        </div>
        @if($siteFeatures->enabled('explore') && ! empty($homeMatchShowcase))
            <div class="hc-match" x-data="hcMatchShowcase(@js($homeMatchShowcase), @js(['aria' => __('home.match_aria_prefix'), 'pisir' => __('home.match_can_cook_prefix')]))" @mouseenter="engelle('fare', true)" @mouseleave="engelle('fare', false)" @focusin="engelle('odak', true)" @focusout="engelle('odak', false)" :class="{ 'is-done': bitti, 'is-out': evre === 'cikis' }">
                <a class="hc-match__card" :href="senaryo.recipe.url" :aria-label="metin.aria + ' ' + senaryo.recipe.title">
                    <div class="hc-match__head" aria-hidden="true">
                        <span class="hc-match__label"><span class="hc-match__pulse"></span>{{ __('home.match_pantry') }}</span>
                        <span class="hc-match__count" x-show="pisirilebilir > 1" x-cloak x-text="metin.pisir + ' ' + pisirilebilir"></span>
                    </div>

                    <ul class="hc-match__pantry" aria-hidden="true">
                        <template x-for="(a, i) in adimlar" :key="sira + '-' + i">
                            <li class="hc-match__chip" :class="{ 'is-in': eklendi(i) }"><span x-text="a.ingredient"></span></li>
                        </template>
                    </ul>

                    <div class="hc-match__meter" aria-hidden="true">
                        <div class="hc-match__ring">
                            <svg viewBox="0 0 120 120" class="hc-match__ring-svg">
                                <circle class="hc-match__ring-track" cx="60" cy="60" r="52" />
                                <circle class="hc-match__ring-fill" cx="60" cy="60" r="52" :style="'stroke-dashoffset:' + (326.73 * (1 - yuzde / 100)).toFixed(2)" />
                            </svg>
                            <span class="hc-match__pct"><span class="hc-match__num"><span class="hc-match__num-val" :style="'--hc-pct:' + Math.round(yuzde)"></span><small>%</small></span></span>
                        </div>
                        <div class="hc-match__recipe">
                            <strong class="hc-match__recipe-title" x-text="senaryo.recipe.title"></strong>
                            <span class="hc-match__recipe-meta"><x-ui.icon name="clock" size="14" /><span x-text="senaryo.recipe.sure"></span></span>
                        </div>
                    </div>

                    <div class="hc-match__foot" aria-hidden="true">
                        <div class="hc-match__missing">
                            <span class="hc-match__missing-label">{{ __('home.match_missing') }}</span>
                            <template x-for="(a, i) in adimlar" :key="sira + '-e-' + i">
                                <span class="hc-match__missing-item" :class="{ 'is-gone': eklendi(i) }" x-text="a.ingredient"></span>
                            </template>
                        </div>
                        <div class="hc-match__done">
                            <span class="hc-match__done-badge"><x-ui.icon name="check" size="16" />{{ __('home.match_ready') }}</span>
                            <span class="hc-match__done-go">{{ __('home.match_open') }}<x-ui.icon name="arrow-right" size="14" /></span>
                        </div>
                    </div>
                </a>
            </div>
        @endif
        </div>
        {{-- Gösteri varken bant ikinci bir hareket olur ve aynı şeyi söyler. --}}
        @if(empty($homeMatchShowcase) && count($homeIngredients) > 7)
            <div class="hc-hero__rail" aria-hidden="true">
                <div class="hc-hero__rail-track">
                    @foreach([1, 2] as $tur)
                        <ul class="hc-hero__rail-set">
                            @foreach(array_slice($homeIngredients, 0, 14) as $malzeme)
                                <li>{{ $malzeme['name'] }}</li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>
        @endif
        <div id="kesfet" class="hc-home-command hc-animate-scale-in" x-data="{ activeSearchTab: '{{ $siteFeatures->enabled('explore') ? 'ingredients' : 'recipes' }}' }">
            <div class="hc-home-command__header"><div><p class="hc-home-command__eyebrow">{{ __('home.search_kicker') }}</p><h2 class="hc-home-command__title">@if($siteFeatures->enabled('explore'))<span x-show="activeSearchTab === 'ingredients'">{{ __('home.search_title') }}</span>@endif<span x-show="activeSearchTab === 'recipes'" @if($siteFeatures->enabled('explore')) x-cloak @endif>{{ __('home.search_recipes_title') }}</span></h2></div>@if($siteFeatures->enabled('explore'))<div class="hc-home-command__tabs" role="tablist" aria-label="{{ __('home.search_mode_label') }}"><button type="button" role="tab" @click="activeSearchTab = 'ingredients'" :aria-selected="activeSearchTab === 'ingredients'" :class="activeSearchTab === 'ingredients' ? 'is-active' : ''"><x-ui.icon name="sparkles" size="15" /> {{ __('home.search_tab_ingredients') }}</button><button type="button" role="tab" @click="activeSearchTab = 'recipes'" :aria-selected="activeSearchTab === 'recipes'" :class="activeSearchTab === 'recipes' ? 'is-active' : ''"><x-ui.icon name="search" size="15" /> {{ __('home.search_tab_recipes') }}</button></div>@endif</div>
            @if($siteFeatures->enabled('explore'))<div x-show="activeSearchTab === 'ingredients'" x-transition.opacity><form id="home-ingredients" class="hc-home-command__form" action="{{ route('ingredients.explore') }}" method="get"><label class="sr-only" for="hero-ingredient">{{ __('home.select_ingredient_label') }}</label><span class="hc-home-command__form-icon" aria-hidden="true"><x-ui.icon name="search" size="20" /></span><select id="hero-ingredient" class="hc-home-command__input" name="ingredients[]" required><option value="">{{ __('home.ingredient_placeholder_example') }}</option>@foreach($homeIngredients as $ingredient)<option value="{{ $ingredient['public_id'] }}">{{ $ingredient['name'] }}</option>@endforeach</select><button class="hc-home-command__submit" type="submit"><span>{{ __('home.what_can_i_cook') }}</span><span aria-hidden="true">↗</span></button></form></div>@endif
            <div x-cloak x-show="activeSearchTab === 'recipes'" x-transition.opacity><form id="home-search" class="hc-home-command__form" action="{{ route('recipes.index') }}" method="get"><label class="sr-only" for="hero-search-input">{{ __('archive.search_recipes') }}</label><span class="hc-home-command__form-icon" aria-hidden="true"><x-ui.icon name="search" size="20" /></span><input id="hero-search-input" class="hc-home-command__input" type="search" name="q" placeholder="{{ __('home.search_recipes_placeholder') }}" autocomplete="off"><button class="hc-home-command__submit" type="submit"><span>{{ __('archive.show_recipes') }}</span><span aria-hidden="true">↗</span></button></form></div>
            {{-- Tek "Hızlı giriş" satırı: aynı bağlantılar sayfanın aşağısında ikinci
                 bir çip satırı olarak tekrar ediyordu. Hub ve koleksiyon
                 bağlantıları (iç bağlantı için önemli) burada toplanır. --}}
            @php($mutfakSayilari = \App\Http\Controllers\CuisineHubController::ceviriSayilari())
            @php($kategoriSayilari = \App\Http\Controllers\CategoryHubController::ceviriSayilari())
            <div class="hc-home-command__links"><span>{{ __('home.quick_links') }}</span>@if(\App\Http\Controllers\CuisineHubController::sayfasiVar($mutfakSayilari['kibris-mutfagi'] ?? 0))<a href="{{ route('cuisines.show', 'kibris-mutfagi') }}">🇨🇾 {{ __('home.cypriot_cuisine') }}</a>@endif @if(\App\Http\Controllers\CuisineHubController::sayfasiVar($mutfakSayilari['turk-mutfagi'] ?? 0))<a href="{{ route('cuisines.show', 'turk-mutfagi') }}">🇹🇷 {{ turkce_sayfa() ? 'Türk Mutfağı' : __('taxonomy.cuisine.turk-mutfagi') }}</a>@endif @foreach([['home.under_20_min', 'sure', '20-dakika', ['max_minutes' => 20]], ['home.easy_recipes', 'zorluk', 'kolay', ['difficulty' => 'easy']]] as [$chipKey, $tur, $slug, $params])<a href="{{ $koleksiyonBaglantisi($tur, $slug) ?? route('recipes.index', $params) }}">{{ __($chipKey) }}</a>@endforeach @if(\App\Http\Controllers\CategoryHubController::sayfasiVar($kategoriSayilari['corba'] ?? 0))<a href="{{ route('categories.show', 'corba') }}">{{ __('home.quick_soups') }}</a>@endif @if(\App\Http\Controllers\CategoryHubController::sayfasiVar($kategoriSayilari['tatli'] ?? 0))<a href="{{ route('categories.show', 'tatli') }}">{{ __('home.quick_desserts') }}</a>@endif</div>
        </div>
    </div>
</section>

<section data-reveal class="hc-home-section hc-home-section--featured hc-container"><div class="hc-home-section__intro"><div><p class="hc-kicker">{{ __('home.whats_for_dinner') }}</p><h2 class="hc-display-lg mt-3">{{ __('home.next_great_plate') }}</h2></div><a class="hc-section-link" href="{{ route('recipes.index') }}">{{ __('home.go_to_archive') }} <span aria-hidden="true">↗</span></a></div>@if($homeHighlights->isNotEmpty())<div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4" data-reveal-group>@foreach($homeHighlights as $recipe)<x-recipe-card-v3 :recipe="$recipe" :show-favorite="false" heading="h3" />@endforeach</div>@else<div class="hc-featured-empty hc-empty mt-8"><p class="text-xl font-extrabold" style="color: var(--hc-text);">{{ __('home.featured_empty_title') }}</p><p class="mt-2">{{ __('home.featured_empty_subtitle') }}</p><a class="hc-btn hc-btn-outline mt-5" href="{{ route('recipes.index') }}">{{ __('home.go_to_archive') }}</a></div>@endif</section>

<x-ad-slot slot-name="display" class="hc-container my-10" />

@if($siteFeatures->enabled('explore'))<section data-reveal class="hc-home-pantry-band hc-home-section--dark"><div class="hc-container grid gap-12 py-20 lg:grid-cols-[.9fr_1.1fr] lg:items-center lg:py-28"><div class="max-w-xl"><p class="hc-kicker text-orange-300">{{ __('home.pantry_kicker') }}</p><h2 class="hc-display-lg mt-4 max-w-2xl text-white">{{ __('home.pantry_title') }}</h2><p class="mt-6 max-w-xl text-base leading-8 text-stone-300 sm:text-lg">{{ __('home.pantry_subtitle') }}</p><a class="hc-future-btn hc-future-btn--light mt-8" href="{{ route('ingredients.explore') }}"><span>{{ __('home.pantry_cta') }}</span><span aria-hidden="true">↗</span></a></div><ol class="hc-home-steps hc-home-steps--future" data-reveal-group><li><span>01</span><div><strong>{{ __('home.step_1') }}</strong></div></li><li><span>02</span><div><strong>{{ __('home.step_2') }}</strong></div></li><li><span>03</span><div><strong>{{ __('home.step_3') }}</strong></div></li></ol></div></section>@endif

<section data-reveal class="hc-home-section hc-home-section--collections hc-container"><div class="hc-home-section__intro"><div><p class="hc-kicker">{{ __('home.cuisines_kicker') }}</p><h2 class="hc-heading-2 mt-3">{{ __('home.cuisines_title') }}</h2></div><a class="hc-section-link" href="{{ route('cuisines.index') }}">{{ __('home.all_cuisines') }} <span aria-hidden="true">↗</span></a></div><div class="hc-collection-grid mt-9" data-reveal-group>@php($mutfakSayilari = \App\Http\Controllers\CuisineHubController::ceviriSayilari())@foreach($cuisines->filter(fn ($c) => ($mutfakSayilari[$c->slug] ?? 0) > 0)->sortByDesc(fn ($c) => $mutfakSayilari[$c->slug])->take(6) as $cuisine)@php($kapak = $mutfakKapaklari[$cuisine->id] ?? null)<a href="{{ \App\Http\Controllers\CuisineHubController::sayfasiVar($mutfakSayilari[$cuisine->slug]) ? route('cuisines.show', $cuisine->slug) : route('recipes.index', ['cuisine' => $cuisine->slug]) }}" class="hc-collection-card group">@if($kapak)<img src="{{ $kapak->responsiveUrl(800) }}" srcset="{{ $kapak->responsiveSrcset([400, 800]) }}" sizes="(min-width: 1024px) 30rem, 100vw" alt="{{ taxonomy_name($cuisine) }}" width="800" height="560" loading="lazy" decoding="async">@endif<span class="hc-collection-card__veil"></span><span class="hc-collection-card__content"><span class="hc-collection-card__count">{{ __('home.recipes_count', ['count' => $mutfakSayilari[$cuisine->slug]]) }}</span><strong>{{ taxonomy_name($cuisine) }}</strong><span class="hc-collection-card__arrow" aria-hidden="true">↗</span></span></a>@endforeach</div></section>

<x-home-testimonials :testimonials="collect(config('testimonials.home', []))->filter(fn ($item, $index) => in_array($index, [0, 2, 4]))->all()" :disclosure="config('testimonials.disclosure')" />

@if(($homeBlogPosts ?? collect())->isNotEmpty())<section data-reveal class="hc-home-section hc-container" aria-labelledby="home-guides-title"><div class="hc-home-section__intro"><div><p class="hc-kicker">{{ __('nav.blog') }}</p><h2 id="home-guides-title" class="hc-heading-2 mt-3">{{ __('home.nav_kitchen_guides') }}</h2></div><a class="hc-section-link" href="{{ route('blog.index') }}">{{ __('blog.show.read_more') }} <span aria-hidden="true">↗</span></a></div><div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>@foreach($homeBlogPosts as $yazi)<x-blog-card :post="$yazi" />@endforeach</div></section>@endif

<section data-reveal class="hc-home-close hc-container" aria-labelledby="home-membership-title"><div class="hc-home-close__inner"><div class="hc-home-close__copy"><p class="hc-kicker">{{ __('home.membership_kicker') }}</p><h2 id="home-membership-title" class="hc-heading-1 mt-4">{{ __('home.membership_title') }}</h2><p class="hc-body-md mt-5">{{ __('home.membership_subtitle') }}</p><a class="hc-future-btn hc-future-btn--primary mt-8" href="{{ auth()->check() ? ($siteFeatures->enabled('explore') ? route('ingredients.explore') : route('recipes.index')) : route('register') }}"><span>{{ auth()->check() ? __('home.membership_cta_logged_in') : __('home.membership_cta_guest') }}</span><span aria-hidden="true">↗</span></a></div><nav class="hc-home-close__nav" aria-label="{{ __('home.collections_nav') }}"><span>{{ __('home.collections_nav') }}</span><a href="{{ route('ingredients.hub.index') }}">{{ __('home.nav_by_ingredient') }} <b>↗</b></a><a href="{{ route('cuisines.index') }}">{{ __('home.nav_by_cuisine') }} <b>↗</b></a><a href="{{ route('categories.index') }}">{{ __('home.nav_by_category') }} <b>↗</b></a><a href="{{ $koleksiyonBaglantisi('sure', '30-dakika') ?? route('recipes.index', ['max_minutes' => 30]) }}">{{ __('home.nav_under_30_min') }} <b>↗</b></a><a href="{{ route('blog.index') }}">{{ __('home.nav_kitchen_guides') }} <b>↗</b></a></nav></div></section>
