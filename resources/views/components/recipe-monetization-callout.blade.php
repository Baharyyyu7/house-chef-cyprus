@props(['recipe'])

@if(turkce_sayfa())
@php
    $isCypriot = ($recipe->cuisine?->slug === 'kibris-mutfagi');
@endphp

<div class="mt-8 space-y-4">
    @if($isCypriot)
        <aside aria-label="Kıbrıs Mutfağı E-Kitabı" class="rounded-2xl border border-amber-300/80 bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-amber-500/15 p-5 sm:p-6 dark:border-amber-500/30 dark:from-amber-950/40 dark:via-stone-900 dark:to-amber-950/20">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-2xl text-white shadow-sm">
                        📖
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-2.5 py-0.5 rounded-full bg-amber-600/15 text-amber-700 dark:text-amber-300 font-extrabold text-[11px] uppercase tracking-wider">
                                Özel E-Kitap
                            </span>
                            <span class="text-xs font-bold text-stone-500 dark:text-stone-400">33 Sayfa · PDF</span>
                        </div>
                        <h3 class="mt-1 text-base sm:text-lg font-bold text-stone-900 dark:text-white">
                            Kıbrıs Mutfağı Seçmeler — Dijital Yemek Kitabı
                        </h3>
                        <p class="mt-1 text-xs sm:text-sm text-stone-600 dark:text-stone-300 max-w-xl">
                            30 otantik ada tarifini, adım adım anlatımı ve mutfak sırlarıyla cihazınıza indirin; internet olmadan mutfakta rahatça kullanın.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-amber-200 dark:border-amber-900/50">
                    <div class="text-left sm:text-right">
                        <span class="text-[11px] text-stone-500 dark:text-stone-400 block">Fiyat</span>
                        <strong class="text-lg font-black text-stone-900 dark:text-white">₺149</strong>
                    </div>
                    <a href="{{ route('shop.index') }}"
                       class="hc-v3-btn hc-v3-btn--primary text-xs sm:text-sm px-4 py-2.5 inline-flex items-center gap-1.5 shadow-sm">
                        <span>E-Kitabı İncele</span>
                        <span aria-hidden="true">➔</span>
                    </a>
                </div>
            </div>
        </aside>
    @else
        <aside aria-label="Haftalık Yemek Menüsü" class="rounded-2xl border border-stone-200/90 bg-stone-100/60 p-5 sm:p-6 dark:border-stone-800 dark:bg-stone-900/70">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-600 text-xl text-white shadow-sm">
                        🗓️
                    </div>
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-orange-600/10 text-orange-700 dark:text-orange-300 font-extrabold text-[11px] uppercase tracking-wider">
                            Haftalık Planlayıcı
                        </span>
                        <h3 class="mt-1 text-base sm:text-lg font-bold text-stone-900 dark:text-white">
                            Bu Hafta Ne Pişirsem? — 7 Günlük Akdeniz Menüsü
                        </h3>
                        <p class="mt-1 text-xs sm:text-sm text-stone-600 dark:text-stone-300 max-w-xl">
                            Pazartesiden pazara dengeli akşam yemeği planı ve tek tıkla hazır toplu market/pazar alışveriş listeniz yayında.
                        </p>
                    </div>
                </div>

                <div class="shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-stone-200 dark:border-stone-800">
                    <a href="{{ route('weekly-menu.index') }}"
                       class="hc-v3-btn hc-v3-btn--outline text-xs sm:text-sm px-4 py-2.5 inline-flex items-center gap-1.5">
                        <span>Haftalık Menüyü Gör</span>
                        <span aria-hidden="true">➔</span>
                    </a>
                </div>
            </div>
        </aside>
    @endif
</div>
@endif
