@php
    /**
     * Başlık ve açıklama sayfaya özel olmalı.
     *
     * Ana sayfanın başlığı yalnız "House Chef Cyprus", tarif arşivininki
     * yalnız "Tarifler" idi; açıklama ise sitedeki her sayfada aynı cümleydi.
     * Arama sonucunda kullanıcı bu iki satıra bakıp tıklayıp tıklamayacağına
     * karar veriyor, Google da tekrar eden açıklamayı değerlendirmiyor.
     */
    $anaSayfa = request()->routeIs('home');

    // Koleksiyon sayfalarinin hangileri var? Esik denetleyicide durur; burada
    // yalniz sonucu soruyoruz. Bes sayim sorgusu her istekte donmesin diye
    // kisa sureli onbellek: yeni nitelenen sayfa cipte birkac dakika sonra
    // gorunur, sitemap zaten ham listeyi kullaniyor.
    $koleksiyonlar = cache()->remember('koleksiyon-baglantilari', now()->addMinutes(10), fn (): array => \App\Http\Controllers\RecipeCollectionLandingController::sayfasiOlanlar()
        ->mapWithKeys(fn (array $k): array => [$k['type'].':'.$k['slug'] => $k['url']])->all());

    $koleksiyonBaglantisi = fn (string $tur, string $slug): ?string => $koleksiyonlar[$tur.':'.$slug] ?? null;
    $sayfaBasligi = $anaSayfa
        ? 'House Chef Cyprus — Kıbrıs ve Akdeniz Mutfağı Tarifleri'
        : 'Tarifler — Kıbrıs ve Akdeniz Mutfağı · House Chef Cyprus';
    $sayfaAciklamasi = $anaSayfa
        ? 'Kıbrıs ve Akdeniz mutfağından ölçüleri doğrulanmış tarifler. Dolabınızdaki malzemeye göre ne pişirebileceğinizi bulun, adım adım pişirin.'
        : 'Kıbrıs ve Akdeniz mutfağından '.$recipes->total().' doğrulanmış tarif. Süreye, mutfağa, kategoriye ve alerjene göre filtreleyin; her tarifte ölçüler ve besin değerleri görünür.';
@endphp
<x-layouts.site :title="$sayfaBasligi" :meta-description="$sayfaAciklamasi" :canonical-url="$seoIndexability['canonical']" :robots="$seoIndexability['robots']">
@if (request()->routeIs('home'))
    @include('home.index')
