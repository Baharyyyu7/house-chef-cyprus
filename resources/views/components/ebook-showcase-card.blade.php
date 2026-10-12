@props(['class' => 'mt-8'])

@if(turkce_sayfa())
<aside {{ $attributes->merge(['class' => "hc-v3-card {$class} p-6 sm:p-8 border border-amber-300/80 dark:border-amber-500/30 bg-gradient-to-br from-amber-50/70 via-white to-orange-50/50 dark:from-stone-900 dark:via-stone-900 dark:to-stone-850 shadow-md relative overflow-hidden"]) }} aria-label="Dijital Mutfak E-Kitabı">
    <!-- Arka plan süsleme deseni -->
    <div class="pointer-events-none absolute -right-8 -top-8 h-40 w-40 rounded-full bg-amber-400/10 blur-2xl" aria-hidden="true"></div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 relative z-10">
        <div class="flex items-start gap-4 sm:gap-5">
            <div class="flex h-16 w-16 sm:h-20 sm:w-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-600 text-3xl sm:text-4xl text-white shadow-lg shadow-amber-500/20">
                📖
            </div>
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/15 px-3 py-1 text-[11px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300">
                        <span aria-hidden="true">★</span> Dijital Şef Koleksiyonu · PDF
                    </span>
                    <span class="text-xs font-semibold text-stone-500 dark:text-stone-400">
                        33 Sayfa · 30 Doğrulanmış Tarif
                    </span>
                </div>

                <h3 class="text-xl sm:text-2xl font-black tracking-tight text-stone-900 dark:text-white">
                    Kıbrıs Mutfağı Seçmeler E-Kitabı
                </h3>

                <p class="text-xs sm:text-sm leading-relaxed text-stone-700 dark:text-stone-300 max-w-2xl">
                    Hellimden Kleftiko'ya, Şeftali Kebabından Molehiya'ya ada mutfağının tüm incelikleri. Gramaj ölçü cetveli, şef püf noktaları ve internetsiz mutfakta yanınızda duracak yazdırmaya hazır A4 sayfa düzeniyle tek bir dijital kitapta.
                </p>

                <div class="pt-2 flex flex-wrap items-center gap-3 text-xs font-medium text-stone-600 dark:text-stone-300">
                    <span class="inline-flex items-center gap-1">
                        <span class="text-emerald-600 font-bold" aria-hidden="true">✓</span> Adım Adım Tarifler
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="text-emerald-600 font-bold" aria-hidden="true">✓</span> Ev Ölçüleri & Gramaj
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="text-emerald-600 font-bold" aria-hidden="true">✓</span> Anında İndirme
                    </span>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row md:flex-col items-start md:items-end justify-between gap-3 shrink-0 pt-4 md:pt-0 border-t md:border-t-0 border-amber-200/60 dark:border-stone-800">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400 block md:text-right">Tek Seferlik İndirme</span>
                <div class="flex items-baseline gap-1 md:justify-end">
                    <span class="text-2xl sm:text-3xl font-black text-stone-900 dark:text-white">149 ₺</span>
                    <span class="text-xs font-semibold text-stone-500 dark:text-stone-400">(~€4.99)</span>
                </div>
            </div>

            <a href="{{ route('shop.index') }}"
               class="hc-v3-btn hc-v3-btn--primary hc-v3-btn--lg">
                <span>Kitabı İncele & İndir</span>
                <x-ui.icon name="arrow-right" size="16" />
            </a>
        </div>
    </div>
</aside>
@endif
