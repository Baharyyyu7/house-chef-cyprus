@props(['recipe'])

@php
    $whatsappNumber = config('contact-requests.whatsapp_number', '905338000000');
    $dishTitle = $recipe->title;
    $waText = urlencode("Merhaba House Chef Cyprus! '{$dishTitle}' tarifinizi inceledim. Kıbrıs'taki villamız / davetimiz için özel şef ve catering hizmeti hakkında fiyat ve menü detaylarını öğrenmek istiyorum.");
    $waUrl = "https://api.whatsapp.com/send?phone={$whatsappNumber}&text={$waText}";
@endphp

<aside itemscope itemtype="https://schema.org/Service" aria-label="Kıbrıs Özel Villa Şefi ve Catering Hizmeti" class="hc-v3-card mt-8 p-6 sm:p-8 border-2 border-orange-300/80 dark:border-orange-500/30 bg-gradient-to-br from-amber-50/90 via-white to-orange-50/70 dark:from-stone-900 dark:via-stone-900 dark:to-stone-850 shadow-lg">
    <meta itemprop="serviceType" content="Private Chef and Catering North Cyprus">
    <meta itemprop="provider" content="House Chef Cyprus">

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div class="flex items-start gap-4">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 text-3xl text-white shadow-xl shadow-orange-600/25">
                👨‍🍳
            </div>
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-600/15 px-3 py-1 text-[11px] font-black uppercase tracking-wider text-orange-700 dark:text-orange-300">
                        <span aria-hidden="true">✦</span> Kuzey Kıbrıs Özel Villa Şefi & Ada Geneli Catering
                    </span>
                    <span class="text-xs font-bold text-stone-600 dark:text-stone-400">
                        Girne · Lefkoşa · Gazimağusa · İskele · Bellapais · Alsancak · Esentepe
                    </span>
                </div>

                <h3 itemprop="name" class="text-xl sm:text-2xl font-black tracking-tight text-stone-900 dark:text-white">
                    Bu Yemeği Villanızda Usta Şefimize Pişirtin
                </h3>

                <p class="text-xs sm:text-sm leading-relaxed text-stone-700 dark:text-stone-300 max-w-2xl">
                    Kıbrıs'ta tatil veya davet mi planlıyorsunuz? Taze ada pazarı malzemeleri, profesyonel villa mutfağı hazırlığı, 5 yıldızlı tabaklama servisi ve eksiksiz mutfak temizliği dahil tam kapsamlı şef deneyimi sunuyoruz.
                </p>

                <!-- İç Bağlantılar & Geo-SEO Backlink Kümesi -->
                <div class="pt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-stone-600 dark:text-stone-400">
                    <span>Hizmetlerimiz:</span>
                    <a href="{{ route('private-chef.index') }}" class="font-bold text-orange-600 hover:text-orange-700 dark:text-orange-400 underline underline-offset-2">
                        Özel Villa Şefi Paketleri
                    </a>
                    <span class="text-stone-300 dark:text-stone-700" aria-hidden="true">•</span>
                    <a href="{{ route('catering.index') }}" class="font-bold text-orange-600 hover:text-orange-700 dark:text-orange-400 underline underline-offset-2">
                        Kıbrıs Villa Catering & Davet
                    </a>
                    <span class="text-stone-300 dark:text-stone-700" aria-hidden="true">•</span>
                    <a href="{{ route('cooking-classes.index') }}" class="font-bold text-orange-600 hover:text-orange-700 dark:text-orange-400 underline underline-offset-2">
                        Ada Gastronomi Atölyeleri
                    </a>
                    <span class="text-stone-300 dark:text-stone-700" aria-hidden="true">•</span>
                    <a href="{{ route('meal-plans.index') }}" class="font-bold text-orange-600 hover:text-orange-700 dark:text-orange-400 underline underline-offset-2">
                        Haftalık VIP Menü Planları
                    </a>
                </div>

                <!-- Affiliate Köprüsü (Evde Kendin Pişir) -->
                <div class="pt-1 flex items-center gap-2 text-xs text-stone-500 dark:text-stone-400">
                    <span aria-hidden="true">🍳</span>
                    <span>Bu tarifi evinizde şef kıvamında pişirmek için gereken ekipmanları mı arıyorsunuz?</span>
                    <a href="#recommended-equipment" class="font-bold text-stone-800 dark:text-stone-200 underline hover:text-orange-600 dark:hover:text-orange-400">
                        Şefin Ekipman Tavsiyelerine Göz Atın ↓
                    </a>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row lg:flex-col items-stretch lg:items-end gap-3 shrink-0 pt-4 lg:pt-0 border-t lg:border-t-0 border-orange-200/70 dark:border-stone-800">
            <a href="{{ route('private-chef.index') }}"
               class="flex items-center justify-center gap-2 rounded-2xl bg-orange-600 px-6 py-3.5 text-xs sm:text-sm font-black text-white shadow-lg shadow-orange-600/25 transition hover:bg-orange-700 hover:scale-102">
                <span>Şef Menüleri & Fiyat Paketleri</span>
                <span aria-hidden="true">➔</span>
            </a>

            <a href="{{ $waUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               class="flex items-center justify-center gap-2 rounded-2xl border-2 border-emerald-600/80 bg-emerald-50 dark:bg-emerald-950/40 px-5 py-2.5 text-xs sm:text-sm font-bold text-emerald-800 dark:text-emerald-200 transition hover:bg-emerald-100 dark:hover:bg-emerald-900/60">
                <span class="text-base" aria-hidden="true">💬</span>
                <span>WhatsApp'tan Hızlı Bilgi Al</span>
            </a>
        </div>
    </div>
</aside>
