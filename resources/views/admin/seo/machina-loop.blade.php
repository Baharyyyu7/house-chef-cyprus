<x-layouts.site :title="__('admin_workspaces.machina_loop')">
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header 
            :eyebrow="__('admin_workspaces.search')" 
            :title="__('admin_workspaces.machina_loop')" 
            :back="['url' => route('admin.seo.index'), 'label' => 'SEO Merkezi']">
            <x-slot:actions>
                <a href="#audit-runner" class="hc-v3-btn hc-v3-btn--primary">
                    <x-ui.icon name="search" :size="16" /> Yeni Sayfa Denetle
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        {{-- Araç Bağlantıları Durum Matrisi --}}
        <section class="mt-6 rounded-2xl border p-5 bg-stone-50 dark:bg-stone-900/50" style="border-color:var(--hc-border-subtle)">
            <h2 class="text-xs font-bold uppercase tracking-wider text-muted mb-3">Entegre Araçlar & Güvenlik Kancası (Tool Connections Stack)</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5 text-xs">
                @foreach($toolStatus as $toolKey => $tool)
                    <div class="rounded-xl border p-3 bg-white dark:bg-stone-800" style="border-color:var(--hc-border-subtle)">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-stone-900 dark:text-stone-100">{{ $tool['name'] }}</span>
                            <span class="inline-block h-2 w-2 rounded-full {{ str_contains($tool['status'], 'ACTIVE') || str_contains($tool['status'], 'READY') ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                        </div>
                        <p class="mt-1 text-[11px] text-muted">{{ $tool['mode'] ?? $tool['status'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Haftalık Döngü Kararı ve TEK Değişiklik --}}
        @php($fix = $audit['single_recommended_fix'])
        <section class="mt-8 rounded-2xl border p-6 bg-gradient-to-r from-orange-50/80 to-amber-50/50 dark:from-stone-900 dark:to-stone-900 border-orange-200 dark:border-orange-900/50">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-3xl">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-orange-200 dark:bg-orange-900/80 px-2.5 py-0.5 text-xs font-bold text-orange-900 dark:text-orange-200">
                            HAFTANIN TEK DEĞİŞİKLİĞİ
                        </span>
                        <span class="text-xs text-muted font-bold">Genel Denetim Skoru: {{ $audit['overall_score'] }}/100</span>
                    </div>
                    <h2 class="mt-2 text-xl font-bold text-stone-900 dark:text-stone-100">
                        {{ $fix['summary'] }}
                    </h2>
                    <p class="mt-2 text-sm text-stone-700 dark:text-stone-300 leading-relaxed">
                        <strong>Gerekçe:</strong> {{ $fix['rationale'] }}
                    </p>
                    <p class="mt-1 text-xs text-muted">
                        <strong>Hedef Sayfa:</strong> <a href="{{ $audit['url'] }}" target="_blank" class="underline text-orange-600 dark:text-orange-400">{{ $audit['url'] }}</a> (Kelime: <em>{{ $audit['primary_keyword'] }}</em>)
                    </p>
                </div>
                <div class="text-right flex flex-col items-end gap-2">
                    <form method="post" action="{{ route('admin.seo.machina-loop.approve') }}">
                        @csrf
                        <input type="hidden" name="action_type" value="{{ $fix['action_type'] }}">
                        <input type="hidden" name="summary" value="{{ $fix['summary'] }}">
                        <button type="submit" class="hc-v3-btn hc-v3-btn--primary px-5 py-2 text-sm shadow">
                            <x-ui.icon name="check" :size="16" /> İnsan Onayı Ver (Approve)
                        </button>
                    </form>
                    <span class="text-[11px] text-muted">
                        Güvenlik Kuralı: İnsan onayı olmadan canlıya alınmaz.
                    </span>
                </div>
            </div>
        </section>

        {{-- Canlı 4-Geçişli Denetim Formu --}}
        <section id="audit-runner" class="mt-8 rounded-2xl border p-6" style="border-color:var(--hc-border-subtle)">
            <h2 class="text-lg font-bold">Canlı 4-Geçişli Sayfa Denetimi (Take The Page Apart)</h2>
            <p class="text-sm text-muted mt-1">İncelemek istediğiniz herhangi bir sayfa URL'sini ve birincil arama sorgusunu girerek 4 geçişi hemen çalıştırın.</p>

            <form method="post" action="{{ route('admin.seo.machina-loop.audit') }}" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                @csrf
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-muted uppercase tracking-wider mb-1" for="url_input">Sayfa URL'si</label>
                    <input id="url_input" name="url" type="url" required value="{{ old('url', $targetUrl) }}" class="w-full rounded-xl border px-3 py-2 text-sm bg-white dark:bg-stone-800" style="border-color:var(--hc-border-subtle)">
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-muted uppercase tracking-wider mb-1" for="query_input">Birincil Arama Sorgusu</label>
                    <input id="query_input" name="query" type="text" required value="{{ old('query', $primaryKeyword) }}" class="w-full rounded-xl border px-3 py-2 text-sm bg-white dark:bg-stone-800" style="border-color:var(--hc-border-subtle)">
                </div>
                <div>
                    <button type="submit" class="w-full hc-v3-btn hc-v3-btn--primary py-2 text-sm">
                        <x-ui.icon name="search" :size="16" /> Denetimi Başlat
                    </button>
                </div>
            </form>
        </section>

        {{-- 4 Geçiş Detay Kartları --}}
        <div class="mt-8 grid gap-6 md:grid-cols-2">
            {{-- Geçiş 1: Erişim ve Hız --}}
            @php($p1 = $audit['passes']['pass_1_access_speed'])
            <div class="rounded-2xl border p-6 bg-white dark:bg-stone-900" style="border-color:var(--hc-border-subtle)">
                <div class="flex items-center justify-between border-b pb-4 mb-4" style="border-color:var(--hc-border-subtle)">
                    <div class="flex items-center gap-2">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300 font-bold text-xs">1</span>
                        <h3 class="font-bold text-base">{{ $p1['name'] }}</h3>
                    </div>
                    <span class="rounded-full bg-green-100 dark:bg-green-950 px-2.5 py-0.5 text-xs font-bold text-green-800 dark:text-green-300">
                        {{ $p1['score'] }}/100
                    </span>
                </div>
                <ul class="space-y-2 text-xs text-muted">
                    <li class="flex justify-between"><span>HTTP Durumu:</span> <strong class="text-stone-800 dark:text-stone-200">{{ $p1['checks']['http_status'] }} OK</strong></li>
                    <li class="flex justify-between"><span>İndekslenebilirlik:</span> <strong class="text-stone-800 dark:text-stone-200">{{ $p1['checks']['indexable'] ? 'Uygun' : 'Hayır' }}</strong></li>
                    <li class="flex justify-between"><span>Kelime Sayısı:</span> <strong class="text-stone-800 dark:text-stone-200">{{ $p1['checks']['content_word_count'] }} kelime</strong></li>
                    <li class="flex justify-between"><span>LCP (Largest Contentful Paint):</span> <strong class="text-green-600 dark:text-green-400 font-mono">{{ $p1['checks']['core_web_vitals']['lcp_seconds'] }}s</strong> (Hedef {{ $p1['checks']['core_web_vitals']['lcp_target'] }})</li>
                    <li class="flex justify-between"><span>CLS (Cumulative Layout Shift):</span> <strong class="text-green-600 dark:text-green-400 font-mono">{{ $p1['checks']['core_web_vitals']['cls'] }}</strong> (Hedef {{ $p1['checks']['core_web_vitals']['cls_target'] }})</li>
                </ul>
                <p class="mt-4 pt-3 border-t text-xs text-stone-700 dark:text-stone-300 font-semibold" style="border-color:var(--hc-border-subtle)">
                    ✓ {{ $p1['finding'] }}
                </p>
            </div>

            {{-- Geçiş 2: Rekabet Analizi --}}
            @php($p2 = $audit['passes']['pass_2_competition'])
            <div class="rounded-2xl border p-6 bg-white dark:bg-stone-900" style="border-color:var(--hc-border-subtle)">
                <div class="flex items-center justify-between border-b pb-4 mb-4" style="border-color:var(--hc-border-subtle)">
                    <div class="flex items-center gap-2">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300 font-bold text-xs">2</span>
                        <h3 class="font-bold text-base">{{ $p2['name'] }}</h3>
                    </div>
                    <span class="rounded-full bg-green-100 dark:bg-green-950 px-2.5 py-0.5 text-xs font-bold text-green-800 dark:text-green-300">
                        {{ $p2['score'] }}/100
                    </span>
                </div>
                <p class="text-xs text-muted mb-3">{{ $p2['top_competitors_analyzed'] }} canlı rakip tam metin olarak okundu. Tespit edilen içerik boşlukları:</p>
                <ul class="space-y-2.5 text-xs">
                    @foreach($p2['content_gaps'] as $gap)
                        <li class="rounded-lg border p-2.5 bg-stone-50 dark:bg-stone-800/50" style="border-color:var(--hc-border-subtle)">
                            <p class="font-medium text-stone-800 dark:text-stone-200">{{ $gap['gap'] }}</p>
                            <a href="{{ $gap['source_url'] }}" target="_blank" class="mt-1 block text-[11px] text-orange-600 dark:text-orange-400 underline truncate">
                                Kaynak: {{ $gap['source_url'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4 pt-3 border-t text-xs text-stone-700 dark:text-stone-300" style="border-color:var(--hc-border-subtle)">
                    <strong>Bizim Üstünlüğümüz:</strong> {{ $p2['our_advantage'] }}
                </p>
            </div>

            {{-- Geçiş 3: Yapay Zeka Motorları (AEO) --}}
            @php($p3 = $audit['passes']['pass_3_answer_engines'])
            <div class="rounded-2xl border p-6 bg-white dark:bg-stone-900" style="border-color:var(--hc-border-subtle)">
                <div class="flex items-center justify-between border-b pb-4 mb-4" style="border-color:var(--hc-border-subtle)">
                    <div class="flex items-center gap-2">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300 font-bold text-xs">3</span>
                        <h3 class="font-bold text-base">{{ $p3['name'] }}</h3>
                    </div>
                    <span class="rounded-full bg-green-100 dark:bg-green-950 px-2.5 py-0.5 text-xs font-bold text-green-800 dark:text-green-300">
                        {{ $p3['score'] }}/100
                    </span>
                </div>
                <ul class="space-y-2.5 text-xs">
                    <li class="flex items-start gap-2">
                        <span class="text-green-500 font-bold">✓</span>
                        <div>
                            <strong>İlk Cümle Doğrudan Yanıt Kuralı:</strong>
                            <p class="text-muted">{{ $p3['checks']['first_sentence_direct_answer']['details'] }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-green-500 font-bold">✓</span>
                        <div>
                            <strong>Soru Formatında H2/H3 Başlıkları:</strong>
                            <p class="text-muted">{{ $p3['checks']['question_style_headings']['details'] }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-green-500 font-bold">✓</span>
                        <div>
                            <strong>Doğrulanmış Şema:</strong>
                            <p class="text-muted">{{ $p3['checks']['structured_data_recipe_faq']['details'] }}</p>
                        </div>
                    </li>
                </ul>
                <p class="mt-4 pt-3 border-t text-xs text-stone-700 dark:text-stone-300 font-semibold" style="border-color:var(--hc-border-subtle)">
                    {{ $p3['ai_readiness'] }}
                </p>
            </div>

            {{-- Geçiş 4: Dönüşüm Yolu (Rule Zero) --}}
            @php($p4 = $audit['passes']['pass_4_conversion_path'])
            <div class="rounded-2xl border p-6 bg-white dark:bg-stone-900" style="border-color:var(--hc-border-subtle)">
                <div class="flex items-center justify-between border-b pb-4 mb-4" style="border-color:var(--hc-border-subtle)">
                    <div class="flex items-center gap-2">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300 font-bold text-xs">4</span>
                        <h3 class="font-bold text-base">{{ $p4['name'] }}</h3>
                    </div>
                    <span class="rounded-full bg-green-100 dark:bg-green-950 px-2.5 py-0.5 text-xs font-bold text-green-800 dark:text-green-300">
                        {{ $p4['score'] }}/100
                    </span>
                </div>
                <div class="mb-3 rounded-lg border border-green-200 dark:border-green-900/50 bg-green-50/50 dark:bg-green-950/20 p-2.5 text-xs">
                    <strong>Rule Zero Uyumu:</strong> {{ $p4['rule_zero_quote'] }}
                </div>
                <ul class="space-y-2 text-xs text-muted">
                    <li class="flex justify-between"><span>Above-the-fold CTA:</span> <strong class="text-green-600 dark:text-green-400">Mevcut (Form + WhatsApp)</strong></li>
                    @foreach($p4['named_events'] as $evt => $val)
                        <li class="flex justify-between">
                            <span>{{ $evt }}:</span>
                            <strong class="font-mono text-stone-800 dark:text-stone-200">{{ $val['count'] }}</strong>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 pt-3 border-t text-xs text-stone-700 dark:text-stone-300" style="border-color:var(--hc-border-subtle)">
                    <strong>İç Bağlantı Önerisi:</strong> {{ $p4['internal_link_opportunity']['source_page'] }} ➔ "{{ $p4['internal_link_opportunity']['anchor_text'] }}"
                </div>
            </div>
        </div>

        {{-- Dosya İçerikleri (Brief, State, Log) --}}
        <section class="mt-10 rounded-2xl border p-6" style="border-color:var(--hc-border-subtle)">
            <h2 class="text-lg font-bold mb-4">Ajan Durum Dosyaları (.agents/seo-aeo/)</h2>
            <div class="space-y-4">
                <details class="rounded-xl border p-4 bg-stone-50 dark:bg-stone-900/50" style="border-color:var(--hc-border-subtle)">
                    <summary class="cursor-pointer font-bold text-sm">state.md (Aktif Sayfa Havuzu & Baz Çizgiler)</summary>
                    <pre class="mt-3 overflow-x-auto rounded-lg bg-stone-900 p-4 text-xs font-mono text-stone-200 max-h-60">{{ $stateContent }}</pre>
                </details>
                <details class="rounded-xl border p-4 bg-stone-50 dark:bg-stone-900/50" style="border-color:var(--hc-border-subtle)" open>
                    <summary class="cursor-pointer font-bold text-sm">log.md (Haftalık Koşu Geçmişi - Append Only)</summary>
                    <pre class="mt-3 overflow-x-auto rounded-lg bg-stone-900 p-4 text-xs font-mono text-stone-200 max-h-60">{{ $logContent }}</pre>
                </details>
                <details class="rounded-xl border p-4 bg-stone-50 dark:bg-stone-900/50" style="border-color:var(--hc-border-subtle)">
                    <summary class="cursor-pointer font-bold text-sm">brief.md (İş Modeli, Gelir Kanalları, Rule Zero)</summary>
                    <pre class="mt-3 overflow-x-auto rounded-lg bg-stone-900 p-4 text-xs font-mono text-stone-200 max-h-60">{{ $briefContent }}</pre>
                </details>
            </div>
        </section>
    </section>
</x-layouts.site>
