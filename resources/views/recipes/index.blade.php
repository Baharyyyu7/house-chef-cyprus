<x-layouts.site title="{{ request()->routeIs('home') ? 'House Chef Cyprus' : 'Tarifler' }}">
@if (request()->routeIs('home'))
    <section class="hc-container pt-6 sm:pt-9">
        <div class="hc-home-hero relative isolate overflow-hidden rounded-[2rem] px-6 py-10 sm:rounded-[2.5rem] sm:px-10 sm:py-14 lg:min-h-[38rem] lg:px-14 lg:py-20">
            <img class="v2-hero-image absolute inset-0 -z-20 h-full w-full object-cover object-[68%_center] opacity-70" src="{{ asset('images/house-chef-hero-v2.png') }}" alt="Halloumi, domates ve zeytinli Kıbrıs esintili Akdeniz tabağı">
            <div class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(9,9,9,.96)_0%,rgba(9,9,9,.78)_42%,rgba(9,9,9,.12)_78%)]"></div>
            <div class="max-w-2xl"><p class="v2-reveal hc-kicker text-orange-300">Modern Akdeniz mutfağı</p><h1 class="v2-reveal v2-delay-1 mt-5 text-5xl font-extrabold leading-[.98] sm:text-6xl lg:text-7xl">Bugün sofranıza<br><span class="text-orange-400">iyi bir fikir</span> bırakın.</h1><p class="hc-hero-muted v2-reveal v2-delay-2 mt-6 max-w-xl text-base leading-7 sm:text-lg">Kıbrıs'ın sıcaklığından ilham alan tarifler, malzemeleriniz ve mutfak ritminiz için tek bir yerde.</p>
                <div class="v2-reveal v2-delay-3 mt-8 flex flex-col gap-3 sm:flex-row"><a class="hc-btn hc-btn-primary px-6" href="{{ route('recipes.index') }}">Tarifleri keşfet <span class="ml-1">→</span></a><a class="hc-hero-secondary hc-btn px-6" href="{{ auth()->check() ? route('ingredients.explore') : route('register') }}">Elindekilerle tarif bul</a></div>
            </div>
            <div class="absolute bottom-5 right-5 hidden max-w-48 rounded-2xl border border-white/15 bg-black/25 p-4 backdrop-blur-md sm:block"><p class="text-xs font-extrabold uppercase tracking-[.16em] text-orange-300">Kıbrıs'tan ilham</p><p class="mt-2 text-sm leading-5 text-stone-200">Ev mutfağına uyarlanmış, editoryal olarak takip edilen tarifler.</p></div>
        </div>
    </section>

    <section class="hc-container py-14 sm:py-18">
        <div class="hc-search-focus v2-reveal hc-panel grid gap-4 p-4 sm:grid-cols-[1fr_auto] sm:items-center sm:p-5"><div><p class="hc-kicker">Akıllı arama</p><form class="mt-3 flex flex-col gap-2 sm:flex-row" action="{{ route('recipes.index') }}"><label class="sr-only" for="hero-search">Tarif ara</label><input id="hero-search" class="min-w-0 flex-1 border-0 bg-transparent px-3 text-base focus:ring-0" name="q" placeholder="Malzeme, mutfak veya tarif ara"><button class="hc-btn hc-btn-primary shrink-0" type="submit">Tarif ara</button></form></div><div class="flex flex-wrap gap-2 sm:justify-end">@foreach(['20 dakikada', 'Kıbrıs Mutfağı', 'Kolay Tarifler'] as $chip)<a href="{{ route('recipes.index', ['q' => $chip]) }}" class="hc-badge transition hover:border-orange-300 hover:text-orange-700">{{ $chip }}</a>@endforeach</div></div>
    </section>

    <section class="hc-container pb-16"><div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="hc-kicker">Seçilmiş tarifler</p><h2 class="hc-title mt-3">Bu hafta mutfağa<br class="sm:hidden"> iyi gelecek tarifler.</h2></div><a class="hc-btn hc-btn-outline" href="{{ route('recipes.index') }}">Tüm tarifleri gör →</a></div>
        @if($recipes->isNotEmpty())
            <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">@foreach($recipes->take(6) as $recipe)
                <article class="hc-featured-card group relative overflow-hidden rounded-3xl border p-0 shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-lift motion-reduce:transform-none" style="border-color: var(--hc-line); background: var(--hc-surface);">
                    <div class="relative aspect-[4/3] overflow-hidden">@php($media = $recipe->media->first(fn ($item) => $item->isApprovedRealMedia()))<img class="hc-featured-card__image h-full w-full object-cover" src="{{ $media?->publicUrl() ?: asset('images/house-chef-hero-v2.png') }}" alt="{{ $media?->alt_text ?: $recipe->title.' için temsili yemek görseli' }}" style="object-position: {{ $loop->odd ? '72%' : '85%' }} center"><div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black/70 to-transparent"></div><span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-charcoal-800">{{ $recipe->cuisine?->name ?? 'Ev mutfağı' }}</span><span class="absolute bottom-4 left-4 text-sm font-bold text-white">{{ $recipe->totalMinutes() }} dk · {{ $recipe->difficulty }}</span>@auth<form class="absolute right-4 top-4 z-20" method="post" action="{{ route('favorites.toggle', $recipe) }}">@csrf<button class="grid h-9 w-9 place-items-center rounded-xl bg-white/95 text-lg text-charcoal-800 transition hover:bg-orange-100 active:scale-95" type="submit" aria-label="{{ $recipe->title }} tarifini favorilere ekle">♡</button></form>@endauth</div>
                    <div class="p-5"><h3 class="line-clamp-2 text-xl font-extrabold tracking-[-.03em]" style="color: var(--hc-text);">{{ $recipe->title }}</h3><p class="mt-2 line-clamp-2 text-sm leading-6" style="color: var(--hc-muted);">{{ $recipe->excerpt }}</p><span class="mt-5 inline-flex items-center text-sm font-extrabold text-orange-700">Tarifi incele <span class="ml-2">→</span></span></div><a class="absolute inset-0 z-10 rounded-3xl focus-visible:ring-2 focus-visible:ring-orange-400" href="{{ route('recipes.show', $recipe) }}" aria-label="{{ $recipe->title }} tarifini incele"></a>
                </article>
            @endforeach</div>
        @else
            <div class="hc-featured-empty hc-empty mt-8"><p class="text-xl font-extrabold" style="color: var(--hc-text);">Seçilmiş tarifler hazırlanıyor.</p><p class="mt-2">Yeni tarifler eklendiğinde burada mutfağınıza uygun öneriler göreceksiniz.</p><a class="hc-btn hc-btn-outline mt-5" href="{{ route('recipes.index') }}">Tarif arşivine git</a></div>
        @endif
    </section>

    <section class="border-y border-line bg-[#fdf1e6] py-16 dark:border-[#2a2a2a] dark:bg-[#141414]"><div class="hc-container grid gap-8 lg:grid-cols-[1.1fr_.9fr] lg:items-center"><div><p class="hc-kicker">Dolabında ne var?</p><h2 class="mt-3 text-4xl font-extrabold tracking-[-.04em] text-ink dark:text-stone-50 sm:text-5xl">Elindekileri seç.<br>Gerisini birlikte bulalım.</h2><p class="mt-5 max-w-xl leading-7 text-warm dark:text-stone-400">Malzeme dolabınızı tariflerle eşleştiriyor; eksikleri ve uygun alternatifleri açıkça gösteriyoruz.</p><a class="hc-btn hc-btn-primary mt-7" href="{{ auth()->check() ? route('ingredients.explore') : route('register') }}">Elindekilerle tarif bul →</a></div><div class="grid grid-cols-2 gap-3"><div class="rounded-3xl bg-orange-500 p-6 text-white"><p class="text-sm font-bold text-orange-100">01</p><p class="mt-10 text-xl font-extrabold">Dolabını ekle</p><p class="mt-2 text-sm leading-6 text-orange-100">Malzemelerini güncel tut.</p></div><div class="rounded-3xl bg-surface p-6 shadow-card dark:bg-[#1c1c1c]"><p class="text-sm font-bold text-orange-600">02</p><p class="mt-10 text-xl font-extrabold text-ink dark:text-stone-50">Tarifleri eşleştir</p><p class="mt-2 text-sm leading-6 text-warm dark:text-stone-400">Uygun sonuçları gör.</p></div><div class="col-span-2 rounded-3xl bg-charcoal-900 p-6 text-white"><p class="text-sm font-bold text-orange-300">03</p><p class="mt-2 text-xl font-extrabold">Eksikleri ve alternatifleri sakin, anlaşılır bir akışta değerlendirin.</p></div></div></div></section>

    <section class="hc-container py-16"><div class="flex items-end justify-between gap-4"><div><p class="hc-kicker">Mutfak seçkisi</p><h2 class="mt-3 text-4xl font-extrabold tracking-[-.04em] text-ink dark:text-stone-50">Kıbrıs'tan başlayın.</h2></div><a class="hidden text-sm font-extrabold text-orange-700 sm:inline" href="{{ route('recipes.index') }}">Tarif arşivine git →</a></div><div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach($cuisines->take(6) as $cuisine)<a href="{{ route('recipes.index', ['cuisine' => $cuisine->slug]) }}" class="group flex min-h-32 flex-col justify-between rounded-3xl border border-line bg-surface p-5 transition hover:-translate-y-1 hover:border-orange-300 hover:shadow-card dark:border-[#2a2a2a] dark:bg-[#1c1c1c]"><span class="text-2xl text-orange-500">✦</span><span class="flex items-center justify-between gap-3 font-extrabold text-ink dark:text-stone-50">{{ $cuisine->name }} <span class="text-orange-500 transition group-hover:translate-x-1">→</span></span></a>@endforeach</div></section>

    <section class="hc-container grid gap-4 pb-16 md:grid-cols-3"><a href="{{ route('recipes.index') }}" class="hc-panel group"><span class="text-2xl text-orange-500">⌁</span><h3 class="mt-5 text-xl font-extrabold">Hızlı kategoriler</h3><p class="mt-2 text-sm leading-6 text-warm dark:text-stone-400">Süreye, mutfağa ve günün temposuna göre tarif keşfedin.</p><span class="mt-5 inline-flex text-sm font-extrabold text-orange-700">Hızlı tarifler →</span></a><div class="hc-panel"><span class="text-2xl text-orange-500">◎</span><h3 class="mt-5 text-xl font-extrabold">Beslenme & alerjen</h3><p class="mt-2 text-sm leading-6 text-warm dark:text-stone-400">Kaynağı ve güven düzeyi görünür bilgilerle daha bilinçli seçimler.</p></div><a href="{{ auth()->check() ? route('ingredients.explore') : route('register') }}" class="hc-panel"><span class="text-2xl text-orange-500">◷</span><h3 class="mt-5 text-xl font-extrabold">Cooking Mode</h3><p class="mt-2 text-sm leading-6 text-warm dark:text-stone-400">Tarifi adım adım, zamanlayıcılarla takip edin.</p><span class="mt-5 inline-flex text-sm font-extrabold text-orange-700">Pişirme modunu keşfet →</span></a></section>

    <section class="hc-container pb-8"><div class="overflow-hidden rounded-[2rem] bg-orange-500 px-7 py-10 text-white sm:px-10 sm:py-14"><p class="text-sm font-extrabold uppercase tracking-[.18em] text-orange-100">Mutfağınız için daha akıllı bir başlangıç</p><div class="mt-4 flex flex-col justify-between gap-6 md:flex-row md:items-end"><h2 class="max-w-2xl text-4xl font-extrabold tracking-[-.05em] sm:text-5xl">Tarifleri saklamak değil, gerçekten pişirmek için keşfedin.</h2><a class="hc-btn shrink-0 bg-white px-6 text-charcoal-900 hover:bg-orange-50" href="{{ route('recipes.index') }}">Tarifleri keşfet</a></div></div></section>
