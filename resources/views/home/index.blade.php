{{-- Hero sayfanın kendisidir, sayfanın üstünde duran bir kart değil.
         Tam genişlikte açılır ve alt kenarı sayfa zeminine karışır; böylece
         yüzen başlık çubuğu gerçekten içeriğin üstünden geçer. --}}
    <section class="hc-home-hero hc-home-hero--master relative isolate -mt-[var(--hc-header-h)] flex flex-col justify-center min-h-[82svh] items-stretch pb-16 pt-[calc(var(--hc-header-h)+3rem)] sm:pb-20">
        {{-- Video tek başına sahnedir: kapak görseli yok, önce-resim-sonra-video geçişi yok. Video gelene kadar koyu zemin durur. --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none -z-30 bg-[#060606]">
            <x-hero-video />
        </div>
        {{-- İki katman: yatay olan metne okunur bir zemin verir, dikey olan
             hem çubuğu hem de alt kenarı sayfa zeminine bağlar. --}}
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(96deg,rgba(9,9,9,.94)_0%,rgba(9,9,9,.88)_34%,rgba(9,9,9,.58)_54%,rgba(9,9,9,.14)_72%,rgba(9,9,9,0)_100%)] pointer-events-none"></div>
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(9,9,9,.50)_0%,rgba(9,9,9,.04)_22%,rgba(9,9,9,.10)_52%,rgba(9,9,9,.62)_100%)] pointer-events-none"></div>
        <div class="hc-home-hero__eritme pointer-events-none" aria-hidden="true"></div>
        <div class="hc-container">
            <div class="max-w-4xl">
                <p class="hc-kicker text-orange-300">{{ __('home.kicker') }}</p>
                <h1 class="hc-display-xl mt-5 max-w-3xl text-white">{{ __('home.hero_title') }}</h1>
                <p class="hc-hero-muted hc-body-lg mt-6 max-w-2xl">{{ __('home.hero_subtitle') }}</p>
                <div class="mt-9 flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                    <a class="hc-btn hc-btn-primary px-7" href="#hero-ingredient">{{ __('home.hero_cta') }} <span class="ml-1" aria-hidden="true">→</span></a>
                    <a class="hc-btn border border-white/40 bg-black/25 text-white backdrop-blur-md hover:bg-white hover:!text-stone-900 shadow-sm transition-all duration-200 px-6 font-semibold" href="{{ route('ingredients.explore') }}">🥕 {{ __('home.hero_pantry_btn') }}</a>
                    <a class="hc-hero-secondary text-sm font-semibold text-white/90 underline-offset-4 transition hover:text-white hover:underline sm:ml-2" href="{{ route('recipes.index') }}">{{ __('home.browse_archive') }}</a>
                </div>
            </div>
        </div>
        {{-- Arama kutusu sayfanın ilk eylemi; malzeme seçimi ve tarif aramasını şık sekmelerle tek alanda sunar --}}
    <div id="kesfet" class="hc-container relative z-20 mt-8">
        <div class="hc-arama hc-home-search" x-data="{ activeSearchTab: 'ingredients' }">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1">
                <div>
                    <p class="hc-kicker">{{ __('home.search_kicker') }}</p>
                    <h2 class="hc-heading-3 mt-1.5" style="color:var(--hc-text-primary)">
                        <span x-show="activeSearchTab === 'ingredients'">{{ __('home.search_title') }}</span>
                        <span x-show="activeSearchTab === 'recipes'" x-cloak>{{ __('home.search_recipes_title') }}</span>
                    </h2>
                </div>

                {{-- Segmented Mode Switcher --}}
                <div class="inline-flex rounded-2xl p-1 bg-stone-100 dark:bg-stone-800/90 border border-stone-200/80 dark:border-stone-700/80 text-xs font-bold shrink-0 self-start sm:self-center shadow-inner" role="tablist" aria-label="{{ __('home.search_mode_label') }}">
                    <button
                        type="button"
                        role="tab"
                        @click="activeSearchTab = 'ingredients'"
                        :class="activeSearchTab === 'ingredients' ? 'bg-white dark:bg-stone-900 text-orange-600 dark:text-orange-400 shadow-sm' : 'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-white'"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-xl transition-all duration-200"
                        :aria-selected="activeSearchTab === 'ingredients'"
                    >
                        <span>🥕 {{ __('home.search_tab_ingredients') }}</span>
                    </button>
                    <button
                        type="button"
                        role="tab"
                        @click="activeSearchTab = 'recipes'"
                        :class="activeSearchTab === 'recipes' ? 'bg-white dark:bg-stone-900 text-orange-600 dark:text-orange-400 shadow-sm' : 'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-white'"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-xl transition-all duration-200"
                        :aria-selected="activeSearchTab === 'recipes'"
                    >
                        <span>🔍 {{ __('home.search_tab_recipes') }}</span>
                    </button>
                </div>
            </div>

            {{-- Form 1: Malzeme Arama --}}
            <div x-show="activeSearchTab === 'ingredients'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <form id="home-ingredients" class="hc-arama__kutu mt-4" action="{{ route('ingredients.explore') }}" method="get">
                    <label class="sr-only" for="hero-ingredient">{{ __('home.select_ingredient_label') }}</label>
                    <span class="hc-arama__ikon" aria-hidden="true"><x-ui.icon name="search" size="20" /></span>
                    <select id="hero-ingredient" class="hc-arama__girdi" name="ingredients[]" required>
                        <option value="">{{ __('home.ingredient_placeholder_example') }}</option>
                        @foreach($homeIngredients as $ingredient)
                            <option value="{{ $ingredient['public_id'] }}">{{ $ingredient['name'] }}</option>
                        @endforeach
                    </select>
                    <button class="hc-arama__dugme" type="submit">
                        <span>{{ __('home.what_can_i_cook') }}</span>
                        <span class="hc-arama__ok" aria-hidden="true">→</span>
                    </button>
                </form>
            </div>

            {{-- Form 2: Doğrudan Tarif Arama --}}
            <div x-cloak x-show="activeSearchTab === 'recipes'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <form id="home-search" class="hc-arama__kutu mt-4" action="{{ route('recipes.index') }}" method="get">
                    <label class="sr-only" for="hero-search-input">{{ __('archive.search_recipes') }}</label>
                    <span class="hc-arama__ikon" aria-hidden="true"><x-ui.icon name="search" size="20" /></span>
                    <input
                        id="hero-search-input"
                        class="hc-arama__girdi"
                        type="search"
                        name="q"
                        placeholder="{{ __('home.search_recipes_placeholder') }}"
                        autocomplete="off"
                    >
                    <button class="hc-arama__dugme" type="submit">
                        <span>{{ __('archive.show_recipes') }}</span>
                        <span class="hc-arama__ok" aria-hidden="true">→</span>
                    </button>
                </form>
            </div>

            {{-- Hızlı Filtre Çipleri --}}
            <div class="hc-arama__oneriler mt-5">
                <span class="hc-arama__etiket">{{ __('home.quick_links') }}</span>
                <a class="hc-arama__cip" href="{{ route('cuisines.show', 'kibris-mutfagi') }}">{{ __('home.cypriot_cuisine') }}</a>
                {{-- Exact-match iç bağlantı: "Kıbrıs Yemekleri" araması hub sayfasına site içi en güçlü sayfadan bağlanır. --}}
                <a class="hc-arama__cip" href="{{ route('cuisines.show', 'kibris-mutfagi') }}">Kıbrıs Yemekleri</a>
                @foreach([['home.under_20_min', 'sure', '20-dakika', ['max_minutes' => 20]], ['home.easy_recipes', 'zorluk', 'kolay', ['difficulty' => 'easy']]] as [$chipKey, $tur, $slug, $params])
                    <a href="{{ $koleksiyonBaglantisi($tur, $slug) ?? route('recipes.index', $params) }}" class="hc-arama__cip">{{ __($chipKey) }}</a>
                @endforeach
                <a class="hc-arama__cip" href="{{ route('categories.show', 'corba') }}">🥣 {{ __('home.quick_soups') }}</a>
                <a class="hc-arama__cip" href="{{ route('categories.show', 'tatli') }}">🍯 {{ __('home.quick_desserts') }}</a>
                <a class="hc-arama__cip !border-orange-500/30 !bg-orange-500/10 !text-orange-700 dark:!text-orange-300 font-bold" href="{{ route('recipes.index') }}">{{ __('home.all_archive') }}</a>
            </div>
        </div>
    </div>

