<x-layouts.site
    title="Cooking Classes Cyprus — Kıbrıs'ta Yemek Kursu ve Mutfak Atölyesi"
    meta-description="Kuzey Kıbrıs'ta profesyonel şef eşliğinde Akdeniz ve geleneksel Kıbrıs yemekleri üzerine yemek kursları. Hellim yapımı, kleftiko atölyesi, meze masterclass ve özel grup dersleri."
    :structured-data="[
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'EducationalOrganization',
                '@id' => url('/cooking-classes') . '#org',
                'name' => 'House Chef Cyprus Cooking Classes',
                'description' => 'Kuzey Kıbrıs genelinde geleneksel Kıbrıs ve Akdeniz mutfağı üzerine profesyonel yemek atölyeleri.',
                'url' => url('/cooking-classes'),
                'areaServed' => 'North Cyprus',
            ],
            [
                '@type' => 'Course',
                'name' => 'Kıbrıs Mutfağı Masterclass',
                'description' => 'Geleneksel Kıbrıs yemeklerini profesyonel şef eşliğinde öğrenin: hellim, kleftiko, meze.',
                'provider' => [
                    '@type' => 'Organization',
                    'name' => 'House Chef Cyprus',
                    'url' => url('/'),
                ],
                'courseMode' => 'in-person',
                'educationalLevel' => 'beginner',
                'inLanguage' => ['tr', 'en'],
            ],
            [
                '@type' => 'FAQPage',
                '@id' => url('/cooking-classes') . '#faq',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Yemek kurslarına katılmak için önceden pişirme deneyimi gerekli mi?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Hayır, tüm kurslarımız başlangıç seviyesinden uygulamalı ileri seviyeye kadar her beceri düzeyine göre tasarlanmıştır.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Yemek kursu süreleri ne kadar?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Kurs süresi programa göre değişir. Yarım gün atölyeleri 3 saat, tam gün masterclass\'lar 6 saattir. Özel grup dersleri istenen süreye göre özelleştirilebilir.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Kursta pişirdiğimiz yemekleri yiyor muyuz?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Evet! Tüm uygulamalı atölyelerde pişirdiğiniz yemekleri şef eşliğinde birlikte yiyoruz. Tam gün kurslarında öğle yemeği dahildir.',
                        ],
                    ],
                ],
            ],
        ],
    ]">

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-stone-900 via-stone-900 to-stone-950 py-16 text-white sm:py-24">
        <div class="hc-container relative z-10 mx-auto max-w-5xl text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-green-500/30 bg-green-500/10 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-green-400">
                👨‍🍳 Kıbrıs'ta Uygulamalı Yemek Kursları
            </span>
            <h1 class="mt-6 text-3xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                Gerçek Kıbrıs Mutfağını <span class="bg-gradient-to-r from-green-400 via-emerald-300 to-teal-400 bg-clip-text text-transparent">Şeften Öğrenin</span>
            </h1>
            <p class="mx-auto mt-6 max-w-3xl text-base leading-relaxed text-stone-300 sm:text-xl">
                Hellim peyniri yapımından odun fırınında kleftiko'ya, otantik meze hazırlamadan Kıbrıs tatlılarına — profesyonel şef eşliğinde uygulamalı mutfak atölyeleri.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="#kurs-listesi" class="hc-v3-btn hc-v3-btn--primary px-8 py-3.5 text-base shadow-xl shadow-green-950/50">
                    Kurslara Göz At <span aria-hidden="true">↓</span>
                </a>
                <a href="#rezervasyon" class="hc-v3-btn hc-v3-btn--ghost border border-white/20 px-8 py-3.5 text-base text-white/80 hover:text-white">
                    Rezervasyon Yap
                </a>
            </div>
        </div>
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(34,197,94,0.10)_0%,_transparent_70%)]" aria-hidden="true"></div>
    </section>

    {{-- Trust Bar --}}
    <section class="border-y border-stone-200 bg-stone-50 py-6 dark:border-stone-800 dark:bg-stone-900">
        <div class="hc-container mx-auto max-w-5xl">
            <ul class="flex flex-wrap items-center justify-center gap-6 text-sm font-medium text-stone-600 dark:text-stone-400 sm:gap-10">
                <li class="flex items-center gap-2"><span aria-hidden="true">✓</span> Bireysel ve grup kursları</li>
                <li class="flex items-center gap-2"><span aria-hidden="true">✓</span> Malzemeler dahil</li>
                <li class="flex items-center gap-2"><span aria-hidden="true">✓</span> Türkçe ve İngilizce</li>
                <li class="flex items-center gap-2"><span aria-hidden="true">✓</span> Pişirilen yemekleri yiyorsunuz</li>
                <li class="flex items-center gap-2"><span aria-hidden="true">✓</span> Tarif kopyası hediye</li>
            </ul>
        </div>
    </section>

    {{-- Kurs Listesi --}}
    <section id="kurs-listesi" class="py-16 sm:py-20">
        <div class="hc-container mx-auto max-w-5xl">
            <div class="mb-12 text-center">
                <h2 class="text-2xl font-extrabold tracking-tight text-stone-900 dark:text-stone-100 sm:text-3xl">Yemek Kursları ve Atölyeler</h2>
                <p class="mt-3 text-base text-stone-500 dark:text-stone-400">Her kurs uygulamalı, interaktif ve eğlenceli — mutfakta deneyim kazanmak için tasarlandı.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-2">
                {{-- Hellim Atölyesi --}}
                <div class="flex flex-col rounded-2xl border border-stone-200 bg-white shadow-sm dark:border-stone-700 dark:bg-stone-900">
                    <div class="flex items-start gap-4 p-6">
                        <span class="text-4xl" aria-hidden="true">🧀</span>
                        <div>
                            <h3 class="text-lg font-bold text-stone-900 dark:text-stone-100">Hellim Peyniri Yapımı</h3>
                            <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Yarım gün · 3 saat · Max 8 kişi</p>
                        </div>
                    </div>
                    <div class="border-t border-stone-100 px-6 pb-6 pt-4 dark:border-stone-800">
                        <p class="text-sm text-stone-600 dark:text-stone-300">Kıbrıs'ın en ikonik peynirini — hellim / halloumi'yi — sıfırdan yapın. Taze keçi ve koyun sütünden rennet ekleme, pıhtılaştırma, şekillendirme ve tuzlama aşamalarını öğrenin. Sonunda pişirip yiyin!</p>
                        <ul class="mt-3 space-y-1 text-sm text-stone-500 dark:text-stone-400">
                            <li>✓ PDO sertifikası ve tarih bilgisi</li>
                            <li>✓ Temel süt kimyası (basit anlatım)</li>
                            <li>✓ Eve götürmek için hellim parçası</li>
                        </ul>
                        <div class="mt-5 flex items-center justify-between">
                            <p class="text-lg font-bold text-green-700 dark:text-green-400">Kişi başı 75€</p>
                            <a href="#rezervasyon" class="text-sm font-semibold text-green-700 hover:text-green-800 dark:text-green-400">Yer Ayırt →</a>
                        </div>
                    </div>
                </div>

                {{-- Meze Masterclass --}}
                <div class="flex flex-col rounded-2xl border border-stone-200 bg-white shadow-sm dark:border-stone-700 dark:bg-stone-900">
                    <div class="flex items-start gap-4 p-6">
                        <span class="text-4xl" aria-hidden="true">🫙</span>
                        <div>
                            <h3 class="text-lg font-bold text-stone-900 dark:text-stone-100">Kıbrıs Meze Masterclass</h3>
                            <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Yarım gün · 3.5 saat · Max 10 kişi</p>
                        </div>
                    </div>
                    <div class="border-t border-stone-100 px-6 pb-6 pt-4 dark:border-stone-800">
                        <p class="text-sm text-stone-600 dark:text-stone-300">8 geleneksel Kıbrıs mezesini baştan sona hazırlayın: çakıstes, talatturi, humus, babaganuş, ezme, hellimli börek, karides güveç ve zeytinyağlı enginar. Meze kültürü ve servis ritüelini de öğrenin.</p>
                        <ul class="mt-3 space-y-1 text-sm text-stone-500 dark:text-stone-400">
                            <li>✓ 8 meze tarifi (Türkçe + İngilizce)</li>
                            <li>✓ Servis sırası ve sunum teknikleri</li>
                            <li>✓ Şarap eşleşme rehberi</li>
                        </ul>
                        <div class="mt-5 flex items-center justify-between">
                            <p class="text-lg font-bold text-green-700 dark:text-green-400">Kişi başı 85€</p>
                            <a href="#rezervasyon" class="text-sm font-semibold text-green-700 hover:text-green-800 dark:text-green-400">Yer Ayırt →</a>
                        </div>
                    </div>
                </div>

                {{-- Kleftiko Tam Gün --}}
                <div class="flex flex-col rounded-2xl border border-stone-200 bg-white shadow-sm dark:border-stone-700 dark:bg-stone-900">
                    <div class="flex items-start gap-4 p-6">
                        <span class="text-4xl" aria-hidden="true">🍖</span>
                        <div>
                            <h3 class="text-lg font-bold text-stone-900 dark:text-stone-100">Kleftiko & Tandır Tam Gün</h3>
                            <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Tam gün · 6 saat · Max 8 kişi</p>
                        </div>
                    </div>
                    <div class="border-t border-stone-100 px-6 pb-6 pt-4 dark:border-stone-800">
                        <p class="text-sm text-stone-600 dark:text-stone-300">Sabah 10'dan akşam 4'e kadar süren bu masterclass'ta gerçek kleftiko hazırlıyorsunuz: marine, sarma, düşük ısı fırın tekniği ve servis. Öğle yemeği dahil; hazırladığınız kleftiko sofranın yıldızı oluyor.</p>
                        <ul class="mt-3 space-y-1 text-sm text-stone-500 dark:text-stone-400">
                            <li>✓ Sabah kahvaltısı ve öğle yemeği dahil</li>
                            <li>✓ Kollajen-jelatin bilimi anlatımı</li>
                            <li>✓ Commandaria şarap eşleşmesi</li>
                        </ul>
                        <div class="mt-5 flex items-center justify-between">
                            <p class="text-lg font-bold text-green-700 dark:text-green-400">Kişi başı 140€</p>
                            <a href="#rezervasyon" class="text-sm font-semibold text-green-700 hover:text-green-800 dark:text-green-400">Yer Ayırt →</a>
                        </div>
                    </div>
                </div>

                {{-- Özel Grup Dersi --}}
                <div class="flex flex-col rounded-2xl border border-stone-200 bg-white shadow-sm dark:border-stone-700 dark:bg-stone-900">
                    <div class="flex items-start gap-4 p-6">
                        <span class="text-4xl" aria-hidden="true">👥</span>
                        <div>
                            <h3 class="text-lg font-bold text-stone-900 dark:text-stone-100">Özel Grup & Kurumsal Ders</h3>
                            <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Esnek süre · 4–20 kişi · Özel tema</p>
                        </div>
                    </div>
                    <div class="border-t border-stone-100 px-6 pb-6 pt-4 dark:border-stone-800">
                        <p class="text-sm text-stone-600 dark:text-stone-300">Bekarlığa veda partisi, şirket team building, doğum günü kutlaması veya villa tatil aktivitesi olarak özel yemek dersi. Menü, süre ve mekan sizin seçiminize göre planlanır.</p>
                        <ul class="mt-3 space-y-1 text-sm text-stone-500 dark:text-stone-400">
                            <li>✓ Tamamen özelleştirilebilir program</li>
                            <li>✓ Villanızda veya mutfağımızda</li>
                            <li>✓ Grup indirimleri mevcuttur</li>
                        </ul>
                        <div class="mt-5 flex items-center justify-between">
                            <p class="text-lg font-bold text-green-700 dark:text-green-400">Fiyat teklif bazlı</p>
                            <a href="#rezervasyon" class="text-sm font-semibold text-green-700 hover:text-green-800 dark:text-green-400">Teklif Al →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Nasıl Çalışır --}}
    <section class="bg-stone-50 py-16 dark:bg-stone-900/60 sm:py-20">
        <div class="hc-container mx-auto max-w-4xl">
            <h2 class="mb-10 text-center text-2xl font-extrabold tracking-tight text-stone-900 dark:text-stone-100 sm:text-3xl">Kurs Nasıl İşler?</h2>
            <ol class="grid gap-6 sm:grid-cols-4">
                <li class="text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-xl font-bold text-green-700 dark:bg-green-900/40 dark:text-green-300">1</div>
                    <h3 class="mb-1 font-semibold text-stone-900 dark:text-stone-100">Kurs Seç</h3>
                    <p class="text-sm text-stone-500 dark:text-stone-400">İlginizi çeken atölyeyi seçin, aşağıdan tarih ve kişi sayısı belirtin.</p>
                </li>
                <li class="text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-xl font-bold text-green-700 dark:bg-green-900/40 dark:text-green-300">2</div>
                    <h3 class="mb-1 font-semibold text-stone-900 dark:text-stone-100">Onay Al</h3>
                    <p class="text-sm text-stone-500 dark:text-stone-400">24 saat içinde mevcut tarih seçenekleri ve ödeme bilgileri iletilir.</p>
                </li>
                <li class="text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-xl font-bold text-green-700 dark:bg-green-900/40 dark:text-green-300">3</div>
                    <h3 class="mb-1 font-semibold text-stone-900 dark:text-stone-100">Pişir & Öğren</h3>
                    <p class="text-sm text-stone-500 dark:text-stone-400">Şef eşliğinde adım adım hazırlayın, ipuçlarını ve teknikleri öğrenin.</p>
                </li>
                <li class="text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-xl font-bold text-green-700 dark:bg-green-900/40 dark:text-green-300">4</div>
                    <h3 class="mb-1 font-semibold text-stone-900 dark:text-stone-100">Ye & Götür</h3>
                    <p class="text-sm text-stone-500 dark:text-stone-400">Pişirdiklerinizi birlikte yiyin, tarif kartlarıyla eve gidin.</p>
                </li>
            </ol>
        </div>
    </section>

    {{-- Rezervasyon Formu --}}
    <section id="rezervasyon" class="py-16 sm:py-20">
        <div class="hc-container mx-auto max-w-2xl">
            <div class="mb-10 text-center">
                <h2 class="text-2xl font-extrabold tracking-tight text-stone-900 dark:text-stone-100 sm:text-3xl">Kurs Rezervasyonu</h2>
                <p class="mt-3 text-base text-stone-500 dark:text-stone-400">İlgilendiğiniz kursu ve tercihinizi yazın — 24 saat içinde tarih ve ödeme detayları iletilir.</p>
            </div>

            @if(session('contact_success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-5 text-center text-sm font-medium text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
                    ✓ Rezervasyon talebiniz alındı! En kısa sürede iletişime geçeceğiz.
                </div>
            @endif

            <form
                action="{{ route('contact.store') }}"
                method="POST"
                class="space-y-5 rounded-2xl border border-stone-200 bg-white p-8 shadow-sm dark:border-stone-700 dark:bg-stone-900">
                @csrf

                <input type="hidden" name="subject" value="private_chef">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="class-name" class="mb-1.5 block text-sm font-medium text-stone-700 dark:text-stone-300">Ad Soyad *</label>
                        <input type="text" id="class-name" name="name" required placeholder="Adınız Soyadınız"
                            class="w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm text-stone-900 focus:border-green-500 focus:ring-1 focus:ring-green-500 dark:border-stone-600 dark:bg-stone-800 dark:text-stone-100">
                    </div>
                    <div>
                        <label for="class-email" class="mb-1.5 block text-sm font-medium text-stone-700 dark:text-stone-300">E-posta *</label>
                        <input type="email" id="class-email" name="email" required placeholder="ornek@email.com"
                            class="w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm text-stone-900 focus:border-green-500 focus:ring-1 focus:ring-green-500 dark:border-stone-600 dark:bg-stone-800 dark:text-stone-100">
                    </div>
                </div>

                <div>
                    <label for="class-course" class="mb-1.5 block text-sm font-medium text-stone-700 dark:text-stone-300">Hangi Kurs? *</label>
                    <select id="class-course" name="course" required
                        class="w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm text-stone-900 focus:border-green-500 focus:ring-1 focus:ring-green-500 dark:border-stone-600 dark:bg-stone-800 dark:text-stone-100">
                        <option value="">Kurs seçin...</option>
                        <option value="hellim">Hellim Peyniri Yapımı (75€/kişi)</option>
                        <option value="meze">Kıbrıs Meze Masterclass (85€/kişi)</option>
                        <option value="kleftiko">Kleftiko Tam Gün (140€/kişi)</option>
                        <option value="ozel">Özel Grup Dersi (fiyat teklif bazlı)</option>
                    </select>
                </div>

                <div>
                    <label for="class-message" class="mb-1.5 block text-sm font-medium text-stone-700 dark:text-stone-300">Tercih Tarih ve Detaylar</label>
                    <textarea id="class-message" name="message" rows="4"
                        placeholder="Tercih ettiğiniz tarih aralığı, kişi sayısı ve varsa özel isteklerinizi yazın..."
                        class="w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm text-stone-900 focus:border-green-500 focus:ring-1 focus:ring-green-500 dark:border-stone-600 dark:bg-stone-800 dark:text-stone-100"></textarea>
                </div>

                <button type="submit" class="hc-v3-btn hc-v3-btn--primary w-full py-3.5 text-base">
                    Rezervasyon Talebi Gönder
                </button>
                <p class="text-center text-xs text-stone-400">Ön ödeme bilgileri onay e-postasıyla iletilecektir. İptal politikası: etkinlikten 72 saat önce ücretsiz iptal.</p>
            </form>
        </div>
    </section>

    {{-- İlgili İçerik --}}
    <section class="border-t border-stone-200 bg-stone-50 py-12 dark:border-stone-800 dark:bg-stone-900">
        <div class="hc-container mx-auto max-w-5xl text-center">
            <h2 class="mb-2 text-lg font-bold text-stone-900 dark:text-stone-100">Kıbrıs Mutfağını Daha Derinlemesine Keşfedin</h2>
            <p class="mb-6 text-sm text-stone-500 dark:text-stone-400">Blog yazılarımızdan Kıbrıs yemek kültürü ve tarifleri hakkında daha fazla bilgi edinin.</p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="/blog/kibris-mutfagi-rehberi" class="rounded-full bg-white px-5 py-2 text-sm font-medium text-stone-700 shadow-sm hover:bg-stone-100 dark:bg-stone-800 dark:text-stone-300">📖 Kıbrıs Mutfağı Rehberi</a>
                <a href="/blog/hellim-peyniri-rehberi" class="rounded-full bg-white px-5 py-2 text-sm font-medium text-stone-700 shadow-sm hover:bg-stone-100 dark:bg-stone-800 dark:text-stone-300">🧀 Hellim Peyniri Rehberi</a>
                <a href="/blog/kibris-meze-rehberi" class="rounded-full bg-white px-5 py-2 text-sm font-medium text-stone-700 shadow-sm hover:bg-stone-100 dark:bg-stone-800 dark:text-stone-300">🫙 Meze Rehberi</a>
                <a href="{{ route('private-chef.index') }}" class="rounded-full bg-white px-5 py-2 text-sm font-medium text-stone-700 shadow-sm hover:bg-stone-100 dark:bg-stone-800 dark:text-stone-300">👨‍🍳 Private Chef Hizmeti</a>
            </div>
        </div>
    </section>

</x-layouts.site>
