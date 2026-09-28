{{-- Hub ve koleksiyon bağlantıları.
     Ana sayfa, mutfak hub'larına ve koleksiyon sayfalarına (20 dakikada,
     kolay tarifler) en güçlü iç bağlantıyı veren yer. c3ccef8 ana sayfayı
     yeniden yazarken bu bölümler düştü ve hub sayfaları ana sayfadan
     gelen bağlantıyı kaybetti; burada geri getiriliyor. --}}
@php($mutfakSayilari = \App\Http\Controllers\CuisineHubController::ceviriSayilari())
@php($kategoriSayilari = \App\Http\Controllers\CategoryHubController::ceviriSayilari())
<div data-reveal class="hc-container py-10" aria-label="{{ __('home.quick_links') }}">
            <div class="hc-arama__oneriler mt-5">
                <span class="hc-arama__etiket">{{ __('home.quick_links') }}</span>
                @if(\App\Http\Controllers\CuisineHubController::sayfasiVar($mutfakSayilari['kibris-mutfagi'] ?? 0))<a class="hc-arama__cip" href="{{ route('cuisines.show', 'kibris-mutfagi') }}">🇨🇾 {{ __('home.cypriot_cuisine') }}</a>@endif
                @if(\App\Http\Controllers\CuisineHubController::sayfasiVar($mutfakSayilari['turk-mutfagi'] ?? 0))<a class="hc-arama__cip" href="{{ route('cuisines.show', 'turk-mutfagi') }}">🇹🇷 Türk Mutfağı</a>@endif
                @foreach([['home.under_20_min', 'sure', '20-dakika', ['max_minutes' => 20]], ['home.easy_recipes', 'zorluk', 'kolay', ['difficulty' => 'easy']]] as [$chipKey, $tur, $slug, $params])
                    <a href="{{ $koleksiyonBaglantisi($tur, $slug) ?? route('recipes.index', $params) }}" class="hc-arama__cip">{{ __($chipKey) }}</a>
                @endforeach
                @if(\App\Http\Controllers\CategoryHubController::sayfasiVar($kategoriSayilari['corba'] ?? 0))<a class="hc-arama__cip" href="{{ route('categories.show', 'corba') }}">🥣 {{ __('home.quick_soups') }}</a>@endif
                @if(\App\Http\Controllers\CategoryHubController::sayfasiVar($kategoriSayilari['tatli'] ?? 0))<a class="hc-arama__cip" href="{{ route('categories.show', 'tatli') }}">🍯 {{ __('home.quick_desserts') }}</a>@endif
                <a class="hc-arama__cip !border-orange-500/30 !bg-orange-500/10 !text-orange-700 dark:!text-orange-300 font-bold" href="{{ route('recipes.index') }}">{{ __('home.all_archive') }}</a>
            </div>
</div>

    <div data-reveal class="hc-container py-16"><div class="flex items-end justify-between gap-4"><div><p class="hc-kicker">{{ __('home.cuisines_kicker') }}</p><h2 class="hc-heading-2 mt-3">{{ __('home.discovery_title') }}</h2></div><a class="hidden text-sm font-extrabold text-orange-700 sm:inline" href="{{ route('recipes.index') }}">{{ __('home.go_to_archive') }} →</a></div>{{-- Kutular eskiden bir yıldız ve bir addan ibaretti; yüksekti ve boştu.
             Artık koleksiyonun büyüklüğünü gösteriyor ve en dolu mutfaklar başa
             geliyor, yani tıklamadan önce ne bulacağını biliyorsun. Tarifi
             olmayan mutfak hiç listelenmez. --}}
        <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach($cuisines->filter(fn ($c) => ($mutfakSayilari[$c->slug] ?? 0) > 0)->sortByDesc(fn ($c) => $mutfakSayilari[$c->slug])->take(6) as $cuisine)
                {{-- Sayfasi olan mutfak indekslenebilir sayfasina, olmayan
                     filtreli goruntusune baglanir. Esik denetleyicide. --}}
@php($kapak = $mutfakKapaklari[$cuisine->id] ?? null)
                <a href="{{ \App\Http\Controllers\CuisineHubController::sayfasiVar($mutfakSayilari[$cuisine->slug]) ? route('cuisines.show', $cuisine->slug) : route('recipes.index', ['cuisine' => $cuisine->slug]) }}" class="hc-hub-card group">
                    @if ($kapak)
                        <span class="hc-hub-card__gorsel" aria-hidden="true">
                            <img src="{{ $kapak->responsiveUrl(400) }}" srcset="{{ $kapak->responsiveSrcset([400, 800]) }}" sizes="(min-width: 1024px) 22rem, (min-width: 640px) 45vw, 92vw" width="400" height="300" alt="{{ taxonomy_name($cuisine) }}" loading="lazy" decoding="async">
                        </span>
                    @endif
                    <span class="hc-hub-card__govde">
                        <span class="hc-hub-card__baslik">{{ __($cuisine->name) }}</span>
                        <span class="hc-caption">{{ __('home.recipes_count', ['count' => $mutfakSayilari[$cuisine->slug]]) }}</span>
                    </span>
                    <span class="hc-hub-card__ok" aria-hidden="true">›</span>
                </a>
            @endforeach
        </div></div>
