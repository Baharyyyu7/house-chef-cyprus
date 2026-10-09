<x-layouts.site
    :title="$seoTitle"
    :meta-description="$seoDescription"
    :canonical-url="route('weekly-menu.index')">
<main id="main-content" class="py-8 sm:py-12 bg-stone-50/50 dark:bg-stone-950">
    <div class="hc-container max-w-5xl">
        {{-- Başlık & Aksiyon Alanı --}}
        <header class="text-center md:text-left md:flex md:items-end md:justify-between border-b border-stone-200/80 dark:border-stone-800 pb-8 gap-6">
            <div>
                <span class="hc-v3-badge hc-v3-badge--success text-xs font-bold uppercase tracking-wider">
                    🗓️ {{ $weekDateRange }}
                </span>
                <h1 class="hc-display-xl mt-3 font-extrabold tracking-tight text-stone-900 dark:text-white">
                    Haftalık Akdeniz Yemek Menüsü
                </h1>
                <p class="hc-body-lg mt-2 text-stone-600 dark:text-stone-300 max-w-2xl">
                    "Bugün ne pişirsem?" derdine son. Herkese açık tariflerden oluşan 7 günlük akşam yemeği planı ve hazır toplu pazar/market alışveriş listeniz.
                </p>
            </div>

            <div class="mt-6 md:mt-0 flex flex-wrap gap-3 shrink-0 justify-center">
                <a href="https://api.whatsapp.com/send?text={{ urlencode('Bu haftanın yemek menüsü ve alışveriş listesi House Chef\'te hazır: ' . route('weekly-menu.index')) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="hc-v3-btn hc-v3-btn--primary inline-flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.311.045-.698.055-1.146-.088-.3-.095-.694-.23-1.196-.445-2.107-.905-3.486-3.039-3.59-3.18-.105-.14-1.026-1.365-1.026-2.604 0-1.238.647-1.849.877-2.099.231-.25.503-.312.67-.312.167 0 .334.002.48.01.156.008.364-.06.57.433.212.508.723 1.764.787 1.892.063.13.105.281.02.45-.084.168-.125.271-.25.417-.126.147-.264.327-.377.439-.126.125-.257.262-.11.514.146.251.651 1.074 1.397 1.738.96.855 1.769 1.121 2.022 1.246.252.126.4-.041.547-.21.147-.168.629-.732.796-.983.167-.251.334-.209.564-.125.231.084 1.464.69 1.716.816.251.125.419.188.48.293.063.104.063.606-.081 1.011z"/></svg>
                    <span>WhatsApp'ta Paylaş</span>
                </a>
                <button type="button"
                        onclick="window.print()"
                        class="hc-v3-btn hc-v3-btn--outline inline-flex items-center gap-2">
                    <svg class="w-4 h-4 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Yazdır / PDF</span>
                </button>
            </div>
        </header>

        {{-- Sekmeli Görünüm (Alpine.js) --}}
        <div x-data="{ activeTab: 'plan' }" class="mt-8">
            <div class="flex border-b border-stone-200 dark:border-stone-800 gap-2">
                <button type="button"
                        @click="activeTab = 'plan'"
                        :class="activeTab === 'plan' ? 'border-orange-600 text-orange-600 dark:border-orange-400 dark:text-orange-400 font-bold' : 'border-transparent text-stone-500 hover:text-stone-800 dark:hover:text-stone-300'"
                        class="py-3 px-5 border-b-2 text-sm font-semibold transition-colors flex items-center gap-2">
                    <span>📅 7 Günlük Menü</span>
                </button>
                <button type="button"
                        @click="activeTab = 'shopping'"
                        :class="activeTab === 'shopping' ? 'border-orange-600 text-orange-600 dark:border-orange-400 dark:text-orange-400 font-bold' : 'border-transparent text-stone-500 hover:text-stone-800 dark:hover:text-stone-300'"
                        class="py-3 px-5 border-b-2 text-sm font-semibold transition-colors flex items-center gap-2">
                    <span>🛒 Toplu Alışveriş Listesi</span>
                    <span class="rounded-full bg-orange-100 dark:bg-orange-950/60 text-orange-700 dark:text-orange-300 px-2 py-0.5 text-xs font-bold">
                        {{ $shoppingList->flatten(1)->count() }}
                    </span>
                </button>
            </div>

            {{-- 1. SEKME: 7 Günlük Menü --}}
            <div x-show="activeTab === 'plan'" class="mt-8 space-y-6">
                @foreach($weeklyPlan as $dayItem)
                    <article class="hc-v3-card p-6 border border-stone-200/80 dark:border-stone-800 bg-white dark:bg-stone-900">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-stone-100 dark:border-stone-800 pb-3">
                            <h2 class="hc-heading-3 text-stone-900 dark:text-white flex items-center gap-2">
                                <span class="text-orange-600 dark:text-orange-400 font-bold">●</span>
                                {{ $dayItem['day'] }}
                            </h2>
                            <span class="hc-v3-badge text-xs">{{ $dayItem['tag'] }}</span>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($dayItem['menu'] as $course => $recipe)
                                @if($recipe)
                                    <a href="{{ route('recipes.show', $recipe->slug) }}"
                                       class="group flex flex-col justify-between rounded-xl border border-stone-150 p-4 transition hover:border-orange-300 hover:shadow-md dark:border-stone-800 dark:hover:border-orange-700 bg-stone-50/50 dark:bg-stone-850">
                                        <div>
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-orange-600 dark:text-orange-400">
                                                @if($course === 'corba') 🥣 ÇORBA
                                                @elseif($course === 'ana_yemek') 🥘 ANA YEMEK
                                                @elseif($course === 'yan_yemek') 🥗 YAN LEZZET & SALATA
                                                @elseif($course === 'tatli') 🍰 TATLI
                                                @endif
                                            </span>
                                            <h3 class="mt-1 text-sm font-bold text-stone-900 group-hover:text-orange-600 transition dark:text-white dark:group-hover:text-orange-400 line-clamp-1">
                                                {{ $recipe->title }}
                                            </h3>
                                            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400 line-clamp-2">
                                                {{ $recipe->excerpt }}
                                            </p>
                                        </div>
                                        <div class="mt-3 flex items-center justify-between text-xs font-semibold text-stone-400">
                                            <span>⏱ {{ $recipe->prep_minutes + $recipe->cook_minutes }} dk</span>
                                            <span class="text-orange-600 dark:text-orange-400 group-hover:underline">Tarife Git →</span>
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </article>
                    @if($loop->iteration === 3)
                        <x-ad-slot slot-name="in_feed" class="my-6" />
                    @endif
                @endforeach
            </div>

            {{-- 2. SEKME: Alışveriş Listesi --}}
            <div x-show="activeTab === 'shopping'" x-cloak class="mt-8">
                <div class="hc-v3-card p-6 border border-stone-200/80 dark:border-stone-800 bg-white dark:bg-stone-900">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-100 dark:border-stone-800 pb-4">
                        <div>
                            <h2 class="hc-heading-3 text-stone-900 dark:text-white">
                                7 Günlük Toplu Malzeme & Alışveriş Listesi
                            </h2>
                            <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">
                                Markette gezerken aldığınız malzemelerin yanındaki kutucuğa tıklayarak üstünü çizebilirsiniz.
                            </p>
                        </div>
                        <button type="button"
                                onclick="window.print()"
                                class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">
                            Listeyi Yazdır
                        </button>
                    </div>

                    <div class="mt-6 grid gap-6 md:grid-cols-2">
                        @foreach($shoppingList as $category => $items)
                            <div class="rounded-xl border border-stone-150 p-4 dark:border-stone-800 bg-stone-50/50 dark:bg-stone-850">
                                <h3 class="text-sm font-extrabold uppercase tracking-wider text-orange-600 dark:text-orange-400 border-b border-stone-200/60 dark:border-stone-700/60 pb-2">
                                    {{ $category }} ({{ count($items) }})
                                </h3>
                                <ul class="mt-3 space-y-2 text-sm">
                                    @foreach($items as $item)
                                        <li x-data="{ checked: false }" class="flex items-center gap-2.5">
                                            <input type="checkbox"
                                                   @click="checked = !checked"
                                                   class="h-4 w-4 rounded border-stone-300 text-orange-600 focus:ring-orange-500 dark:border-stone-700 dark:bg-stone-900">
                                            <span :class="checked ? 'line-through text-stone-400 dark:text-stone-600' : 'text-stone-800 dark:text-stone-200'">
                                                {{ $item['name'] }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-12">
                <x-ad-slot slot-name="multiplex" class="my-8" />
            </div>
        </div>
    </div>
</main>
</x-layouts.site>