</section>

    <section data-reveal class="hc-container pb-20"><div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end"><div><p class="hc-kicker">{{ __('home.whats_for_dinner') }}</p><h2 class="hc-display-lg mt-3">{{ __('home.next_great_plate') }}</h2></div><p class="hc-body-sm max-w-sm" style="color:var(--hc-text-secondary)">{{ __('home.editorial_verified_only') }}</p></div>
        @if($homeHighlights->isNotEmpty())
            <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4" data-reveal-group>
                @foreach($homeHighlights as $recipe)
                    <x-recipe-card-v3 :recipe="$recipe" :show-favorite="false" heading="h3" />
                @endforeach
            </div>
        @else
            <div class="hc-featured-empty hc-empty mt-8"><p class="text-xl font-extrabold" style="color: var(--hc-text);">{{ __('home.featured_empty_title') }}</p><p class="mt-2">{{ __('home.featured_empty_subtitle') }}</p><a class="hc-btn hc-btn-outline mt-5" href="{{ route('recipes.index') }}">{{ __('home.go_to_archive') }}</a></div>
        @endif
    </section>

    {{-- Anasayfada hic reklam yuvasi yoktu: en cok goruntulenen sayfa hicbir
         gelir uretmiyordu. AD_SLOT_DISPLAY yapilandirmada tanimliydi ama hicbir
         sayfada kullanilmiyordu.

         Konum kasitli: one cikan tarifler bittikten sonra, dolap bandindan
         once. Ilk ekranda reklam yok - hero ve arama once gelir. --}}
    <x-ad-slot slot-name="display" class="hc-container my-10" />

    <section data-reveal class="hc-home-pantry-band"><div class="hc-container grid gap-8 py-14 lg:grid-cols-[1.18fr_.82fr] lg:items-end"><div><p class="hc-kicker text-orange-300">{{ __('home.pantry_kicker') }}</p><h2 class="hc-display-lg mt-3 max-w-2xl text-white">{{ __('home.pantry_title') }}</h2><p class="mt-5 max-w-xl hc-body-md text-stone-300">{{ __('home.pantry_subtitle') }}</p><a class="hc-btn mt-7 bg-white text-charcoal-900 hover:bg-orange-50" href="{{ route('ingredients.explore') }}">{{ __('home.pantry_cta') }} <span aria-hidden="true">→</span></a></div><ol class="hc-home-steps"><li><span>01</span><p>{{ __('home.step_1') }}</p></li><li><span>02</span><p>{{ __('home.step_2') }}</p></li><li><span>03</span><p>{{ __('home.step_3') }}</p></li></ol></div></section>

    <section data-reveal class="hc-container py-16"><div class="flex items-end justify-between gap-4"><div><p class="hc-kicker">{{ __('home.cuisines_kicker') }}</p><h2 class="hc-heading-2 mt-3">{{ __('home.cuisines_title') }}</h2></div><a class="hidden text-sm font-extrabold text-orange-700 sm:inline" href="{{ route('recipes.index') }}">{{ __('home.go_to_archive') }} →</a></div>{{-- Kutular eskiden bir yıldız ve bir addan ibaretti; yüksekti ve boştu.
             Artık koleksiyonun büyüklüğünü gösteriyor ve en dolu mutfaklar başa
             geliyor, yani tıklamadan önce ne bulacağını biliyorsun. Tarifi
             olmayan mutfak hiç listelenmez. --}}
        <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach($cuisines->where('recipes_count', '>', 0)->sortByDesc('recipes_count')->take(6) as $cuisine)
                {{-- Sayfasi olan mutfak indekslenebilir sayfasina, olmayan
                     filtreli goruntusune baglanir. Esik denetleyicide. --}}
