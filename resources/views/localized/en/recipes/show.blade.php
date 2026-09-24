<x-layouts.site :title="$metadata['title']" :meta-description="$metadata['description']" :canonical-url="$metadata['canonical']" :robots="$metadata['robots']" :alternates="$alternates" :social-image="$heroMedia?->publicUrl()" :social-image-width="$heroMedia?->width" :social-image-height="$heroMedia?->height" :social-image-alt="$heroMedia ? ($translation->image_alt ?: $translation->title) : null" :structured-data="$schema">
    @php($isSyntheticHero = (bool) $heroMedia?->generated_by_ai)
    <section class="hc-recipe-detail-hero relative isolate overflow-hidden bg-charcoal-950 text-white">
        @if($heroMedia)
            <picture>
                @if($heroMedia->responsiveSrcset([800, 1200, 1600], 'avif'))<source type="image/avif" srcset="{{ $heroMedia->responsiveSrcset([800, 1200, 1600], 'avif') }}" sizes="100vw">@endif
                @if($heroMedia->responsiveSrcset([800, 1200, 1600], 'webp'))<source type="image/webp" srcset="{{ $heroMedia->responsiveSrcset([800, 1200, 1600], 'webp') }}" sizes="100vw">@endif
                <img class="absolute inset-0 -z-20 h-full w-full object-cover opacity-70" src="{{ $heroMedia->responsiveUrl(1600) }}" srcset="{{ $heroMedia->responsiveSrcset([800, 1200, 1600]) }}" sizes="100vw" width="1600" height="1000" alt="{{ $translation->image_alt ?: $translation->title }}" fetchpriority="high">
            </picture>
        @else
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_75%_20%,rgba(255,102,0,.32),transparent_28%),linear-gradient(135deg,#111913,#17251d_48%,#20160e)]" aria-hidden="true"></div>
        @endif
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(10,16,13,.97)_0%,rgba(10,16,13,.82)_48%,rgba(10,16,13,.25)_100%)]"></div>
        <div class="hc-container py-10 sm:py-14 lg:py-20">
            <div class="max-w-3xl">
                <nav aria-label="Breadcrumb" class="text-sm text-stone-300"><a class="hover:text-white" href="{{ route('en.home') }}">Home</a> <span aria-hidden="true">/</span> <a class="hover:text-white" href="{{ route('en.recipes.index') }}">Recipes</a></nav>
                <p class="hc-overline mt-8 text-orange-300">{{ $translation->cuisine_label ?: 'Home cooking' }} <span aria-hidden="true">/</span> {{ $translation->category_label ?: 'Recipe' }}</p>
                <h1 class="hc-display-xl mt-4 max-w-3xl">{{ $translation->title }}</h1>
                <p class="hc-body-lg mt-5 max-w-2xl text-stone-200">{{ $translation->excerpt }}</p>
                <dl class="mt-7 flex flex-wrap gap-2">@foreach([['Prep', $translation->prepMinutes().' min'], ['Cook', $translation->cookMinutes().' min'], ['Total', $translation->totalMinutes().' min'], ['Serves', $translation->servingCount()], ['Difficulty', $translation->difficulty_label ?: '—']] as [$label, $value])<div class="hc-v3-badge border-white/20 bg-white/10 text-white backdrop-blur"><dt class="font-extrabold text-orange-200">{{ $label }}</dt><dd class="ml-1">{{ $value }}</dd></div>@endforeach</dl>
                @if($heroMedia?->unsplashAttribution())
                    @php($credit = $heroMedia->unsplashAttribution())
                    <p class="mt-4 text-xs text-stone-300">Photo: @if($credit['photographer_url'])<a class="underline" href="{{ $credit['photographer_url'] }}" rel="noopener noreferrer" target="_blank">{{ $credit['photographer'] }}</a>@else{{ $credit['photographer'] }}@endif / @if($credit['unsplash_url'])<a class="underline" href="{{ $credit['unsplash_url'] }}" rel="noopener noreferrer" target="_blank">Unsplash</a>@else Unsplash @endif</p>
                @elseif($heroMedia?->attribution)
                    <p class="mt-4 text-xs text-stone-300">{{ $heroMedia->attribution }}</p>
                @endif
                @if($heroMedia && $isSyntheticHero)<p class="mt-2 text-xs text-stone-300">AI-generated image, editorially approved; not a photograph of this exact dish.</p>@endif
            </div>
        </div>
    </section>

    <nav class="sticky top-[4.25rem] z-30 border-b bg-[color:var(--hc-background-page)]/95 backdrop-blur" style="border-color: var(--hc-border-subtle)" aria-label="Recipe sections"><div class="hc-container flex gap-1 overflow-x-auto py-2 text-sm font-extrabold whitespace-nowrap"><a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#ingredients">Ingredients</a><a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#method">Method</a>@if(!empty($translation->tips))<a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#tips">Tips</a>@endif<a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#storage">Storage</a></div></nav>

    <section class="hc-container grid gap-8 py-8 sm:py-12 lg:grid-cols-[minmax(17rem,.78fr)_minmax(0,1.22fr)] lg:items-start">
        <aside class="space-y-5 lg:sticky lg:top-24">
            <section id="ingredients" class="hc-v3-card scroll-mt-32 p-5 sm:p-6" aria-labelledby="ingredients-heading">
                <div class="flex items-center justify-between gap-3"><h2 id="ingredients-heading" class="hc-heading-3">Ingredients</h2><span class="hc-v3-badge">Serves {{ $translation->servingCount() }}</span></div>
                @foreach(collect((array) $translation->ingredients)->groupBy(fn ($item) => $item['group'] ?? '') as $group => $items)
                    @if($group !== '')<h3 class="hc-overline mt-6" style="color: var(--hc-text-muted)">{{ $group }}</h3>@endif
                    <ul class="mt-3 divide-y" style="border-color: var(--hc-border-subtle)">@foreach($items as $item)<li class="py-3"><label class="flex cursor-pointer items-start gap-3"><input class="mt-1 shrink-0" type="checkbox" aria-label="Mark {{ $item['text'] }} as ready"><span class="text-sm font-bold" style="color: var(--hc-text-primary)">{{ $item['text'] }}</span></label></li>@endforeach</ul>
                @endforeach
            </section>
            @if(!empty($translation->equipment))
                <section class="hc-v3-card p-5 sm:p-6"><h2 class="hc-heading-3">Equipment</h2><div class="mt-4 flex flex-wrap gap-2">@foreach($translation->equipment as $equipment)<span class="hc-v3-badge">{{ $equipment }}</span>@endforeach</div></section>
            @endif
            <section class="hc-v3-card p-5 sm:p-6"><h2 class="hc-heading-3">Read in Turkish</h2><p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">The Turkish edition includes serving-size scaling, nutrition estimates and allergen analysis.</p><a class="hc-v3-btn hc-v3-btn--outline mt-4" href="{{ $turkishUrl }}" hreflang="tr" lang="tr">Türkçe tarif →</a></section>
        </aside>

        <div class="min-w-0">
            @if($translation->description)
                <div class="hc-body-md max-w-none space-y-4" style="color: var(--hc-text-secondary)">@foreach(preg_split('/\n\s*\n/', (string) $translation->description) as $paragraph)<p>{{ $paragraph }}</p>@endforeach</div>
            @endif
            <section id="method" class="mt-8 scroll-mt-32" aria-labelledby="method-heading">
                <div class="flex items-end justify-between gap-4"><div><p class="hc-kicker">Step by step</p><h2 id="method-heading" class="hc-display-lg mt-2">Method</h2></div><span class="hc-v3-badge">{{ count((array) $translation->steps) }} steps</span></div>
                <ol class="mt-7 space-y-4">@foreach((array) $translation->steps as $index => $step)<li id="step-{{ $index + 1 }}" class="hc-v3-card flex scroll-mt-32 gap-4 p-5 sm:gap-5 sm:p-6"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-orange-100 font-display text-lg font-bold text-orange-900 dark:bg-orange-500/20 dark:text-orange-200" aria-hidden="true">{{ $index + 1 }}</span><div class="min-w-0 flex-1"><div class="flex flex-wrap items-start justify-between gap-2"><h3 class="hc-heading-3">{{ $step['title'] ?? 'Step '.($index + 1) }}</h3>@if(!empty($step['minutes']))<span class="hc-v3-badge">⏱ {{ $step['minutes'] }} min</span>@endif</div><p class="hc-body-md mt-3" style="color: var(--hc-text-secondary)">{{ $step['text'] ?? '' }}</p>@if(!empty($step['cue']))<p class="mt-4 rounded-2xl bg-orange-50 p-3 text-sm leading-6 text-orange-950 dark:bg-orange-500/10 dark:text-orange-100"><strong>Look for:</strong> {{ $step['cue'] }}</p>@endif</div></li>@endforeach</ol>
            </section>

            @if(!empty($translation->tips) || !empty($translation->common_mistakes))
                <section id="tips" class="mt-8 grid scroll-mt-32 gap-4 md:grid-cols-2">@if(!empty($translation->tips))<article class="rounded-[1.5rem] border border-orange-200 bg-orange-50 p-5 dark:border-orange-500/20 dark:bg-orange-500/10 sm:p-6"><h2 class="hc-heading-3">Cook’s tips</h2><ul class="mt-4 space-y-3 text-sm leading-6">@foreach($translation->tips as $tip)<li class="flex gap-2"><span aria-hidden="true">✓</span><span>{{ $tip }}</span></li>@endforeach</ul></article>@endif @if(!empty($translation->common_mistakes))<article class="hc-v3-card p-5 sm:p-6"><h2 class="hc-heading-3">Common mistakes</h2><ul class="mt-4 space-y-3 text-sm leading-6" style="color: var(--hc-text-secondary)">@foreach($translation->common_mistakes as $mistake)<li class="flex gap-2"><span aria-hidden="true">ⓘ</span><span>{{ $mistake }}</span></li>@endforeach</ul></article>@endif</section>
            @endif

            <section id="storage" class="hc-v3-card mt-8 scroll-mt-32 p-5 sm:p-6"><h2 class="hc-heading-3">Serving &amp; storage</h2>
                @if($translation->serving_suggestions)<p class="hc-body-sm mt-4" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">To serve:</strong> {{ $translation->serving_suggestions }}</p>@endif
                @if($translation->storage_notes)<p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">Storage:</strong> {{ $translation->storage_notes }}</p>@endif
                @if($translation->freezing_notes)<p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">Freezing:</strong> {{ $translation->freezing_notes }}</p>@endif
                @if($translation->reheating_notes)<p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">Reheating:</strong> {{ $translation->reheating_notes }}</p>@endif
                <p class="mt-4 text-xs leading-5" style="color: var(--hc-text-muted)">Check labels for allergens; food-safety and health decisions are your own responsibility and this page is not medical advice.</p>
            </section>

            @if($relatedRecipes->isNotEmpty())
                <section class="mt-12" aria-labelledby="related-heading"><h2 id="related-heading" class="hc-display-lg">You may also like</h2><div class="mt-6 grid gap-5 sm:grid-cols-2">@foreach($relatedRecipes as $related)<x-localized.recipe-card :translation="$related" />@endforeach</div></section>
            @endif
        </div>
    </section>
</x-layouts.site>
