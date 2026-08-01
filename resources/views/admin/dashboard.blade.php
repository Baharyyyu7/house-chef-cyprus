<x-layouts.site title="Genel Bakış">
    @php
        $periods = [1 => '24 saat', 7 => '7 gün', 30 => '30 gün', 90 => '90 gün'];
        $statusLabel = static fn (string $status): string => match ($status) {
            'healthy', 'ok' => 'Sağlıklı',
            'degraded', 'delayed' => 'Dikkat gerekli',
            'unhealthy', 'failed' => 'Sorun var',
            default => 'Henüz veri yok',
        };
    @endphp

    <section class="hc-admin-dashboard hc-v3-section-reveal">
        <div class="hc-admin-page-header">
            <div>
                <p class="hc-kicker">YÖNETİM / GENEL BAKIŞ</p>
                <h1 class="hc-heading-1 mt-2">İçerik ve operasyon özeti</h1>
                <p class="hc-body-sm mt-3 max-w-2xl" style="color:var(--hc-text-secondary)">Tüm sayılar, seçilen dönem ve mevcut kayıtlar üzerinden hesaplanır. Veri oluşmamış alanlar açıkça belirtilir.</p>
            </div>
            <div class="hc-admin-periods" role="group" aria-label="Dashboard dönemi">
                @foreach ($periods as $days => $label)
                    <a href="{{ route('admin.dashboard', ['period' => $days]) }}" @class(['hc-admin-period', 'hc-admin-period--active' => $period === $days]) aria-current="{{ $period === $days ? 'true' : 'false' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        <p class="hc-caption mt-4" style="color:var(--hc-text-muted)">Güncelleme: {{ \Illuminate\Support\Carbon::parse($updatedAt)->translatedFormat('d M Y, H:i') }} · {{ $periodLabel }}</p>

        <section class="hc-admin-metrics hc-v3-stagger mt-7" aria-label="Temel metrikler">
            @foreach ($cards as $card)
                <article class="hc-admin-metric hc-v3-card hc-v3-card--interactive" aria-label="{{ $card['label'] }}: {{ $card['current_value'] ?? 'veri yok' }}{{ $card['suffix'] }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="hc-label-md" style="color:var(--hc-text-muted)">{{ $card['label'] }}</p>
                            @if ($card['data_status'] === 'available')
                                <strong class="hc-admin-metric__value hc-display-lg" data-hc-count="{{ $card['current_value'] }}">{{ number_format((float) $card['current_value'], 0, ',', '.') }}{{ $card['suffix'] }}</strong>
                            @else
                                <strong class="hc-admin-metric__empty">Veri henüz oluşmadı</strong>
                            @endif
                        </div>
                        <span class="hc-admin-metric__dot {{ $card['data_status'] === 'available' ? 'hc-admin-metric__dot--available' : '' }}" aria-hidden="true"></span>
                    </div>
                    <div class="hc-admin-metric__footer mt-4">
                        <p class="hc-caption" style="color:var(--hc-text-muted)">{{ $card['description'] }}</p>
                        @if ($card['percentage_change'] !== null)
                            <span @class(['hc-admin-trend', 'hc-admin-trend--inverse' => $card['inverse']])>{{ $card['trend_direction'] === 'up' ? '↑' : ($card['trend_direction'] === 'down' ? '↓' : '—') }} {{ number_format(abs($card['percentage_change']), 1, ',', '.') }}%</span>
                        @else
                            <span class="hc-caption hc-admin-metric__trend-label" style="color:var(--hc-text-muted)">Karşılaştırma için yeterli veri yok</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        <section class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-2">
                    @foreach ($charts as $chart)
                        <article class="hc-admin-chart hc-v3-card p-5 sm:p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="hc-heading-4">{{ $chart['title'] }}</h2>
                                    <p class="hc-caption mt-1" style="color:var(--hc-text-muted)">{{ $chart['description'] }}</p>
                                </div>
                                <span class="hc-v3-badge hc-v3-badge--accent">{{ $periodLabel }}</span>
                            </div>
                            @if ($chart['data_status'] === 'available')
                                <div class="hc-admin-chart__canvas mt-5">
                                    <canvas data-admin-chart data-admin-chart-config='@json($chart)' role="img" aria-label="{{ $chart['title'] }} grafiği"></canvas>
                                </div>
                                <p class="sr-only">{{ $chart['summary'] }}</p>
                                <p class="hc-caption mt-4" style="color:var(--hc-text-muted)">{{ $chart['summary'] }}</p>
                            @else
                                <div class="hc-v3-empty mt-5 py-9"><p class="font-bold">Veri henüz oluşmadı</p><p class="hc-caption mt-1">Bu grafik yalnız gerçek kayıtlar oluştuğunda gösterilir.</p></div>
                            @endif
                        </article>
                    @endforeach
                </div>

                <article id="system-status" class="hc-v3-card p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div><p class="hc-kicker">OPERASYONEL SAĞLIK</p><h2 class="hc-heading-3 mt-1">Sistem durumu</h2></div>
                        <div class="flex flex-wrap gap-2"><a href="{{ route('admin.product-experience.index') }}" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Ürün deneyimi</a><a href="{{ route('admin.system-events.index') }}" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Sistem olayları</a></div>
                    </div>
                    <div class="hc-admin-system-grid mt-6">
                        <div class="hc-admin-system-card"><span>Uygulama</span><strong>{{ $statusLabel((string) ($system['health']['status'] ?? 'never_run')) }}</strong></div>
                        <div class="hc-admin-system-card"><span>Queue</span><strong>{{ $statusLabel((string) ($system['queue']['overall_status'] ?? 'never_run')) }}</strong><small>{{ (int) ($system['queue']['pending_jobs'] ?? 0) }} bekleyen · {{ (int) ($system['queue']['failed_jobs'] ?? 0) }} başarısız</small></div>
                        <div class="hc-admin-system-card"><span>Scheduler</span><strong>{{ $statusLabel((string) ($system['scheduler']['status'] ?? 'never_run')) }}</strong><small>{{ $system['scheduler']['last_task_name'] ?? 'Henüz görev kaydı yok' }}</small></div>
                        <div class="hc-admin-system-card"><span>Yedek</span><strong>{{ $system['backup'] ? 'Kayıtlı' : 'Kayıt yok' }}</strong><small>{{ $system['backup']['created_at'] ?? 'Henüz doğrulanabilir yedek yok' }}</small></div>
                        <div class="hc-admin-system-card"><span>Medya</span><strong>{{ (int) $system['media']['failed_processing'] === 0 ? 'Sağlıklı' : 'İnceleme gerekli' }}</strong><small>{{ (int) $system['media']['pending_thumbnails'] }} bekleyen küçük görsel</small></div>
                        <div class="hc-admin-system-card"><span>İstek metrikleri</span><strong>{{ (int) ($system['requestMetrics']['requests'] ?? 0) }} istek</strong><small>{{ (int) ($system['requestMetrics']['errors'] ?? 0) }} hata · {{ $system['requestMetrics']['average_duration_ms'] ?? '—' }} ms</small></div>
                        <div class="hc-admin-system-card"><span>Release durumu</span><strong>{{ $system['production']['release_state'] ?? 'development' }}</strong><small>{{ $system['production']['environment'] ?? 'local' }} · ortam hazırlığı: {{ $system['production']['environment_readiness'] ?? 'not_configured' }}</small></div>
                        <div class="hc-admin-system-card"><span>Production bağımlılıkları</span><strong>Mail: {{ $statusLabel((string) ($system['production']['mail'] ?? 'not_configured')) }}</strong><small>Güvenlik: {{ $statusLabel((string) ($system['production']['security'] ?? 'not_configured')) }} · Analytics: {{ $system['production']['analytics'] ?? 'not_configured' }} · Search Console: {{ $system['production']['search_console'] ?? 'not_configured' }}</small></div>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <form method="post" action="{{ route('admin.system-status.refresh-queue') }}">@csrf<button class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Queue yenile</button></form>
                        <form method="post" action="{{ route('admin.system-status.refresh-scheduler') }}">@csrf<button class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Scheduler yenile</button></form>
                        <form method="post" action="{{ route('admin.system-status.refresh-cache') }}">@csrf<button class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Cache yenile</button></form>
                    </div>
                </article>

                <article class="hc-v3-card p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="hc-kicker">VERİ GÜVENİ</p><h2 class="hc-heading-3 mt-1">Kalite skorları</h2></div><a href="{{ route('admin.data-quality.index') }}" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Veri kalitesine git</a></div>
                    <div class="hc-admin-quality-grid mt-6">
                        @foreach ($qualityScores as $key => $score)
                            <div><div class="flex items-center justify-between gap-3"><span>{{ config('data_quality.categories.'.$key.'.label', $key === 'overall' ? 'Genel' : ucfirst(str_replace('_', ' ', $key))) }}</span><strong>{{ $score }}%</strong></div><div class="hc-admin-progress mt-2" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $score }}"><span style="width: {{ max(0, min(100, (int) $score)) }}%"></span></div></div>
                        @endforeach
                    </div>
                </article>
            </div>

            <aside class="space-y-6">
                <section class="hc-v3-card p-5">
                    <div class="flex items-center justify-between gap-3"><h2 class="hc-heading-4">Hızlı işlemler</h2><span class="hc-v3-badge">Yetkili</span></div>
                    <div class="mt-4 grid gap-2">
                        @foreach ($quickActions as $action)
                            <a href="{{ route($action['route']) }}" class="hc-admin-quick-action"><span><x-ui.icon :name="$action['icon']" size="17" /></span>{{ $action['label'] }}<span aria-hidden="true">→</span></a>
                        @endforeach
                    </div>
                </section>

                <section class="hc-v3-card p-5" aria-labelledby="attention-heading">
                    <div class="flex items-center justify-between gap-3"><h2 id="attention-heading" class="hc-heading-4">Dikkat gerekenler</h2><span class="hc-v3-badge {{ count($attention) ? 'hc-v3-badge--warning' : 'hc-v3-badge--success' }}">{{ count($attention) }}</span></div>
                    @if (count($attention))
                        <div class="mt-4 space-y-3">
                            @foreach ($attention as $item)
                                <a href="{{ $item['url'] ?? route($item['route']) }}" class="hc-admin-attention hc-admin-attention--{{ $item['severity'] }}"><strong>{{ $item['label'] }}</strong><span>{{ $item['detail'] }}</span></a>
                            @endforeach
                        </div>
                    @else
                        <div class="hc-v3-empty mt-4 py-7"><p class="font-bold">Açık dikkat kaydı yok</p><p class="hc-caption mt-1">Şu an listelenebilir kritik durum bulunmuyor.</p></div>
                    @endif
                </section>

                <section class="hc-v3-card p-5">
                    <div class="flex items-center justify-between gap-3"><h2 class="hc-heading-4">Son etkinlikler</h2><a href="{{ route('admin.system-events.index') }}" class="hc-caption font-bold" style="color:var(--hc-text-accent)">Tümü</a></div>
                    @if (count($activity))
                        <ol class="mt-4 space-y-4">
                            @foreach ($activity as $item)
                                <li class="hc-admin-activity"><span class="hc-admin-activity__marker" aria-hidden="true"></span><div><strong>{{ $item['action'] }}</strong><p>{{ $item['actor'] }} · {{ $item['at'] }}</p>@if($item['entity_public_id'])<code>{{ $item['entity_public_id'] }}</code>@endif</div></li>
                            @endforeach
                        </ol>
                    @else
                        <div class="hc-v3-empty mt-4 py-7"><p class="font-bold">Etkinlik kaydı yok</p><p class="hc-caption mt-1">Güvenli bir audit kaydı oluştuğunda burada görünür.</p></div>
                    @endif
                </section>
            </aside>
        </section>
    </section>
</x-layouts.site>
