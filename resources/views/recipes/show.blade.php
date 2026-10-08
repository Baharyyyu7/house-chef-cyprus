<x-layouts.site :title="$metadata['title']" :meta-description="$metadata['description']" :canonical-url="$metadata['canonical']" :robots="$metadata['robots']" :social-image="$seoImage" :social-image-width="$seoImageWidth" :social-image-height="$seoImageHeight" :social-image-alt="$seoImageAlt" :structured-data="$seoSchema" :seo-content="$recipe">
    @php
        // Favori ve koleksiyon "Mutfağım"a ait; bayrak kapalıyken rotaları 404.
        $mutfagimAcik = auth()->check() && app(\App\Services\SiteFeatureService::class)->enabled('member_kitchen');
        $displayMedia = app(\App\Services\Media\RecipeDisplayMediaService::class)->resolve($recipe);
        $heroMedia = $displayMedia['media'];
        $hasApprovedMedia = $displayMedia['has_specific_media'];
        $isSyntheticHero = $displayMedia['is_synthetic'];
    @endphp

    {{-- Hero başlık çubuğunun altından başlar; çubuk sayfa tepesindeyken
         saydam durduğu için arada krem bir bant kalmaz. --}}
    <section class="hc-recipe-detail-hero relative isolate -mt-[var(--hc-header-h)] overflow-hidden bg-charcoal-950 pt-[var(--hc-header-h)] text-white">
        @if($hasApprovedMedia && $heroMedia)
            <picture>
                @if($heroMedia->responsiveSrcset([800, 1200, 1600], 'avif'))<source type="image/avif" srcset="{{ $heroMedia->responsiveSrcset([800, 1200, 1600], 'avif') }}" sizes="100vw">@endif
                @if($heroMedia->responsiveSrcset([800, 1200, 1600], 'webp'))<source type="image/webp" srcset="{{ $heroMedia->responsiveSrcset([800, 1200, 1600], 'webp') }}" sizes="100vw">@endif
                <img class="absolute inset-0 -z-20 h-full w-full object-cover opacity-70" src="{{ $heroMedia->responsiveUrl(1600) }}" srcset="{{ $heroMedia->responsiveSrcset([800, 1200, 1600]) }}" sizes="100vw" width="1600" height="1000" alt="{{ $recipe->translated('title') }}" aria-hidden="true" fetchpriority="high" style="view-transition-name: hc-kahraman">
            </picture>
        @else
            <img class="absolute inset-0 -z-20 h-full w-full object-cover opacity-60" src="{{ $displayMedia['hero_url'] }}" width="1600" height="1000" alt="{{ $recipe->translated('title') }}" aria-hidden="true" fetchpriority="high" style="view-transition-name: hc-kahraman">
        @endif
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(10,16,13,.97)_0%,rgba(10,16,13,.82)_48%,rgba(10,16,13,.25)_100%)]"></div>
        <div class="hc-container grid gap-8 py-10 sm:py-14 lg:grid-cols-[minmax(0,1fr)_19rem] lg:py-20">
            <div class="max-w-3xl">
                <a class="hc-v3-btn hc-v3-btn--ghost -ml-3 text-white hover:bg-white/10" href="{{ route('recipes.index') }}"><span aria-hidden="true">←</span> {{ __('recipe.back_to_archive') }}</a>
                <p class="hc-overline mt-8 text-orange-300">@if($recipe->cuisine && $cuisineUrl)<a href="{{ $cuisineUrl }}">{{ taxonomy_name($recipe->cuisine) }}</a>@else {{ __('recipe.home_cooking') }} @endif <span aria-hidden="true">/</span> {{ $recipe->region ?: ($recipe->category ? taxonomy_name($recipe->category) : __('recipe.recipe')) }}</p>
                <h1 class="hc-display-xl mt-4 max-w-3xl">{{ $recipe->translated('title') }}</h1>
                <p class="hc-body-lg mt-5 max-w-2xl text-stone-200">{{ $recipe->translated('excerpt') }}</p>
                <div class="mt-7 flex flex-wrap gap-2">@foreach([[__('recipe.prep'), $recipe->prep_minutes.' '.__('recipe.min')], [__('recipe.cook'), $recipe->cook_minutes.' '.__('recipe.min')], [__('recipe.total'), $recipe->totalMinutes().' '.__('recipe.min')], [__('recipe.servings'), $recipe->servings], [__('recipe.difficulty'), $recipe->zorlukEtiketi()]] as [$label, $value])<span class="hc-v3-badge border-white/20 bg-white/10 text-white backdrop-blur"><strong class="text-orange-200">{{ $label }}</strong> {{ $value }}</span>@endforeach</div>
                {{-- Onaylı fotoğrafı olmayan tarifte arka plan temsilî stok
                     görselidir; yemeğin kendi fotoğrafı gibi sunulmaz. --}}
                @if(!$hasApprovedMedia)<p class="mt-3 inline-flex items-center gap-1.5 rounded-full border border-dashed border-white/40 px-3 py-1 text-xs font-semibold text-stone-200"><span aria-hidden="true">🖼</span> {{ __('recipe.show.representative_image') }}</p>@endif
                @include('recipes.partials.rating', ['recipe' => $recipe, 'summary' => $ratingSummary])
                <div class="mt-6 flex flex-wrap items-center gap-3"><span class="text-xs text-stone-300">{{ __('recipe.last_editorial_review') }}: {{ $recipe->editorial_reviewed_at?->format('d.m.Y') ?: __('recipe.not_recorded') }}</span>@if($mutfagimAcik)<form method="post" action="{{ route('favorites.toggle', $recipe) }}">@csrf<button class="hc-v3-btn hc-v3-btn--outline border-white/20 bg-white/10 text-white hover:bg-white/20" type="submit" aria-pressed="{{ $isFavorited ? 'true' : 'false' }}" aria-label="{{ __('card.add_to_favorites_aria', ['title' => $recipe->translated('title')]) }}">{{ $isFavorited ? __('recipe.in_favorites') : __('recipe.add_to_favorites') }}</button></form>@endif</div>
                <div class="mt-2"><x-social-share :on-dark="true" :title="$recipe->title" :description="$recipe->excerpt" :image="$heroMedia?->publicUrl() ? $heroMedia->responsiveUrl(1200) : $displayMedia['hero_url']" /></div>
                <div class="mt-3 flex flex-col items-start" x-data="{ openEmbed: false, copied: false }">
                    <button type="button" @click="openEmbed = !openEmbed" class="inline-flex items-center gap-1.5 text-xs font-semibold text-stone-300 hover:text-white bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-full transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        <span>{{ app()->getLocale() === 'en' ? 'Embed Recipe Badge' : 'Tarifi Sitene Ekle' }}</span>
                    </button>
                    <div x-show="openEmbed" x-cloak class="mt-2 w-full max-w-lg rounded-xl bg-charcoal-900/95 border border-white/20 p-4 text-xs text-stone-200 backdrop-blur shadow-xl">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-orange-300">{{ app()->getLocale() === 'en' ? 'Embed Code (HTML)' : 'Gömme Kodu (HTML)' }}</span>
                            <button type="button" @click="openEmbed = false" class="text-stone-400 hover:text-white text-base">&times;</button>
                        </div>
                        <p class="text-stone-400 mb-2">{{ __('recipe.show.embed_help') }}</p>
                        <textarea readonly class="w-full bg-black/50 border border-white/10 rounded p-2 font-mono text-[11px] text-amber-200 h-20 select-all" x-ref="embedInput"><iframe src="{{ route('recipes.embed', $recipe) }}" width="100%" height="340" frameborder="0" style="border:none; border-radius:12px; max-width:540px;"></iframe></textarea>
                        <button type="button" @click="navigator.clipboard.writeText($refs.embedInput.value); copied = true; setTimeout(() => copied = false, 2000)" class="mt-2 bg-amber-600 hover:bg-amber-500 text-white font-bold px-3 py-1.5 rounded text-xs transition flex items-center gap-1">
                            <span x-text="copied ? '{{ app()->getLocale() === 'en' ? '✓ Copied' : '✓ Kopyalandı' }}' : '{{ app()->getLocale() === 'en' ? 'Copy Embed Code' : 'Kodu Kopyala' }}'"></span>
                        </button>
                    </div>
                </div>
                @if($mutfagimAcik)<div class="mt-3 flex flex-wrap gap-2" aria-label="{{ __('recipe.show.collect_manage') }}">@foreach($recipeCollections as $collection)<form method="post" action="{{ route('collections.recipes.add', ['recipeCollection' => $collection, 'recipe' => $recipe->slug]) }}">@csrf<button class="rounded-full border border-white/20 bg-white/10 px-3 py-2 text-xs font-bold text-white hover:bg-white/20">+ {{ $collection->name }}</button></form>@endforeach<a class="rounded-full border border-white/20 px-3 py-2 text-xs font-bold text-white" href="{{ route('collections.index') }}">{{ __('recipe.show.collect_manage') }}</a></div>@endif
                @if($heroMedia?->unsplashAttribution())
                    @php($unsplashCredit = $heroMedia->unsplashAttribution())
                    <p class="mt-4 text-xs text-stone-300">
                        {{ __('recipe.show.photo_credit') }}
                        @if($unsplashCredit['photographer_url'])
                            <a class="underline" href="{{ $unsplashCredit['photographer_url'] }}" rel="noopener noreferrer" target="_blank">{{ $unsplashCredit['photographer'] }}</a>
                        @else
                            {{ $unsplashCredit['photographer'] }}
                        @endif
                        {{ __('recipe.show.photo_by') }}
                        @if($unsplashCredit['unsplash_url'])
                            <a class="underline" href="{{ $unsplashCredit['unsplash_url'] }}" rel="noopener noreferrer" target="_blank">Unsplash</a>
                        @else
                            Unsplash
                        @endif
                    </p>
                @elseif($heroMedia?->attribution)
                    <p class="mt-4 text-xs text-stone-300">{{ $heroMedia->attribution }}</p>
                @endif
                @if($hasApprovedMedia && $isSyntheticHero)
                    <p class="mt-2 text-xs text-stone-300">{{ __('recipe.show.ai_image_note') }}</p>
                @endif
            </div>
            <aside class="self-end rounded-[1.5rem] border border-white/15 bg-black/30 p-5 backdrop-blur-md">
                <p class="hc-overline text-orange-300">{{ __('recipe.show.trust_kicker') }}</p>
                <p class="mt-3 text-sm leading-6 text-stone-100">{{-- Yapay zekâ beyanı hero altındaki tek küçük satırda; kutu aynı cümleyi ikinci kez söylüyordu. --}}{{ $hasApprovedMedia ? __('recipe.show.trust_with_approved_real') : __('recipe.show.trust_no_photo') }}</p>
                <div class="mt-4 flex flex-wrap gap-2"><span class="hc-v3-badge border-white/20 bg-white/10 text-white">{{ $hasApprovedMedia && $recipe->verification_status === 'verified' && $dataComplete ? __('recipe.show.record_verified') : __('recipe.show.under_review') }}</span><span class="hc-v3-badge border-white/20 bg-white/10 text-white">{{ __('recipe.show.ingredient_count', ['n' => $recipe->ingredients->count()]) }}</span></div>
            </aside>
        </div>
    </section>

    <nav class="sticky hc-sticky-under-header z-30 border-b bg-[color:var(--hc-background-page)]/95 backdrop-blur" style="border-color: var(--hc-border-subtle)" aria-label="{{ __('recipe.show.section_nav_label') }}"><div class="hc-container flex gap-1 overflow-x-auto py-2 text-sm font-extrabold whitespace-nowrap"><a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#ingredients">{{ __('recipe.ingredients') }}</a><a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#steps">{{ __('recipe.instructions') }}</a>@if(turkce_sayfa() && !empty($recipe->equipment))<a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#equipment">{{ __('recipe.show.equipment_title') }}</a>@endif<a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#nutrition">{{ __('recipe.nutrition') }}</a><a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#allergens">{{ __('recipe.allergens') }}</a>@if(filled($recipe->storage_notes) || (turkce_sayfa() && (filled($recipe->serving_suggestions) || filled($recipe->reheating_notes) || filled($recipe->freezing_notes))))<a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#storage">{{ __('recipe.storage') }}</a>@endif</div></nav>

    <section class="hc-container grid gap-8 py-8 sm:py-12 lg:grid-cols-[minmax(17rem,.78fr)_minmax(0,1.22fr)] lg:items-start" data-hc-okuma>
        <aside class="space-y-5 lg:sticky lg:top-24">
            @unless($hasApprovedMedia)
                <section class="hc-v3-alert hc-v3-alert--warning" aria-label="{{ __('recipe.show.no_photo_warning_title') }}"><span aria-hidden="true">ⓘ</span><div><strong>{{ __('recipe.show.no_photo_warning_title') }}</strong><p>{{ __('recipe.show.no_photo_warning_body') }}</p></div></section>
            @endunless
            <section id="ingredients" class="hc-v3-card hc-scroll-target p-5 sm:p-6" aria-labelledby="household-ingredients" x-data="hcRecipeIngredients('{{ $recipe->slug }}', { base: {{ (int) $recipe->servings }}, current: {{ $servings }}, harita: 'hc-porsiyon-haritasi', not: @js(__('recipe.portion_note', ['servings' => '__N__'])) })">
                {{-- Porsiyon artık sayfayı yenilemiyor: mobilde "+" sayfayı en
                     başa atıyordu ve malzeme kartı ~1300 px aşağıda kalıyordu.
                     1-20 porsiyonun satırları sunucuda aynı biçimleyiciyle
                     hazırlanır; JS kapalıysa form #ingredients'e döner. --}}
                <script type="application/json" id="hc-porsiyon-haritasi">@json($servingsMap)</script>
                <div class="flex items-center justify-between gap-3"><h2 id="household-ingredients" class="hc-heading-3">{{ __('recipe.ingredients') }}</h2><div class="flex items-center gap-2"><button type="button" class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm text-xs" x-show="count > 0" x-cloak x-on:click="reset()">{{ __('recipe.show.ingredients_reset') }}</button><span class="hc-v3-badge">{{ __('recipe.show.ingredients_count', ['n' => count($ingredientLines)]) }}</span></div></div>
                <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)" x-text="portionNote" aria-live="polite">{{ __('recipe.portion_note', ['servings' => $servings]) }}</p>
                <form class="mt-5 flex items-end gap-2" method="get" action="#ingredients" x-on:submit="if (setServings($refs.servingsInput.value)) $event.preventDefault()" toolname="adjust_servings" tooldescription="Adjust recipe portions and scaled ingredient quantities"><button class="hc-v3-btn hc-v3-btn--outline !min-h-11 !min-w-11 !px-3" type="submit" name="servings_step" value="-1" aria-label="{{ __('recipe.decrease_servings') }}" @disabled($servings <= 1) x-bind:disabled="servings <= 1" x-on:click="if (step(-1)) $event.preventDefault()">−</button><div class="min-w-0 flex-1"><label class="hc-v3-field-label" for="servings">{{ __('recipe.servings') }}</label><input id="servings" class="hc-v3-input text-center" type="number" min="1" max="99" name="servings" value="{{ $servings }}" aria-label="{{ __('recipe.servings') }}" x-ref="servingsInput" x-bind:value="servings" x-on:change="setServings($event.target.value)"></div><button class="hc-v3-btn hc-v3-btn--outline !min-h-11 !min-w-11 !px-3" type="submit" name="servings_step" value="1" aria-label="{{ __('recipe.increase_servings') }}" x-on:click="if (step(1)) $event.preventDefault()">+</button></form><a class="mt-3 inline-flex min-h-[24px] items-center text-xs font-extrabold" style="color: var(--hc-text-accent)" href="{{ route('recipes.show', $recipe) }}#ingredients" x-on:click="if (setServings({{ (int) $recipe->servings }})) $event.preventDefault()">{{ __('recipe.default_servings', ['servings' => $recipe->servings]) }}</a>
                @php($malzemeGruplari = collect($ingredientLines)->groupBy('group'))
                @foreach($malzemeGruplari as $group => $lines)
                    {{-- Tek grup varsa adini basmak anahati kirletiyor: Cig
                         Kofte'de grup adi "Malzemeler"di ve bolumun kendi
                         basligini tekrar ediyordu. Grup basligi ancak birden
                         fazla grup varken bilgi tasiyor. --}}
                    @if($malzemeGruplari->count() > 1)
                        <h3 class="hc-overline mt-6" style="color: var(--hc-text-muted)">{{ $group }}</h3>
                    @endif
                    <ul class="mt-2 divide-y" style="border-color: var(--hc-border-subtle)">@foreach($lines as $item)<li class="py-3 transition-opacity duration-200" :class="isChecked('{{ $item['line']->id }}') ? 'opacity-40 line-through' : ''"><label class="flex cursor-pointer items-start gap-3"><input class="mt-1 shrink-0 rounded text-orange-600 focus:ring-orange-500" type="checkbox" aria-label="{{ $item['formatted'] }}" :checked="isChecked('{{ $item['line']->id }}')" @change="toggle('{{ $item['line']->id }}')"><span><span class="text-sm font-bold" style="color: var(--hc-text-primary)"><span x-effect="const t = line('{{ $item['line']->id }}', 0); if (t !== null) $el.textContent = t">{{ $item['formatted'] }}</span> @if($item['line']->is_optional)<span class="hc-v3-badge ml-1">{{ __('recipe.show.optional_badge') }}</span>@endif</span>@if($item['line']->preparation_note && app()->getLocale() === config('locales.default', 'tr'))<span class="mt-1 block text-xs" style="color: var(--hc-text-secondary)">{{ $item['line']->preparation_note }}</span>@endif@if($item['canonical_text'] !== '')<span class="mt-1 block text-xs" style="color: var(--hc-text-muted)" data-canonical-measure>{{ $item['is_approximate'] ? __('recipe.show.approx_prefix') : '' }}<span x-effect="const t = line('{{ $item['line']->id }}', 1); if (t) $el.textContent = t">{{ $item['canonical_text'] }}</span> · {{ $item['line']->conversion_confidence === 'verified' ? __('recipe.show.verified_measure') : __('recipe.show.approx_measure') }}</span>@endif@if($item['scaling_note'] && app()->getLocale() === config('locales.default', 'tr'))<span class="mt-1 block text-xs text-amber-700 dark:text-amber-300">{{ $item['scaling_note'] }}</span>@endif</span></label></li>@endforeach</ul>
                @endforeach
                @auth
                    @if($mutfagimAcik)<form class="mt-6" method="post" action="{{ route('favorites.toggle', $recipe) }}" toolname="save_recipe" tooldescription="Save or remove this recipe from your personal favorites collection">@csrf<button class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--block" type="submit" aria-pressed="{{ $isFavorited ? 'true' : 'false' }}" aria-label="{{ $isFavorited ? __('recipe.in_favorites') : __('recipe.add_to_favorites') }}">{{ $isFavorited ? __('recipe.in_favorites') : __('recipe.add_to_favorites') }}</button></form>@endif
                    <form class="mt-3" method="post" action="{{ route('cooking-sessions.start', $recipe) }}" toolname="start_cooking_session" tooldescription="Start an interactive step-by-step cooking session with timers and progress tracking">@csrf<input type="hidden" name="servings" value="{{ $servings }}" x-bind:value="servings"><button class="hc-v3-btn hc-v3-btn--primary hc-v3-btn--block" type="submit">{{ __('recipe.show.start_cooking_btn') }}</button></form>
                @else
                    <a class="hc-v3-btn hc-v3-btn--primary hc-v3-btn--block mt-6" href="{{ route('login') }}">{{ __('recipe.show.login_to_cook_btn') }}</a>
                @endauth
            </section>

            @if($match)<div class="hc-v3-alert hc-v3-alert--success"><span aria-hidden="true">✓</span><div><strong>{{ $match['status'] }}</strong><p>{{ __('recipe.show.pantry_match_required', ['available' => $match['available'], 'required' => $match['required']]) }}</p></div></div>@endif

            {{-- Kapsamı eşiğin altındaki toplam yemeğin değil, ölçülebilen birkaç
                 malzemenin toplamıdır (kuzu kolda "6 kcal"). Değer o zaman hiç
                 basılmaz; kcal tam sayı, makrolar tek ondalık ve dilin ayracıyla. --}}
            @php($sayiBicim = fn ($deger, int $ondalik): string => number_format(round((float) $deger, $ondalik), $ondalik, app()->getLocale() === 'en' ? '.' : ',', ''))
            <section id="nutrition" class="hc-v3-card hc-scroll-target p-5 sm:p-6"><div class="flex items-start justify-between gap-3"><div><p class="hc-overline" style="color: var(--hc-text-muted)">{{ __('recipe.show.nutrition_kicker') }}</p><h2 class="hc-heading-3 mt-2">{{ __('recipe.show.nutrition_title') }}</h2></div><span class="hc-v3-badge {{ $nutritionShown ? 'hc-v3-badge--accent' : 'hc-v3-badge--warning' }}">{{ __('recipe.show.nutrition_completeness', ['pct' => $nutritionCoverage]) }}</span></div>@if($nutritionShown)<p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">{{ __('recipe.show.nutrition_confidence', ['pct' => $nutrition['confidence_score'], 'estimated' => $nutrition['contains_estimated_values'] ? __('recipe.show.nutrition_estimated') : __('recipe.show.nutrition_calculated')]) }}</p><dl class="mt-5 grid grid-cols-2 gap-3">@foreach([['energy_kcal', __('recipe.calories'), 0], ['protein', __('recipe.protein'), 1], ['carbohydrate', __('recipe.carbs'), 1], ['fat', __('recipe.fat'), 1]] as [$code, $label, $ondalik])@if(isset($nutrition['nutrients'][$code]))<div class="rounded-2xl p-3" style="background: var(--hc-background-subtle)"><dt class="text-xs" style="color: var(--hc-text-muted)">{{ $label }}</dt><dd class="mt-1 text-sm font-extrabold"><span class="inline-block tabular-nums" data-hc-say>{{ $sayiBicim($nutrition['nutrients'][$code]['per_serving'], $ondalik) }}</span> {{ $nutrition['nutrients'][$code]['unit'] }}</dd></div>@endif@endforeach</dl>@else<p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)" data-nutrition-insufficient><strong style="color: var(--hc-text-primary)">{{ __('recipe.show.nutrition_insufficient_title') }}</strong> {{ __('recipe.show.nutrition_insufficient_body') }}</p>@endif<p class="mt-4 text-xs leading-5" style="color: var(--hc-text-muted)">{{ __('recipe.show.nutrition_disclaimer') }}</p></section>

            <section id="allergens" class="hc-v3-card hc-scroll-target p-5 sm:p-6"><h2 class="hc-heading-3">{{ __('recipe.show.allergens_title') }}</h2>{{-- "İçerir" doğrulanmış kayıtla malzeme adından çıkarımın birleşimi:
                 eşleme tablosu eksikken flaouna süt, yumurta ve susama rağmen
                 uyarısızdı. Çıkarım yalnız uyarı yönünde çalışır. --}}@if($allergenSummary['contains'] !== [])<p class="mt-3 text-sm font-bold text-red-700 dark:text-red-300" data-allergens-contains>{{ __('recipe.show.allergens_contains') }} {{ collect($allergenSummary['contains'])->pluck('label')->join(', ') }}</p><ul class="mt-2 space-y-1 text-xs" style="color: var(--hc-text-secondary)">@foreach($allergenSummary['contains'] as $alerjen)@if($alerjen['ingredients'] !== [])<li><strong style="color: var(--hc-text-primary)">{{ $alerjen['label'] }}</strong>: {{ implode(', ', $alerjen['ingredients']) }}</li>@endif@endforeach</ul>@elseif(! $allergenSummary['verified'])<p class="mt-3 text-sm" style="color: var(--hc-text-secondary)">{{ __('recipe.show.allergens_none_found') }}</p>@endif@if($allergenSummary['may_contain'] !== [])<p class="mt-2 text-sm font-bold text-amber-700 dark:text-amber-300">{{ __('recipe.show.allergens_may_contain') }} {{ implode(', ', $allergenSummary['may_contain']) }}</p>@endif@unless($allergenSummary['verified'])<div class="hc-v3-alert hc-v3-alert--danger mt-4" role="note" data-allergens-unverified><span aria-hidden="true">⚠</span><div><strong>{{ __('recipe.show.allergens_unverified_title') }}</strong><p>{{ __('recipe.show.allergens_unverified_body') }}</p></div></div>@endunless@php($bilinen = collect($dietaryCompatibility)->reject(fn (array $d): bool => $d['status'] === 'uncertain'))@php($bilinmeyen = collect($dietaryCompatibility)->filter(fn (array $d): bool => $d['status'] === 'uncertain')){{-- Dokuz satirin dokuzu da "belirsiz" diyor ve bilgi tasiyan satirlari boguyordu. Bilinmeyen gizlenmiyor, tek durust satirda toplaniyor. Etiketler dietary_tags'teki Turkce adlardan geliyor; sablon ham kodu title-case edip Turkce bir sitede "Gluten Free" yaziyordu. --}}<div class="mt-4 flex flex-wrap gap-2">@foreach($bilinen as $compatibility)<span class="hc-v3-badge {{ $compatibility['status'] === 'incompatible' ? 'hc-v3-badge--warning' : 'hc-v3-badge--success' }}"><span aria-hidden="true">{{ $compatibility['status'] === 'incompatible' ? '✕' : '✓' }}</span> {{ $compatibility['label'] }}: {{ $compatibility['status'] === 'incompatible' ? __('recipe.show.compat_incompatible') : __('recipe.show.compat_compatible') }}</span>@endforeach</div>@if($bilinmeyen->isNotEmpty())<p class="mt-3 text-sm" style="color: var(--hc-text-secondary)">{{ __('recipe.show.allergens_unknown_prefix') }} {{ $bilinmeyen->pluck('label')->join(', ') }}.</p>@endif<p class="mt-4 text-xs leading-5" style="color: var(--hc-text-muted)">{{ $allergenAnalysis['medical_disclaimer'] }}</p></section>

            <x-membership-invite />
        </aside>


        <main id="steps" class="min-w-0 hc-scroll-target">
            @php($stepCount = $recipe->steps->count())
            <div x-data="hcRecipeSteps('{{ $recipe->slug }}', {{ $stepCount }})" x-id="['step']">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div><p class="hc-kicker">{{ __('recipe.show.steps_kicker') }}</p><h2 class="hc-display-lg mt-2">{{ __('recipe.show.steps_title') }}</h2></div>
                <div class="flex items-center gap-2">
                    <div x-data="hcCookMode()" x-show="supported" x-cloak>
                        <button type="button"
                                @click="toggle()"
                                class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm inline-flex items-center gap-1.5 transition-colors"
                                :class="active ? '!border-emerald-500 !text-emerald-700 dark:!text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40' : ''"
                                :title="active ? '{{ __('recipe.show.cook_mode_on') }}' : '{{ __('recipe.show.cook_mode_off') }}'"
                                :aria-pressed="active ? 'true' : 'false'"
                                :aria-label="active ? '{{ __('recipe.show.cook_mode_on') }}' : '{{ __('recipe.show.cook_mode_off') }}'">
                            <span class="inline-block w-2 h-2 rounded-full" :class="active ? 'bg-emerald-500 animate-pulse' : 'bg-stone-400'" aria-hidden="true"></span>
                            <span x-text="active ? '{{ __('recipe.show.cook_mode_on') }}' : '{{ __('recipe.show.cook_mode_off') }}'"></span>
                        </button>
                    </div>
                    <span class="hc-v3-badge" x-text="label"></span>
                    <button type="button" class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" x-show="completed > 0" x-cloak x-on:click="reset()">{{ __('recipe.show.steps_reset') }}</button>
                </div>
            </div>
            @if($stepCount > 0)
                <p class="hc-body-sm mt-3" style="color: var(--hc-text-muted)">{{ __('recipe.show.steps_remember') }}</p>
                <div class="hc-step-progress mt-4" role="progressbar" aria-label="{{ __('recipe.show.steps_progress_label') }}" x-bind:aria-valuenow="completed" aria-valuemin="0" aria-valuemax="{{ $stepCount }}">
                    <span class="hc-step-progress__bar" x-bind:style="`width: ${percent}%`"></span>
                </div>
            @endif
            <div class="hc-v3-stagger mt-7 space-y-4" role="list" aria-label="{{ __('recipe.instructions') }}">@forelse($recipe->steps as $step)<article id="step-{{ $step->position }}" role="listitem" class="hc-v3-card hc-step flex gap-4 p-5 sm:gap-5 sm:p-6" x-bind:class="isDone({{ $step->position }}) ? 'hc-step--done' : ''"><button type="button" class="hc-step__toggle" x-on:click="toggle({{ $step->position }})" x-bind:aria-pressed="isDone({{ $step->position }}) ? 'true' : 'false'" aria-label="{{ __('recipe.show.steps_mark_done', ['n' => $step->position]) }}"><span x-show="!isDone({{ $step->position }})">{{ $step->position }}</span><span x-show="isDone({{ $step->position }})" x-cloak aria-hidden="true">✓</span></button><div class="min-w-0 flex-1"><div class="flex flex-wrap items-start justify-between gap-2"><h3 class="hc-heading-3">{{ $step->title }}</h3>@if($step->duration_seconds)<span class="hc-v3-badge">⏱ {{ intdiv($step->duration_seconds, 60) }} {{ __('recipe.show.step_timer', ['n' => '']) }}</span>@endif@php($sureSaniye = $step->duration_seconds ?: (app(\App\Services\StepDurationParser::class)->minutes($step->instruction) ?? app(\App\Services\StepDurationParser::class)->minutes($step->title)) * 60)@if($sureSaniye > 0)<button type="button" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm" x-on:click="$dispatch('hc-start-timer', { seconds: {{ $sureSaniye }}, label: @js($step->title) })">⏱ @if($sureSaniye % 60 === 0){{ intdiv($sureSaniye, 60) }} {{ __('recipe.timer.minutes_short') }}@else{{ sprintf('%02d:%02d', intdiv($sureSaniye, 60), $sureSaniye % 60) }}@endif</button>@endif</div><p class="hc-body-md mt-3" style="color: var(--hc-text-secondary)">{{ $step->instruction }}</p>@if($step->equipment)<p class="mt-3 text-sm" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">{{ __('recipe.show.equipment_title') }}:</strong> {{ $step->equipment }}</p>@endif@if($step->chef_tip)<p class="mt-4 rounded-2xl bg-orange-50 p-3 text-sm leading-6 text-orange-950 dark:bg-orange-500/10 dark:text-orange-100"><strong>{{ __('recipe.show.chef_tip') }}</strong> {{ $step->chef_tip }}</p>@endif@if($step->safety_note)<p class="mt-3 rounded-2xl bg-red-50 p-3 text-sm leading-6 text-red-950 dark:bg-red-500/10 dark:text-red-100"><strong>{{ __('recipe.show.safety_note') }}</strong> {{ $step->safety_note }}</p>@endif</div></article>@empty<div class="hc-v3-empty">{{ __('recipe.show.steps_empty') }}</div>@endforelse</div>
            </div>

            @include('recipes.partials.cook-timer')

            @if(app(\App\Services\SiteFeatureService::class)->enabled('services'))<x-recipe-chef-lead-card :recipe="$recipe" />@endif

            @if(turkce_sayfa() && !empty($recipe->equipment))
                <section id="equipment" class="hc-v3-card mt-8 hc-scroll-target p-5 sm:p-6"><p class="hc-overline" style="color: var(--hc-text-muted)">{{ __('recipe.show.equipment_kicker') }}</p><h2 class="hc-heading-3 mt-2">{{ __('recipe.show.equipment_title') }}</h2><div class="mt-4 flex flex-wrap gap-2">@foreach($recipe->equipment as $equipment)<span class="hc-v3-badge">⌁ {{ $equipment }}</span>@endforeach</div></section>
            @endif

            <x-recipe-equipment-affiliate :recipe="$recipe" />

            @php($ikameler = ! turkce_sayfa() ? collect() : $recipe->recipeIngredients->filter(fn ($kalem) => is_string($kalem->substitution) && trim($kalem->substitution) !== '')->values())
            @if(turkce_sayfa() && (!empty($recipe->tips) || !empty($recipe->common_mistakes) || $ikameler->isNotEmpty()))
                <section id="tips" class="mt-8 grid gap-4 hc-scroll-target md:grid-cols-2">@if(!empty($recipe->tips))<article class="rounded-[1.5rem] border border-orange-200 bg-orange-50 p-5 dark:border-orange-500/20 dark:bg-orange-500/10 sm:p-6"><p class="hc-overline text-orange-700 dark:text-orange-200">{{ __('recipe.show.tips_kicker') }}</p><h2 class="hc-heading-3 mt-2">{{ __('recipe.show.tips_title') }}</h2><ul class="mt-4 space-y-3 text-sm leading-6">@foreach($recipe->tips as $tip)<li class="flex gap-2"><span aria-hidden="true">✓</span><span>{{ $tip }}</span></li>@endforeach</ul></article>@endif@if(!empty($recipe->common_mistakes))<article class="hc-v3-card p-5 sm:p-6"><p class="hc-overline" style="color: var(--hc-text-muted)">{{ __('recipe.show.mistakes_kicker') }}</p><h2 class="hc-heading-3 mt-2">{{ __('recipe.show.mistakes_title') }}</h2><ul class="mt-4 space-y-3 text-sm leading-6" style="color: var(--hc-text-secondary)">@foreach($recipe->common_mistakes as $mistake)<li class="flex gap-2"><span aria-hidden="true">ⓘ</span><span>{{ $mistake }}</span></li>@endforeach</ul></article>@endif@if($ikameler->isNotEmpty())<article class="rounded-[1.5rem] border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-500/20 dark:bg-emerald-500/10 sm:p-6 md:col-span-2"><p class="hc-overline text-emerald-700 dark:text-emerald-200">{{ __('recipe.geo.subs_kicker') }}</p><h2 class="hc-heading-3 mt-2">{{ __('recipe.geo.subs_title') }}</h2><dl class="mt-4 space-y-3 text-sm leading-6">@foreach($ikameler as $kalem)<div class="flex gap-2"><dt class="font-bold">{{ $kalem->ingredient?->name ?? $kalem->group_name }}</dt><dd><span aria-hidden="true">→</span> {{ $kalem->substitution }}</dd></div>@endforeach</dl></article>@endif</section>
            @endif

            {{-- Boş bölüm basılmaz: 109 tarifte "Servis önerisi henüz eklenmedi" ve
                 "Saklama notu henüz eklenmedi" yazıyordu, SSS ise aynı tarif için
                 "3-4 gün" diyordu. Not yoksa kart da yok; kaynak kartı her zaman var. --}}
            @php($servisOnerisi = turkce_sayfa() ? trim((string) $recipe->serving_suggestions) : '')
            @php($saklamaUyari = turkce_sayfa() ? trim((string) (is_array($recipe->storage_notes) ? data_get($recipe->storage_notes, 'uyari', '') : $recipe->storage_notes)) : '')
            @php($saklamaGun = is_array($recipe->storage_notes) ? (int) data_get($recipe->storage_notes, 'buzdolabi_gun', 0) : 0)
            @php($isitma = turkce_sayfa() ? trim((string) $recipe->reheating_notes) : '')
            @php($dondurma = turkce_sayfa() ? trim((string) $recipe->freezing_notes) : '')
            @php($saklamaVar = $servisOnerisi !== '' || $saklamaUyari !== '' || $saklamaGun > 0 || $isitma !== '' || $dondurma !== '')
            <section id="storage" class="mt-8 grid gap-4 hc-scroll-target md:grid-cols-2">@if($saklamaVar)<article class="hc-v3-card p-5 sm:p-6" data-storage-notes><p class="hc-overline" style="color: var(--hc-text-muted)">{{ __('recipe.show.storage_kicker') }}</p><h2 class="hc-heading-3 mt-2">{{ __('recipe.show.storage_title') }}</h2>@if($servisOnerisi !== '')<p class="hc-body-sm mt-4" style="color: var(--hc-text-secondary)">{{ $servisOnerisi }}</p>@endif @if($saklamaUyari !== '' || $saklamaGun > 0)<p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">{{ __('recipe.show.storage_label') }}</strong> @if($saklamaGun > 0){{ __('recipe.show.storage_fridge_days', ['days' => $saklamaGun]) }} @endif{{ $saklamaUyari }}</p>@endif @if($isitma !== '')<p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">{{ __('recipe.show.reheat_label') }}</strong> {{ $isitma }}</p>@endif @if($dondurma !== '')<p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">{{ __('recipe.show.freeze_label') }}</strong> {{ $dondurma }}</p>@endif</article>@endif<article class="hc-v3-card p-5 sm:p-6"><p class="hc-overline" style="color: var(--hc-text-muted)">{{ __('recipe.show.source_kicker') }}</p><h2 class="hc-heading-3 mt-2">{{ __('recipe.show.source_title') }}</h2><p class="hc-body-sm mt-4" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">{{ \Illuminate\Support\Str::ucfirst($hasApprovedMedia && $recipe->verification_status === 'verified' && $dataComplete ? __('recipe.show.source_verified') : __('recipe.show.source_reviewing')) }}</strong></p><p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)">{{ __('recipe.show.source_last_check') }} {{ $recipe->editorial_reviewed_at?->format('d.m.Y') ?: __('recipe.show.source_not_recorded') }}</p>@if($recipe->provenances->isNotEmpty())<p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)">{{ $recipe->provenances->count() > 1 ? __('recipe.show.source_multi') : ($recipe->provenances->first()->contentSource?->source_name ?: __('recipe.show.source_single')) }}</p>@endif</article></section>

            {{-- Yazı içi reklam adımların bittiği yerde durur. AdSense'in kendi
                 yerleşim rehberi reklamı kenar çubuğuna koymayı açıkça
                 önermiyor; okuma akışının doğal bir molasında duruyor. --}}
            <x-ad-slot slot-name="in_article" />

            <x-recipe-monetization-callout :recipe="$recipe" />

            @if(turkce_sayfa() && (!empty($recipe->tips) || !empty($recipe->common_mistakes) || $ikameler->isNotEmpty()))<section class="mt-8 rounded-[1.5rem] bg-charcoal-950 p-5 text-white sm:p-7"><p class="hc-overline text-orange-300">{{ __('recipe.show.notes_kicker') }}</p><h2 class="hc-heading-3 mt-2">{{ __('recipe.show.notes_title') }}</h2><p class="mt-2 text-sm leading-6 text-stone-300">{{ __('recipe.show.notes_body') }}</p><a class="hc-v3-btn hc-v3-btn--primary mt-5" href="#tips">{{ __('recipe.show.notes_cta') }}</a></section>@endif

            @if(!empty($faqs))
                <section id="faq" class="mt-8 rounded-[1.5rem] border p-5 sm:p-7 hc-scroll-target" style="border-color: var(--hc-border-default); background-color: var(--hc-surface-card, #fff)">
                    <p class="hc-overline text-orange-600 dark:text-orange-400">{{ __('recipe.show.faq_kicker') }}</p>
                    <h2 class="hc-heading-3 mt-2" style="color: var(--hc-text-primary)">{{ __('recipe.show.faq_title') }}</h2>
                    <div class="mt-5 divide-y" style="border-color: var(--hc-border-subtle)">
                        @foreach($faqs as $faq)
                            <details class="group py-4 first:pt-0 last:pb-0" @if($loop->first) open @endif>
                                <summary class="flex cursor-pointer items-center justify-between font-semibold text-base leading-snug list-none" style="color: var(--hc-text-primary)">
                                    <span>{{ $faq['name'] }}</span>
                                    <span class="ml-4 flex-shrink-0 text-orange-600 transition group-open:rotate-180" aria-hidden="true">▾</span>
                                </summary>
                                <p class="hc-body-sm mt-3 leading-relaxed" style="color: var(--hc-text-secondary)">
                                    {{ $faq['acceptedAnswer']['text'] }}
                                </p>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($relatedRecipes->isNotEmpty())
                <section class="mt-12"><div class="flex items-end justify-between gap-4"><div><p class="hc-kicker">{{ __('recipe.show.related_kicker') }}</p><h2 class="hc-display-lg mt-2">{{ __('recipe.show.related_title') }}</h2></div><a class="hc-v3-btn hc-v3-btn--ghost" href="{{ $cuisineUrl ?: route('recipes.index') }}">{{ __('recipe.show.related_view_all') }}</a></div><div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">@foreach($relatedRecipes as $relatedRecipe)<x-recipe-card-v3 :recipe="$relatedRecipe" :show-favorite="false" heading="h3" />@endforeach</div></section>
            @endif

            @if(!empty($relatedGuides))
                <section class="mt-12">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="hc-kicker">{{ __('recipe.show.guides_kicker') }}</p>
                            <h2 class="hc-display-lg mt-2">{{ __('recipe.show.guides_title') }}</h2>
                        </div>
                        <a class="hc-v3-btn hc-v3-btn--ghost" href="{{ route('blog.index') }}">{{ __('recipe.show.guides_view_all') }}</a>
                    </div>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($relatedGuides as $guide)
                            <article class="hc-v3-card hc-v3-card--interactive flex flex-col p-5 rounded-2xl border relative" style="border-color: var(--hc-border-default)">
                                <span class="hc-kicker text-xs mb-2">{{ $guide['kicker'] }}</span>
                                <h3 class="font-bold text-base leading-snug line-clamp-2 mb-3" style="color: var(--hc-text-primary)">
                                    <a href="{{ $guide['url'] }}" class="before:absolute before:inset-0 hover:text-orange-600 transition-colors">{{ $guide['label'] }}</a>
                                </h3>
                                @if(!empty($guide['excerpt']))
                                    <p class="text-xs line-clamp-2 mb-4" style="color: var(--hc-text-secondary)">{{ $guide['excerpt'] }}</p>
                                @endif
                                <span class="mt-auto text-xs font-bold text-orange-600 flex items-center gap-1">{{ __('blog.show.read_more') }} &rarr;</span>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
            {{-- Multiplex birimi içerik önerisi ızgarası; yazının sonunda,
                 benzer tariflerin ardında duruyor. --}}
            <x-ad-slot slot-name="multiplex" class="mt-12" />
        </main>
    </section>
</x-layouts.site>
