<x-layouts.site
    title="Meal Planning Cyprus — Kıbrıs & Akdeniz Haftalık Yemek Planlayıcı"
    meta-description="Kuzey Kıbrıs'ın taze yerel malzemeleriyle dengeli Akdeniz yemek planları. Kalp dostu zeytinyağlılar, yüksek proteinli geleneksel tarifler ve pratik haftalık menüler."
    :structured-data="[
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebPage',
                '@id' => url('/meal-plans') . '#webpage',
                'name' => 'Kıbrıs ve Akdeniz Haftalık Yemek Planlayıcı',
                'description' => 'Akdeniz diyetine uygun haftalık yemek planları, kalori ve besin dengesi, otomatik alışveriş listeleri.',
                'url' => url('/meal-plans'),
                'inLanguage' => ['tr', 'en'],
            ],
            [
                '@type' => 'FAQPage',
                '@id' => url('/meal-plans') . '#faq',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Akdeniz yemek planı haftalık olarak nasıl uygulanır?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Haftalık planlarımız haftada 2 gün baklagil, 2 gün taze balık ve deniz mahsulü, 2 gün zeytinyağlı sebze ve 1 gün geleneksel Kıbrıs et yemeği prensibine göre dengelenir.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Yemek planlarında malzeme israfı nasıl önleniyor?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Ortak malzeme rotasyonu sayesinde haftalık alışverişte aldığınız taze otlar, zeytinyağı ve temel sebzeler birden fazla tarifte kullanılarak sıfır israf hedeflenir.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Özel diyet gereksinimlerine (vegan, glütensiz) uygun menüler var mı?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Evet, vegan Akdeniz planımız ve glütensiz alternatiflerimiz tamamen doğal ada malzemeleriyle hazırlanmıştır.',
                        ],
                    ],
                ],
            ],
        ],
    ]">

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-stone-900 via-stone-900 to-stone-950 py-16 text-white sm:py-24">
        <div class="hc-container relative z-10 mx-auto max-w-5xl text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-emerald-400">
                🥗 Akdeniz & Kıbrıs Usulü Beslenme
            </span>
            <h1 class="mt-6 text-3xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                Ne Pişireceğinizi Düşünmeyin, <br class="hidden sm:inline">
                <span class="bg-gradient-to-r from-emerald-400 via-teal-300 to-amber-300 bg-clip-text text-transparent">Dengeli Beslenin</span>
            </h1>
            <p class="mx-auto mt-6 max-w-3xl text-base leading-relaxed text-stone-300 sm:text-xl">
                Sıfır malzeme israfı, optimum besin dengesi ve gerçek Kıbrıs lezzetleri. Şeflerimiz tarafından kurgulanan 7 günlük dengeli menüler ve otomatik alışveriş listeleri.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('weekly-menu.index') }}" class="hc-v3-btn hc-v3-btn--primary px-8 py-3.5 text-base shadow-xl shadow-emerald-950/50">
                    Bu Haftanın Menüsünü Gör →
                </a>
                <a href="#planlar" class="hc-v3-btn hc-v3-btn--ghost border border-white/20 px-8 py-3.5 text-base text-white/80 hover:text-white">
                    Plan Seçenekleri ↓
                </a>
            </div>
        </div>
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(16,185,129,0.12)_0%,_transparent_70%)]" aria-hidden="true"></div>
    </section>

    {{-- Temel Avantajlar --}}
    <section class="border-y border-stone-200 bg-stone-50 py-8 dark:border-stone-800 dark:bg-stone-900">
        <div class="hc-container mx-auto max-w-6xl">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 text-center">
                <div class="p-4">
                    <div class="text-3xl mb-2">⏱️</div>
                    <h3 class="font-bold text-stone-900 dark:text-stone-100">Haftada 4 Saat Kazanın</h3>
                    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Her gün "bugün ne pişirsem" stresini ortadan kaldırır.</p>
                </div>
                <div class="p-4">
                    <div class="text-3xl mb-2">🥑</div>
                    <h3 class="font-bold text-stone-900 dark:text-stone-100">%100 Akdeniz Dengesi</h3>
                    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Zeytinyağı, lif, taze sebze ve kaliteli protein uyumu.</p>
                </div>
                <div class="p-4">
                    <div class="text-3xl mb-2">🛒</div>
                    <h3 class="font-bold text-stone-900 dark:text-stone-100">Sıfır Malzeme İsrafı</h3>
                    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Aynı malzemeleri akıllıca paylaştıran rotasyonlu menüler.</p>
                </div>
                <div class="p-4">
                    <div class="text-3xl mb-2">📉</div>
                    <h3 class="font-bold text-stone-900 dark:text-stone-100">Bütçe Dostu Mutfak</h3>
                    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Planlı alışverişle market harcamalarınızı %30 düşürün.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Plan Seçenekleri --}}
    <section id="planlar" class="py-16 sm:py-24">
        <div class="hc-container mx-auto max-w-6xl">
            <div class="mb-14 text-center">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Hedefinize Uygun</span>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-stone-900 dark:text-stone-100 sm:text-4xl">4 Özelleştirilmiş Yemek Planı</h2>
                <p class="mt-3 text-base text-stone-500 dark:text-stone-400">Yaşam tarzınıza ve beslenme hedeflerinize en uygun Akdeniz menüsü.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-2">
                {{-- Plan 1: Klasik Akdeniz --}}
                <div class="flex flex-col rounded-2xl border border-stone-200 bg-white p-7 shadow-sm transition hover:shadow-md dark:border-stone-700 dark:bg-stone-900">
                    <div class="flex items-center justify-between">
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">En Popüler</span>
                        <span class="text-xs font-medium text-stone-400">7 Günlük Döngü</span>
                    </div>
                    <h3 class="mt-4 text-xl font-bold text-stone-900 dark:text-stone-100">Klasik Akdeniz Sağlık Planı</h3>
                    <p class="mt-2 text-sm text-stone-600 dark:text-stone-300">
                        Dünya Sağlık Örgütü tarafından altın standart kabul edilen geleneksel Akdeniz diyeti. Kalp damar sağlığı, uzun ömür ve sürdürülebilir enerji için ideal.
                    </p>
                    <ul class="mt-5 space-y-2 border-t border-stone-100 pt-5 text-sm text-stone-600 dark:border-stone-800 dark:text-stone-300">
                        <li class="flex items-center gap-2">✓ Sızma zeytinyağı ve taze yabani otlar</li>
                        <li class="flex items-center gap-2">✓ Haftada 2 gün Akdeniz levreği / çipura</li>
                        <li class="flex items-center gap-2">✓ Lif zengini baklagiller (nohut, börülce, mercimek)</li>
                        <li class="flex items-center gap-2">✓ Günde ortalama 1800-2100 kcal dengeli enerji</li>
                    </ul>
                    <div class="mt-6 pt-4 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                        <span class="text-xs text-stone-400">21 Tarif Dahil</span>
                        <a href="{{ route('weekly-menu.index') }}" class="hc-v3-btn hc-v3-btn--primary text-sm py-2 px-4">Planı İncele →</a>
                    </div>
                </div>

                {{-- Plan 2: Kıbrıs Yüksek Protein --}}
                <div class="flex flex-col rounded-2xl border border-stone-200 bg-white p-7 shadow-sm transition hover:shadow-md dark:border-stone-700 dark:bg-stone-900">
                    <div class="flex items-center justify-between">
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Aktif Yaşam</span>
                        <span class="text-xs font-medium text-stone-400">7 Günlük Döngü</span>
                    </div>
                    <h3 class="mt-4 text-xl font-bold text-stone-900 dark:text-stone-100">Kıbrıs Yüksek Protein Planı</h3>
                    <p class="mt-2 text-sm text-stone-600 dark:text-stone-300">
                        Geleneksel Kıbrıs fırın etleri, kleftiko, ızgara hellim ve anari peyniri ile güçlendirilmiş, kas kütlesini koruyan ve tokluk sağlayan zengin menü.
                    </p>
                    <ul class="mt-5 space-y-2 border-t border-stone-100 pt-5 text-sm text-stone-600 dark:border-stone-800 dark:text-stone-300">
                        <li class="flex items-center gap-2">✓ Yüksek kaliteli keçi/koyun proteini & hellim</li>
                        <li class="flex items-center gap-2">✓ Ağır ateşte pişen kuzu incik ve tandır</li>
                        <li class="flex items-center gap-2">✓ Düşük glisemik indeksli bulgur ve kuskus</li>
                        <li class="flex items-center gap-2">✓ Günlük 110-130g doğal protein hedefi</li>
                    </ul>
                    <div class="mt-6 pt-4 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                        <span class="text-xs text-stone-400">21 Tarif Dahil</span>
                        <a href="{{ route('recipes.index', ['cuisine' => 'kibris-mutfagi']) }}" class="hc-v3-btn hc-v3-btn--outline text-sm py-2 px-4">Tarifleri Keşfet →</a>
                    </div>
                </div>

                {{-- Plan 3: Vegan & Bitkisel Akdeniz --}}
                <div class="flex flex-col rounded-2xl border border-stone-200 bg-white p-7 shadow-sm transition hover:shadow-md dark:border-stone-700 dark:bg-stone-900">
                    <div class="flex items-center justify-between">
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800 dark:bg-green-900/40 dark:text-green-300">%100 Bitkisel</span>
                        <span class="text-xs font-medium text-stone-400">7 Günlük Döngü</span>
                    </div>
                    <h3 class="mt-4 text-xl font-bold text-stone-900 dark:text-stone-100">Vegan & Bitkisel Akdeniz</h3>
                    <p class="mt-2 text-sm text-stone-600 dark:text-stone-300">
                        Kıbrıs'ın bereketli topraklarından zeytinyağlı taze fasulye, enginar göbeği, tahinli humus, fava ve molehiya ile tam bitkisel beslenme.
                    </p>
                    <ul class="mt-5 space-y-2 border-t border-stone-100 pt-5 text-sm text-stone-600 dark:border-stone-800 dark:text-stone-300">
                        <li class="flex items-center gap-2">✓ Sıfır hayvansal gıda, %100 doğal bitkisel lezzet</li>
                        <li class="flex items-center gap-2">✓ Doğal demir ve mineral deposu ada yeşillikleri</li>
                        <li class="flex items-center gap-2">✓ Tahin, ceviz ve susamla zenginleştirilmiş mezeler</li>
                        <li class="flex items-center gap-2">✓ Yüksek lifle hafif ve enerjik sindirim</li>
                    </ul>
                    <div class="mt-6 pt-4 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                        <span class="text-xs text-stone-400">21 Tarif Dahil</span>
                        <a href="{{ route('recipes.index', ['category' => 'zeytinyagli-sebze']) }}" class="hc-v3-btn hc-v3-btn--outline text-sm py-2 px-4">Sebze Menüsü →</a>
                    </div>
                </div>

                {{-- Plan 4: 20 Dakikalık Pratik Aile Menüsü --}}
                <div class="flex flex-col rounded-2xl border border-stone-200 bg-white p-7 shadow-sm transition hover:shadow-md dark:border-stone-700 dark:bg-stone-900">
                    <div class="flex items-center justify-between">
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Hızlı & Kolay</span>
                        <span class="text-xs font-medium text-stone-400">Hafta İçi Akşamları</span>
                    </div>
                    <h3 class="mt-4 text-xl font-bold text-stone-900 dark:text-stone-100">Pratik Hafta İçi Aile Planı</h3>
                    <p class="mt-2 text-sm text-stone-600 dark:text-stone-300">
                        Yoğun çalışanlar ve aileler için maksimum 25 dakikada sofraya gelen, tek tavada pişen lezzetli ve besleyici akşam yemekleri.
                    </p>
                    <ul class="mt-5 space-y-2 border-t border-stone-100 pt-5 text-sm text-stone-600 dark:border-stone-800 dark:text-stone-300">
                        <li class="flex items-center gap-2">✓ Her tarif 15–25 dakika hazırlık ve pişirme</li>
                        <li class="flex items-center gap-2">✓ Minimum bulaşık (tek tencere / fırın tepsisi)</li>
                        <li class="flex items-center gap-2">✓ Çocukların severek tükettiği Akdeniz tatları</li>
                        <li class="flex items-center gap-2">✓ Önceden hazırlanabilen akıllı soslar ve mezeler</li>
                    </ul>
                    <div class="mt-6 pt-4 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                        <span class="text-xs text-stone-400">14 Tarif Dahil</span>
                        <a href="{{ route('recipes.index') }}" class="hc-v3-btn hc-v3-btn--outline text-sm py-2 px-4">Hızlı Menü →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Akıllı Alışveriş Listesi Callout --}}
    <section class="bg-gradient-to-r from-emerald-900 to-teal-950 py-16 text-white">
        <div class="hc-container mx-auto max-w-5xl text-center">
            <h2 class="text-2xl font-extrabold tracking-tight sm:text-4xl">Tek Tıkla Akıllı Alışveriş Listesi</h2>
            <p class="mx-auto mt-4 max-w-2xl text-base text-emerald-200">
                Seçtiğiniz haftalık menünün tüm malzemeleri gramaj bazında toplanır, evinizdeki kilerle karşılaştırılır ve fazladan tek kuruş harcamadan markete çıkmanız sağlanır.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="{{ route('weekly-menu.index') }}" class="hc-v3-btn hc-v3-btn--primary px-8 py-3 text-base">
                    Haftalık Planı Görüntüle
                </a>
                <a href="{{ route('ingredients.explore') }}" class="hc-v3-btn hc-v3-btn--ghost border border-white/30 text-white hover:bg-white/10 px-8 py-3 text-base">
                    Dolabımdaki Malzemelerle Eşleştir
                </a>
            </div>
        </div>
    </section>

    {{-- FAQ Bölümü --}}
    <section class="py-16 sm:py-20">
        <div class="hc-container mx-auto max-w-4xl">
            <h2 class="mb-10 text-center text-2xl font-extrabold tracking-tight text-stone-900 dark:text-stone-100 sm:text-3xl">Sıkça Sorulan Sorular</h2>
            <div class="space-y-4">
                <details class="group rounded-xl border border-stone-200 bg-white p-5 dark:border-stone-700 dark:bg-stone-900" open>
                    <summary class="cursor-pointer font-bold text-stone-900 dark:text-stone-100 flex items-center justify-between">
                        Akdeniz yemek planı haftalık olarak nasıl dengelenir?
                        <span class="transition group-open:rotate-180">▼</span>
                    </summary>
                    <p class="mt-3 text-sm text-stone-600 dark:text-stone-300 leading-relaxed">
                        Haftalık planlarımız haftada 2 gün baklagil (mercimek, nohut, börülce), 2 gün taze balık ve deniz mahsulü, 2 gün zeytinyağlı sebze yemeği ve 1 gün geleneksel Kıbrıs et yemeği (kleftiko, tandır vb.) prensibine göre kurgulanır. Bu dağılım optimal kolesterol ve sindirim sağlığı sağlar.
                    </p>
                </details>

                <details class="group rounded-xl border border-stone-200 bg-white p-5 dark:border-stone-700 dark:bg-stone-900">
                    <summary class="cursor-pointer font-bold text-stone-900 dark:text-stone-100 flex items-center justify-between">
                        Yemek planlarında malzeme israfı nasıl önleniyor?
                        <span class="transition group-open:rotate-180">▼</span>
                    </summary>
                    <p class="mt-3 text-sm text-stone-600 dark:text-stone-300 leading-relaxed">
                        Örneğin Pazartesi günü için aldığınız taze maydanoz, dereotu ve zeytinyağı Salı günkü mezede ve Çarşamba günkü balık buğulamada da kullanılır. Böylece dolapta çürüyen malzeme kalmaz.
                    </p>
                </details>

                <details class="group rounded-xl border border-stone-200 bg-white p-5 dark:border-stone-700 dark:bg-stone-900">
                    <summary class="cursor-pointer font-bold text-stone-900 dark:text-stone-100 flex items-center justify-between">
                        Tariflerin kalori ve porsiyonları ölçeklenebilir mi?
                        <span class="transition group-open:rotate-180">▼</span>
                    </summary>
                    <p class="mt-3 text-sm text-stone-600 dark:text-stone-300 leading-relaxed">
                        Her tarif sayfamızda porsiyon ölçekleme aracı mevcuttur. Kişi sayısını 2'den 8'e çıkardığınızda malzeme miktarları ve tahmini besin değerleri anında yeniden hesaplanır.
                    </p>
                </details>
            </div>
        </div>
    </section>

</x-layouts.site>