@php($kapak = $mutfakKapaklari[$cuisine->id] ?? null)
                <a href="{{ \App\Http\Controllers\CuisineHubController::sayfasiVar($cuisine->recipes_count) ? route('cuisines.show', $cuisine->slug) : route('recipes.index', ['cuisine' => $cuisine->slug]) }}" class="hc-hub-card group">
                    @if ($kapak)
                        <span class="hc-hub-card__gorsel" aria-hidden="true">
                            <img src="{{ $kapak->responsiveUrl(400) }}" srcset="{{ $kapak->responsiveSrcset([400, 800]) }}" sizes="(min-width: 1024px) 22rem, (min-width: 640px) 45vw, 92vw" width="400" height="300" alt="" loading="lazy" decoding="async">
                        </span>
                    @endif
                    <span class="hc-hub-card__govde">
                        <span class="hc-hub-card__baslik">{{ __($cuisine->name) }}</span>
                        <span class="hc-caption">{{ __('home.recipes_count', ['count' => $cuisine->recipes_count]) }}</span>
                    </span>
                    <span class="hc-hub-card__ok" aria-hidden="true">›</span>
                </a>
            @endforeach
        </div></section>

    <x-home-testimonials :testimonials="collect(config('testimonials.home', []))->filter(fn ($item, $index) => in_array($index, [0, 2, 4]))->all()" :disclosure="config('testimonials.disclosure')" />


