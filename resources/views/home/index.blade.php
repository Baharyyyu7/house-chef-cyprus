{{-- Hero sayfanın kendisidir, sayfanın üstünde duran bir kart değil.
         Tam genişlikte açılır ve alt kenarı sayfa zeminine karışır; böylece
         yüzen başlık çubuğu gerçekten içeriğin üstünden geçer. --}}
    <section class="hc-home-hero hc-home-hero--master relative isolate -mt-[var(--hc-header-h)] flex flex-col justify-end min-h-[82svh] items-stretch overflow-hidden pb-16 pt-[calc(var(--hc-header-h)+3rem)] sm:pb-20">
        {{-- Bu görsel sayfanın LCP öğesi. Kaynak PNG 2.9 MB'tı; fotoğraf için
             yanlış format. Aynı kare AVIF olarak 222 KB, WebP olarak 294 KB;
             dar ekranda 61 KB. PNG en sonda, eski tarayıcılar için duruyor. --}}
        <picture>
            <source type="image/avif" sizes="100vw" srcset="{{ asset('images/house-chef-hero-v2-900.avif').'?v=2' }} 900w, {{ asset('images/house-chef-hero-v2-1440.avif').'?v=2' }} 1440w, {{ asset('images/house-chef-hero-v2-1717.avif').'?v=2' }} 1717w">
            <source type="image/webp" sizes="100vw" srcset="{{ asset('images/house-chef-hero-v2-900.webp') }} 900w, {{ asset('images/house-chef-hero-v2-1440.webp') }} 1440w, {{ asset('images/house-chef-hero-v2-1717.webp') }} 1717w">
            <img class="v2-hero-image absolute inset-0 -z-20 h-full w-full object-cover object-[64%_center]" src="{{ asset('images/house-chef-hero-v2.png') }}" width="1717" height="916" alt="Halloumi, domates ve zeytinli Kıbrıs esintili Akdeniz tabağı" fetchpriority="high" decoding="async">
        </picture>
        {{-- Görselin üstünde duran döngü videosu. Görselin yerine geçmez:
             LCP hâlâ yukarıdaki karedir, video sayfa yüklendikten sonra
             açılır ve yalnız geniş ekranda, hareket kısıtlı değilse yüklenir.
             Dosya yoksa bileşen hiçbir şey basmaz. --}}
        <x-hero-video />
        {{-- İki katman: yatay olan metne okunur bir zemin verir, dikey olan
             hem çubuğu hem de alt kenarı sayfa zeminine bağlar.
             Perde metnin bittiği yerde açılıyor: önceki eğri sağ tarafı da
             karartıyordu ve tabak neredeyse görünmüyordu. Metnin durduğu
             %54'e kadar koyu kalıyor (beyaz yazı orada ~7:1 kontrast
             görüyor), sonra hızla siliniyor. --}}
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(96deg,rgba(9,9,9,.94)_0%,rgba(9,9,9,.88)_34%,rgba(9,9,9,.58)_54%,rgba(9,9,9,.14)_72%,rgba(9,9,9,0)_100%)]"></div>
        {{-- Dar ekranda metin tüm genişliğe yayıldığı için yatay perde yetmez;
             ağırlık alt yarıya verilir. --}}
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(9,9,9,.50)_0%,rgba(9,9,9,.04)_22%,rgba(9,9,9,.10)_52%,rgba(9,9,9,.62)_100%)]"></div>
        {{-- Kahraman görselin altı sayfa zeminine kesilerek değil, eriyerek
             bağlanır: koyu kareden beyaz kağıda tek pikselde geçmek, iki ayrı
             sayfa yapıştırılmış gibi duruyordu. --}}
        <div class="hc-home-hero__eritme" aria-hidden="true"></div>
        <div class="hc-container">
            <div class="max-w-4xl">
                <p class="hc-kicker text-orange-300">Kıbrıs'tan, bugünün sofrasına</p>
                <h1 class="hc-display-xl mt-5 max-w-3xl text-white">Elindeki malzemeden güvenilir akşam yemeğine.</h1>
                <p class="hc-hero-muted hc-body-lg mt-6 max-w-2xl">Malzemeni seç. Sana uyan tarifleri, eksiklerini ve pişirme adımlarını birlikte gör. Başlamak için üyelik gerekmez.</p>
                {{-- Tek bir birincil eylem. İkincil eylem onunla yarışmaz. --}}
                <div class="mt-9 flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                    <a class="hc-btn hc-btn-primary px-7" href="#hero-ingredient">Malzememle başla <span class="ml-1" aria-hidden="true">→</span></a>
                    <a class="hc-hero-secondary text-sm font-semibold text-white/80 underline-offset-4 transition hover:text-white hover:underline" href="{{ route('recipes.index') }}">Tarif arşivine göz at</a>
                </div>
            </div>
        </div>
        {{-- Arama kutusu sayfanın ilk eylemi; öyle de görünmeli. Girdi ile
         düğme tek bir yüzeyin parçası, ikon içeride, öneriler altında. --}}
    <div id="kesfet" class="hc-container relative z-10 mt-8">
        <div class="hc-arama hc-home-search ">
            <div class="flex flex-wrap items-end justify-between gap-3"><div><p class="hc-kicker">Mutfak kararını kolaylaştırın</p><h2 class="hc-heading-3 mt-2" style="color:var(--hc-text-primary)">Bir malzemeyle başlayın.</h2></div><a class="hc-text-link text-sm font-extrabold" href="{{ route('recipes.index') }}">Tüm arşiv →</a></div>
            <form id="home-ingredients" class="hc-arama__kutu mt-5" action="{{ route('ingredients.explore') }}" method="get">
                <label class="sr-only" for="hero-ingredient">Elindeki malzemeyi seç</label>
                <span class="hc-arama__ikon" aria-hidden="true"><x-ui.icon name="search" size="20" /></span>
                <select id="hero-ingredient" class="hc-arama__girdi" name="ingredients[]" required>
                    <option value="">Elinde hangi malzeme var?</option>
                    @foreach($homeIngredients as $ingredient)
                        <option value="{{ $ingredient['public_id'] }}">{{ $ingredient['name'] }}</option>
                    @endforeach
                </select>
                <button class="hc-arama__dugme" type="submit">
                    <span>Ne pişirebilirim?</span>
                    <span class="hc-arama__ok" aria-hidden="true">→</span>
                </button>
            </form>
            <div class="hc-arama__oneriler">
                <span class="hc-arama__etiket">Hızlı giriş:</span>
                {{-- 'kibris' hicbir mutfaga karsilik gelmiyordu: cip sifir sonuc
                     donuyordu. Artik indekslenebilir mutfak sayfasina gidiyor. --}}
                <a class="hc-arama__cip" href="{{ route('cuisines.show', 'kibris-mutfagi') }}">Kıbrıs mutfağı</a>
                {{-- Sure ve zorluk koleksiyonlarinin artik indekslenebilir
                     sayfasi var; cip filtreli (noindex) goruntuye degil oraya
                     gidiyor. Sayfa esigi gecmiyorsa filtreye dusuluyor:
                     anasayfadan 404'e baglanmak, filtreye baglanmaktan kotu. --}}
                @foreach([['20 dk altında', 'sure', '20-dakika', ['max_minutes' => 20]], ['Kolay tarifler', 'zorluk', 'kolay', ['difficulty' => 'easy']]] as [$chip, $tur, $slug, $params])
                    <a href="{{ $koleksiyonBaglantisi($tur, $slug) ?? route('recipes.index', $params) }}" class="hc-arama__cip">{{ $chip }}</a>
                @endforeach
            </div>
        </div>
    </div>

</section>

    <section data-reveal class="hc-container pb-20"><div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end"><div><p class="hc-kicker">Bugün ne pişirsem?</p><h2 class="hc-display-lg mt-3">Bir sonraki güzel tabak.</h2></div><p class="hc-body-sm max-w-sm" style="color:var(--hc-text-secondary)">Yalnız editoryal doğrulamadan geçmiş, onaylı görseli bulunan tarifler.</p></div>
        @if($homeHighlights->isNotEmpty())
            <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4" data-reveal-group>
                @foreach($homeHighlights as $recipe)
                    <x-recipe-card-v3 :recipe="$recipe" :show-favorite="false" heading="h3" />
                @endforeach
            </div>
        @else
            <div class="hc-featured-empty hc-empty mt-8"><p class="text-xl font-extrabold" style="color: var(--hc-text);">Seçilmiş tarifler hazırlanıyor.</p><p class="mt-2">Yeni tarifler eklendiğinde burada mutfağınıza uygun öneriler göreceksiniz.</p><a class="hc-btn hc-btn-outline mt-5" href="{{ route('recipes.index') }}">Tarif arşivine git</a></div>
        @endif
    </section>

    <section data-reveal class="hc-home-pantry-band"><div class="hc-container grid gap-8 py-14 lg:grid-cols-[1.18fr_.82fr] lg:items-end"><div><p class="hc-kicker text-orange-300">Elinizdekilerle daha iyi karar</p><h2 class="hc-display-lg mt-3 max-w-2xl text-white">Dolabınızdakiler, akşam yemeğine dönüşsün.</h2><p class="mt-5 max-w-xl hc-body-md text-stone-300">Malzemelerinizi seçin. Uygun tarifleri, eksikleri ve güvenli alternatifleri tek akışta görün.</p><a class="hc-btn mt-7 bg-white text-charcoal-900 hover:bg-orange-50" href="{{ route('ingredients.explore') }}">Malzemelerimle tarif bul <span aria-hidden="true">→</span></a></div><ol class="hc-home-steps"><li><span>01</span><p>Dolabınızdakileri ekleyin.</p></li><li><span>02</span><p>Size uyan tarifleri görün.</p></li><li><span>03</span><p>Tarifi açın, adım adım pişirin.</p></li></ol></div></section>

    <section data-reveal class="hc-container py-16"><div class="flex items-end justify-between gap-4"><div><p class="hc-kicker">Mutfak seçkisi</p><h2 class="hc-heading-2 mt-3">Kıbrıs'tan başlayın.</h2></div><a class="hidden text-sm font-extrabold text-orange-700 sm:inline" href="{{ route('recipes.index') }}">Tarif arşivine git →</a></div>{{-- Kutular eskiden bir yıldız ve bir addan ibaretti; yüksekti ve boştu.
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
                        <span class="hc-hub-card__baslik">{{ $cuisine->name }}</span>
                        <span class="hc-caption">{{ $cuisine->recipes_count }} tarif</span>
                    </span>
                    <span class="hc-hub-card__ok" aria-hidden="true">›</span>
                </a>
            @endforeach
        </div></section>

    <x-home-testimonials :testimonials="collect(config('testimonials.home', []))->filter(fn ($item, $index) => in_array($index, [0, 2, 4]))->all()" :disclosure="config('testimonials.disclosure')" />


<section class="hc-container py-16" aria-labelledby="home-membership-title">
    <div class="hc-v3-card p-8 sm:p-12">
        <p class="hc-kicker">Bir sonraki öğüne hazır olun</p>
        <h2 id="home-membership-title" class="hc-heading-1 mt-3">Beğendiğiniz tarifler elinizin altında kalsın.</h2>
        <p class="hc-body-md mt-4">Ücretsiz hesabınızla favorilerinizi saklayın ve malzeme dolabınızı yönetin. Tarif bulmak ve pişirmeye başlamak herkese açık.</p>
        <a class="hc-btn hc-btn-primary mt-6" href="{{ auth()->check() ? route('ingredients.explore') : route('register') }}">{{ auth()->check() ? 'Dolabımla devam et' : 'Ücretsiz hesabımı oluştur' }}</a>
        @if(config('monetization.payments_enabled'))
            <a class="hc-text-link ml-4" href="{{ route('pricing') }}">Üyelik seçenekleri</a>
        @else
            <p class="hc-caption mt-4">Ücretli üyelik yakında aktif. Şimdi ücretsiz keşfedebilirsiniz.</p>
        @endif
        <nav class="mt-8 flex flex-wrap gap-4" aria-label="Tarif koleksiyonları">
            <a class="hc-text-link" href="{{ route('ingredients.hub.index') }}">Malzemeye göre</a>
            <a class="hc-text-link" href="{{ route('cuisines.index') }}">Mutfağa göre</a>
            <a class="hc-text-link" href="{{ route('categories.index') }}">Yemek türüne göre</a>
            <a class="hc-text-link" href="{{ $koleksiyonBaglantisi('sure', '30-dakika') ?? route('recipes.index', ['max_minutes' => 30]) }}">30 dakikanın altındakileri aç →</a>
            <a class="hc-text-link" href="{{ route('blog.index') }}">Mutfak rehberleri</a>
        </nav>
    </div>
</section>
