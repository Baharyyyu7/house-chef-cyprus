@props(['recipe'])

@php
    $affiliateService = app(\App\Services\Affiliate\RecipeAffiliateMatchingService::class);
    $matchedEquipment = $affiliateService->forRecipe($recipe, 2);
@endphp

@if($matchedEquipment->isNotEmpty())
    <section id="recommended-equipment" class="hc-v3-card mt-8 hc-scroll-target p-5 sm:p-6 border border-orange-200/60 dark:border-orange-500/20 bg-gradient-to-br from-orange-50/40 via-white to-amber-50/20 dark:from-stone-900 dark:via-stone-900 dark:to-stone-850">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-orange-100 dark:border-stone-800 pb-3">
            <div>
                <p class="hc-overline flex items-center gap-1.5 font-bold text-orange-600 dark:text-orange-400">
                    <span aria-hidden="true">🍳</span> Şefin Tavsiyesi · Ekipman
                </p>
                <h2 class="hc-heading-3 mt-1">Bu Tarifi Kusursuz Pişirmek İçin Önerilen Ekipmanlar</h2>
            </div>
            <span class="hc-v3-badge hc-v3-badge--success text-xs">Denenmiş ve Önerilen</span>
        </div>

        <p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)">
            Tarifimizin kıvamını, lezzetini ve sunumunu evinizde şef standartlarında yakalamanız için seçtiğimiz mutfak ekipmanları:
        </p>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach($matchedEquipment as $item)
                <article class="flex flex-col justify-between rounded-2xl border border-stone-200/80 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-stone-800 dark:bg-stone-800/80">
                    <div class="flex gap-4">
                        @if($item->image_url)
                            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-stone-150 bg-stone-100 dark:border-stone-700 dark:bg-stone-700">
                                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="h-full w-full object-cover" loading="lazy" width="80" height="80">
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
                        @if($item->price)
                            <div class="text-xs">
                                <span class="text-stone-500 dark:text-stone-400">Tahmini:</span>
                                <strong class="text-stone-900 dark:text-white">{{ number_format((float)$item->price, 0, ',', '.') }} {{ $item->currency }}</strong>
                            </div>
                        @else
                            <div></div>
                        @endif

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
