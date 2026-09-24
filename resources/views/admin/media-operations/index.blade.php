<x-layouts.site title="Medya Operasyonları">
    @php
        /**
         * Sayfa 14 ham durum kodunu ("candidate_search_required", "fidelity_review")
         * olduğu gibi listeliyordu; bir görselin nerede takıldığını anlamak için
         * sözlüğü bilmek gerekiyordu. Durumlar, sorumluluğu kimde olduğuna göre
         * dört aşamaya toplanır.
         */
        $asamalar = [
            'yok' => ['Görsel yok', 'Bu içerik için hiç aday yok. Görsel Talepleri ekranından üretim isteyin.', ['no_media', 'branded_fallback', 'candidate_search_required'], 'rose'],
            'hazirlik' => ['Hazırlanıyor', 'Aday bulundu, otomatik kontroller sürüyor.', ['candidates_ready', 'selected_private', 'optimization_pending'], 'stone'],
            'inceleme' => ['İnceleme bekliyor', 'Bir editörün bakması gerekiyor.', ['fidelity_review', 'license_review', 'attribution_review', 'ready_for_human_approval'], 'amber'],
            'yayinda' => ['Yayında', 'Görsel sitede görünüyor.', ['approved_real_media'], 'emerald'],
        ];

        $sorunlu = ['rejected', 'unavailable', 'blocked'];

        // Sayımlar tek geçişte çıkar. Önce her aşama için ayrı ayrı
        // whereIn(...)->count() çalışıyordu; envanter 2891 satır olduğu için
        // aynı koleksiyon baştan sona beş kez taranıyordu ve sayfanın
        // maliyetinin çoğu buradaydı.
        $durumSayilari = collect($rows)->countBy('status');
        $topla = fn (array $durumlar) => collect($durumlar)->sum(fn ($d) => $durumSayilari[$d] ?? 0);

        $asamaSayilari = collect($asamalar)->map(fn ($tanim) => $topla($tanim[2]));
        $sorunluSayi = $topla($sorunlu);

        $renkler = [
            'rose' => 'border-rose-200 bg-rose-50 text-rose-950',
            'amber' => 'border-amber-200 bg-amber-50 text-amber-950',
            'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
            'stone' => 'border-stone-200 bg-stone-50 text-stone-900',
        ];

        $secili = request('asama', '');
        $seciliDurumlar = $secili === 'sorunlu' ? $sorunlu : ($asamalar[$secili][2] ?? []);
        // Tablo zaten ilk 100 kaydı basıyor; tüm koleksiyonu ayrıca
        // filtrelemek yerine sayfaya girecek kadarını almak yeterli.
        $gosterilecek = $seciliDurumlar === [] ? collect($rows) : collect($rows)->whereIn('status', $seciliDurumlar);
        $gosterilecekSayi = $gosterilecek->count();
    @endphp

    <section class="hc-container py-8 sm:py-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="hc-heading-1">Medya operasyonları</h1>
                <p class="hc-body-sm mt-2 max-w-2xl" style="color: var(--hc-text-secondary)">Her içeriğin görselinin hangi aşamada olduğu ve kimin sırası olduğu.</p>
            </div>
            <a class="hc-v3-btn hc-v3-btn--primary" href="{{ route('admin.image-requests.index') }}">Görsel iste</a>
        </div>

        <div class="mt-7 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($asamalar as $anahtar => [$baslik, $aciklama, $durumlar, $renk])
                <a href="{{ route('admin.media-operations.index', array_filter(['type' => request('type'), 'asama' => $secili === $anahtar ? null : $anahtar])) }}"
                   @class([
                       'rounded-2xl border p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md',
                       $renkler[$renk],
                       'ring-2 ring-offset-2 ring-stone-900' => $secili === $anahtar,
                   ])>
                    <p class="text-3xl font-black">{{ number_format($asamaSayilari[$anahtar]) }}</p>
                    <p class="mt-1 font-bold">{{ $baslik }}</p>
                    <p class="mt-1 text-xs leading-5 opacity-80">{{ $aciklama }}</p>
                </a>
            @endforeach
        </div>

        @if($sorunluSayi > 0)
            <a href="{{ route('admin.media-operations.index', array_filter(['type' => request('type'), 'asama' => $secili === 'sorunlu' ? null : 'sorunlu'])) }}"
               @class(['mt-3 flex items-center gap-3 rounded-2xl border border-stone-300 bg-white p-4 transition hover:shadow-md', 'ring-2 ring-stone-900' => $secili === 'sorunlu'])>
                <x-ui.icon name="warning" size="18" class="text-rose-700" />
                <span class="font-bold">{{ $sorunluSayi }} içerik reddedildi, engellendi veya kaynağı kayboldu</span>
            </a>
        @endif

        <div class="mt-7 flex flex-wrap items-center gap-2">
            @foreach(['' => 'Tümü', 'recipe' => 'Tarifler', 'blog' => 'Blog yazıları'] as $deger => $etiket)
                <a href="{{ route('admin.media-operations.index', array_filter(['type' => $deger ?: null, 'asama' => $secili ?: null])) }}" @class([
                    'rounded-full px-4 py-2 text-sm font-bold transition-colors duration-200',
                    'bg-stone-900 text-white' => request('type', '') === $deger,
                    'bg-stone-100 text-stone-700 hover:bg-stone-200' => request('type', '') !== $deger,
                ])>{{ $etiket }}</a>
            @endforeach
        </div>

        <div class="mt-5 overflow-x-auto rounded-2xl border" style="border-color: var(--hc-border-subtle)">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="bg-stone-50 dark:bg-white/5">
                    <tr><th class="p-3">İçerik</th><th class="p-3">Aşama</th><th class="p-3">Aday</th><th class="p-3">Bekleyen adım</th></tr>
                </thead>
                <tbody>
                    @forelse($gosterilecek->take(100) as $row)
                        @php($asama = collect($asamalar)->first(fn ($t) => in_array($row['status'], $t[2], true)))
                        <tr class="border-t" style="border-color: var(--hc-border-subtle)">
                            <td class="p-3">{{ $row['entity_type'] === 'recipe' ? 'Tarif' : 'Blog' }}<br><span class="font-mono text-[11px]" style="color: var(--hc-text-muted)">{{ $row['entity_public_id'] }}</span></td>
                            <td class="p-3 font-semibold">{{ $asama[0] ?? 'Sorunlu' }}</td>
                            <td class="p-3">{{ $row['candidate_count'] ?: '—' }}</td>
                            <td class="p-3" style="color: var(--hc-text-secondary)">
                                @if(! $row['license_complete']) Lisans bilgisi
                                @elseif(! $row['attribution_complete']) Attribution
                                @elseif(! $row['responsive_ready']) Boyut üretimi
                                @elseif($row['blocking_reason']) {{ $row['blocking_reason'] }}
                                @else — @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-8 text-center" style="color: var(--hc-text-muted)">Bu aşamada içerik yok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($gosterilecekSayi > 100)
            <p class="mt-3 text-xs" style="color: var(--hc-text-muted)">İlk 100 kayıt gösteriliyor ({{ number_format($gosterilecekSayi) }} kayıt var). Aşama veya içerik türü seçerek daraltın.</p>
        @endif

        @if($candidates->isNotEmpty())
            <section class="mt-9">
                <h2 class="hc-heading-3">Kararınızı bekleyen adaylar</h2>
                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($candidates as $candidate)
                        <article class="hc-v3-card p-4">
                            <p class="font-bold">{{ $candidate->recipe?->title ?? 'Silinmiş tarif' }}</p>
                            <p class="mt-1 text-xs" style="color: var(--hc-text-muted)">{{ strtoupper($candidate->provider) }}</p>
                            @if($candidate->status === 'pending_review')
                                <div class="mt-3 flex gap-2">
                                    <form method="post" action="{{ route('admin.media-operations.candidates.select', $candidate) }}">@csrf<button class="hc-v3-btn hc-v3-btn--primary hc-v3-btn--sm" type="submit">Seç</button></form>
                                    <form method="post" action="{{ route('admin.media-operations.candidates.reject', $candidate) }}">@csrf<button class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" type="submit">Reddet</button></form>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <details class="mt-9 rounded-2xl border p-5" style="border-color: var(--hc-border-subtle)">
            <summary class="cursor-pointer font-bold">Sağlayıcı durumu ve teknik sayılar</summary>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($health['provider_health'] as $provider)
                    <article class="hc-admin-detail-card"><span>{{ strtoupper($provider['name']) }}</span><strong>{{ $provider['status'] }}</strong><small>{{ $provider['candidates'] }} kayıtlı aday</small></article>
                @endforeach
            </div>
            <p class="mt-4 text-sm" style="color: var(--hc-text-secondary)">Medya sağlık skoru: <strong>{{ $health['score'] }}/100</strong> · Private sızıntı: <strong>{{ $health['private_leakage'] }}</strong></p>
            <p class="mt-2 text-xs" style="color: var(--hc-text-muted)">Anahtar veya token hiçbir zaman gösterilmez.</p>
        </details>
    </section>
</x-layouts.site>
