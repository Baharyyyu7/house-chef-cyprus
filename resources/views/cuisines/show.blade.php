<x-layouts.site
    :title="($seoTitle ?? __('cuisine.page_title', ['name' => $mutfak->name, 'count' => $tarifler->count()]))"
    :meta-description="($seoDescription ?? __('cuisine.meta_description', ['name' => $mutfak->name, 'count' => $tarifler->count()]))"
    :structured-data="$seoSchema"
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">

    <section class="hc-container py-14">
        <nav class="hc-body-sm text-muted" aria-label="{{ __('cuisine.breadcrumb') }}">
            <a class="font-semibold text-gold-700 transition hover:underline dark:text-gold-400" href="{{ route('cuisines.index') }}">{{ __('cuisine.breadcrumb') }}</a>
            <span aria-hidden="true"> › </span><span class="text-charcoal-900 dark:text-charcoal-100 font-medium">{{ $mutfak->name }}</span>
        </nav>

        <h1 class="mt-4 font-display text-4xl font-semibold sm:text-5xl">{{ $seoH1 ?? __('cuisine.h1', ['name' => $mutfak->name]) }}</h1>

        @if(($isKibrisHub ?? false) === true)
            <h2 class="mt-4 font-display text-2xl font-semibold text-gold-700 dark:text-gold-400">Kıbrıs Yemekleri</h2>
            <p class="hc-body-sm mt-2 max-w-2xl font-semibold tracking-wide text-gold-700 dark:text-gold-300">Kıbrıs · Kıbrıs Yemek · Kıbrıs Yemek Tarif · Yemek Kıbrıs · Kıbrıs Yemekleri · Kıbrıs Mutfağı</p>
            <p class="hc-body-lg mt-3 max-w-3xl leading-relaxed text-muted">Kıbrıs Mutfağı, Akdeniz'in kuzey kıyısında yer alan ada mutfağının en zengin örneğidir. Osmanlı, Yunan ve Türk etkilerini birleştiren bu mutfak, hellim, şeftali kebabı, molehiya, kolokas ve {{ $tarifler->count() }} doğrulanmış tarifiyle dünyaya bilinir. House Chef Cyprus arşivi bu mirası gramajlı, süreli ve adımlı olarak belgeler.</p>
        @else
            <p class="hc-body-lg mt-4 max-w-2xl text-muted">
                {{ __('cuisine.body_count', ['name' => $mutfak->name, 'count' => $tarifler->count()]) }}
                {{ __('cuisine.body_detail') }}
            </p>
        @endif

        @if (filled($mutfak->description))
            <p class="hc-body-sm mt-3 max-w-3xl leading-relaxed text-muted">{{ $mutfak->description }}</p>
        @endif

        @if(($isKibrisHub ?? false) === true)
            <div class="mt-8 max-w-3xl rounded-2xl border border-gold-100 bg-gold-50/50 p-6 dark:border-gold-900/30 dark:bg-gold-950/20">
                <h2 class="font-display text-xl font-semibold">Kıbrıs Yemekleri Nedir? Adanın Mutfak Mirası</h2>
                <p class="hc-body-sm mt-3 leading-relaxed text-muted">Kıbrıs Yemekleri; hellim, şeftali kebabı, molehiya, kolokas, magarina bulli, pirohu ve pilavuna gibi ada mutfağına özgü lezzetler ile Akdeniz'in zeytinyağlı ve ızgara geleneğini birleştiren mutfaktır. House Chef Cyprus arşivi bu mirası {{ $tarifler->count() }} doğrulanmış <strong>Kıbrıs Yemek Tarifi</strong> ile belgeler. Her tarif porsiyonu, gramajı ve pişirme süresiyle birlikte test edilmiştir.</p>
                <h2 class="mt-6 font-display text-lg font-semibold">Kıbrıs Yemek Tarifleri — 20 En Popüler</h2>
                <p class="hc-body-sm mt-2 leading-relaxed text-muted">Aşağıdaki koleksiyon “Kıbrıs Yemek Tarif” ve “Yemek Kıbrıs” niyetlerini de karşılar: filtre, arama veya kategoriyle daraltmadan önce tüm <strong>Kıbrıs Yemek Tarifleri</strong> tek adreste listelenir. Aradığınızı bulamadıysanız üst menüdeki “Ne Pişirebilirim?” ile dolabınıza göre eşleşen Kıbrıs tariflerini görün.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('recipes.index') }}?q=Kıbrıs" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Kıbrıs ara →</a>
                    <a href="{{ route('blog.index') }}?q=Kıbrıs" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Kıbrıs rehberleri →</a>
                    <a href="{{ route('ingredients.hub.show', 'hellim') }}" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Hellim ile →</a>
                </div>
            </div>
        @endif

        @if(($isKibrisHub ?? false) === true)
            <section class="mt-12 max-w-3xl">
                <h2 class="font-display text-xl font-semibold">Kıbrıs Mutfağı Tarihi</h2>
                <p class="hc-body-sm mt-3 leading-relaxed text-muted">Kıbrıs Mutfağı, adanın yerli halkları, Osmanlı İmparatorluğu ve Yunan mirasının etkilerini birleştiren zengin bir gastronomi geleneğidir. Deniz ürünleri, yaban otları ve yerel sebzeler bu mutfağın temelini oluşturur. Hellim peyniri, şeftali kebabı, molehiya ve kolokas gibi yemekler yüzyılların öncesinden bu yana ada mutfağının simgeleri olmuştur.</p>
            </section>
            <section class="mt-8 max-w-3xl">
                <h2 class="font-display text-xl font-semibold">Kıbrıs Yemekleri — En Çok Aranan 20 Tarif</h2>
                <p class="hc-body-sm mt-2 leading-relaxed text-muted">Bu liste, "Kıbrıs yemek" anahtar kelimesiyle en çok aranan 20 tarifi kapsar. Her bir tarif gramajlı, süreli ve adımlı olarak doğrulanmıştır.</p>
                <ul class="mt-4 space-y-2">
                    @foreach($tarifler->take(20) as $tarif)
                        <li class="flex items-center gap-3 hc-body-sm">
                            <span class="font-semibold text-gold-700 dark:text-gold-400">{{ $loop->iteration }}.</span>
                            <a href="{{ route('recipes.show', $tarif) }}" class="hover:underline">{{ $tarif->title }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Kıbrıs Malzeme Sözlüğü: Long-tail "Kıbrıs malzemeleri" araması için --}}
            <section class="mt-12 max-w-3xl">
                <h2 class="font-display text-xl font-semibold">Kıbrıs Mutfağı Malzeme Sözlüğü</h2>
                <p class="hc-body-sm mt-3 leading-relaxed text-muted">Kıbrıs Yemekleri'nin temel malzemeleri: hellim peyniri, hellim, şeftali, zeytinyağı, narenciye, ada nanesi, kekik, harnup pekmezi, ceviz, badem, nar, patlıcan, domates, biber ve daha fazlası. Her malzeme Kıbrıs'ın kendine özgü iklim ve toprak yapısından lezzet alır.</p>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <a href="{{ route('recipes.show', 'hellim-ve-domatesli-kahvalti-tavasi') }}" class="rounded-xl border border-charcoal-100 bg-white p-3 text-center transition hover:border-gold-300 hover:bg-gold-50 dark:border-charcoal-800 dark:bg-charcoal-900 dark:hover:border-gold-700">
                        <span class="font-semibold">Hellim</span>
                        <span class="hc-body-xs mt-1 block text-muted">Kıbrıs peyniri</span>
                    </a>
                    <a href="{{ route('recipes.show', 'kibris-usulu-seftali-kebabi') }}" class="rounded-xl border border-charcoal-100 bg-white p-3 text-center transition hover:border-gold-300 hover:bg-gold-50 dark:border-charcoal-800 dark:bg-charcoal-900 dark:hover:border-gold-700">
                        <span class="font-semibold">Şeftali Kebabı</span>
                        <span class="hc-body-xs mt-1 block text-muted">Kıbrıs klasiği</span>
                    </a>
                    <a href="{{ route('recipes.show', 'kibris-usulu-kleftiko-kuzu-firin-55M4W') }}" class="rounded-xl border border-charcoal-100 bg-white p-3 text-center transition hover:border-gold-300 hover:bg-gold-50 dark:border-charcoal-800 dark:bg-charcoal-900 dark:hover:border-gold-700">
                        <span class="font-semibold">Kleftiko</span>
                        <span class="hc-body-xs mt-1 block text-muted">Kuzu tandır</span>
                    </a>
                    <a href="{{ route('recipes.show', 'kibris-usulu-molohiya-FY1DJ') }}" class="rounded-xl border border-charcoal-100 bg-white p-3 text-center transition hover:border-gold-300 hover:bg-gold-50 dark:border-charcoal-800 dark:bg-charcoal-900 dark:hover:border-gold-700">
                        <span class="font-semibold">Molehiya</span>
                        <span class="hc-body-xs mt-1 block text-muted">Yaprak yemeği</span>
                    </a>
                    <a href="{{ route('recipes.show', 'kibris-usulu-kolokas-1G5V5') }}" class="rounded-xl border border-charcoal-100 bg-white p-3 text-center transition hover:border-gold-300 hover:bg-gold-50 dark:border-charcoal-800 dark:bg-charcoal-900 dark:hover:border-gold-700">
                        <span class="font-semibold">Kolokas</span>
                        <span class="hc-body-xs mt-1 block text-muted">Taro kökü</span>
                    </a>
                    <a href="{{ route('recipes.show', 'kibris-hellimli-gozleme-J6K0C') }}" class="rounded-xl border border-charcoal-100 bg-white p-3 text-center transition hover:border-gold-300 hover:bg-gold-50 dark:border-charcoal-800 dark:bg-charcoal-900 dark:hover:border-gold-700">
                        <span class="font-semibold">Gözleme</span>
                        <span class="hc-body-xs mt-1 block text-muted">Hellimli gözleme</span>
                    </a>
                </div>
            </section>

            {{-- Kıbrıs Mevsimsel Yemek Rehberi: "Kıbrıs'ta ne yenir mevsim" araması için --}}
            <section class="mt-12 max-w-3xl">
                <h2 class="font-display text-xl font-semibold">Kıbrıs'ta Mevsimlere Göre Ne Yenir?</h2>
                <p class="hc-body-sm mt-3 leading-relaxed text-muted">Kıbrıs'ın Akdeniz iklimi, her mevsim taze ve yerel malzemenin Available olmasını sağlar. İşte mevsimlere göre Kıbrıs Yemekleri rehberi:</p>
                <div class="mt-4 space-y-4">
                    <div class="rounded-xl border border-charcoal-100 bg-white p-4 dark:border-charcoal-800 dark:bg-charcoal-900">
                        <h3 class="font-semibold">Bahar (Mart–Mayıs)</h3>
                        <p class="hc-body-sm mt-2 text-muted">Taze otlar, enginar, bakla, fratella (girit kavurması),Amtelı (olive salad). Kıbrıs baharında yaban otları ve narenciye çiçekleri eşsiz lezzetler sunar.</p>
                    </div>
                    <div class="rounded-xl border border-charcoal-100 bg-white p-4 dark:border-charcoal-800 dark:bg-charcoal-900">
                        <h3 class="font-semibold">Yaz (Haziran–Ağustos)</h3>
                        <p class="hc-body-sm mt-2 text-muted">Domates, biber, patlıcan, kavun, karpuz. Şeftali kebabı, ızgara hellim, moussaka ve taze salatalar yaz aylarının vazgeçilmezleri.</p>
                    </div>
                    <div class="rounded-xl border border-charcoal-100 bg-white p-4 dark:border-charcoal-800 dark:bg-charcoal-900">
                        <h3 class="font-semibold">Sonbahar (Eylül–Kasım)</h3>
                        <p class="hc-body-sm mt-2 text-muted">Narenciye, nar, kestane, mantar, zeytin hasadı. Kleftiko, pilavuna ve sıcak çorbalar sonbahar sofralarının baş tacı.</p>
                    </div>
                    <div class="rounded-xl border border-charcoal-100 bg-white p-4 dark:border-charcoal-800 dark:bg-charcoal-900">
                        <h3 class="font-semibold">Kış (Aralık–Şubat)</h3>
                        <p class="hc-body-sm mt-2 text-muted">Turunç, greyfurt, karnıbahar, lahana. Sıcak çorbalar, fırın yemekleri, pasta ve tatlılar kış mevsiminin favorileri.</p>
                    </div>
                </div>
            </section>

            {{-- Kıbrıs Kültürel Mirası: "Kıbrıs hakkında" araması için --}}
            <section class="mt-12 max-w-3xl">
                <h2 class="font-display text-xl font-semibold">Kıbrıs Hakkında — Ada, Kültür ve Mutfak</h2>
                <p class="hc-body-sm mt-3 leading-relaxed text-muted">Kıbrıs, Akdeniz'in üçüncü büyük adasıdır. Lefkoşa, Girne, Gazimağusa, Lefke ve Güzelyurt gibi şehirleriyle zengin bir kültüre sahiptir. Kıbrıs Mutfağı, bu ada kültürünün en önemli yansımasıdır — her yemek yüzyıllık bir geleneğin devamıdır. House Chef Cyprus, Kıbrıs'ın mutfak mirasını dijital dünyada korur ve yayır.</p>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-charcoal-100 bg-white p-3 text-center dark:border-charcoal-800 dark:bg-charcoal-900">
                        <span class="text-2xl">🏙️</span>
                        <span class="hc-body-sm mt-1 block font-semibold">Lefkoşa</span>
                        <span class="hc-body-xs text-muted">Başkent</span>
                    </div>
                    <div class="rounded-xl border border-charcoal-100 bg-white p-3 text-center dark:border-charcoal-800 dark:bg-charcoal-900">
                        <span class="text-2xl">🏰</span>
                        <span class="hc-body-sm mt-1 block font-semibold">Girne</span>
                        <span class="hc-body-xs text-muted">Liman kenti</span>
                    </div>
                    <div class="rounded-xl border border-charcoal-100 bg-white p-3 text-center dark:border-charcoal-800 dark:bg-charcoal-900">
                        <span class="text-2xl">🏛️</span>
                        <span class="hc-body-sm mt-1 block font-semibold">Gazimağusa</span>
                        <span class="hc-body-xs text-muted">Tarihi surlar</span>
                    </div>
                    <div class="rounded-xl border border-charcoal-100 bg-white p-3 text-center dark:border-charcoal-800 dark:bg-charcoal-900">
                        <span class="text-2xl">🌿</span>
                        <span class="hc-body-sm mt-1 block font-semibold">Lefke</span>
                        <span class="hc-body-xs text-muted">Narenciye vadisi</span>
                    </div>
                    <div class="rounded-xl border border-charcoal-100 bg-white p-3 text-center dark:border-charcoal-800 dark:bg-charcoal-900">
                        <span class="text-2xl">🍊</span>
                        <span class="hc-body-sm mt-1 block font-semibold">Güzelyurt</span>
                        <span class="hc-body-xs text-muted">Portakal başkenti</span>
                    </div>
                    <div class="rounded-xl border border-charcoal-100 bg-white p-3 text-center dark:border-charcoal-800 dark:bg-charcoal-900">
                        <span class="text-2xl"> peninsula️</span>
                        <span class="hc-body-sm mt-1 block font-semibold">Karpaz</span>
                        <span class="hc-body-xs text-muted">Yarımada</span>
                    </div>
                </div>
            </section>

            {{-- Coğrafi İşaretler: "Kıbrıs hellim", "Kıbrıs şeftali kebabı" araması için --}}
            <section class="mt-12 max-w-3xl">
                <h2 class="font-display text-xl font-semibold">Kıbrıs Coğrafi İşaretleri — PDO Lisanslı Lezzetler</h2>
                <p class="hc-body-sm mt-3 leading-relaxed text-muted">Kıbrıs, coğrafi işaret tescilli lezzetleriyle dünya çapında tanınır. Hellim peyniri (PDO), şeftali kebabı, kolokas ve Flaouna gibi yemekler yalnız Kıbrıs'a özgüdür ve patentlidir.</p>
                <ul class="mt-4 space-y-2">
                    <li class="hc-body-sm"><strong>Hellim (Halloumi)</strong> — Yarı sert tuzlu peynir, keçi ve koyun sütünden. Kızartılabilir veya ızgarada pişirilebilir. <a href="{{ route('ingredients.hub.show', 'hellim') }}" class="text-gold-700 hover:underline dark:text-gold-400">Detay →</a></li>
                    <li class="hc-body-sm"><strong>Şeftali Kebabı (Sheftalia)</strong> — Kuzu eti ve baharat karışımı, arı Lemdenin zarında. <a href="{{ route('recipes.show', 'kibris-usulu-seftali-kebabi') }}" class="text-gold-700 hover:underline dark:text-gold-400">Tarif →</a></li>
                    <li class="hc-body-sm"><strong>Flaouna (Pilavuna)</strong> — Paskalya çöreği, hellim ve nane ile. <a href="{{ route('recipes.show', 'kibris-usulu-flaouna-Q4HMP') }}" class="text-gold-700 hover:underline dark:text-gold-400">Tarif →</a></li>
                    <li class="hc-body-sm"><strong>Kolokas (Taro)</strong> — Taro kökü, domates ve limon sosuyla pişirilir. <a href="{{ route('recipes.show', 'kibris-usulu-kolokas-1G5V5') }}" class="text-gold-700 hover:underline dark:text-gold-400">Tarif →</a></li>
                    <li class="hc-body-sm"><strong>Molehiya</strong> — Kurutulmuş yapraklar, kuzu eti ve limon ile pişirilir. <a href="{{ route('recipes.show', 'kibris-usulu-molohiya-FY1DJ') }}" class="text-gold-700 hover:underline dark:text-gold-400">Tarif →</a></li>
                </ul>
            </section>

            {{-- Sık Sorulanlar --}}
            <section class="mt-12 max-w-3xl">
                <h2 class="font-display text-xl font-semibold">Sık Sorulanlar — Kıbrıs Yemekleri</h2>
                <dl class="mt-6 space-y-4">
                    <div class="rounded-xl border border-charcoal-100 bg-white p-4 dark:border-charcoal-800 dark:bg-charcoal-900">
                        <dt class="font-semibold">Kıbrıs Yemekleri nelerdir?</dt>
                        <dd class="hc-body-sm mt-2 leading-relaxed text-muted">Hellim, şeftali kebabı, molehiya, kolokas, magarina bulli, pirohu, pilavuna ve {{ max(0, $tarifler->count() - 7) }} ada lezzeti daha — toplam {{ $tarifler->count() }} doğrulanmış Kıbrıs Mutfağı tarifi.</dd>
                    </div>
                    <div class="rounded-xl border border-charcoal-100 bg-white p-4 dark:border-charcoal-800 dark:bg-charcoal-900">
                        <dt class="font-semibold">Kıbrıs Yemek Tarifi nereden bulunur?</dt>
                        <dd class="hc-body-sm mt-2 leading-relaxed text-muted">Bu sayfada. “Kıbrıs Usulü” etiketli {{ $tarifler->count() }} tarifin tamamı ölçülmüş, denenmiş ve besin değerleriyle birlikte yayınlanır.</dd>
                    </div>
                    <div class="rounded-xl border border-charcoal-100 bg-white p-4 dark:border-charcoal-800 dark:bg-charcoal-900">
                        <dt class="font-semibold">Kıbrıs Mutfağı ile Kıbrıs Yemekleri aynı mı?</dt>
                        <dd class="hc-body-sm mt-2 leading-relaxed text-muted">Evet. “Kıbrıs Mutfağı”, “Kıbrıs Yemekleri” ve “Yemek Kıbrıs” aynı ada mutfağını ifade eder ve bu sayfa 5 varyantın kanonik kaynağıdır.</dd>
                    </div>
                </dl>
                <p class="hc-body-sm mt-6 leading-relaxed text-muted">Daha fazla Kıbrıs rehberi için <a class="font-semibold text-gold-700 underline-offset-2 hover:underline dark:text-gold-400" href="{{ route('blog.index') }}?q=Kıbrıs">Kıbrıs blog arşivi</a> ve <a class="font-semibold text-gold-700 underline-offset-2 hover:underline dark:text-gold-400" href="{{ route('ingredients.hub.show', 'hellim') }}">Hellim</a> / <a class="font-semibold text-gold-700 underline-offset-2 hover:underline dark:text-gold-400" href="{{ route('cuisines.show', 'kibris-mutfagi') }}">Kıbrıs Mutfağı</a> koleksiyonuna bakın.</p>
            </section>
        @endif

        <div class="mt-12">
            <h2 class="font-display text-2xl font-semibold">{{ $mutfak->name }} {{ __('cuisine.recipes_suffix') }}</h2>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                @foreach($tarifler as $tarif)
                    <x-recipe-card-v3 :recipe="$tarif" :show-favorite="false" />
                    @if($loop->iteration === 4 && ! $loop->last)
                        <x-ad-slot slot-name="in_feed" class="col-span-full my-2" />
                    @endif
                @endforeach
            </div>
        </div>

        <div class="mt-12 max-w-3xl">
            <x-ad-slot slot-name="multiplex" class="my-8" />
        </div>

        <x-collection-siblings :baslik="__('cuisine.siblings_title')" :ogeler="$kardesler" :mevcut-url="route('cuisines.show', $mutfak->slug)" />
    </section>
</x-layouts.site>
