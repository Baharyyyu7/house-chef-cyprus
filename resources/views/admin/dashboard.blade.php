<x-layouts.site title="Genel Bakış">
    @php
        $periods = [1 => '24 saat', 7 => '7 gün', 30 => '30 gün', 90 => '90 gün'];
        $statusLabel = static fn (string $status): string => match ($status) {
            'healthy', 'ok', 'ready' => 'Sağlıklı',
            'degraded', 'delayed', 'warning' => 'Dikkat gerekli',
            'unhealthy', 'failed', 'critical' => 'Sorun var',
            'configured' => 'Yapılandırılmış',
            'not_configured' => 'Yapılandırılmamış',
            default => 'Henüz veri yok',
        };
        $statusTone = static fn (string $status): string => match ($status) {
            'healthy', 'ok', 'ready' => 'success',
            'degraded', 'delayed', 'warning' => 'warning',
            'unhealthy', 'failed', 'critical' => 'danger',
            default => 'neutral',
        };
        $metricLinks = [
            'yayinda' => ['route' => 'admin.recipes.index', 'icon' => 'book', 'note' => 'Yayın durumu'],
            'inceleme' => ['route' => 'admin.recipe-verification.index', 'icon' => 'shield', 'note' => 'İçerik kuyruğu'],
            'failed_jobs' => ['route' => 'admin.system-events.index', 'icon' => 'server', 'note' => 'İş kuyruğundaki hatalar'],
            'kullanicilar' => ['route' => 'admin.users.index', 'icon' => 'users', 'note' => 'Kayıtlı hesaplar'],
        ];
        $cardsByKey = collect($cards)->keyBy('key');
        $secondaryCards = $cardsByKey->except(array_keys($metricLinks));
        $healthSignals = [
            ['label' => 'Uygulama', 'status' => $system['health']['status'] ?? 'never_run'],
            ['label' => 'İş kuyruğu', 'status' => $system['queue']['overall_status'] ?? 'never_run'],
            ['label' => 'Zamanlayıcı', 'status' => $system['scheduler']['status'] ?? 'never_run'],
        ];
    @endphp

    <div class="hc-admin-dashboard">
        <header class="hc-dashboard-header">
            <div>
                <h1 class="hc-heading-1">İçerik ve operasyon özeti</h1>
                <p class="hc-dashboard-updated">
                    <span>{{ $periodLabel }}</span>
                    <span>Güncelleme: <time datetime="{{ $updatedAt }}">{{ \Illuminate\Support\Carbon::parse($updatedAt)->translatedFormat('d M Y, H:i') }}</time></span>
                </p>
            </div>
            <div class="hc-admin-periods" role="group" aria-label="Dashboard dönemi">
                @foreach ($periods as $days => $label)
                    <a href="{{ route('admin.dashboard', ['period' => $days]) }}" @class(['hc-admin-period', 'hc-admin-period--active' => $period === $days]) @if($period === $days) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </div>
        </header>

        <nav class="hc-dashboard-nav" aria-label="Panel bölümleri">
            <a href="#overview"><x-ui.icon name="gauge" size="16" />Genel özet</a>
            <a href="#workflows"><x-ui.icon name="book" size="16" />İş akışları</a>
            <a href="#quality"><x-ui.icon name="shield" size="16" />Kalite ve sistem</a>
            <a href="#audience"><x-ui.icon name="users" size="16" />Kitle</a>
        </nav>

        <section id="overview" class="hc-dashboard-section" aria-label="Genel özet">
            <div class="hc-dashboard-metrics" aria-label="Öncelikli metrikler">
                @foreach ($metricLinks as $key => $link)
                    @php
                        $card = $cardsByKey[$key];
                        $value = $card['data_status'] === 'available' ? $card['current_value'] : null;
                        $needsAttention = $value !== null && $value > 0 && in_array($key, ['inceleme', 'failed_jobs'], true);
                    @endphp
                    <a href="{{ route($link['route'], $key === 'yayinda' ? ['status' => 'published'] : []) }}" @class(['hc-dashboard-metric', 'hc-dashboard-metric--attention' => $needsAttention])>
                        <span class="hc-dashboard-metric__label"><x-ui.icon :name="$link['icon']" size="17" />{{ $card['label'] }}<x-ui.icon name="arrow-right" size="15" /></span>
                        <strong class="hc-dashboard-metric__value">{{ $value === null ? '—' : number_format((float) $value, 0, ',', '.').$card['suffix'] }}</strong>
                        <span class="hc-dashboard-metric__note">{{ $link['note'] }}</span>
                        @if ($card['percentage_change'] !== null)
                            <span class="hc-dashboard-metric__comparison">
                                {{ $card['trend_direction'] === 'up' ? 'Artış' : ($card['trend_direction'] === 'down' ? 'Azalış' : 'Değişim') }}:
                                %{{ number_format(abs($card['percentage_change']), 1, ',', '.') }} <span>dönemdeki yeni kayıtlar</span>
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>

            <dl class="hc-dashboard-reference" aria-label="Referans sayılar">
                @foreach ($secondaryCards as $card)
                    <div><dt>{{ $card['label'] }}</dt><dd>{{ $card['data_status'] === 'available' ? number_format((float) $card['current_value'], 0, ',', '.').$card['suffix'] : 'Ölçülmedi' }}</dd></div>
                @endforeach
            </dl>

            <div class="hc-dashboard-priorities grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
                <section aria-labelledby="attention-heading">
                    <div class="hc-dashboard-section-heading">
                        <h2 id="attention-heading">Dikkat gerekenler</h2>
                        <span class="hc-dashboard-count">{{ count($attention) }} başlık</span>
                    </div>
                    @if (count($attention))
                        <ul class="hc-dashboard-attention-list">
                            @foreach ($attention as $item)
                                <li>
                                    <a href="{{ $item['url'] ?? route($item['route']) }}" class="hc-dashboard-attention hc-dashboard-attention--{{ $item['severity'] }}">
                                        <x-ui.icon name="warning" size="18" />
                                        <span><strong>{{ $item['label'] }}</strong><small>{{ $item['detail'] }}</small></span>
                                        <x-ui.icon name="arrow-right" size="17" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="hc-dashboard-empty"><x-ui.icon name="check" size="22" /><strong>Açık dikkat kaydı yok</strong></div>
                    @endif
                </section>
                <aside aria-labelledby="quick-actions-heading">
                    <div class="hc-dashboard-section-heading"><h2 id="quick-actions-heading">Hızlı işlemler</h2></div>
                    <div class="hc-dashboard-actions">
                        @foreach ($quickActions as $action)
                            <a href="{{ route($action['route']) }}" class="hc-admin-quick-action"><x-ui.icon :name="$action['icon']" size="18" /><span>{{ $action['label'] }}</span><x-ui.icon name="arrow-right" size="15" /></a>
                        @endforeach
                    </div>
                </aside>
            </div>

            <div class="hc-dashboard-charts">
                @foreach ($charts as $chart)
                    <article class="hc-admin-chart">
                        <div class="hc-dashboard-section-heading">
                            <h2>{{ $chart['title'] }}</h2>
                            <span class="hc-dashboard-scope">{{ $chart['type'] === 'line' ? $periodLabel : 'Mevcut katalog' }}</span>
                        </div>
                        <p class="hc-caption">{{ $chart['description'] }}</p>
                        @if ($chart['data_status'] === 'available')
                            <div class="hc-admin-chart__canvas">
                                <canvas data-admin-chart data-admin-chart-config='@json($chart)' role="img" aria-label="{{ $chart['title'] }} grafiği"></canvas>
                            </div>
                            <details class="hc-dashboard-chart-data">
                                <summary>Sayısal veriler <x-ui.icon name="chevron-down" size="14" /></summary>
                                <dl>
                                    @foreach ($chart['labels'] as $index => $label)
                                        <div><dt>{{ $label }}</dt><dd>{{ number_format($chart['values'][$index], 0, ',', '.') }}</dd></div>
                                    @endforeach
                                </dl>
                            </details>
                        @else
                            <div class="hc-dashboard-chart-empty"><x-ui.icon name="gauge" size="26" /><strong>Veri henüz oluşmadı</strong><p>Bu dönem için kayıt yok.</p></div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <section id="workflows" class="hc-dashboard-section" aria-labelledby="workflows-heading">
            <div class="hc-dashboard-section-heading hc-dashboard-section-heading--major">
                <h2 id="workflows-heading">İş akışları</h2>
                <span class="hc-dashboard-scope">Mevcut kayıtlar</span>
            </div>
            <section class="hc-dashboard-operations" aria-label="Operasyon kuyrukları">
                <article class="hc-dashboard-operation">
                    <div class="flex items-center justify-between gap-3"><div><p class="hc-kicker">İÇERİK OPERASYONLARI</p><h2 class="hc-heading-4 mt-1">Yayın ve inceleme</h2></div><a href="{{ route('admin.recipe-verification.index') }}" class="hc-caption font-bold" style="color:var(--hc-text-accent)">İncele</a></div>
                    <dl class="hc-admin-operation-list mt-5">
                        <div><dt>Yayındaki tarif</dt><dd>{{ number_format($operations['content']['recipes_published'], 0, ',', '.') }}</dd></div>
                        <div><dt>İnceleme bekleyen tarif</dt><dd>{{ number_format($operations['content']['recipes_review_required'], 0, ',', '.') }}</dd></div>
                        <div><dt>Onaylı medya eksiği</dt><dd>{{ number_format($operations['content']['recipes_missing_approved_media'], 0, ',', '.') }}</dd></div>
                        <div><dt>Yayındaki blog</dt><dd>{{ number_format($operations['content']['blogs_published'], 0, ',', '.') }}</dd></div>
                        <div><dt>Blog medya eksiği</dt><dd>{{ number_format($operations['content']['blogs_missing_approved_media'], 0, ',', '.') }}</dd></div>
                        <div><dt>Zamanlanmış içerik</dt><dd>{{ number_format($operations['content']['scheduled'], 0, ',', '.') }}</dd></div>
                    </dl>
                </article>

                <article class="hc-dashboard-operation">
                    <div class="flex items-center justify-between gap-3"><div><p class="hc-kicker">ÜYE OPERASYONLARI</p><h2 class="hc-heading-4 mt-1">Hesap durumu</h2></div><a href="{{ route('admin.users.index') }}" class="hc-caption font-bold" style="color:var(--hc-text-accent)">Üyeleri aç</a></div>
                    <dl class="hc-admin-operation-list mt-5">
                        <div><dt>Toplam üye</dt><dd>{{ number_format($operations['members']['total'], 0, ',', '.') }}</dd></div>
                        <div><dt>Yeni kayıtlar</dt><dd>{{ number_format($operations['members']['recent_registrations'], 0, ',', '.') }}</dd></div>
                        <div><dt>Doğrulanmamış e-posta</dt><dd>{{ number_format($operations['members']['unverified'], 0, ',', '.') }}</dd></div>
                        <div><dt>Askıya alınmış hesap</dt><dd>{{ number_format($operations['members']['suspended'], 0, ',', '.') }}</dd></div>
                        <div><dt>Silme talebi</dt><dd>{{ number_format($operations['members']['deletion_requests'], 0, ',', '.') }}</dd></div>
                        <div><dt>Açık veri dışa aktarma</dt><dd>{{ number_format($operations['members']['export_requests'], 0, ',', '.') }}</dd></div>
                    </dl>
                </article>

                <article class="hc-dashboard-operation">
                    <div class="flex items-center justify-between gap-3"><div><p class="hc-kicker">İLETİŞİM VE KURTARMA</p><h2 class="hc-heading-4 mt-1">İş akışı sağlığı</h2></div><a href="{{ route('admin.contact-requests.index') }}" class="hc-caption font-bold" style="color:var(--hc-text-accent)">Talepleri aç</a></div>
                    <dl class="hc-admin-operation-list mt-5">
                        <div><dt>Yeni iletişim talebi</dt><dd>{{ number_format($operations['communication']['new_contact_requests'], 0, ',', '.') }}</dd></div>
                        <div><dt>Açık iletişim talebi</dt><dd>{{ number_format($operations['communication']['open_contact_requests'], 0, ',', '.') }}</dd></div>
                        <div><dt>Bekleyen e-posta işi</dt><dd>{{ number_format($operations['communication']['pending_mail_jobs'], 0, ',', '.') }}</dd></div>
                        <div><dt>Başarısız e-posta işi</dt><dd>{{ number_format($operations['communication']['failed_mail_jobs'], 0, ',', '.') }}</dd></div>
                        <div><dt>Son geri yükleme testi</dt><dd>{{ $operations['restore']['status'] ? $statusLabel($operations['restore']['status']) : 'Kayıt yok' }}</dd></div>
                        <div><dt>Geri yükleme zamanı</dt><dd>{{ $operations['restore']['finished_at'] ? \Illuminate\Support\Carbon::parse($operations['restore']['finished_at'])->translatedFormat('d M Y, H:i') : 'Kayıt yok' }}</dd></div>
                    </dl>
                </article>
                </section>
            <div class="hc-dashboard-secondary-grid">
                <section class="hc-dashboard-demand" aria-labelledby="zero-result-demand-heading">

                    <h2 id="zero-result-demand-heading" class="hc-heading-3 mt-1">Sonuçsuz arama talepleri</h2>
                    <p class="hc-caption mt-1">Ziyaretçinin arayıp bulamadığı terimler ve sonradan karşılandıkları koleksiyon sayfaları.</p>
                    <div class="mt-5 grid gap-6 lg:grid-cols-2">
                        <div>
                            <h3 class="hc-heading-4">Karşılanmamış</h3>
                            @forelse ($zeroResultDemand['open'] as $search)
                                <div class="mt-3 flex items-center justify-between gap-4 border-b pb-3" style="border-color:var(--hc-border-subtle)">
                                    <span class="font-semibold">{{ $search['normalized_query'] }}</span>
                                    <span class="hc-v3-badge hc-v3-badge--warning">{{ (int) $search['search_count'] }} arama</span>
                                </div>
                            @empty
                                <p class="hc-caption mt-3">Açık sonuçsuz arama talebi yok.</p>
                            @endforelse
                        </div>
                        <div>
                            <h3 class="hc-heading-4">Karşılanmış</h3>
                            @forelse ($zeroResultDemand['resolved'] as $search)
                                @php
                                    $hedef = str_starts_with((string) $search['suggested_action'], '/')
                                        ? url($search['suggested_action'])
                                        : null;
                                @endphp
                                <div class="mt-3 border-b pb-3" style="border-color:var(--hc-border-subtle)">
                                    <p class="font-semibold">{{ $search['normalized_query'] }}</p>
                                    @if ($hedef)
                                        <a class="hc-caption font-bold" style="color:var(--hc-text-accent)" href="{{ $hedef }}">{{ $search['suggested_action'] }} →</a>
                                    @else
                                        <span class="hc-caption">Güvenli hedef kaydı yok</span>
                                    @endif
                                </div>
                            @empty
                                <p class="hc-caption mt-3">Henüz bir koleksiyon sayfasıyla karşılanan talep yok.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
                <section class="hc-dashboard-activity">
                    <div class="flex items-center justify-between gap-3"><h2 class="hc-heading-4">Son etkinlikler</h2><a href="{{ route('admin.system-events.index') }}" class="hc-caption font-bold" style="color:var(--hc-text-accent)">Tümü</a></div>
                    @if (count($activity))
                        <ol class="mt-4 space-y-4">
                            @foreach ($activity as $item)
                                <li class="hc-admin-activity"><span class="hc-admin-activity__marker" aria-hidden="true"></span><div><strong>{{ $item['action'] }}</strong>@if(($item['repeat'] ?? 1) > 1)<span class="hc-v3-badge ml-2">{{ $item['repeat'] }}×</span>@endif<p>{{ $item['actor'] }} · {{ $item['at'] }}</p>@if($item['entity_public_id'])<code>{{ $item['entity_public_id'] }}</code>@endif</div></li>
                            @endforeach
                        </ol>
                    @else
                        <div class="hc-v3-empty mt-4 py-7"><p class="font-bold">Etkinlik kaydı yok</p><p class="hc-caption mt-1">Son işlemler burada listelenecek.</p></div>
                    @endif
                </section>
            </div>
        </section>

        <section id="quality" class="hc-dashboard-section" aria-label="Kalite ve sistem">
            <div class="hc-dashboard-quality-system">
                <article class="hc-dashboard-quality">
                <div class="flex flex-wrap items-start justify-between gap-4"><div><h2 class="hc-heading-3 mt-1">Kalite skorları</h2></div><a href="{{ route('admin.data-quality.index') }}" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Veri kalitesine git</a></div>
                @if ($qualityScanned)
                    @if ($qualityScannedAt)
                        <p class="mt-2 text-xs" style="color: var(--hc-text-muted)">Son tarama: {{ \Illuminate\Support\Carbon::parse($qualityScannedAt)->diffForHumans() }}</p>
                    @endif
                    @php
                        // Panelde on kategori aynı ağırlıkta sıralanıyordu ve asıl
                        // sayı olan "Genel" en altta, diğerleriyle aynı boyutta
                        // duruyordu. Bakan kişi hangisine bakacağını bilmiyordu.
                        // Genel skor öne alındı; kalanlar düşükten yükseğe sıralandı,
                        // çünkü ekranın işi neyin eksik olduğunu göstermek.
                        $genel = $qualityScores['overall'] ?? null;
                        $kategoriler = collect($qualityScores)->except('overall')->sort();
                        $etiket = fn ($k) => config('data_quality.categories.'.$k.'.label')
                            ?: ucfirst(str_replace('_', ' ', $k));
                    @endphp

                    @if ($genel !== null)
                        <div class="mt-6 flex items-end gap-4">
                            <span class="hc-dashboard-quality-total" style="color: var(--hc-text-primary)">%{{ $genel }}</span>
                            <span class="hc-body-sm pb-1" style="color: var(--hc-text-secondary)">genel kalite</span>
                        </div>
                            <div @class(['hc-admin-progress mt-3', 'is-dusuk' => $genel < 40, 'is-orta' => $genel >= 40 && $genel < 90]) role="presentation">
                            <span style="width: {{ $genel }}%"></span>
                        </div>
                    @endif


                    <div class="hc-admin-quality-grid mt-3">
                        @foreach ($kategoriler as $key => $score)
                            @php($olculuyor = $score !== null)
                            @php($durum = ! $olculuyor ? 'is-olculmedi' : ($score < 40 ? 'is-dusuk' : ($score < 90 ? 'is-orta' : 'is-tamam')))
                            <div>
                                <div class="flex items-center justify-between gap-3">
                                    <span>{{ $etiket($key) }}</span>
                                    <strong @class(['hc-quality-score', $durum])>{{ $olculuyor ? '%'.$score : 'ölçülmedi' }}</strong>
                                </div>
                                <div @class(['hc-admin-progress mt-2', $durum]) role="presentation">
                                    @if($olculuyor)<span style="width: {{ $score }}%"></span>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="hc-dashboard-empty mt-5" style="border-color: var(--hc-border-subtle)">
                        <p class="font-bold">Kalite taraması henüz çalıştırılmadı</p>
                        <p class="hc-caption mt-2">İlk tarama tamamlandığında kalite sonuçları burada görünecek.</p>
                    </div>
                @endif
            </article>
                <section aria-labelledby="system-heading">
                    <div class="hc-dashboard-section-heading"><h2 id="system-heading">Sistem durumu</h2><a href="{{ route('admin.system-events.index') }}">Olayları aç <x-ui.icon name="arrow-right" size="15" /></a></div>
                    <dl class="hc-dashboard-health">
                        @foreach ($healthSignals as $signal)
                            <div><dt>{{ $signal['label'] }}</dt><dd class="hc-dashboard-health__status hc-dashboard-health__status--{{ $statusTone((string) $signal['status']) }}">{{ $statusLabel((string) $signal['status']) }}</dd></div>
                        @endforeach
                        <div><dt>Son yedek</dt><dd>{{ $system['backup'] ? \Illuminate\Support\Carbon::parse($system['backup']['created_at'])->translatedFormat('d M, H:i') : 'Kayıt yok' }}</dd></div>
                    </dl>
                    <details id="system-status" class="hc-admin-disclosure hc-v3-card">
                    <summary>
                        <span><p class="hc-kicker">OPERASYONEL SAĞLIK</p><h2 class="hc-heading-3 mt-1">Bakım ve bağlantılar</h2><p class="hc-caption mt-1">Kritik çalışma sinyalleri ve bakım araçları.</p></span>
                        <span class="hc-admin-disclosure__hint">Ayrıntılar</span>
                    </summary>
                    <div class="hc-admin-disclosure__body">
                    <div class="flex flex-wrap justify-end gap-2"><a href="{{ route('admin.product-experience.index') }}" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Ürün deneyimi</a><a href="{{ route('admin.system-events.index') }}" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Sistem olayları</a></div>
                    <div class="hc-admin-system-grid mt-5">
                        <div class="hc-admin-system-card"><span>Uygulama</span><strong>{{ $statusLabel((string) ($system['health']['status'] ?? 'never_run')) }}</strong></div>
                        <div class="hc-admin-system-card"><span>İş kuyruğu</span><strong>{{ $statusLabel((string) ($system['queue']['overall_status'] ?? 'never_run')) }}</strong><small>{{ (int) ($system['queue']['pending_jobs'] ?? 0) }} bekleyen · {{ (int) ($system['queue']['failed_jobs'] ?? 0) }} başarısız</small></div>
                        <div class="hc-admin-system-card"><span>Zamanlayıcı</span><strong>{{ $statusLabel((string) ($system['scheduler']['status'] ?? 'never_run')) }}</strong><small>{{ $system['scheduler']['last_task_name'] ?? 'Henüz görev kaydı yok' }}</small></div>
                        <div class="hc-admin-system-card"><span>Yedek</span><strong>{{ $system['backup'] ? 'Kayıtlı' : 'Kayıt yok' }}</strong><small>{{ $system['backup']['created_at'] ?? 'Henüz doğrulanabilir yedek yok' }}</small></div>
                        <div class="hc-admin-system-card"><span>Medya</span><strong>{{ (int) $system['media']['failed_processing'] === 0 ? 'Sağlıklı' : 'İnceleme gerekli' }}</strong><small>{{ (int) $system['media']['pending_thumbnails'] }} bekleyen küçük görsel</small></div>
                        <div class="hc-admin-system-card"><span>İstek metrikleri</span><strong>{{ (int) ($system['requestMetrics']['requests'] ?? 0) }} istek</strong><small>{{ (int) ($system['requestMetrics']['errors'] ?? 0) }} hata · {{ $system['requestMetrics']['average_duration_ms'] ?? '—' }} ms</small></div>
                        <div class="hc-admin-system-card"><span>Sürüm durumu</span><strong>{{ $system['production']['release_state'] ?? 'development' }}</strong><small>{{ $system['production']['environment'] ?? 'local' }} · ortam hazırlığı: {{ $system['production']['environment_readiness'] ?? 'not_configured' }}</small></div>
                        <div class="hc-admin-system-card"><span>Servis bağlantıları</span><strong>Mail: {{ $statusLabel((string) ($system['production']['mail'] ?? 'not_configured')) }}</strong><small>Güvenlik: {{ $statusLabel((string) ($system['production']['security'] ?? 'not_configured')) }} · Analytics: {{ $system['production']['analytics'] ?? 'not_configured' }} · Search Console: {{ $system['production']['search_console'] ?? 'not_configured' }}</small></div>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <form method="post" action="{{ route('admin.system-status.refresh-queue') }}">@csrf<button class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm"><x-ui.icon name="refresh" size="15" />İş kuyruğunu yenile</button></form>
                        <form method="post" action="{{ route('admin.system-status.refresh-scheduler') }}">@csrf<button class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm"><x-ui.icon name="refresh" size="15" />Zamanlayıcıyı yenile</button></form>
                        <form method="post" action="{{ route('admin.system-status.refresh-cache') }}">@csrf<button class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm"><x-ui.icon name="refresh" size="15" />Önbelleği yenile</button></form>
                    </div></div>
                </details>
                </section>
            </div>
            <section class="hc-dashboard-audits" aria-labelledby="otonom-denetim-baslik">

                    <h2 id="otonom-denetim-baslik" class="hc-heading-3 mt-1">Sitenin kendini denetlemesi</h2>
                    <p class="hc-caption mt-1">Son denetim sonuçları ve önceki koşuya göre değişim.</p>

                    <div class="hc-dashboard-audit-grid">
                        @foreach ($integrity as $denetim)
                            <div class="hc-admin-system-card">
                                <span>{{ $denetim['baslik'] }}</span>

                                @if ($denetim['durum'] === 'hic_kosmadi')
                                    <strong>Henüz koşmadı</strong>
                                    <small>İlk koşusunu bekliyor</small>
                                @else
                                    <strong>{{ $denetim['sayi'] === null ? '—' : $denetim['sayi'] }} {{ $denetim['birim'] }}</strong>
                                    <small>
                                        @if ($denetim['fark'] === null)
                                            İlk koşu · karşılaştırılacak önceki koşu yok
                                        @elseif ($denetim['fark'] < 0)
                                            ▼ {{ abs($denetim['fark']) }} azaldı
                                        @elseif ($denetim['fark'] > 0)
                                            ▲ {{ $denetim['fark'] }} arttı
                                        @else
                                            Değişmedi
                                        @endif
                                        · {{ \Illuminate\Support\Carbon::parse($denetim['kosma_zamani'])->diffForHumans() }}
                                    </small>
                                    @if (! empty($denetim['sebep']))
                                        <small class="hc-admin-system-card__sebep">{{ $denetim['sebep'] }}</small>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
        </section>

        <section id="audience" class="hc-dashboard-section" aria-label="Kitle">
            <x-admin.world-activity-map :map="$audienceMap" :period-label="$audienceMap['periodLabel'] ?? $periodLabel" :periods="$audienceMapPeriods ?? []" :dashboard-period="$period" />
        </section>
    </div>
</x-layouts.site>
