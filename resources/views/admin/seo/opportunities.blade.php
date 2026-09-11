<x-layouts.site title="SEO Backlink ve İçerik Fırsatları">
<section class="hc-container py-8 sm:py-10">
    <x-admin.page-header 
        eyebrow="SEO & Digital PR" 
        title="Backlink & İletişim Operasyonları" 
        description="Akdeniz ve Kıbrıs mutfak otoritesini artırmak için taranan yüksek otoriteli yayıncılar, eşleşen kaynaklar ve tek tıkla e-posta iletişim araçları." 
        :back="['url' => route('admin.seo.index'), 'label' => 'SEO merkezi']"
    >
        <x-slot:actions>
            <form method="post" action="{{ route('admin.seo.opportunities.refresh') }}">
                @csrf
                <button type="submit" class="hc-v3-btn hc-v3-btn--ghost">
                    🔄 Fırsatları Yenile
                </button>
            </form>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Üst İstatistik Sayaçları --}}
    <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="hc-admin-stat">
            <span>Toplam Backlink Adayı</span>
            <strong class="text-indigo-400">{{ $stats['total_backlinks'] }}</strong>
        </div>
        <div class="hc-admin-stat">
            <span>İletişim Bekleyen (Açık)</span>
            <strong class="text-amber-400">{{ $stats['open_backlinks'] }}</strong>
        </div>
        <div class="hc-admin-stat">
            <span>İletişime Geçilen</span>
            <strong class="text-blue-400">{{ $stats['contacted_backlinks'] }}</strong>
        </div>
        <div class="hc-admin-stat">
            <span>Kazanılan Backlink</span>
            <strong class="text-emerald-400">{{ $stats['won_backlinks'] }}</strong>
        </div>
    </div>

    {{-- Sekmeler ve Filtreler --}}
    <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-b pb-4" style="border-color: var(--hc-border-subtle)">
        <div class="flex flex-wrap items-center gap-2">
            <a 
                href="{{ route('admin.seo.opportunities', ['tab' => 'backlinks']) }}" 
                class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $tab === 'backlinks' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'text-muted hover:text-white' }}"
            >
                🔗 Backlink Adayları ({{ $stats['total_backlinks'] }})
            </a>
            <a 
                href="{{ route('admin.seo.opportunities', ['tab' => 'gaps']) }}" 
                class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $tab === 'gaps' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'text-muted hover:text-white' }}"
            >
                📝 İçerik Boşlukları ({{ $stats['total_gaps'] }})
            </a>
            <a 
                href="{{ route('admin.seo.opportunities', ['tab' => 'all']) }}" 
                class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $tab === 'all' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'text-muted hover:text-white' }}"
            >
                Tüm Kayıtlar
            </a>
        </div>

        {{-- Durum Filtresi --}}
        <div class="flex items-center gap-2 text-sm">
            <span class="text-muted">Durum:</span>
            <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}" class="px-2.5 py-1 rounded-lg text-xs font-medium {{ !request('status') ? 'bg-white/10 text-white' : 'text-muted hover:text-white' }}">Tümü</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'open', 'page' => null]) }}" class="px-2.5 py-1 rounded-lg text-xs font-medium {{ request('status') === 'open' ? 'bg-amber-500/20 text-amber-300' : 'text-muted hover:text-white' }}">Açık</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'contacted', 'page' => null]) }}" class="px-2.5 py-1 rounded-lg text-xs font-medium {{ request('status') === 'contacted' ? 'bg-blue-500/20 text-blue-300' : 'text-muted hover:text-white' }}">İletişimde</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'won', 'page' => null]) }}" class="px-2.5 py-1 rounded-lg text-xs font-medium {{ request('status') === 'won' ? 'bg-emerald-500/20 text-emerald-300' : 'text-muted hover:text-white' }}">Kazanıldı</a>
        </div>
    </div>

    {{-- İçerik Listesi --}}
    <div class="mt-6 space-y-4">
        @forelse($opportunities as $opportunity)
            @php
                $evidence = $opportunity->evidence ?? [];
                $isBacklink = $opportunity->kind === 'backlink_prospect';
                $score = $evidence['authority_score'] ?? $opportunity->priority;
                $targetDomain = $evidence['target_domain'] ?? (isset($evidence['target_url']) ? parse_url($evidence['target_url'], PHP_URL_HOST) : null) ?? $opportunity->title;
                $targetUrl = $evidence['target_url'] ?? '#';
                $topic = $evidence['topic'] ?? $opportunity->topic_cluster ?? $opportunity->title;
                $matchedAssetTitle = $evidence['matched_asset_title'] ?? 'House Chef Doğrulanmış Tarif';
                $matchedAssetUrl = $evidence['matched_asset_url'] ?? route('recipes.index');
                $pitchTr = $evidence['pitch_draft_tr'] ?? '';
                $pitchEn = $evidence['pitch_draft_en'] ?? '';
                $mailtoSubject = rawurlencode("House Chef Cyprus - Doğrulanmış Kaynak Önerisi: " . $topic);
                $mailtoBody = rawurlencode($pitchTr ?: $pitchEn);
                $embedCode = '<iframe src="' . rtrim($matchedAssetUrl, '/') . '/embed" width="100%" height="450" frameborder="0" style="border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.15);" loading="lazy" title="' . e($matchedAssetTitle) . '"></iframe>';
            @endphp

            <div 
                x-data="{ 
                    trCopied: false, 
                    enCopied: false, 
                    embedOpen: false, 
                    pitchOpen: false,
                    copyTr() {
                        navigator.clipboard.writeText({{ json_encode($pitchTr) }});
                        this.trCopied = true;
                        setTimeout(() => this.trCopied = false, 2500);
                    },
                    copyEn() {
                        navigator.clipboard.writeText({{ json_encode($pitchEn) }});
                        this.enCopied = true;
                        setTimeout(() => this.enCopied = false, 2500);
                    }
                }"
                class="rounded-2xl border p-5 sm:p-6 transition hover:border-white/20"
                style="background: var(--hc-surface-card, #121212); border-color: var(--hc-border-subtle, rgba(255,255,255,0.08));"
            >
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    {{-- Sol Taraf: Alan Adı ve Konu --}}
                    <div class="space-y-2 max-w-2xl">
                        <div class="flex flex-wrap items-center gap-2.5">
                            @if($isBacklink)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                    🌐 {{ $targetDomain }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $score >= 85 ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                                    Otorite Skoru: {{ $score }}/100
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold uppercase tracking-wider bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                    {{ $opportunity->kind }}
                                </span>
                            @endif

                            {{-- Durum Rozeti --}}
                            @if($opportunity->status === 'won')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">✓ Kazanıldı</span>
                            @elseif($opportunity->status === 'contacted')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/40">⏳ İletişime Geçildi</span>
                            @elseif($opportunity->status === 'declined')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-zinc-500/20 text-zinc-400 border border-zinc-500/40">Reddedildi</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">Açık</span>
                            @endif
                        </div>

                        <h3 class="text-lg font-bold text-white tracking-tight">
                            {{ $opportunity->title }}
                        </h3>

                        @if(!empty($evidence['rationale']))
                            <p class="text-sm text-zinc-400 leading-relaxed">
                                {{ $evidence['rationale'] }}
                            </p>
                        @endif

                        {{-- Hedef URL ve Eşleşen İçerik Kartı --}}
                        <div class="mt-3 flex flex-wrap items-center gap-3 pt-2 text-xs">
                            @if($targetUrl !== '#')
                                <a href="{{ $targetUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-zinc-400 hover:text-white underline underline-offset-4">
                                    <span>🔗 Hedef Makale / Sayfa</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            @endif

                            <span class="text-zinc-600">•</span>

                            <div class="inline-flex items-center gap-1.5 text-amber-400">
                                <span class="font-medium">Eşleşen Kaynak:</span>
                                <a href="{{ $matchedAssetUrl }}" target="_blank" class="font-semibold underline underline-offset-4 hover:text-amber-300">
                                    {{ $matchedAssetTitle }}
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Sağ Taraf: Eylemler & CRM Durum Yönetimi --}}
                    <div class="flex flex-col items-start lg:items-end gap-3 shrink-0">
                        {{-- Hızlı Eylem Butonları --}}
                        <div class="flex flex-wrap items-center gap-2">
                            @if($isBacklink && $pitchTr)
                                <button 
                                    type="button" 
                                    @click="copyTr()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition"
                                    :class="trCopied ? 'bg-emerald-500 text-white' : 'bg-white/10 text-white hover:bg-white/20'"
                                >
                                    <span x-show="!trCopied">📋 Türkçe Pitch</span>
                                    <span x-show="trCopied">✓ Kopyalandı!</span>
                                </button>

                                <button 
                                    type="button" 
                                    @click="copyEn()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition"
                                    :class="enCopied ? 'bg-emerald-500 text-white' : 'bg-white/10 text-white hover:bg-white/20'"
                                >
                                    <span x-show="!enCopied">📋 English Pitch</span>
                                    <span x-show="enCopied">✓ Copied!</span>
                                </button>

                                <a 
                                    href="mailto:?subject={{ $mailtoSubject }}&body={{ $mailtoBody }}" 
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-amber-500 text-black hover:bg-amber-400 transition shadow-sm"
                                    title="E-posta İstemcisinde Aç"
                                >
                                    ✉️ E-posta Başlat
                                </a>
                            @endif

                            <button 
                                type="button" 
                                @click="embedOpen = !embedOpen"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white/5 text-zinc-300 hover:bg-white/10 transition border border-white/10"
                            >
                                &lt;/&gt; Gömülü Widget
                            </button>
                        </div>

                        {{-- Durum Güncelleme Butonları --}}
                        <form method="post" action="{{ route('admin.seo.opportunities.update-status', $opportunity) }}" class="flex items-center gap-1.5 mt-1">
                            @csrf
                            <span class="text-[11px] text-zinc-500 uppercase tracking-wider font-semibold mr-1">Durumu Değiştir:</span>
                            @if($opportunity->status !== 'open')
                                <button type="submit" name="status" value="open" class="px-2 py-1 rounded text-[11px] font-medium bg-white/5 text-zinc-400 hover:text-white hover:bg-white/10">Açık</button>
                            @endif
                            @if($opportunity->status !== 'contacted')
                                <button type="submit" name="status" value="contacted" class="px-2 py-1 rounded text-[11px] font-medium bg-blue-500/20 text-blue-300 hover:bg-blue-500/30">İletişimde</button>
                            @endif
                            @if($opportunity->status !== 'won')
                                <button type="submit" name="status" value="won" class="px-2 py-1 rounded text-[11px] font-medium bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30">Kazanıldı</button>
                            @endif
                            @if($opportunity->status !== 'declined')
                                <button type="submit" name="status" value="declined" class="px-2 py-1 rounded text-[11px] font-medium bg-zinc-500/20 text-zinc-400 hover:bg-zinc-500/30">Red</button>
                            @endif
                        </form>
                    </div>
                </div>

                {{-- Gömülü Widget Kodu Çekmecesi --}}
                <div x-show="embedOpen" x-collapse class="mt-4 pt-4 border-t border-white/10">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="text-xs font-bold text-zinc-300">İçerik İframe Gömme Kodu (Yayıncıya Teklif Edilebilir):</span>
                        <button 
                            type="button" 
                            @click="navigator.clipboard.writeText({{ json_encode($embedCode) }}); alert('Gömme kodu kopyalandı!')"
                            class="text-xs text-amber-400 hover:text-amber-300 font-semibold"
                        >
                            Kodu Kopyala
                        </button>
                    </div>
                    <pre class="p-3 rounded-xl bg-black/60 border border-white/10 text-xs text-zinc-300 font-mono overflow-x-auto whitespace-pre-wrap">{{ $embedCode }}</pre>
                </div>

                {{-- Pitch Önizleme Çekmecesi --}}
                @if($isBacklink && ($pitchTr || $pitchEn))
                    <div class="mt-3 pt-3 border-t border-white/5 flex flex-col gap-2">
                        <button 
                            type="button" 
                            @click="pitchOpen = !pitchOpen" 
                            class="text-xs text-zinc-400 hover:text-zinc-200 inline-flex items-center gap-1 self-start font-medium"
                        >
                            <span x-text="pitchOpen ? '▲ İletişim Metinlerini Gizle' : '▼ Hazır İletişim / Pitch Metinlerini İncele'"></span>
                        </button>

                        <div x-show="pitchOpen" x-collapse class="grid gap-3 pt-2 md:grid-cols-2 text-xs">
                            <div class="p-3 rounded-xl bg-black/40 border border-white/10 space-y-1">
                                <span class="font-bold text-amber-300 block">Türkçe İletişim Taslağı:</span>
                                <p class="text-zinc-300 leading-relaxed">{{ $pitchTr }}</p>
                            </div>
                            <div class="p-3 rounded-xl bg-black/40 border border-white/10 space-y-1">
                                <span class="font-bold text-blue-300 block">İngilizce İletişim Taslağı:</span>
                                <p class="text-zinc-300 leading-relaxed">{{ $pitchEn }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border p-12 text-center" style="border-color: var(--hc-border-subtle)">
                <p class="text-muted text-base">Bu filtrede gösterilecek fırsat bulunamadı.</p>
            </div>
        @endforelse
    </div>

    {{-- Sayfalama --}}
    <div class="mt-8">
        {{ $opportunities->links() }}
    </div>
</section>
</x-layouts.site>
