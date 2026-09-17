<x-layouts.site
    title="Catering Cyprus — Kıbrıs'ta Profesyonel Catering ve Etkinlik Yemeği Hizmeti"
    meta-description="Kuzey Kıbrıs genelinde düğün, kurumsal etkinlik, villa partisi ve özel davetler için profesyonel catering hizmeti. Otantik Kıbrıs ve Akdeniz menüleri, ekip servisi, tam donanım."
    :structured-data="[
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'FoodService',
                '@id' => url('/catering-cyprus') . '#service',
                'name' => 'House Chef Cyprus Catering Service',
                'description' => 'Kuzey Kıbrıs genelinde düğün, kurumsal etkinlik ve villa davetleri için profesyonel catering hizmeti.',
                'provider' => [
                    '@type' => 'Organization',
                    'name' => 'House Chef Cyprus',
                    'url' => url('/'),
                ],
                'areaServed' => [
                    ['@type' => 'City', 'name' => 'Girne (Kyrenia)'],
                    ['@type' => 'City', 'name' => 'İskele (Trikomo)'],
                    ['@type' => 'City', 'name' => 'Gazimağusa (Famagusta)'],
                    ['@type' => 'City', 'name' => 'Lefkoşa (Nicosia)'],
                ],
                'serviceType' => 'Catering & Event Dining',
            ],
            [
                '@type' => 'FAQPage',
                '@id' => url('/catering-cyprus') . '#faq',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Catering hizmetiniz kaç kişilik etkinlikler için uygundur?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'House Chef Cyprus catering hizmeti 10 kişilik özel villa akşam yemeklerinden 500 kişilik büyük düğün ve kurumsal ziyafetlere kadar her ölçekte planlama yapar.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Catering menülerinizde neler bulunur?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Geleneksel Kıbrıs meze açık büfesi, odun ateşinde souvla ve kleftiko, Akdeniz deniz ürünleri, vejetaryen ve vegan seçenekler, özel diyet uyumlu menüler sunmaktayız.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Catering fiyatlandırması nasıl çalışır?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Fiyatlandırma seçilen menü paketi, kişi sayısı, etkinlik süresi ve ekipman kirasına bağlı olarak belirlenir. Ortalama kişi başı 35 € ile 90 € arasında değişmekte olup ücretsiz keşif görüşmesiyle size özel teklif hazırlanır.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Düğün catering için ne kadar önceden rezervasyon yapmalıyım?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Özellikle yaz sezonu (Nisan–Ekim) için en az 6–8 hafta önceden rezervasyon önerilir. Kurumsal etkinlikler ve villa davetleri için 2–3 haftalık süre yeterlidir.',
                        ],
                    ],
                ],
            ],
        ],
    ]">

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-stone-900 via-stone-900 to-stone-950 py-16 text-white sm:py-24">
        <div class="hc-container relative z-10 mx-auto max-w-5xl text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-amber-400">
                🍽️ Kuzey Kıbrıs Profesyonel Catering Hizmeti
            </span>
            <h1 class="mt-6 text-3xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                Kıbrıs'ta Her Etkinliğe Özel: <span class="bg-gradient-to-r from-amber-400 via-orange-300 to-yellow-400 bg-clip-text text-transparent">Catering Cyprus</span>
            </h1>
            <p class="mx-auto mt-6 max-w-3xl text-base leading-relaxed text-stone-300 sm:text-xl">
                Düğünden kurumsal ziyafete, villa davetinden boş arazi partisine — Girne, İskele, Lefkoşa ve Gazimağusa genelinde otantik Kıbrıs ve Akdeniz lezzetleriyle profesyonel catering.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="#teklif-formu" class="hc-v3-btn hc-v3-btn--primary px-8 py-3.5 text-base shadow-xl shadow-amber-950/50">
                    Ücretsiz Teklif Al <span aria-hidden="true">↓</span>
                </a>
                <a href="{{ route('private-chef.index') }}" class="hc-v3-btn hc-v3-btn--ghost border border-white/20 px-8 py-3.5 text-base text-white/80 hover:text-white">
                    Private Chef Hizmeti →
                </a>
            </div>
        </div>
        {{-- Decorative gradient --}}
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(217,119,6,0.12)_0%,_transparent_70%)]" aria-hidden="true"></div>
    </section>

    {{-- Trust Bar --}}
    <section class="border-y border-stone-200 bg-stone-50 py-6 dark:border-stone-800 dark:bg-stone-900">
        <div class="hc-container mx-auto max-w-5xl">
            <ul class="flex flex-wrap items-center justify-center gap-6 text-sm font-medium text-stone-600 dark:text-stone-400 sm:gap-10">
                <li class="flex items-center gap-2"><span aria-hidden="true">✓</span> 10–500 kişilik etkinlikler</li>
                <li class="flex items-center gap-2"><span aria-hidden="true">✓</span> Tam ekipman ve servis ekibi</li>
                <li class="flex items-center gap-2"><span aria-hidden="true">✓</span> Vejetaryen ve vegan seçenekler</li>
                <li class="flex items-center gap-2"><span aria-hidden="true">✓</span> Ücretsiz keşif görüşmesi</li>
                <li class="flex items-center gap-2"><span aria-hidden="true">✓</span> Tüm Kuzey Kıbrıs'a hizmet</li>
            </ul>
        </div>
    </section>

    {{-- Hizmet Kategorileri --}}
    <section class="py-16 sm:py-20">
        <div class="hc-container mx-auto max-w-5xl">
            <div class="mb-12 text-center">
                <h2 class="text-2xl font-extrabold tracking-tight text-stone-900 dark:text-stone-100 sm:text-3xl">Her Etkinlik Türüne Uygun Catering Paketi</h2>
                <p class="mt-3 text-base text-stone-500 dark:text-stone-400">Detaylı keşif görüşmesinin ardından size özel menü ve fiyat teklifi hazırlanır.</p>
            </div>

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Düğün --}}
                <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm dark:border-stone-700 dark:bg-stone-900">
                    <div class="mb-4 text-3xl" aria-hidden="true">💒</div>
                    <h3 class="text-lg font-bold text-stone-900 dark:text-stone-100">Düğün & Nişan Catering</h3>
                    <p class="mt-2 text-sm text-stone-500 dark:text-stone-400">50–500 misafir. Geleneksel Kıbrıs düğün menüsü, açık büfe veya oturma servisi. Pasta kesilmesi ve çiftin damak zevkine özel menü tasarımı.</p>
                    <ul class="mt-4 space-y-1.5 text-sm text-stone-600 dark:text-stone-300">
                        <li>✓ Meze açık büfesi</li>
                        <li>✓ Odun ateşinde souvla veya kleftiko</li>
                        <li>✓ Canlı pişirme istasyonu</li>
                        <li>✓ Servis ekibi ve ekipman dahil</li>
                    </ul>
                    <p class="mt-5 text-xs font-semibold text-amber-600 dark:text-amber-400">Kişi başı 45€'dan başlayan fiyatlarla</p>
                </div>

                {{-- Kurumsal --}}
                <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm dark:border-stone-700 dark:bg-stone-900">
                    <div class="mb-4 text-3xl" aria-hidden="true">🏢</div>
                    <h3 class="text-lg font-bold text-stone-900 dark:text-stone-100">Kurumsal Etkinlik Catering</h3>
                    <p class="mt-2 text-sm text-stone-500 dark:text-stone-400">Konferans öğle yemekleri, şirket gezileri, ekip buluşmaları ve VIP akşam yemekleri. Sunum kalitesi ve zamanlama konusunda ekstra hassasiyet.</p>
                    <ul class="mt-4 space-y-1.5 text-sm text-stone-600 dark:text-stone-300">
                        <li>✓ Markalı sunum seçenekleri</li>
                        <li>✓ Diyet filtreli menü planlaması</li>
                        <li>✓ Fatura ve ön ödeme esnekliği</li>
                        <li>✓ Minimum 10 kişiden itibaren</li>
                    </ul>
                    <p class="mt-5 text-xs font-semibold text-amber-600 dark:text-amber-400">Kişi başı 35€'dan başlayan fiyatlarla</p>
                </div>

                {{-- Villa & Özel Parti --}}
                <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm dark:border-stone-700 dark:bg-stone-900">
                    <div class="mb-4 text-3xl" aria-hidden="true">🏡</div>
                    <h3 class="text-lg font-bold text-stone-900 dark:text-stone-100">Villa & Özel Parti Catering</h3>
                    <p class="mt-2 text-sm text-stone-500 dark:text-stone-400">Doğum günü, yıl dönümü, arkadaş buluşması veya villa tatil akşam yemekleri. Butik ve kişisel deneyim odaklı hizmet.</p>
                    <ul class="mt-4 space-y-1.5 text-sm text-stone-600 dark:text-stone-300">
                        <li>✓ 10–80 kişilik gruplar</li>
                        <li>✓ Havuz başı veya teras düzeni</li>
                        <li>✓ Barbeque ve meze özel paketleri</li>
                        <li>✓ Kokteyl & şarap eşleşmesi</li>
                    </ul>
                    <p class="mt-5 text-xs font-semibold text-amber-600 dark:text-amber-400">Kişi başı 40€'dan başlayan fiyatlarla</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Menü Örnekleri --}}
    <section class="bg-amber-50 py-16 dark:bg-stone-900/60 sm:py-20">
        <div class="hc-container mx-auto max-w-5xl">
            <h2 class="mb-10 text-center text-2xl font-extrabold tracking-tight text-stone-900 dark:text-stone-100 sm:text-3xl">Örnek Catering Menüleri</h2>

            <div class="grid gap-8 md:grid-cols-2">
                {{-- Klasik Kıbrıs Meze Büfesi --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm dark:bg-stone-800">
                    <h3 class="mb-1 text-base font-bold text-amber-700 dark:text-amber-400">🫒 Klasik Kıbrıs Meze Büfesi</h3>
                    <p class="mb-4 text-xs text-stone-400">Kişi başı 45€ · Minimum 20 kişi</p>
                    <ul class="space-y-1 text-sm text-stone-700 dark:text-stone-300">
                        <li><strong>Soğuk Mezeler:</strong> Çakıstes, talatturi, humus, tahin ezmesi, taze hellim</li>
                        <li><strong>Sıcak Ara Sıcaklar:</strong> Izgara hellim, hellimli börek, karides güveç</li>
                        <li><strong>Ana Yemek:</strong> Odun fırınında kleftiko (kuzu but)</li>
                        <li><strong>Pilav:</strong> Şehriyeli tereyağlı pilav</li>
                        <li><strong>Tatlı:</strong> Loukoumades ve Kıbrıs macunu</li>
                    </ul>
                </div>

                {{-- Akdeniz BBQ Paketi --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm dark:bg-stone-800">
                    <h3 class="mb-1 text-base font-bold text-amber-700 dark:text-amber-400">🔥 Akdeniz BBQ Paketi</h3>
                    <p class="mb-4 text-xs text-stone-400">Kişi başı 55€ · Minimum 15 kişi</p>
                    <ul class="space-y-1 text-sm text-stone-700 dark:text-stone-300">
                        <li><strong>Mezeler:</strong> Kıbrıs köy salatası, tzatziki, pide ve köy ekmeği</li>
                        <li><strong>Souvla:</strong> Odun ateşinde dönen tam kuzu veya domuz</li>
                        <li><strong>Şeftali Kebabı:</strong> Kuzu gömleğine sarılı otantik Kıbrıs kebabı</li>
                        <li><strong>Sebzeler:</strong> Izgara hellim, biber, kabak, patlıcan</li>
                        <li><strong>Tatlı:</strong> Taze meyve ve bal-kaymak</li>
                    </ul>
                </div>

                {{-- Vegan & Vejetaryen Paket --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm dark:bg-stone-800">
                    <h3 class="mb-1 text-base font-bold text-green-700 dark:text-green-400">🥗 Vegan & Vejetaryen Menü</h3>
                    <p class="mb-4 text-xs text-stone-400">Kişi başı 40€ · Her gruba eklenebilir</p>
                    <ul class="space-y-1 text-sm text-stone-700 dark:text-stone-300">
                        <li><strong>Soğuklar:</strong> Humus, babaganuş, zeytinli salata, tabule</li>
                        <li><strong>Sıcaklar:</strong> Zeytinyağlı enginar, mercimek köftesi, falafel</li>
                        <li><strong>Ana Yemek:</strong> Vegan güveç veya zeytinyağlı nohut yemeği</li>
                        <li><strong>Yan Ürünler:</strong> Bulgur pilavı, taze ekmek</li>
                        <li><strong>Tatlı:</strong> Mevsim meyveleri ve incir tatlısı</li>
                    </ul>
                </div>

                {{-- Kurumsal Öğle --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm dark:bg-stone-800">
                    <h3 class="mb-1 text-base font-bold text-amber-700 dark:text-amber-400">💼 Kurumsal Öğle Paketi</h3>
                    <p class="mb-4 text-xs text-stone-400">Kişi başı 35€ · 10–200 kişi</p>
                    <ul class="space-y-1 text-sm text-stone-700 dark:text-stone-300">
                        <li><strong>Çorba:</strong> Günün çorbası (kırmızı mercimek veya sebze)</li>
                        <li><strong>Ana:</strong> Fırın tavuk + pilav veya vejetaryen seçenek</li>
                        <li><strong>Salata:</strong> Mevsim salatası</li>
                        <li><strong>Servis:</strong> Bardak su, ekmek, tek kullanımlık veya real tabak</li>
                        <li><strong>Opsiyonel:</strong> Tatlı ve içecek</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Teklif Formu --}}
    <section id="teklif-formu" class="py-16 sm:py-20">
        <div class="hc-container mx-auto max-w-2xl">
            <div class="mb-10 text-center">
                <h2 class="text-2xl font-extrabold tracking-tight text-stone-900 dark:text-stone-100 sm:text-3xl">Ücretsiz Catering Teklifi Alın</h2>
                <p class="mt-3 text-base text-stone-500 dark:text-stone-400">Etkinliğinizin detaylarını paylaşın, 24 saat içinde kişiye özel teklif iletilsin.</p>
            </div>

            @if(session('contact_success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-5 text-center text-sm font-medium text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
                    ✓ Talebiniz alındı! En kısa sürede sizinle iletişime geçeceğiz.
                </div>
            @endif

            <form
                action="{{ route('contact.store') }}"
                method="POST"
                class="space-y-5 rounded-2xl border border-stone-200 bg-white p-8 shadow-sm dark:border-stone-700 dark:bg-stone-900">
                @csrf

                <input type="hidden" name="subject" value="partnership">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="catering-name" class="mb-1.5 block text-sm font-medium text-stone-700 dark:text-stone-300">Ad Soyad *</label>
                        <input
                            type="text"
                            id="catering-name"
                            name="name"
                            required
                            placeholder="Adınız Soyadınız"
                            class="w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm text-stone-900 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-stone-600 dark:bg-stone-800 dark:text-stone-100">
                    </div>
                    <div>
                        <label for="catering-email" class="mb-1.5 block text-sm font-medium text-stone-700 dark:text-stone-300">E-posta *</label>
                        <input
                            type="email"
                            id="catering-email"
                            name="email"
                            required
                            placeholder="ornek@email.com"
                            class="w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm text-stone-900 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-stone-600 dark:bg-stone-800 dark:text-stone-100">
                    </div>
                </div>

                <div>
                    <label for="catering-phone" class="mb-1.5 block text-sm font-medium text-stone-700 dark:text-stone-300">Telefon</label>
                    <input
                        type="tel"
                        id="catering-phone"
                        name="phone"
                        placeholder="+90 542 000 00 00"
                        class="w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm text-stone-900 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-stone-600 dark:bg-stone-800 dark:text-stone-100">
                </div>

                <div>
                    <label for="catering-message" class="mb-1.5 block text-sm font-medium text-stone-700 dark:text-stone-300">Etkinlik Detayları *</label>
                    <textarea
                        id="catering-message"
                        name="message"
                        rows="5"
                        required
                        placeholder="Etkinlik tarihi, yer, kişi sayısı, menü tercihleri ve varsa özel taleplerinizi paylaşın..."
                        class="w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm text-stone-900 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-stone-600 dark:bg-stone-800 dark:text-stone-100"></textarea>
                </div>

                <button type="submit" class="hc-v3-btn hc-v3-btn--primary w-full py-3.5 text-base">
                    Teklif Talebini Gönder
                </button>
                <p class="text-center text-xs text-stone-400">Bilgileriniz yalnızca teklif hazırlama amacıyla kullanılır. Üçüncü taraflarla paylaşılmaz.</p>
            </form>
        </div>
    </section>

    {{-- Hizmet Bölgesi --}}
    <section class="border-t border-stone-200 bg-stone-50 py-12 dark:border-stone-800 dark:bg-stone-900">
        <div class="hc-container mx-auto max-w-5xl text-center">
            <h2 class="mb-4 text-xl font-bold text-stone-900 dark:text-stone-100">Kıbrıs Genelinde Catering Hizmeti Veriyoruz</h2>
            <p class="mx-auto mb-8 max-w-2xl text-sm text-stone-500 dark:text-stone-400">
                Girne (Kyrenia), İskele (Long Beach ve Trikomo), Gazimağusa (Famagusta), Lefkoşa (Nicosia), Lapta, Alsancak, Bellapais, Çatalköy ve Karpaz bölgesi dahil tüm Kuzey Kıbrıs.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3 text-sm font-medium text-stone-600 dark:text-stone-400">
                <span class="rounded-full bg-white px-4 py-2 shadow-sm dark:bg-stone-800">📍 Girne & Kyrenia</span>
                <span class="rounded-full bg-white px-4 py-2 shadow-sm dark:bg-stone-800">📍 İskele & Long Beach</span>
                <span class="rounded-full bg-white px-4 py-2 shadow-sm dark:bg-stone-800">📍 Gazimağusa</span>
                <span class="rounded-full bg-white px-4 py-2 shadow-sm dark:bg-stone-800">📍 Lefkoşa</span>
                <span class="rounded-full bg-white px-4 py-2 shadow-sm dark:bg-stone-800">📍 Lapta & Alsancak</span>
                <span class="rounded-full bg-white px-4 py-2 shadow-sm dark:bg-stone-800">📍 Karpaz</span>
            </div>
            <div class="mt-8">
                <a href="{{ route('private-chef.index') }}" class="text-sm font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400">
                    Daha özel bir deneyim mi arıyorsunuz? Private Chef hizmetimizi inceleyin →
                </a>
            </div>
        </div>
    </section>

</x-layouts.site>
