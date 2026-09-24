<x-layouts.site :title="__('admin_workspaces.links')">
    <section class="hc-admin-page">
        <x-admin.page-header :title="__('admin_workspaces.links')" :eyebrow="__('admin_workspaces.search')" :back="['url' => route('admin.seo.index'), 'label' => 'SEO merkezi']">
            <x-slot:actions>
                <a class="hc-v3-btn hc-v3-btn--outline" href="{{ route('admin.seo.opportunities') }}"><x-ui.icon name="check" size="16" />İnceleme işleri</a>
                <form method="post" action="{{ route('admin.seo.growth.refresh') }}" x-data="{ busy: false }" @submit="busy = true">@csrf<button class="hc-v3-btn hc-v3-btn--primary" :disabled="busy"><x-ui.icon name="refresh" size="16" /><span x-text="busy ? 'Yenileniyor…' : 'Kuyruğu yenile'">Kuyruğu yenile</span></button></form>
            </x-slot:actions>
        </x-admin.page-header>
        <dl class="grid grid-cols-2 gap-5 border-b py-6 xl:grid-cols-4" style="border-color:var(--hc-border-default)">
            <div><dt class="hc-caption">Açık inceleme işi</dt><dd class="mt-1 text-2xl font-bold"><a href="{{ route('admin.seo.opportunities') }}">{{ number_format($growth['open_opportunities'], 0, ',', '.') }}</a></dd></div>
            <div><dt class="hc-caption">Araştırma adayı</dt><dd class="mt-1 text-2xl font-bold">{{ number_format($growth['research_candidates'], 0, ',', '.') }}</dd></div>
            <div><dt class="hc-caption">Editörün gözlemlediği bağlantı</dt><dd class="mt-1 text-2xl font-bold">{{ number_format($growth['observed_links'], 0, ',', '.') }}</dd></div>
            <div><dt class="hc-caption">Son 7 günde doğrulanan bağlantı</dt><dd class="mt-1 text-2xl font-bold">{{ $growth['machine_verified_links'] }}</dd></div>
        </dl>
        @php($metrics = $growth['organic_metrics'])
        <section class="border-b py-6" style="border-color:var(--hc-border-default)">
            <h2>Google arama performansı</h2>
            <p class="hc-caption mt-2">{{ $metrics['note'] }}</p>
            @if(isset($metrics['property']))<p class="hc-caption mt-1">{{ $metrics['property'] }}</p>@endif
            @if($metrics['google_clicks'] !== null)
                <dl class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div><dt>Tıklama</dt><dd class="text-2xl font-bold">{{ $metrics['google_clicks'] }}</dd></div>
                    <div><dt>Gösterim</dt><dd class="text-2xl font-bold">{{ $metrics['google_impressions'] }}</dd></div>
                    <div><dt>Tıklama oranı</dt><dd class="text-2xl font-bold">%{{ number_format($metrics['ctr'], 1, ',', '.') }}</dd></div>
                    <div><dt>Ortalama konum</dt><dd class="text-2xl font-bold">{{ number_format($metrics['average_position'], 1, ',', '.') }}</dd></div>
                </dl>
                <p class="hc-caption mt-3">{{ $metrics['query_sample_count'] }} sorguluk örneklem · {{ $metrics['status'] === 'stale' ? 'Ölçüm güncellenmeli' : 'Dönem ölçümü' }}</p>
            @endif
        </section>
        <section class="border-b py-6" style="border-color:var(--hc-border-default)">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2>7 Dilde Otonom Büyüme &amp; GEO (Üretken AI) Hazır Bulunuşluk</h2>
                    <p class="hc-caption mt-1">Perplexity, ChatGPT, Claude, Gemini ve Apple Intelligence için 7 dilde doğrulanmış tam metin korpusları ve uluslararası backlink matrisi.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Otonom Motor Aktif
                    </span>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-lg border p-4 bg-card" style="border-color:var(--hc-border-default)">
                    <span class="text-xs font-medium text-muted">Günlük SEO / İndeksleme</span>
                    <p class="mt-1 text-2xl font-bold">{{ $autonomousTargets['seo']['achieved'] }} / {{ $autonomousTargets['seo']['target'] }}</p>
                    <div class="mt-2 w-full bg-gray-200 dark:bg-neutral-800 rounded-full h-1.5">
                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $autonomousTargets['seo']['progress_percent'] }}%"></div>
                    </div>
                    <span class="text-xs text-muted mt-1 block">%{{ $autonomousTargets['seo']['progress_percent'] }} IndexNow iletimi</span>
                </div>

                <div class="rounded-lg border p-4 bg-card" style="border-color:var(--hc-border-default)">
                    <span class="text-xs font-medium text-muted">GEO Korpusları (AI Engines)</span>
                    <p class="mt-1 text-2xl font-bold">14 / 14 Aktif</p>
                    <p class="text-xs text-muted mt-1">7 dilde llms.txt + llms-full.txt</p>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach(['tr', 'en', 'de', 'fa', 'pl', 'ru', 'pt'] as $lang)
                            <a href="{{ $lang === 'tr' ? url('/llms.txt') : url('/'.$lang.'/llms.txt') }}" target="_blank" class="text-[10px] px-1.5 py-0.5 rounded bg-muted font-mono hover:underline">{{ strtoupper($lang) }}</a>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-lg border p-4 bg-card" style="border-color:var(--hc-border-default)">
                    <span class="text-xs font-medium text-muted">Uluslararası Backlink Havuzu</span>
                    <p class="mt-1 text-2xl font-bold">{{ array_sum(array_column($backlinkMarkets, 'total')) }} Aday</p>
                    <p class="text-xs text-muted mt-1">7 dilde özelleştirilmiş pitch taslakları</p>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach($backlinkMarkets as $marketKey => $marketVal)
                            <span class="text-[10px] px-1.5 py-0.5 rounded border border-muted font-mono">{{ strtoupper($marketKey) }}: {{ $marketVal['total'] }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-lg border p-4 bg-card" style="border-color:var(--hc-border-default)">
                    <span class="text-xs font-medium text-muted">Çok Dilli Çevrilmiş Sayfa</span>
                    <p class="mt-1 text-2xl font-bold">{{ array_sum(array_column($localeCoverage, 'recipes')) + array_sum(array_column($localeCoverage, 'posts')) }}</p>
                    <p class="text-xs text-muted mt-1">Yalnız tam çevirisi olan kayıtlar yayında</p>
                    <span class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 block">✓ Kalite Kapısı Onaylı</span>
                </div>
            </div>
        </section>
        <div class="grid gap-6 border-b py-6 lg:grid-cols-2" style="border-color:var(--hc-border-default)">
            <section>
                <h2>Otomasyon</h2>
                <p class="hc-caption mt-2">Günlük 01:49 ({{ config('app.timezone') }}). Koşu başına en fazla 1.000 inceleme işi ve 1.000 kaynak taraması.</p>
                <p class="hc-caption mt-2">Bağlantı doğrulama: 02:17, en fazla 3 kaynak. Google API: 06:47, yalnız yetki yapılandırıldıysa. Yönlendirme ve erişim engeli bağlantı yokluğu sayılmaz.</p>
                <p class="hc-caption mt-2">Yayın, medya onayı ve dış sitelere gönderim kapalı. Araştırma adayı backlink değildir; editör gözlemleri bağımsız doğrulanmış sayılmaz.</p>
                <dl class="mt-3 divide-y">
                    @forelse($runs as $run)
                        <div class="flex flex-wrap justify-between gap-2 py-2 text-xs"><dt>{{ $run->created_at->format('d.m.Y H:i') }}</dt><dd>{{ $run->status === 'success' ? 'Tamamlandı' : 'Başarısız' }} · {{ $run->payload['opportunities_created'] ?? 0 }} iş · {{ $run->payload['candidates_created'] ?? 0 }} aday</dd></div>
                    @empty
                        <div class="py-3 text-sm"><dt>Son koşu</dt><dd>Henüz çalışmadı</dd></div>
                    @endforelse
                </dl>
            </section>
            <section>
                <h2>Aday listesi aktarımı</h2>
                <form class="mt-3 grid gap-3" method="post" action="{{ route('admin.seo.growth.import') }}" enctype="multipart/form-data">
                    @csrf
                    <label class="hc-admin-field" for="backlink-csv">CSV dosyası<input id="backlink-csv" type="file" name="csv" accept=".csv,text/csv" required class="hc-input" aria-describedby="csv-format"></label>
                    <p id="csv-format" class="hc-caption">En fazla 10 MB / 10.000 satır. Sütunlar: <code>source_url,target_url,evidence</code>. Kanıt alanı isteğe bağlı; hedef bu sitenin adresi olmalı.</p>
                    <div><button class="hc-v3-btn hc-v3-btn--outline"><x-ui.icon name="archive" size="16" />Adayları içe aktar</button></div>
                </form>
            </section>
        </div>
        <section class="py-6">
            <div class="flex flex-wrap items-center justify-between gap-3"><h2>Dış bağlantı araştırması</h2><span class="hc-caption">{{ number_format($candidates->total(), 0, ',', '.') }} kayıt</span></div>
            <form class="mt-4 flex flex-wrap items-center gap-2" method="get">
                <label class="sr-only" for="candidate-status">Aday durumu</label>
                <select id="candidate-status" name="status" class="hc-input"><option value="">Tüm durumlar</option>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }} ({{ $growth['backlinks'][$value] }})</option>@endforeach</select>
                <button class="hc-v3-btn hc-v3-btn--outline" title="Filtrele" aria-label="Filtrele"><x-ui.icon name="sliders" size="18" /></button>
            </form>
            <div class="hc-table-wrap mt-4 overflow-x-auto" role="region" aria-label="Backlink adayları" tabindex="0">
                <table class="hc-table min-w-[850px]">
                    <thead><tr><th>Kaynak / hedef</th><th>Kanıt</th><th>İnceleme</th></tr></thead>
                    <tbody>
                        @forelse($candidates as $candidate)
                            <tr>
                                <td class="max-w-sm whitespace-normal"><strong>{{ $candidate->source_host }}</strong><p class="mt-1 text-xs">{{ $candidate->source_url }}</p><p class="mt-2 text-xs text-muted">Hedef: {{ $candidate->target_url }}</p></td>
                                <td class="max-w-xs whitespace-normal"><span class="text-xs">{{ ['admin_csv' => 'Yönetici CSV aktarımı', 'recipe_source' => 'Tarif kaynağı', 'recipe_provenance' => 'Tarif kaynak kaydı'][$candidate->evidence['source'] ?? ''] ?? 'Kaynak kaydı' }}</span><p class="mt-1 text-sm">{{ $candidate->evidence['note'] ?? '' }}</p>@if($candidate->review_note)<p class="mt-2 text-sm">{{ $candidate->review_note }}</p>@endif</td>
                                <td class="min-w-[250px] whitespace-normal">
                                    <p class="mb-2 text-xs">{{ \App\Services\Seo\BacklinkVerificationService::LABELS[$candidate->check_status] ?? 'Kontrol tamamlanamadı' }}@if($candidate->checked_at) · {{ $candidate->checked_at->format('d.m.Y H:i') }}@endif</p>
                                    @if($candidate->check_status === 'present')<p class="mb-2 text-xs">Bağlantı niteliği: {{ $candidate->link_rel ?: 'rel niteliği yok' }}</p>@endif
                                    <p class="mb-2 text-xs font-semibold">{{ $statuses[$candidate->status] }}</p>
                                    <form class="grid gap-2" method="post" action="{{ route('admin.seo.growth.review', $candidate->public_id) }}">
                                        @csrf
                                        <label class="sr-only" for="candidate-{{ $candidate->public_id }}">Yeni durum</label><select id="candidate-{{ $candidate->public_id }}" class="hc-input" name="status">@foreach(\App\Models\SeoBacklinkCandidate::TRANSITIONS[$candidate->status] as $value)<option value="{{ $value }}">{{ $statuses[$value] }}</option>@endforeach</select>
                                        <label class="sr-only" for="note-{{ $candidate->public_id }}">İnceleme kanıtı</label><textarea id="note-{{ $candidate->public_id }}" name="review_note" maxlength="2000" rows="2" class="hc-input" placeholder="Gözlem veya iletişim kanıtı">{{ $candidate->review_note }}</textarea>
                                        <div><button class="hc-v3-btn hc-v3-btn--outline" aria-label="İnceleme kararını kaydet" title="İnceleme kararını kaydet"><x-ui.icon name="check" size="18" /></button></div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-8 text-center">Bu durumda araştırma adayı yok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $candidates->links() }}</div>
        </section>
    </section>
</x-layouts.site>