@else
    <section class="hc-container hc-page hc-recipe-archive">
        <div class="grid gap-6 border-b pb-8 sm:grid-cols-[1fr_auto] sm:items-end" style="border-color: var(--hc-line)">
            <div class="max-w-3xl">
                <p class="hc-kicker">Doğrulanmış tarif koleksiyonu</p>
                <h1 class="hc-display-lg mt-3">Tarifleri keşfedin.</h1>
                <p class="hc-body-lg mt-4" style="color: var(--hc-text-secondary)">Kıbrıs ve Akdeniz mutfağından, ölçüleri ve editoryal bilgileri görünür {{ $recipes->total() }} özgün tarif.</p>
            </div>
            <a href="{{ route('ingredients.explore') }}" class="hc-v3-btn hc-v3-btn--outline">Malzemelerimle eşleştir <span aria-hidden="true">→</span></a>
        </div>

        <form class="hc-v3-card mt-7 grid gap-4 p-4 sm:p-5" action="{{ route('recipes.index') }}" aria-label="Tarif filtreleri">
            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_12rem_12rem_auto]">
            <div>
                <label class="hc-v3-field-label" for="recipe-search">Tarif ara</label>
                <input id="recipe-search" class="hc-v3-input" name="q" value="{{ request('q') }}" placeholder="Tarif, malzeme veya mutfak ara">
            </div>
            <div>
                <label class="hc-v3-field-label" for="cuisine">Mutfak</label>
                <select id="cuisine" class="hc-v3-input" name="cuisine"><option value="">Tüm mutfaklar</option>@foreach($cuisines as $cuisine)<option value="{{ $cuisine->slug }}" @selected(request('cuisine') === $cuisine->slug)>{{ $cuisine->name }}</option>@endforeach</select>
            </div>
            <div>
                <label class="hc-v3-field-label" for="max_minutes">En fazla süre</label>
                <select id="max_minutes" class="hc-v3-input" name="max_minutes"><option value="">Her süre</option><option value="20" @selected(request('max_minutes') === '20')>20 dk altında</option><option value="40" @selected(request('max_minutes') === '40')>40 dk altında</option><option value="60" @selected(request('max_minutes') === '60')>60 dk altında</option></select>
            </div>
                <button class="hc-v3-btn hc-v3-btn--primary self-end" type="submit">Tarifleri göster</button>
            </div>
            <details class="group border-t pt-4" style="border-color: var(--hc-border-subtle)">
                <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-extrabold"><span>Gelişmiş filtreler</span><span class="transition group-open:rotate-45" aria-hidden="true">+</span></summary>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div><label class="hc-v3-field-label" for="category">Kategori</label><select id="category" class="hc-v3-input" name="category"><option value="">Tüm kategoriler</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>@endforeach</select></div>
                    <div><label class="hc-v3-field-label" for="difficulty">Zorluk</label><select id="difficulty" class="hc-v3-input" name="difficulty"><option value="">Her seviye</option>@foreach(\App\Models\Recipe::ZORLUKLAR as $difficultyValue => $difficultyLabel)<option value="{{ $difficultyValue }}" @selected(\App\Models\Recipe::zorlukKanonik(request('difficulty')) === $difficultyValue)>{{ $difficultyLabel }}</option>@endforeach</select></div>
                    <div><label class="hc-v3-field-label" for="dietary_tag">Beslenme tercihi</label><select id="dietary_tag" class="hc-v3-input" name="dietary_tag"><option value="">Tümü</option>@foreach($dietaryTags as $tag)<option value="{{ $tag->code }}" @selected(request('dietary_tag') === $tag->code)>{{ $tag->name }}</option>@endforeach</select></div>
                    <div><label class="hc-v3-field-label" for="sort">Sıralama</label><select id="sort" class="hc-v3-input" name="sort"><option value="">Öne çıkanlar</option><option value="time" @selected(request('sort') === 'time')>En kısa süre</option></select></div>
                    <div><label class="hc-v3-field-label" for="min_protein">En az protein (g)</label><input id="min_protein" class="hc-v3-input" type="number" min="0" step="0.1" name="min_protein" value="{{ request('min_protein') }}" placeholder="Örn. 15"></div>
                    <div><label class="hc-v3-field-label" for="max_calories">En fazla kalori</label><input id="max_calories" class="hc-v3-input" type="number" min="0" step="1" name="max_calories" value="{{ request('max_calories') }}" placeholder="Örn. 500"></div>
                    <div class="sm:col-span-2"><label class="hc-v3-field-label" for="exclude_allergens">Kesin alerjenleri hariç tut</label><select id="exclude_allergens" class="hc-v3-input min-h-24" name="exclude_allergens[]" multiple aria-describedby="allergen-filter-help">@foreach($allergens as $allergen)<option value="{{ $allergen->public_id }}" @selected(in_array($allergen->public_id, (array) request('exclude_allergens', []), true))>{{ $allergen->name }}</option>@endforeach</select><p id="allergen-filter-help" class="hc-v3-field-help">Yalnız kesin “içerir” bilgisi sonuçları hariç tutar.</p></div>
                </div>
            </details>
        </form>

        @php($activeFilters = collect(request()->except(['page']))->filter(fn ($value) => filled($value) || is_array($value) && $value !== []))
        @if($activeFilters->isNotEmpty())
            <div class="mt-4 flex flex-wrap items-center gap-2" aria-live="polite"><span class="hc-v3-badge hc-v3-badge--accent">{{ $activeFilters->count() }} filtre uygulandı</span>@foreach($activeFilters as $key => $value)@if($key === 'exclude_allergens')@foreach((array) $value as $allergenId)@php($allergen = $allergens->firstWhere('public_id', $allergenId))<a class="hc-v3-badge" href="{{ route('recipes.index', array_merge(request()->except(['page', 'exclude_allergens']), ['exclude_allergens' => array_values(array_filter((array) request('exclude_allergens', []), fn ($id) => $id !== $allergenId))])) }}">{{ $allergen?->name ?? 'Alerjen' }} <span aria-hidden="true">×</span><span class="sr-only"> filtresini kaldır</span></a>@endforeach@else<a class="hc-v3-badge" href="{{ route('recipes.index', request()->except(['page', $key])) }}">{{ match($key) {'q' => 'Arama: '.$value, 'cuisine' => 'Mutfak', 'category' => 'Kategori', 'difficulty' => 'Zorluk: '.$value, 'dietary_tag' => 'Beslenme tercihi', 'max_minutes' => 'Süre: '.$value.' dk', 'min_protein' => 'Protein: '.$value.' g', 'max_calories' => 'Kalori: '.$value, 'sort' => 'En kısa süre', default => 'Filtre'} }} <span aria-hidden="true">×</span><span class="sr-only"> filtresini kaldır</span></a>@endif@endforeach<a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="{{ route('recipes.index') }}">Tümünü temizle</a></div>
        @endif

        <div class="mt-10 flex flex-wrap items-center justify-between gap-3">
            <p class="hc-body-sm" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">{{ $recipes->total() }}</strong> doğrulanmış tarif bulundu</p>
            <span class="hc-v3-badge hc-v3-badge--success"><span aria-hidden="true">●</span> Türkçe editoryal içerik</span>
        </div>
        @if($koleksiyonlar !== [])
            <nav class="mt-5 flex flex-wrap gap-2" aria-label="Tarif seçkileri">
                @foreach(\App\Http\Controllers\RecipeCollectionLandingController::sayfasiOlanlar() as $koleksiyon)
                    <a class="hc-v3-badge" href="{{ $koleksiyon['url'] }}">{{ $koleksiyon['title'] }}</a>
                @endforeach
            </nav>
        @endif
        <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" data-reveal-group>
            @forelse($recipes as $recipe)
                <x-recipe-card-v3 :recipe="$recipe" />
                {{-- Feed reklamı akışın içine girer, kenar çubuğuna değil. Altıncı
                     karttan sonra: okur önce içeriği görsün, reklam araya girsin. --}}
                @if($loop->iteration === 6 && ! $loop->last)
                    <x-ad-slot slot-name="in_feed" class="col-span-full my-2" />
                @endif
            @empty
                <div class="hc-v3-empty col-span-full"><p class="hc-heading-3">Bu ölçütlere uygun tarif yok.</p><p class="hc-body-sm mt-2">Arama ifadenizi veya filtreleri değiştirip tekrar deneyin.</p><div class="mt-5 flex flex-wrap justify-center gap-3"><a class="hc-v3-btn hc-v3-btn--outline" href="{{ route('recipes.index') }}">Tüm tarifleri göster</a><a class="hc-v3-btn hc-v3-btn--primary" href="{{ route('ingredients.explore') }}">Elindekilerle tarif bul</a></div></div>
            @endforelse
        </div>
        <div class="mt-10">{{ $recipes->withQueryString()->links() }}</div>
    </section>
@endif
</x-layouts.site>
