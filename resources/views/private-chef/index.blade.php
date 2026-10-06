<x-layouts.site 
    title="Private Chef Cyprus — Kıbrıs Villa & Özel Davet Şef Hizmeti" 
    meta-description="Kuzey Kıbrıs genelinde (Girne, İskele, Lefkoşa, Gazimağusa) lüks villanızda veya özel davetlerinizde profesyonel özel şef deneyimi. Otantik Kıbrıs meze menüleri, Akdeniz deniz mahsulleri ve gurme barbekü."
    :structured-data="[
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'FoodService',
                '@id' => url('/private-chef') . '#service',
                'name' => 'House Chef Cyprus Private Chef Service',
                'description' => 'Kuzey Kıbrıs genelinde lüks villa tatilleri ve özel etkinlikler için profesyonel özel şef ve gurme catering hizmeti.',
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
                'serviceType' => 'Private Chef & Villa Dining',
            ],
            [
                '@type' => 'FAQPage',
                '@id' => url('/private-chef') . '#faq',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Private Chef hizmeti neleri kapsar?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Özel şef hizmetimiz menü planlama, günlük yerel ada malzemelerinin tedariki, villanızda taze pişirme, masaya şef servisi ve servis sonrası mutfağın kusursuz temizliğini eksiksiz kapsar.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Kıbrıs\'ta hangi bölgelerde özel şef hizmeti veriyorsunuz?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Başta Girne (Bellapais, Alsancak, Çatalköy, Lapta), İskele Long Beach, Gazimağusa, Lefkoşa ve Karpaz bölgesi olmak üzere tüm Kuzey Kıbrıs genelindeki kiralık veya şahsi villalarda hizmet vermekteyiz.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Özel şef fiyatları ne kadardır?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Fiyatlandırma seçilen menü konseptine (geleneksel meze & tandır, deniz ürünleri veya gurme barbekü), kişi sayısına ve özel şarap/içecek taleplerine göre kişi başı ortalama 60 € ile 120 € arasında değişmektedir.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Alerjen ve özel diyet talepleri karşılanabilir mi?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Evet. Menü tasarım aşamasında glütensiz, vegan, vejetaryen, helal, laktozsuz veya fındık/fıstık alerjisi gibi tüm özel hassasiyetler şefimiz tarafından titizlikle dikkate alınır.',
                        ],
                    ],
                ],
            ],
        ],
    ]">

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-stone-900 via-stone-900 to-stone-950 py-16 text-white sm:py-24">
        <div class="hc-container relative z-10 mx-auto max-w-5xl text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-orange-500/30 bg-orange-500/10 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-orange-400">
                ✨ Kuzey Kıbrıs Özel Şef & Villa Gastronomi Hizmeti
            </span>
            <h1 class="mt-6 text-3xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                Villanızda Gurme Şölen: <span class="bg-gradient-to-r from-orange-400 via-amber-300 to-yellow-400 bg-clip-text text-transparent">Private Chef Cyprus</span>
            </h1>
            <p class="mx-auto mt-6 max-w-3xl text-base leading-relaxed text-stone-300 sm:text-xl">
                Girne, İskele, Lefkoşa ve Gazimağusa'daki tatil villanızda unutulmaz bir akşam yemeği. Taze Akdeniz lezzetleri, geleneksel Kıbrıs mezeleri ve odun ateşinde tandır; kişisel şefinizin dokunuşuyla masanızda.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="#rezervasyon-formu" class="hc-v3-btn hc-v3-btn--primary px-8 py-3.5 text-base shadow-xl shadow-orange-950/50">
                    Rezervasyon Talebi Gönder <span aria-hidden="true">↓</span>
                </a>
                <a href="#menu-konseptleri" class="hc-v3-btn hc-v3-btn--outline border-stone-700 px-7 py-3.5 text-base text-stone-200 hover:border-orange-500 hover:text-white">
                    İmza Menüleri İncele
                </a>
            </div>

            {{-- Güven Rozetleri --}}
            <div class="mt-12 grid grid-cols-2 gap-4 border-t border-stone-800 pt-8 sm:grid-cols-4 sm:gap-6 text-center">
                <div>
                    <div class="text-2xl font-black text-orange-400 sm:text-3xl">%100</div>
                    <div class="mt-1 text-xs text-stone-400">Yerel & Taze Malzeme</div>
                </div>
                <div>
                    <div class="text-2xl font-black text-orange-400 sm:text-3xl">Tüm Ada</div>
                    <div class="mt-1 text-xs text-stone-400">Girne • İskele • Mağusa</div>
                </div>
                <div>
                    <div class="text-2xl font-black text-orange-400 sm:text-3xl">Özel Menü</div>
                    <div class="mt-1 text-xs text-stone-400">Diyet & Alerjen Uyumlu</div>
                </div>
                <div>
                    <div class="text-2xl font-black text-orange-400 sm:text-3xl">Sıfır Zahmet</div>
                    <div class="mt-1 text-xs text-stone-400">Alışverişten Temizliğe</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Neden Private Chef Deneyimi? --}}
    <section class="hc-container py-16 sm:py-24">
        <div class="mx-auto max-w-3xl text-center">
            <p class="hc-kicker">Ayrıcalıklı Mutfak</p>
            <h2 class="hc-display-lg mt-2">Restoran Kalitesi Villanızın Rahatlığında</h2>
            <p class="hc-body-lg mt-4" style="color: var(--hc-text-secondary)">
                Restoran rezervasyonu, trafik veya kalabalıkla uğraşmadan; aileniz ve dostlarınızla villanızın havuz başında gün batımının tadını çıkarın. Gerisini şefimize bırakın.
            </p>
        </div>

        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="hc-v3-card p-6 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-orange-50 text-2xl dark:bg-orange-950/50">
                    👨‍🍳
                </div>
                <h3 class="mt-4 text-lg font-bold">Kişiselleştirilmiş Menü</h3>
                <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">
                    Damak tadınıza, alerjenlerinize ve özel diyet isteklerinize göre şefimiz tarafından size özel tasarlanan çok aşamalı tadım menüsü.
                </p>
            </div>

            <div class="hc-v3-card p-6 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-orange-50 text-2xl dark:bg-orange-950/50">
                    🌿
                </div>
                <h3 class="mt-4 text-lg font-bold">Günlük Ada Malzemeleri</h3>
                <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">
                    Karpaz'ın sızma zeytinyağı, yerel köylerden günlük sıcak hellim, Akdeniz'in taze levrekleri ve dağ kekiğiyle harmanlanan etler.
                </p>
            </div>

            <div class="hc-v3-card p-6 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-orange-50 text-2xl dark:bg-orange-950/50">
                    🍷
                </div>
                <h3 class="mt-4 text-lg font-bold">Profesyonel Masaya Servis</h3>
                <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">
                    Her yemeğin hikayesini anlatan, şarap ve içecek uyumunu sağlayan profesyonel sunum ve masaya özel servis deneyimi.
                </p>
            </div>

            <div class="hc-v3-card p-6 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-orange-50 text-2xl dark:bg-orange-950/50">
                    ✨
                </div>
                <h3 class="mt-4 text-lg font-bold">Kusursuz Mutfak Temizliği</h3>
                <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">
                    Yemek sonrasında tezgahlar, tabaklar ve pişirme ekipmanları pırıl pırıl temizlenir. Siz yalnızca akşamın keyfini sürersiniz.
                </p>
            </div>
        </div>
    </section>

    {{-- İmza Menü Konseptleri --}}
    <section id="menu-konseptleri" class="border-y py-16 sm:py-24" style="background: var(--hc-surface-subtle); border-color: var(--hc-line)">
        <div class="hc-container">
            <div class="mx-auto max-w-3xl text-center">
                <p class="hc-kicker">Gastronomi Seçenekleri</p>
                <h2 class="hc-display-lg mt-2">Öne Çıkan Menü Konseptleri</h2>
                <p class="hc-body-lg mt-4" style="color: var(--hc-text-secondary)">
                    En çok tercih edilen üç imza menümüz arasından seçim yapabilir veya isteklerinize göre tamamen özel bir menü oluşturabilirsiniz.
                </p>
            </div>

            <div class="mt-12 grid gap-8 lg:grid-cols-3">
                {{-- Menü 1 --}}
                <div class="hc-v3-card flex flex-col justify-between p-6 sm:p-8">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-800 dark:bg-orange-900/60 dark:text-orange-200">Geleneksel Ada Lezzeti</span>
                            <span class="text-sm font-semibold" style="color: var(--hc-text-muted)">€70 - €95 / kişi</span>
                        </div>
                        <h3 class="mt-4 text-xl font-black">Kıbrıs Meze & Fırın Kleftiko</h3>
                        <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">Asırlık ada tariflerinin odun fırını lezzetiyle buluştuğu zengin Kıbrıs ziyafeti.</p>

                        <div class="mt-6 space-y-4 text-sm">
                            <div>
                                <strong class="block font-bold text-orange-600 dark:text-orange-400">Soğuk & Sıcak Mezeler</strong>
                                <p class="mt-1" style="color: var(--hc-text-secondary)">Izgara Hellim, Humus Paça, Fava, Çakıstes Zeytin, Kabak Çiçeği Dolması, Tahinli Patlıcan Ezme</p>
                            </div>
                            <div>
                                <strong class="block font-bold text-orange-600 dark:text-orange-400">Ana Yemek</strong>
                                <p class="mt-1" style="color: var(--hc-text-secondary)">Kıbrıs Usulü Ağır Pişmiş Kuzu Kleftiko & El Yapımı Şeftali Kebabı, Meyhane Pilavı ile</p>
                            </div>
                            <div>
                                <strong class="block font-bold text-orange-600 dark:text-orange-400">Tatlı Bitiş</strong>
                                <p class="mt-1" style="color: var(--hc-text-secondary)">Cevizli Ev Baklavası veya Geleneksel Çıtır Kıbrıs Tatlısı</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 border-t pt-6" style="border-color: var(--hc-line)">
                        <a href="#rezervasyon-formu" class="hc-v3-btn hc-v3-btn--outline w-full text-center">Bu Menüyü Talep Et</a>
                    </div>
                </div>

                {{-- Menü 2 --}}
                <div class="hc-v3-card relative flex flex-col justify-between border-2 border-orange-500 p-6 sm:p-8 shadow-xl">
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-orange-600 px-4 py-1 text-xs font-black uppercase tracking-wider text-white shadow-md">En Popüler</span>
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">Taze Deniz Ürünleri</span>
                            <span class="text-sm font-semibold" style="color: var(--hc-text-muted)">€85 - €120 / kişi</span>
                        </div>
                        <h3 class="mt-4 text-xl font-black">Akdeniz Balık & Deniz Mahsulleri</h3>
                        <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">Akdeniz'in taze deniz balıkları ve kabuklularıyla hazırlanan hafif ve sofistike menü.</p>

                        <div class="mt-6 space-y-4 text-sm">
                            <div>
                                <strong class="block font-bold text-orange-600 dark:text-orange-400">Başlangıçlar & Mezeler</strong>
                                <p class="mt-1" style="color: var(--hc-text-secondary)">Sarımsaklı Tereyağlı Karides Güveç, Nar Ekşili Deniz Börülcesi, Ahtapot Salatası, Tarator</p>
                            </div>
                            <div>
                                <strong class="block font-bold text-orange-600 dark:text-orange-400">Ana Yemek</strong>
                                <p class="mt-1" style="color: var(--hc-text-secondary)">Kömür Ateşinde Levrek veya Taze Çipura Buğulama, Körpe Kalamar Tava ve Taze Ada Salatası ile</p>
                            </div>
                            <div>
                                <strong class="block font-bold text-orange-600 dark:text-orange-400">Tatlı Bitiş</strong>
                                <p class="mt-1" style="color: var(--hc-text-secondary)">Kıbrıs Narenciyeli Panna Cotta veya Sakızlı İpeksi Fırın Sütlaç</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 border-t pt-6" style="border-color: var(--hc-line)">
                        <a href="#rezervasyon-formu" class="hc-v3-btn hc-v3-btn--primary w-full text-center">Bu Menüyü Talep Et</a>
                    </div>
                </div>

                {{-- Menü 3 --}}
                <div class="hc-v3-card flex flex-col justify-between p-6 sm:p-8">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">Gurme Barbekü</span>
                            <span class="text-sm font-semibold" style="color: var(--hc-text-muted)">€75 - €105 / kişi</span>
                        </div>
                        <h3 class="mt-4 text-xl font-black">Villa Günbatımı Barbekü Şöleni</h3>
                        <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">Özel dinlendirilmiş etler ve taze baharat marinasyonlarıyla villanızın bahçesinde mangal keyfi.</p>

                        <div class="mt-6 space-y-4 text-sm">
                            <div>
                                <strong class="block font-bold text-orange-600 dark:text-orange-400">Mezeler & Salatalar</strong>
                                <p class="mt-1" style="color: var(--hc-text-secondary)">Közlenmiş Patlıcan Ezme, Gavurdağı Salatası, Acılı Atom Sos, Haydari, Taze Köy Ekmeği</p>
                            </div>
                            <div>
                                <strong class="block font-bold text-orange-600 dark:text-orange-400">Izgara Seçkisi</strong>
                                <p class="mt-1" style="color: var(--hc-text-secondary)">Biberiyeli Kuzu Pirzola, Kuru Dinlendirilmiş Dana Antrikot, Kasap Köfte, Baharatlı Tavuk Şiş</p>
                            </div>
                            <div>
                                <strong class="block font-bold text-orange-600 dark:text-orange-400">Tatlı Bitiş</strong>
                                <p class="mt-1" style="color: var(--hc-text-secondary)">Izgara Şeftali & Keçi Sütü Dondurması, Keçiboynuzu Pekmezi Sosu ile</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 border-t pt-6" style="border-color: var(--hc-line)">
                        <a href="#rezervasyon-formu" class="hc-v3-btn hc-v3-btn--outline w-full text-center">Bu Menüyü Talep Et</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Hizmet Verdiğimiz Bölgeler --}}
    <section class="hc-container py-16 sm:py-20">
        <div class="mx-auto max-w-3xl text-center">
            <p class="hc-kicker">Lokasyon Kapsamı</p>
            <h2 class="hc-display-lg mt-2">Kuzey Kıbrıs Genelinde Villa Servisi</h2>
            <p class="hc-body-lg mt-4" style="color: var(--hc-text-secondary)">
                Kiralık lüks villanız, şahsi yazlığınız veya özel tekne davetiniz nerede olursa olsun, şef ekibimiz tüm donanımıyla adresinize gelir.
            </p>
        </div>

        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border p-5 text-center" style="border-color: var(--hc-line)">
                <h3 class="font-bold text-lg text-orange-600 dark:text-orange-400">Girne (Kyrenia)</h3>
                <p class="mt-1 text-xs" style="color: var(--hc-text-secondary)">Bellapais, Alsancak, Çatalköy, Lapta, Ozanköy, Karaoğlanoğlu</p>
            </div>
            <div class="rounded-2xl border p-5 text-center" style="border-color: var(--hc-line)">
                <h3 class="font-bold text-lg text-orange-600 dark:text-orange-400">İskele & Long Beach</h3>
                <p class="mt-1 text-xs" style="color: var(--hc-text-secondary)">Long Beach sahil villaları, Boğaz, Bafra Turizm Bölgesi</p>
            </div>
            <div class="rounded-2xl border p-5 text-center" style="border-color: var(--hc-line)">
                <h3 class="font-bold text-lg text-orange-600 dark:text-orange-400">Gazimağusa</h3>
                <p class="mt-1 text-xs" style="color: var(--hc-text-secondary)">Salamis Koyu, Glapsides, Yeni Boğaziçi ve merkez rezidanslar</p>
            </div>
            <div class="rounded-2xl border p-5 text-center" style="border-color: var(--hc-line)">
                <h3 class="font-bold text-lg text-orange-600 dark:text-orange-400">Lefkoşa & Karpaz</h3>
                <p class="mt-1 text-xs" style="color: var(--hc-text-secondary)">Başkent diplomatik davetleri, butik çiftlik evleri ve Karpaz villaları</p>
            </div>
        </div>
    </section>

    {{-- Rezervasyon ve Teklif Formu --}}
    <section id="rezervasyon-formu" class="border-t py-16 sm:py-24" style="background: var(--hc-surface-subtle); border-color: var(--hc-line)">
        <div class="hc-container max-w-4xl">
            <div class="text-center">
                <p class="hc-kicker">Özel Şef Rezervasyonu</p>
                <h2 class="hc-display-lg mt-2">Özel Şef Teklifi Alın</h2>
                <p class="hc-body-lg mt-4" style="color: var(--hc-text-secondary)">
                    Villanız için tarih, kişi sayısı ve tercih ettiğiniz menü konseptini belirtin; şefimiz 24 saat içinde size özel menü taslağı ve net fiyat teklifiyle geri dönsün.
                </p>
            </div>

            @if(session('status'))
                <div class="hc-v3-alert hc-v3-alert--success mt-8" role="status">
                    <span aria-hidden="true">✓</span>
                    <div><strong>Talebiniz Alındı!</strong><p>{{ session('status') }}</p></div>
                </div>
            @endif

            <form class="hc-v3-card mt-10 p-6 sm:p-10" method="post" action="{{ route('legal.center.contact.store') }}" toolname="request_private_chef" tooldescription="Request a private chef consultation and booking for Northern Cyprus villas and private events">
                @csrf
                <input type="hidden" name="source_page" value="{{ route('private-chef.index') }}">
                <input type="hidden" name="subject" value="private_chef">

                {{-- Honeypot --}}
                <div class="hidden" aria-hidden="true">
                    <label>Web sitesi<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="hc-v3-field-label" for="first_name">Adınız</label>
                        <input id="first_name" class="hc-v3-input" type="text" name="first_name" value="{{ old('first_name') }}" required maxlength="60" placeholder="Ahmet">
                        @error('first_name')<span class="mt-1 block text-xs text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="hc-v3-field-label" for="last_name">Soyadınız</label>
                        <input id="last_name" class="hc-v3-input" type="text" name="last_name" value="{{ old('last_name') }}" required maxlength="60" placeholder="Yılmaz">
                        @error('last_name')<span class="mt-1 block text-xs text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="hc-v3-field-label" for="email">E-posta Adresiniz</label>
                        <input id="email" class="hc-v3-input" type="email" name="email" value="{{ old('email') }}" required placeholder="ahmet@example.com">
                        @error('email')<span class="mt-1 block text-xs text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="hc-v3-field-label" for="phone">Telefon / WhatsApp Numarası</label>
                        <input id="phone" class="hc-v3-input" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="+90 533 ... veya +44 ...">
                        @error('phone')<span class="mt-1 block text-xs text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="mt-5">
                    <label class="hc-v3-field-label" for="message">Etkinlik & Villa Detayları</label>
                    <textarea id="message" class="hc-v3-input min-h-36" name="message" required minlength="20" maxlength="4000" placeholder="Lütfen şu detayları paylaşın:&#10;1. İstenen Tarih ve Saat&#10;2. Villa Konumu (Girne, İskele, Mağusa vb.)&#10;3. Tahmini Kişi Sayısı (Yetişkin / Çocuk)&#10;4. Tercih Edilen Menü (Meze & Kleftiko, Balık, Barbekü vb.)&#10;5. Alerjen veya Diyet Kısıtlamaları">{{ old('message') }}</textarea>
                    @error('message')<span class="mt-1 block text-xs text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                </div>

                <label class="mt-6 flex items-start gap-3 cursor-pointer">
                    <input class="mt-1 h-4 w-4 shrink-0 rounded border-stone-300 text-orange-600 focus:ring-orange-500" type="checkbox" name="privacy_consent" value="1" required>
                    <span class="hc-body-sm text-xs leading-relaxed" style="color: var(--hc-text-secondary)">
                        Talebimin değerlendirilmesi ve şef teklifi hazırlanması için kişisel verilerimin
                        <a class="font-bold text-orange-700 underline underline-offset-2 dark:text-orange-400" href="{{ route('policy.gizlilik') }}">Gizlilik Politikası</a>
                        kapsamında işlenmesini kabul ediyorum.
                    </span>
                </label>
                @error('privacy_consent')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

                @if(config('contact-requests.captcha.enabled') && config('contact-requests.captcha.site_key'))
                    <div class="cf-turnstile mt-5" data-sitekey="{{ config('contact-requests.captcha.site_key') }}" data-response-field-name="captcha_token"></div>
                    @once<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>@endonce
                @endif
                @error('captcha_token')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

                <div class="mt-8 flex flex-wrap items-center justify-between gap-4">
                    <button class="hc-v3-btn hc-v3-btn--primary px-10 py-3 text-base shadow-lg" type="submit">
                        Şef Teklifi İste <span aria-hidden="true">→</span>
                    </button>
                    <span class="text-xs" style="color: var(--hc-text-muted)">
                        Talebiniz doğrudan şef ekibimize iletilir ve ön teklif ücretsizdir.
                    </span>
                </div>
            </form>
        </div>
    </section>

    {{-- Sıkça Sorulan Sorular (FAQ) --}}
    <section class="hc-container py-16 sm:py-24">
        <div class="mx-auto max-w-3xl text-center">
            <p class="hc-kicker">Sık Sorulan Sorular</p>
            <h2 class="hc-display-lg mt-2">Private Chef Hakkında Merak Edilenler</h2>
        </div>

        <div class="mx-auto mt-10 max-w-3xl divide-y" style="border-color: var(--hc-line)">
            <details class="group py-5">
                <summary class="flex cursor-pointer list-none items-center justify-between text-base font-bold sm:text-lg">
                    <span>Özel şef hizmeti tam olarak neleri kapsar?</span>
                    <span class="transition group-open:rotate-180 text-orange-500" aria-hidden="true">▼</span>
                </summary>
                <p class="mt-3 text-sm leading-relaxed" style="color: var(--hc-text-secondary)">
                    Özel şef hizmetimiz menü danışmanlığı, taze ada malzemelerinin şefimiz tarafından yerel üreticilerden tedarik edilmesi, villanızda taze pişirme, şık masaya servis ve yemek sonrası mutfağın tertemiz teslim edilmesini eksiksiz içerir.
                </p>
            </details>

            <details class="group py-5">
                <summary class="flex cursor-pointer list-none items-center justify-between text-base font-bold sm:text-lg">
                    <span>Villada hangi ekipmanların olması gerekir?</span>
                    <span class="transition group-open:rotate-180 text-orange-500" aria-hidden="true">▼</span>
                </summary>
                <p class="mt-3 text-sm leading-relaxed" style="color: var(--hc-text-secondary)">
                    Standart bir ocak, fırın ve buzdolabı olması yeterlidir. Şefimiz profesyonel bıçaklarını, özel sunum gereçlerini, termometrelerini ve gerekirse ek pişirme ekipmanlarını beraberinde getirir.
                </p>
            </details>

            <details class="group py-5">
                <summary class="flex cursor-pointer list-none items-center justify-between text-base font-bold sm:text-lg">
                    <span>Fiyatlandırma nasıl belirlenir?</span>
                    <span class="transition group-open:rotate-180 text-orange-500" aria-hidden="true">▼</span>
                </summary>
                <p class="mt-3 text-sm leading-relaxed" style="color: var(--hc-text-secondary)">
                    Fiyatlandırma seçtiğiniz menü konseptine (örneğin odun ateşinde kleftiko meze menüsü veya taze deniz mahsulleri), kişi sayısına ve özel şarap/içecek taleplerine göre kişi başı ortalama 60 € ile 120 € arasında değişmektedir. Detaylı ve şeffaf teklif önceden sunulur.
                </p>
            </details>

            <details class="group py-5">
                <summary class="flex cursor-pointer list-none items-center justify-between text-base font-bold sm:text-lg">
                    <span>Ne kadar süre önceden rezervasyon yaptırmalıyım?</span>
                    <span class="transition group-open:rotate-180 text-orange-500" aria-hidden="true">▼</span>
                </summary>
                <p class="mt-3 text-sm leading-relaxed" style="color: var(--hc-text-secondary)">
                    Özellikle yaz sezonunda (Mayıs - Ekim) yoğunluk sebebiyle en az 3 ila 7 gün önceden talep oluşturmanızı öneririz. Ancak müsaitlik durumuna göre son dakika (24-48 saat önce) taleplerine de memnuniyetle çözüm üretiyoruz.
                </p>
            </details>
        </div>
    </section>
</x-layouts.site>
