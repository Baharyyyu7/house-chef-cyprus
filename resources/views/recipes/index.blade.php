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
    $sayfaBasligi = $anaSayfa
        ? 'House Chef Cyprus — Kıbrıs ve Akdeniz Mutfağı Tarifleri'
        : 'Tarifler — Kıbrıs ve Akdeniz Mutfağı · House Chef Cyprus';
    $sayfaAciklamasi = $anaSayfa
        ? 'Kıbrıs ve Akdeniz mutfağından ölçüleri doğrulanmış tarifler. Dolabınızdaki malzemeye göre ne pişirebileceğinizi bulun, adım adım pişirin.'
        : 'Kıbrıs ve Akdeniz mutfağından '.$recipes->total().' doğrulanmış tarif. Süreye, mutfağa, kategoriye ve alerjene göre filtreleyin; her tarifte ölçüler ve besin değerleri görünür.';
@endphp
<x-layouts.site :title="$sayfaBasligi" :meta-description="$sayfaAciklamasi" :canonical-url="$seoIndexability['canonical']" :robots="$seoIndexability['robots']">
@if (request()->routeIs('home'))
    {{-- Hero sayfanın kendisidir, sayfanın üstünde duran bir kart değil.
         Tam genişlikte açılır ve alt kenarı sayfa zeminine karışır; böylece
         yüzen başlık çubuğu gerçekten içeriğin üstünden geçer. --}}
    <section class="hc-home-hero relative isolate -mt-[var(--hc-header-h)] flex min-h-[86svh] items-end overflow-hidden pb-14 pt-[calc(var(--hc-header-h)+3rem)] sm:pb-20">
        {{-- Bu görsel sayfanın LCP öğesi. Kaynak PNG 2.9 MB'tı; fotoğraf için
             yanlış format. Aynı kare AVIF olarak 222 KB, WebP olarak 294 KB;
             dar ekranda 61 KB. PNG en sonda, eski tarayıcılar için duruyor. --}}
        <picture>
            <source type="image/avif" sizes="100vw" srcset="{{ asset('images/house-chef-hero-v2-900.avif') }} 900w, {{ asset('images/house-chef-hero-v2-1440.avif') }} 1440w, {{ asset('images/house-chef-hero-v2-1717.avif') }} 1717w">
            <source type="image/webp" sizes="100vw" srcset="{{ asset('images/house-chef-hero-v2-900.webp') }} 900w, {{ asset('images/house-chef-hero-v2-1440.webp') }} 1440w, {{ asset('images/house-chef-hero-v2-1717.webp') }} 1717w">
            <img class="v2-hero-image absolute inset-0 -z-20 h-full w-full object-cover object-[64%_center]" src="{{ asset('images/house-chef-hero-v2.png') }}" width="1717" height="916" alt="Halloumi, domates ve zeytinli Kıbrıs esintili Akdeniz tabağı" fetchpriority="high" decoding="async">
        </picture>
        {{-- Görselin üstünde duran döngü videosu. Görselin yerine geçmez:
             LCP hâlâ yukarıdaki karedir, video sayfa yüklendikten sonra
             açılır ve yalnız geniş ekranda, hareket kısıtlı değilse yüklenir.
             Dosya yoksa bileşen hiçbir şey basmaz. --}}
        <x-hero-video />
        {{-- İki katman: yatay olan metne okunur bir zemin verir, dikey olan
             hem çubuğu hem de alt kenarı sayfa zeminine bağlar. --}}
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(96deg,rgba(9,9,9,.93)_0%,rgba(9,9,9,.78)_34%,rgba(9,9,9,.28)_66%,rgba(9,9,9,.12)_100%)]"></div>
        {{-- Dar ekranda metin tüm genişliğe yayıldığı için yatay perde yetmez;
             ağırlık alt yarıya verilir. --}}
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(9,9,9,.58)_0%,rgba(9,9,9,.06)_22%,rgba(9,9,9,.34)_52%,rgba(9,9,9,.8)_100%)]"></div>
        {{-- Kahraman görselin altı sayfa zeminine kesilerek değil, eriyerek
             bağlanır: koyu kareden beyaz kağıda tek pikselde geçmek, iki ayrı
             sayfa yapıştırılmış gibi duruyordu. --}}
        <div class="hc-home-hero__eritme" aria-hidden="true"></div>
        <div class="hc-container">
            <div class="max-w-3xl">
                <p class="v2-reveal hc-kicker text-orange-300">Modern Akdeniz mutfağı</p>
                <h1 class="v2-reveal v2-delay-1 hc-display-xl mt-5 text-white">Bugün sofranıza iyi bir fikir bırakın.</h1>
                <p class="hc-hero-muted v2-reveal v2-delay-2 hc-body-lg mt-6 max-w-xl">Kıbrıs'ın sıcaklığından ilham alan tarifler, malzemeleriniz ve mutfak ritminiz için tek bir yerde.</p>
                {{-- Tek bir birincil eylem. İkincil eylem onunla yarışmaz. --}}
                <div class="v2-reveal v2-delay-3 mt-9 flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                    <a class="hc-btn hc-btn-primary px-7" href="{{ route('recipes.index') }}">Tarifleri keşfet <span class="ml-1" aria-hidden="true">→</span></a>
                    <a class="hc-hero-secondary text-sm font-semibold text-white/80 underline-offset-4 transition hover:text-white hover:underline" href="{{ auth()->check() ? route('ingredients.explore') : route('register') }}">Elindekilerle tarif bul</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Arama kutusu sayfanın ilk eylemi; öyle de görünmeli. Girdi ile
         düğme tek bir yüzeyin parçası, ikon içeride, öneriler altında. --}}
    <section data-reveal class="hc-container py-14 sm:py-18">
        <div class="hc-arama v2-reveal">
            <p class="hc-kicker">Akıllı arama</p>
            <form class="hc-arama__kutu mt-4" action="{{ route('recipes.index') }}">
                <label class="sr-only" for="hero-search">Tarif ara</label>
                <span class="hc-arama__ikon" aria-hidden="true"><x-ui.icon name="search" size="20" /></span>
                <input id="hero-search" class="hc-arama__girdi" name="q" placeholder="Malzeme, mutfak veya tarif ara" autocomplete="off">
                <button class="hc-arama__dugme" type="submit">
                    <span>Tarif ara</span>
                    <span class="hc-arama__ok" aria-hidden="true">→</span>
                </button>
            </form>
            <div class="hc-arama__oneriler">
                <span class="hc-arama__etiket">Popüler:</span>
                @foreach(['20 dakikada', 'Kıbrıs Mutfağı', 'Kolay Tarifler'] as $chip)
                    <a href="{{ route('recipes.index', ['q' => $chip]) }}" class="hc-arama__cip">{{ $chip }}</a>
                @endforeach
            </div>
        </div>
    </section>

    <section data-reveal class="hc-container pb-16"><div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="hc-kicker">Seçilmiş tarifler</p><h2 class="hc-title mt-3">Bu hafta mutfağa<br class="sm:hidden"> iyi gelecek tarifler.</h2></div><a class="hc-btn hc-btn-outline" href="{{ route('recipes.index') }}">Tüm tarifleri gör →</a></div>
        @if($recipes->isNotEmpty())
            <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-reveal-group>
                @foreach($recipes->take(6) as $recipe)
                    <x-recipe-card-v3 :recipe="$recipe" />
                @endforeach
            </div>
        @else
            <div class="hc-featured-empty hc-empty mt-8"><p class="text-xl font-extrabold" style="color: var(--hc-text);">Seçilmiş tarifler hazırlanıyor.</p><p class="mt-2">Yeni tarifler eklendiğinde burada mutfağınıza uygun öneriler göreceksiniz.</p><a class="hc-btn hc-btn-outline mt-5" href="{{ route('recipes.index') }}">Tarif arşivine git</a></div>
        @endif
    </section>

    <section data-reveal class="border-y border-line bg-[#fdf1e6] py-16 dark:border-[#2a2a2a] dark:bg-[#141414]"><div class="hc-container grid gap-8 lg:grid-cols-[1.1fr_.9fr] lg:items-center"><div><p class="hc-kicker">Dolabında ne var?</p><h2 class="mt-3 text-4xl font-extrabold tracking-[-.04em] text-ink dark:text-stone-50 sm:text-5xl">Elindekileri seç.<br>Gerisini birlikte bulalım.</h2><p class="mt-5 max-w-xl leading-7 text-warm dark:text-stone-400">Malzeme dolabınızı tariflerle eşleştiriyor; eksikleri ve uygun alternatifleri açıkça gösteriyoruz.</p><a class="hc-btn hc-btn-primary mt-7" href="{{ auth()->check() ? route('ingredients.explore') : route('register') }}">Elindekilerle tarif bul →</a></div><div class="grid grid-cols-2 gap-3"><div class="rounded-3xl bg-orange-500 p-6 text-white"><p class="text-sm font-bold text-orange-100">01</p><p class="mt-10 text-xl font-extrabold">Dolabını ekle</p><p class="mt-2 text-sm leading-6 text-orange-100">Malzemelerini güncel tut.</p></div><div class="rounded-3xl bg-surface p-6 shadow-card dark:bg-[#1c1c1c]"><p class="text-sm font-bold text-orange-600">02</p><p class="mt-10 text-xl font-extrabold text-ink dark:text-stone-50">Tarifleri eşleştir</p><p class="mt-2 text-sm leading-6 text-warm dark:text-stone-400">Uygun sonuçları gör.</p></div><div class="col-span-2 rounded-3xl bg-charcoal-900 p-6 text-white"><p class="text-sm font-bold text-orange-300">03</p><p class="mt-2 text-xl font-extrabold">Eksikleri ve alternatifleri sakin, anlaşılır bir akışta değerlendirin.</p></div></div></div></section>

    <section data-reveal class="hc-container py-16"><div class="flex items-end justify-between gap-4"><div><p class="hc-kicker">Mutfak seçkisi</p><h2 class="hc-heading-2 mt-3">Kıbrıs'tan başlayın.</h2></div><a class="hidden text-sm font-extrabold text-orange-700 sm:inline" href="{{ route('recipes.index') }}">Tarif arşivine git →</a></div>{{-- Kutular eskiden bir yıldız ve bir addan ibaretti; yüksekti ve boştu.
             Artık koleksiyonun büyüklüğünü gösteriyor ve en dolu mutfaklar başa
             geliyor, yani tıklamadan önce ne bulacağını biliyorsun. Tarifi
             olmayan mutfak hiç listelenmez. --}}
        <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach($cuisines->where('recipes_count', '>', 0)->sortByDesc('recipes_count')->take(6) as $cuisine)
                <a href="{{ route('recipes.index', ['cuisine' => $cuisine->slug]) }}" class="hc-card-hover group flex items-center justify-between gap-4 rounded-3xl border p-5" style="border-color: var(--hc-line); background: var(--hc-surface)">
                    <span class="min-w-0">
                        <span class="hc-heading-4 block truncate" style="color: var(--hc-text-primary)">{{ $cuisine->name }}</span>
                        <span class="hc-caption mt-1 block" style="color: var(--hc-text-secondary)">{{ $cuisine->recipes_count }} tarif</span>
                    </span>
                    <span class="shrink-0 text-orange-500 transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true">→</span>
                </a>
            @endforeach
        </div></section>

    @foreach([
        'quick' => ['30 dakikanın altında', 'Yoğun günlerde hızdan ödün vermeden sofraya gelen tarifler.'],
        'cyprus' => ['Kıbrıs ve Akdeniz lezzetleri', 'Yerel mutfak ritminden ilham alan seçilmiş tarifler.'],
        'breakfast' => ['Kahvaltı tarifleri', 'Güne iyi başlayan, paylaşması kolay tarifler.'],
        'mains' => ['Ana yemekler', 'Hafta içi ve misafir sofraları için güvenilir ana yemekler.'],
        'desserts' => ['Tatlılar', 'Sofrayı tamamlayan dengeli tatlı seçenekleri.'],
    ] as $sectionKey => [$sectionTitle, $sectionDescription])
        @if(($homeSections[$sectionKey] ?? collect())->isNotEmpty())
            <section class="hc-container pb-16">
                <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="hc-kicker">Tarif seçkisi</p><h2 class="hc-title mt-2">{{ $sectionTitle }}</h2><p class="mt-2 max-w-2xl text-sm leading-6 text-warm dark:text-stone-400">{{ $sectionDescription }}</p></div><a class="hc-btn hc-btn-outline" href="{{ route('recipes.index') }}">Tümünü keşfet →</a></div>
                <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group>@foreach($homeSections[$sectionKey] as $sectionRecipe)<x-recipe-card-v3 :recipe="$sectionRecipe" />@endforeach</div>
            </section>
        @endif
    @endforeach

    <section class="hc-container grid gap-4 pb-16 md:grid-cols-3" data-reveal-group><a href="{{ route('recipes.index') }}" class="hc-panel group"><span class="text-2xl text-orange-500">⌁</span><h3 class="mt-5 text-xl font-extrabold">Hızlı kategoriler</h3><p class="mt-2 text-sm leading-6 text-warm dark:text-stone-400">Süreye, mutfağa ve günün temposuna göre tarif keşfedin.</p><span class="mt-5 inline-flex text-sm font-extrabold text-orange-700">Hızlı tarifler →</span></a><div class="hc-panel"><span class="text-2xl text-orange-500">◎</span><h3 class="mt-5 text-xl font-extrabold">Beslenme & alerjen</h3><p class="mt-2 text-sm leading-6 text-warm dark:text-stone-400">Kaynağı ve güven düzeyi görünür bilgilerle daha bilinçli seçimler.</p></div><a href="{{ auth()->check() ? route('ingredients.explore') : route('register') }}" class="hc-panel"><span class="text-2xl text-orange-500">◷</span><h3 class="mt-5 text-xl font-extrabold">Cooking Mode</h3><p class="mt-2 text-sm leading-6 text-warm dark:text-stone-400">Tarifi adım adım, zamanlayıcılarla takip edin.</p><span class="mt-5 inline-flex text-sm font-extrabold text-orange-700">Pişirme modunu keşfet →</span></a></section>

    @if(($latestPosts ?? collect())->isNotEmpty())
        <section class="hc-container pb-16"><div class="flex flex-wrap items-end justify-between gap-4"><div><p class="hc-kicker">Editoryal mutfak rehberi</p><h2 class="hc-title mt-2">Son blog yazıları</h2></div><a class="hc-btn hc-btn-outline" href="{{ route('blog.index') }}">Tüm yazılar →</a></div><div class="mt-7 grid gap-5 md:grid-cols-3">@foreach($latestPosts as $post)@php($heroMedia = app(\App\Services\Media\PublicMediaEligibilityService::class)->heroOrFirstEligible($post->heroMedia, $post->media))<article class="hc-v3-card overflow-hidden"><a class="block aspect-[16/9] bg-stone-100 dark:bg-stone-900" href="{{ route('blog.show', $post) }}">@if($heroMedia?->publicUrl())<img class="h-full w-full object-cover" src="{{ $heroMedia->publicUrl() }}" alt="{{ $heroMedia->alt_text }}" loading="lazy">@else<div class="flex h-full flex-col items-center justify-center gap-2 p-5 text-center"><span class="hc-kicker">{{ $post->category?->name ?? 'Mutfak notu' }}</span><strong>Yazıya özel görsel hazırlanıyor</strong><span class="text-xs text-muted">Onaylı editoryal görsel bekleniyor.</span></div>@endif</a><div class="p-5"><p class="hc-kicker">{{ $post->category?->name ?? 'Mutfak notu' }}</p><h3 class="mt-2 text-xl font-extrabold"><a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a></h3><p class="mt-2 line-clamp-2 text-sm text-warm dark:text-stone-400">{{ $post->excerpt }}</p><a class="mt-4 inline-flex font-extrabold text-orange-700" href="{{ route('blog.show', $post) }}">Yazıyı oku →</a></div></article>@endforeach</div></section>
    @endif

    <x-home-testimonials :testimonials="config('testimonials.home', [])" :disclosure="config('testimonials.disclosure')" />

    <section data-reveal class="hc-container pb-8"><div class="overflow-hidden rounded-[2rem] bg-orange-500 px-7 py-10 text-white sm:px-10 sm:py-14"><p class="text-sm font-extrabold uppercase tracking-[.18em] text-orange-100">Mutfağınız için daha akıllı bir başlangıç</p><div class="mt-4 flex flex-col justify-between gap-6 md:flex-row md:items-end"><h2 class="hc-heading-1 max-w-2xl">Tarifleri saklamak değil, gerçekten pişirmek için keşfedin.</h2><a class="hc-btn shrink-0 bg-white px-6 text-charcoal-900 hover:bg-orange-50" href="{{ route('recipes.index') }}">Tarifleri keşfet</a></div></div></section>
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
