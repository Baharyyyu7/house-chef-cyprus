@props(['recipe' => null, 'post' => null, 'products' => null, 'title' => null, 'heading' => 'h2'])

@php
    $affiliateService = app(\App\Services\Affiliate\RecipeAffiliateMatchingService::class);
    if ($products !== null) {
        $matchedEquipment = $products;
    } elseif ($post !== null) {
        $matchedEquipment = $affiliateService->forBlog($post, 2);
    } elseif ($recipe !== null) {
        $matchedEquipment = $affiliateService->forRecipe($recipe, 2);
    } else {
        $matchedEquipment = collect();
    }

    $isBlog = $post !== null;
    $boxTitle = $title ?? ($isBlog ? 'Bu Rehberde Önerilen Profesyonel Mutfak Ekipmanları' : 'Bu Tarifi Kusursuz Pişirmek İçin Önerilen Ekipmanlar');
    // Stok fotoğraf ürünün kendi fotoğrafı değildir. "Tefal Clipso 6L"
    // başlığının yanına rastgele bir tencere görseli koymak, okura o ürünü
    // gösteriyormuş gibi yapmaktır. 20 Eylül 2026'da ölçüldü: 35 ürünün
    // 35'inin görseli Unsplash'ten geliyordu.
    $stokSaglayicilar = ['images.unsplash.com', 'unsplash.com', 'images.pexels.com', 'pexels.com', 'cdn.pixabay.com', 'pixabay.com'];
    $urunFotografi = static function (?string $adres) use ($stokSaglayicilar): ?string {
        if (! is_string($adres) || $adres === '') {
            return null;
        }

        $host = strtolower((string) parse_url($adres, PHP_URL_HOST));

        return in_array($host, $stokSaglayicilar, true) ? null : $adres;
    };

    $desc = $isBlog
        ? 'Rehberimizdeki teknikleri ve ada tariflerini evinizde şef standartlarında uygulamanız için tavsiye ettiğimiz mutfak gereçleri:'
        : 'Tarifimizin kıvamını, lezzetini ve sunumunu evinizde şef standartlarında yakalamanız için seçtiğimiz mutfak ekipmanları:';
@endphp

@if($matchedEquipment->isNotEmpty())
    <section id="recommended-equipment" class="hc-v3-card mt-8 hc-scroll-target p-5 sm:p-6 border border-orange-200/60 dark:border-orange-500/20 bg-gradient-to-br from-orange-50/40 via-white to-amber-50/20 dark:from-stone-900 dark:via-stone-900 dark:to-stone-850">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-orange-100 dark:border-stone-800 pb-3">
            <div>
                <p class="hc-overline flex items-center gap-1.5 font-bold text-orange-600 dark:text-orange-400">
                    <span aria-hidden="true">🍳</span> Şefin Tavsiyesi · Ekipman
                </p>
                <{{ $heading }} class="hc-heading-3 mt-1">{{ $boxTitle }}</{{ $heading }}>
            </div>
            {{-- "Denenmiş" demiyoruz: bu ürünleri test etmedik. Etiket ne
                 olduğunu söyler, yapmadığımız bir iddiayı taşımaz. --}}
            <span class="hc-v3-badge text-xs">Gelir ortaklığı bağlantısı</span>
        </div>

        <p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)">
            {{ $desc }}
        </p>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach($matchedEquipment as $item)
                <article class="flex flex-col justify-between rounded-2xl border border-stone-200/80 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-stone-800 dark:bg-stone-800/80">
                    <div class="flex gap-4">
                        @if($urunFotografi($item->image_url))
                            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-stone-150 bg-stone-100 dark:border-stone-700 dark:bg-stone-700">
                                <img src="{{ $urunFotografi($item->image_url) }}" alt="{{ $item->title }}" class="h-full w-full object-cover" loading="lazy" width="80" height="80">
                            </div>
                        @else
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-2xl text-orange-700 dark:bg-orange-950/50 dark:text-orange-300">
                                🍳
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <span class="text-xs font-bold uppercase tracking-wider text-orange-600 dark:text-orange-400">
                                {{ strtoupper($item->provider) }}
                            </span>
                            <h3 class="mt-0.5 text-base font-bold text-stone-900 line-clamp-1 dark:text-white">
                                {{ $item->title }}
                            </h3>
                            <p class="mt-1 text-xs text-stone-600 line-clamp-2 dark:text-stone-300">
                                {{ $item->description }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-stone-100 pt-3 dark:border-stone-700/60">
                        {{-- Fiyat gösterilmiyor: doğrulanmış bir fiyat akışımız
                             yok ve tohumlanan değerler eskiyor. Yanlış fiyat
                             yazmaktansa okuru satıcının kendi sayfasına
                             göndermek dürüst olan. --}}
                        <span class="text-[11px] text-stone-500 dark:text-stone-400">Güncel fiyat satıcıda</span>

                        <a href="{{ route('affiliate.redirect', ['slug' => $item->slug]) }}"
                           target="_blank"
                           rel="sponsored nofollow noopener"
                           class="hc-v3-btn hc-v3-btn--primary hc-v3-btn--sm whitespace-nowrap shadow-sm">
                            <span>{{ ucfirst($item->provider) }}'da İncele</span>
                            <span aria-hidden="true">↗</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <p class="mt-4 text-[11px] leading-relaxed text-stone-400 dark:text-stone-500">
            * Bilgilendirme: Bu kutuda yer alan ürün yönlendirmeleri gelir ortaklığı (affiliate) kapsamındadır. Beğendiğiniz ürünleri satın almanız durumunda siteye ek maliyet yansımaksızın küçük bir komisyon sağlanır.
        </p>
    </section>
@endif