<section class="hc-container py-16" aria-labelledby="home-membership-title">
    <div class="hc-v3-card p-8 sm:p-12">
        <p class="hc-kicker">{{ __('home.membership_kicker') }}</p>
        <h2 id="home-membership-title" class="hc-heading-1 mt-3">{{ __('home.membership_title') }}</h2>
        <p class="hc-body-md mt-4">{{ __('home.membership_subtitle') }}</p>
        <a class="hc-btn hc-btn-primary mt-6" href="{{ auth()->check() ? route('ingredients.explore') : route('register') }}">{{ auth()->check() ? __('home.membership_cta_logged_in') : __('home.membership_cta_guest') }}</a>
        @if(config('monetization.payments_enabled'))
            <a class="hc-text-link ml-4" href="{{ route('pricing') }}">{{ __('home.pricing_link') }}</a>
        @else
            <p class="hc-caption mt-4">{{ __('home.pricing_soon') }}</p>
        @endif
        <nav class="mt-8 flex flex-wrap gap-4" aria-label="Tarif koleksiyonları">
            <a class="hc-text-link" href="{{ route('ingredients.hub.index') }}">{{ __('home.nav_by_ingredient') }}</a>
            <a class="hc-text-link" href="{{ route('cuisines.index') }}">{{ __('home.nav_by_cuisine') }}</a>
            <a class="hc-text-link" href="{{ route('categories.index') }}">{{ __('home.nav_by_category') }}</a>
            <a class="hc-text-link" href="{{ $koleksiyonBaglantisi('sure', '30-dakika') ?? route('recipes.index', ['max_minutes' => 30]) }}">{{ __('home.nav_under_30_min') }}</a>
            <a class="hc-text-link" href="{{ route('blog.index') }}">{{ __('home.nav_kitchen_guides') }}</a>
        </nav>
    </div>
</section>