@else
    <section class="hc-container hc-page hc-recipe-archive">
        <div class="grid gap-6 border-b pb-8 sm:grid-cols-[1fr_auto] sm:items-end" style="border-color: var(--hc-line)">
            <div class="max-w-3xl">
                <p class="hc-kicker">Doğrulanmış tarif koleksiyonu</p>
                <h1 class="hc-display-lg mt-3">Tarifleri keşfedin.</h1>
                <p class="hc-body-lg mt-4" style="color: var(--hc-text-secondary)">Kıbrıs ve Akdeniz mutfağından, ölçüleri ve editoryal bilgileri görünür 50 özgün tarif.</p>
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
                    <div><label class="hc-v3-field-label" for="difficulty">Zorluk</label><select id="difficulty" class="hc-v3-input" name="difficulty"><option value="">Her seviye</option>@foreach(['Kolay', 'Orta', 'Zor'] as $difficulty)<option value="{{ $difficulty }}" @selected(request('difficulty') === $difficulty)>{{ $difficulty }}</option>@endforeach</select></div>
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
        <div class="hc-v3-stagger mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($recipes as $recipe)
                <x-recipe-card-v3 :recipe="$recipe" />
            @empty
                <div class="hc-v3-empty col-span-full"><p class="hc-heading-3">Bu ölçütlere uygun tarif yok.</p><p class="hc-body-sm mt-2">Arama ifadenizi veya filtreleri değiştirip tekrar deneyin.</p><div class="mt-5 flex flex-wrap justify-center gap-3"><a class="hc-v3-btn hc-v3-btn--outline" href="{{ route('recipes.index') }}">Tüm tarifleri göster</a><a class="hc-v3-btn hc-v3-btn--primary" href="{{ route('ingredients.explore') }}">Elindekilerle tarif bul</a></div></div>
            @endforelse
        </div>
        <div class="mt-10">{{ $recipes->withQueryString()->links() }}</div>
    </section>
@endif
</x-layouts.site>
