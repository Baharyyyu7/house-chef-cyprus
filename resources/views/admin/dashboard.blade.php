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
                {{-- Üst çubukta zaten "Yönetim / Genel Bakış" breadcrumb'ı var;
                     aynı satırı burada tekrarlamak başlığı geriye itiyordu. --}}
                <h1 class="hc-heading-1">İçerik ve operasyon özeti</h1>
                <p class="hc-body-sm mt-3 max-w-2xl" style="color:var(--hc-text-secondary)">Tüm sayılar, seçilen dönem ve mevcut kayıtlar üzerinden hesaplanır. Veri oluşmamış alanlar açıkça belirtilir.</p>
            </div>
            <div class="hc-admin-periods" role="group" aria-label="Dashboard dönemi">
                @foreach ($periods as $days => $label)
                    <a href="{{ route('admin.dashboard', ['period' => $days]) }}" @class(['hc-admin-period', 'hc-admin-period--active' => $period === $days]) aria-current="{{ $period === $days ? 'true' : 'false' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        <p class="hc-caption mt-4" style="color:var(--hc-text-muted)">Güncelleme: {{ \Illuminate\Support\Carbon::parse($updatedAt)->translatedFormat('d M Y, H:i') }} · {{ $periodLabel }}</p>

        {{-- Bu bölüm sağ sütunun altındaydı: sayfanın tek eyleme dönüşebilir
             bilgisi, kimsenin bakmadığı yerde duruyordu. Bir şey bozuksa
             operatör onu sayılardan önce görmeli. --}}
        @if (count($attention))
            <section class="mt-6" aria-labelledby="attention-heading">
                <div class="flex items-center gap-3">
                    <h2 id="attention-heading" class="hc-heading-4">Dikkat gerekenler</h2>
                    <span class="hc-v3-badge hc-v3-badge--warning">{{ count($attention) }}</span>
                </div>
                <div class="mt-3 grid gap-3 lg:grid-cols-2">
                    @foreach ($attention as $item)
                        <a href="{{ $item['url'] ?? route($item['route']) }}" class="hc-admin-attention hc-admin-attention--{{ $item['severity'] }}"><strong>{{ $item['label'] }}</strong><span>{{ $item['detail'] }}</span></a>
                    @endforeach
                </div>
            </section>
        @endif

        @php
            // Operatörün üzerine eylem aldığı metrikler önde; geri kalanı referans şeridi.
            $birincilAnahtarlar = ['yayinda', 'inceleme', 'failed_jobs', 'kullanicilar'];
            $kartlar = collect($cards)->keyBy('key');
            $birincil = collect($birincilAnahtarlar)->map(fn ($k) => $kartlar->get($k))->filter()->values();
            $ikincil = $kartlar->reject(fn ($c) => in_array($c['key'], $birincilAnahtarlar, true))->values();
        @endphp

        <section class="hc-admin-primary mt-7" aria-label="Öncelikli metrikler">
            @foreach ($birincil as $card)
                @php
                    $deger = $card['data_status'] === 'available' ? (float) $card['current_value'] : null;
                    $dikkat = $deger !== null && $deger > 0 && in_array($card['key'], ['inceleme', 'failed_jobs'], true);
                @endphp
                <article @class(['hc-admin-primary__card', 'hc-admin-primary__card--attention' => $dikkat]) aria-label="{{ $card['label'] }}: {{ $card['current_value'] ?? 'veri yok' }}{{ $card['suffix'] }}">
                    <p class="hc-admin-primary__label">{{ $card['label'] }}</p>
                    @if ($deger !== null)
                        <strong class="hc-admin-primary__value">{{ number_format($deger, 0, ',', '.') }}{{ $card['suffix'] }}</strong>
                    @else
                        <strong class="hc-admin-primary__value hc-admin-primary__value--empty">—</strong>
                    @endif
                    <p class="hc-admin-primary__note">{{ $card['description'] }}</p>
                    @if ($card['percentage_change'] !== null)
                        <span @class(['hc-admin-trend', 'hc-admin-trend--inverse' => $card['inverse']])>{{ $card['trend_direction'] === 'up' ? '↑' : ($card['trend_direction'] === 'down' ? '↓' : '·') }} %{{ number_format(abs($card['percentage_change']), 1, ',', '.') }}</span>
                    @endif
                </article>
            @endforeach
        </section>

        <section class="hc-admin-reference mt-4" aria-label="Referans sayılar">
            @foreach ($ikincil as $card)
                <div class="hc-admin-reference__item">
                    <span class="hc-admin-reference__label">{{ $card['label'] }}</span>
                    <strong class="hc-admin-reference__value">{{ $card['data_status'] === 'available' ? number_format((float) $card['current_value'], 0, ',', '.').$card['suffix'] : '—' }}</strong>
                </div>
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

                <details id="system-status" class="hc-admin-disclosure hc-v3-card">
                    <summary>
                        <span><p class="hc-kicker">OPERASYONEL SAĞLIK</p><h2 class="hc-heading-3 mt-1">Sistem durumu</h2><p class="hc-caption mt-1">Kritik çalışma sinyalleri ve bakım araçları.</p></span>
                        <span class="hc-admin-disclosure__hint">Ayrıntılar</span>
                    </summary>
                    <div class="hc-admin-disclosure__body">
                    <div class="flex flex-wrap justify-end gap-2"><a href="{{ route('admin.product-experience.index') }}" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Ürün deneyimi</a><a href="{{ route('admin.system-events.index') }}" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Sistem olayları</a></div>
                    <div class="hc-admin-system-grid mt-5">
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
                    </div></div>
                </details>

                <details class="hc-admin-disclosure hc-v3-card">
                    <summary>
                        <span><p class="hc-kicker">OPERASYON KUYRUKLARI</p><h2 class="hc-heading-3 mt-1">Ayrıntılı iş akışı verileri</h2><p class="hc-caption mt-1">İçerik, üye ve iletişim operasyonlarının detayları.</p></span>
                        <span class="hc-admin-disclosure__hint">Ayrıntılar</span>
                    </summary>
                    <section class="hc-admin-disclosure__body grid gap-6 lg:grid-cols-3" aria-label="Operasyon kuyrukları">
                    <article class="hc-v3-card p-5">
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

                    <article class="hc-v3-card p-5">
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

                    <article class="hc-v3-card p-5">
                        <div class="flex items-center justify-between gap-3"><div><p class="hc-kicker">İLETİŞİM VE KURTARMA</p><h2 class="hc-heading-4 mt-1">İş akışı sağlığı</h2></div><a href="{{ route('admin.contact-requests.index') }}" class="hc-caption font-bold" style="color:var(--hc-text-accent)">Talepleri aç</a></div>
                        <dl class="hc-admin-operation-list mt-5">
                            <div><dt>Yeni iletişim talebi</dt><dd>{{ number_format($operations['communication']['new_contact_requests'], 0, ',', '.') }}</dd></div>
                            <div><dt>Açık iletişim talebi</dt><dd>{{ number_format($operations['communication']['open_contact_requests'], 0, ',', '.') }}</dd></div>
                            <div><dt>Bekleyen e-posta işi</dt><dd>{{ number_format($operations['communication']['pending_mail_jobs'], 0, ',', '.') }}</dd></div>
                            <div><dt>Başarısız e-posta işi</dt><dd>{{ number_format($operations['communication']['failed_mail_jobs'], 0, ',', '.') }}</dd></div>
                            <div><dt>Son restore testi</dt><dd>{{ $operations['restore']['status'] ? $statusLabel($operations['restore']['status']) : 'Kayıt yok' }}</dd></div>
                            <div><dt>Restore tamamlanma</dt><dd>{{ $operations['restore']['finished_at'] ? \Illuminate\Support\Carbon::parse($operations['restore']['finished_at'])->translatedFormat('d M Y, H:i') : 'Kayıt yok' }}</dd></div>
                        </dl>
                    </article>
                    </section>
                </details>

                <article class="hc-v3-card p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="hc-kicker">VERİ GÜVENİ</p><h2 class="hc-heading-3 mt-1">Kalite skorları</h2></div><a href="{{ route('admin.data-quality.index') }}" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Veri kalitesine git</a></div>
                    @if ($qualityScanned)
                        @if ($qualityScannedAt)
                            <p class="mt-2 text-xs" style="color: var(--hc-text-muted)">Son tarama: {{ \Illuminate\Support\Carbon::parse($qualityScannedAt)->diffForHumans() }}</p>
                        @endif
                        <div class="hc-admin-quality-grid mt-6">
                            @foreach ($qualityScores as $key => $score)
                                <div><div class="flex items-center justify-between gap-3"><span>{{ config('data_quality.categories.'.$key.'.label', $key === 'overall' ? 'Genel' : ucfirst(str_replace('_', ' ', $key))) }}</span><strong>{{ $score }}%</strong></div><div class="hc-admin-progress mt-2" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $score }}"><span style="width: {{ max(0, min(100, (int) $score)) }}%"></span></div></div>
                            @endforeach
                        </div>
                    @else
                        {{-- Skorlar açık issue cezasından türetilir: tarama hiç çalışmadıysa
                             ceza da olmaz ve her kategori %100 çıkar. Bu sayıyı göstermek
                             "her şey yolunda" demek olur; oysa henüz bakılmamıştır. --}}
                        <div class="mt-6 rounded-2xl border border-dashed p-5" style="border-color: var(--hc-border-subtle)">
                            <p class="font-bold">Kalite taraması henüz çalıştırılmadı</p>
                            <p class="mt-2 text-sm leading-6" style="color: var(--hc-text-secondary)">
                                Skorlar açık bulgu kayıtlarından hesaplanır. Tarama çalışmadığı için kayıt yok; bu yüzden burada yüzde göstermiyoruz. Tarama çalıştığında kategori skorları gerçek bulgularla dolar.
                            </p>
                            <code class="mt-4 inline-block rounded-lg px-3 py-2 text-xs" style="background: var(--hc-surface-muted)">php artisan data:quality-scan</code>
                        </div>
                    @endif
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

                <section class="hc-v3-card p-5">
                    <div class="flex items-center justify-between gap-3"><h2 class="hc-heading-4">Son etkinlikler</h2><a href="{{ route('admin.system-events.index') }}" class="hc-caption font-bold" style="color:var(--hc-text-accent)">Tümü</a></div>
                    @if (count($activity))
                        <ol class="mt-4 space-y-4">
                            @foreach ($activity as $item)
                                <li class="hc-admin-activity"><span class="hc-admin-activity__marker" aria-hidden="true"></span><div><strong>{{ $item['action'] }}</strong>@if(($item['repeat'] ?? 1) > 1)<span class="hc-v3-badge ml-2">{{ $item['repeat'] }}×</span>@endif<p>{{ $item['actor'] }} · {{ $item['at'] }}</p>@if($item['entity_public_id'])<code>{{ $item['entity_public_id'] }}</code>@endif</div></li>
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
