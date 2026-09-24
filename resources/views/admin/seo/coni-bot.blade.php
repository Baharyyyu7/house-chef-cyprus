<x-layouts.site :title="__('admin_workspaces.coni_bot')">
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header 
            :eyebrow="__('admin_workspaces.search')" 
            :title="__('admin_workspaces.coni_bot')" 
            :back="['url' => route('admin.seo.index'), 'label' => 'SEO Merkezi']">
            <x-slot:actions>
                <a href="#calculator" class="hc-v3-btn hc-v3-btn--secondary">
                    <x-ui.icon name="calculator" :size="16" /> Kelime Hesapla
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        {{-- Özet Kartları --}}
        <dl class="mt-6 grid grid-cols-2 gap-5 border-y py-5 xl:grid-cols-4" style="border-color:var(--hc-border-subtle)">
            <div class="min-w-0">
                <dt class="text-sm text-muted">Aylık Tahmini Gelir (MID)</dt>
                <dd class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">
                    ₺{{ number_format($summary['potential_monthly_try'], 0) }}
                </dd>
                <p class="text-xs text-muted mt-0.5">~€{{ number_format($summary['potential_monthly_eur'], 0) }} / ay</p>
            </div>
            <div class="min-w-0">
                <dt class="text-sm text-muted">Yıllık Gelir Projeksiyonu</dt>
                <dd class="mt-1 text-2xl font-bold text-green-700 dark:text-green-300">
                    ₺{{ number_format($summary['potential_yearly_try'], 0) }}
                </dd>
                <p class="text-xs text-muted mt-0.5">Organik arama tavanı</p>
            </div>
            <div class="min-w-0">
                <dt class="text-sm text-muted">Toplam Arama Hacmi</dt>
                <dd class="mt-1 text-2xl font-bold">
                    {{ number_format($summary['total_volume']) }}
                </dd>
                <p class="text-xs text-muted mt-0.5">Aylık hedef sorgu hacmi</p>
            </div>
            <div class="min-w-0">
                <dt class="text-sm text-muted">İzlenen Ticari Kelime</dt>
                <dd class="mt-1 text-2xl font-bold">
                    {{ $summary['total_keywords'] }}
                </dd>
                <p class="text-xs text-muted mt-0.5">4 dilde izole pazar analizi</p>
            </div>
        </dl>

        {{-- Matematiksel Formül Bilgi Kutusu --}}
        <section class="mt-8 rounded-2xl border p-6 bg-stone-50 dark:bg-stone-900/50" style="border-color:var(--hc-border-subtle)">
            <div class="flex items-start gap-4">
                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300">
                    <x-ui.icon name="calculator" size="20" />
                </div>
                <div>
                    <h2 class="text-base font-bold">ConiSEOBot Matematiksel Değer Formülü</h2>
                    <p class="mt-1 text-sm text-muted leading-relaxed">
                        ConiSEOBot arama hacmini tek başına bir zafer değil, altı değişkenden yalnızca biri olarak kabul eder:
                    </p>
                    <code class="mt-2.5 inline-block rounded-lg bg-white px-3 py-2 text-xs font-mono font-semibold text-stone-800 shadow-sm dark:bg-stone-800 dark:text-stone-200">
                        Beklenen Değer = Arama Hacmi × Hedef CTR × SERP İndirimi × Ürün Uyumu × Dönüşüm Oranı × Dönüşüm Başı Değer
                    </code>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-xs text-muted">
                        <div><strong>CTR:</strong> İlk 3 sıra %11-%28 arası</div>
                        <div><strong>SERP İndirimi:</strong> Local Pack (0.7), AI Overview (0.55)</div>
                        <div><strong>Ürün Uyumu:</strong> Doğrudan (1.0), Yakın (0.6), İçerik (0.25)</div>
                        <div><strong>Dönüşüm Başı Değer:</strong> Özel Şef (₺5.500), Pişir Modu (₺60)</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- İnteraktif Kelime Hesaplayıcı Formu --}}
        <section id="calculator" class="mt-8 rounded-2xl border p-6" style="border-color:var(--hc-border-subtle)">
            <h2 class="text-lg font-bold">Canlı Anahtar Kelime Değerleme Aracı</h2>
            <p class="text-sm text-muted mt-1">Herhangi bir anahtar kelimeyi anında formüle tabi tutarak aylık Low, Mid, High getiri aralığını hesaplayın.</p>

            <form method="post" action="{{ route('admin.seo.coni-bot.evaluate') }}" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                @csrf
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-muted uppercase tracking-wider mb-1" for="kw_input">Anahtar Kelime</label>
                    <input id="kw_input" name="keyword" type="text" required value="{{ old('keyword', $customKeyword) }}" placeholder="Örn: kıbrıs villa aşçısı, kolokas tarifi..." class="w-full rounded-xl border px-3 py-2 text-sm bg-white dark:bg-stone-800" style="border-color:var(--hc-border-subtle)">
                </div>
                <div>
                    <label class="block text-xs font-bold text-muted uppercase tracking-wider mb-1" for="vol_input">Tahmini Hacim (Opsiyonel)</label>
                    <input id="vol_input" name="volume" type="number" min="0" placeholder="Örn: 450" class="w-full rounded-xl border px-3 py-2 text-sm bg-white dark:bg-stone-800" style="border-color:var(--hc-border-subtle)">
                </div>
                <div>
                    <label class="block text-xs font-bold text-muted uppercase tracking-wider mb-1" for="locale_select">Hedef Dil</label>
                    <select id="locale_select" name="locale" class="w-full rounded-xl border px-3 py-2 text-sm bg-white dark:bg-stone-800" style="border-color:var(--hc-border-subtle)">
                        <option value="tr">TR (Türkçe)</option>
                        <option value="en">EN (İngilizce)</option>
                        <option value="ru">RU (Rusça)</option>
                        <option value="de">DE (Almanca)</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full hc-v3-btn hc-v3-btn--primary py-2 text-sm">
                        <x-ui.icon name="calculator" :size="16" /> Değeri Hesapla
                    </button>
                </div>
            </form>

            @if($customResult)
                <div class="mt-6 rounded-xl border p-5 bg-orange-50/50 dark:bg-orange-950/20 border-orange-200 dark:border-orange-900/50">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-100 dark:bg-orange-900/50 px-2.5 py-0.5 text-xs font-bold text-orange-800 dark:text-orange-200">
                                {{ $customResult['intent'] }}
                            </span>
                            <span class="ml-2 text-xs text-muted font-bold">{{ strtoupper($customResult['locale']) }} Pazarı</span>
                            <h3 class="mt-1 text-lg font-bold text-stone-900 dark:text-stone-100">"{{ $customResult['keyword'] }}"</h3>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-muted">Tahmini Aylık Değer (MID)</span>
                            <p class="text-xl font-black text-green-600 dark:text-green-400">₺{{ number_format($customResult['expected_value_monthly_try']['mid'], 2) }}</p>
                            <span class="text-xs text-muted">Aralık: ₺{{ number_format($customResult['expected_value_monthly_try']['low'], 0) }} - ₺{{ number_format($customResult['expected_value_monthly_try']['high'], 0) }}</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-orange-200/50 dark:border-orange-900/50 grid gap-3 sm:grid-cols-3 text-xs">
                        <div><strong>Ürün Uyumu:</strong> {{ $customResult['product_fit']['label'] }}</div>
                        <div><strong>Aylık Tıklama (MID):</strong> {{ $customResult['expected_clicks_monthly']['mid'] }} tıklama</div>
                        <div><strong>Aylık Dönüşüm (MID):</strong> {{ $customResult['expected_conversions_monthly']['mid'] }} işlem</div>
                    </div>
                    <p class="mt-3 text-xs text-stone-700 dark:text-stone-300 font-semibold">
                        🎯 Tavsiye: {{ $customResult['recommendation'] }}
                    </p>
                </div>
            @endif
        </section>

        {{-- Kıbrıs Ticari Portföy Tablosu --}}
        <section class="mt-10">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-bold">House Chef Cyprus Ticari Anahtar Kelime Havuzu</h2>
                    <p class="text-sm text-muted">Formül ile sıralanmış en yüksek finansal getiri potansiyeline sahip hedef kelimeler.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border" style="border-color:var(--hc-border-subtle)">
                <table class="w-full text-left text-sm divide-y" style="border-color:var(--hc-border-subtle)">
                    <thead class="bg-stone-50 dark:bg-stone-900/50 text-xs font-bold text-muted uppercase">
                        <tr>
                            <th class="px-4 py-3">Anahtar Kelime</th>
                            <th class="px-3 py-3">Dil</th>
                            <th class="px-3 py-3">Niyet</th>
                            <th class="px-3 py-3 text-right">Hacim</th>
                            <th class="px-3 py-3 text-center">Uyum</th>
                            <th class="px-4 py-3 text-right">Aralık (Düşük - Yüksek)</th>
                            <th class="px-4 py-3 text-right">Beklenen Değer (MID)</th>
                            <th class="px-3 py-3 text-center">Skor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y bg-white dark:bg-stone-900" style="border-color:var(--hc-border-subtle)">
                        @foreach($summary['all_items'] as $item)
                            <tr class="hover:bg-stone-50/50 dark:hover:bg-stone-800/50 transition">
                                <td class="px-4 py-3 font-semibold">
                                    {{ $item['keyword'] }}
                                    <p class="text-[11px] text-muted font-normal mt-0.5">{{ $item['recommendation'] }}</p>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold bg-stone-100 text-stone-700 dark:bg-stone-800 dark:text-stone-300">
                                        {{ strtoupper($item['locale']) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-block rounded px-2 py-0.5 text-xs font-semibold
                                        @if($item['intent'] === 'TRANSACTIONAL') bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300
                                        @elseif($item['intent'] === 'COMMERCIAL') bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300
                                        @else bg-stone-100 text-stone-700 dark:bg-stone-800 dark:text-stone-300
                                        @endif">
                                        {{ $item['intent'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right font-mono">{{ number_format($item['volume']) }}</td>
                                <td class="px-3 py-3 text-center font-mono font-bold">{{ $item['product_fit']['score'] }}</td>
                                <td class="px-4 py-3 text-right text-xs text-muted font-mono">
                                    ₺{{ number_format($item['expected_value_monthly_try']['low'], 0) }} - ₺{{ number_format($item['expected_value_monthly_try']['high'], 0) }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold font-mono text-green-600 dark:text-green-400">
                                    ₺{{ number_format($item['expected_value_monthly_try']['mid'], 0) }}
                                    <span class="block text-[10px] text-muted font-normal">~€{{ number_format($item['expected_value_monthly_eur']['mid'], 0) }}</span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex items-center justify-center h-6 w-8 rounded-full text-xs font-bold
                                        @if($item['priority_score'] >= 70) bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300
                                        @elseif($item['priority_score'] >= 45) bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300
                                        @else bg-stone-100 text-stone-700 dark:bg-stone-800 dark:text-stone-300
                                        @endif">
                                        {{ $item['priority_score'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</x-layouts.site>
