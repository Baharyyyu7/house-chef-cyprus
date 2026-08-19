@props(['map', 'periodLabel'])

<article class="hc-admin-world-map hc-v3-card p-5 sm:p-6" aria-labelledby="world-activity-heading">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="hc-kicker">GİZLİLİK KORUMALI ÜYE ETKİNLİĞİ</p>
            <h2 id="world-activity-heading" class="hc-heading-3 mt-1">Ülke bazlı hareketlilik</h2>
            <p class="hc-caption mt-2 max-w-2xl" style="color:var(--hc-text-muted)">{{ $map['source'] === 'visitor_country_aggregate' ? 'Seçilen dönemde gelen ziyaretlerin ülkeleri toplulaştırılır. Kişi, IP adresi, şehir ve oturum bilgisi tutulmaz.' : 'Seçilen dönemde etkin olan üyelerin profil ülkeleri toplulaştırılır. Kişi, IP adresi ve şehir gösterilmez.' }}</p>
        </div>
        <span class="hc-v3-badge hc-v3-badge--accent">{{ $periodLabel }}</span>
    </div>

    @if (($map['data_status'] ?? 'empty') === 'available')
        <div class="hc-world-map__canvas mt-5">
            <svg class="hc-world-map__svg" viewBox="0 18 960 344" role="img" aria-labelledby="world-activity-map-title world-activity-map-description">
                <title id="world-activity-map-title">Etkin üye ülkeleri için şematik dünya haritası</title>
                <desc id="world-activity-map-description">{{ $map['summary'] }}</desc>
                <defs>
                    <linearGradient id="worldMapSurface" x1="0" x2="1" y1="0" y2="1"><stop stop-color="var(--hc-background-subtle)"/><stop offset="1" stop-color="var(--hc-surface-raised)"/></linearGradient>
                    <filter id="worldMapGlow" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="6"/></filter>
                </defs>
                <rect x="0" y="18" width="960" height="344" rx="24" fill="url(#worldMapSurface)"/>
                {{-- Kıtalar elle çizilmiş beş serbest şekildi ve hiçbirine
                     benzemiyordu. Artık gerçek sınır verisinden üretilen nokta
                     ızgarası: kaynak Natural Earth 110m, kamu malı. --}}
                <x-admin.world-map-dots />
                <g aria-hidden="true">
                    @foreach ($map['countries'] as $index => $country)
                        @php
                            $controlX = (int) (($country['x'] + 569) / 2);
                            $controlY = max(26, (int) (min($country['y'], 131) - 46));
                        @endphp
                        @if ($country['country_code'] !== 'CY')
                            <path class="hc-world-map__route" style="--route-delay: {{ $index * 0.42 }}s" d="M{{ $country['x'] }} {{ $country['y'] }} Q {{ $controlX }} {{ $controlY }} 569 131"/>
                        @endif
                    @endforeach
                </g>
                <g aria-hidden="true">
                    @foreach ($map['countries'] as $index => $country)
                        <circle class="hc-world-map__pulse" style="--pulse-delay: {{ $index * 0.42 }}s" cx="{{ $country['x'] }}" cy="{{ $country['y'] }}" r="10"/>
                        <circle class="hc-world-map__point" cx="{{ $country['x'] }}" cy="{{ $country['y'] }}" r="4.5"/>
                    @endforeach
                    <circle class="hc-world-map__hub-glow" cx="569" cy="131" r="16"/>
                    <circle class="hc-world-map__hub" cx="569" cy="131" r="6"/>
                </g>
            </svg>
        </div>

        <div class="hc-world-map__summary mt-5">
            <p><strong>{{ number_format($map['total_members'], 0, ',', '.') }}</strong> {{ $map['source'] === 'visitor_country_aggregate' ? 'ziyaret' : 'etkin üye' }} · <strong>{{ count($map['countries']) }}</strong> ülke</p>
            <p>Harita şematiktir; yalnız ülke toplamlarını gösterir.</p>
        </div>
        <ul class="hc-world-map__countries mt-4" aria-label="Ülke bazlı toplamlar">
            @foreach ($map['countries'] as $country)
                <li><span class="hc-world-map__country-dot" aria-hidden="true"></span><span>{{ $country['label'] }}</span><strong>{{ number_format((int) $country['members'], 0, ',', '.') }}</strong></li>
            @endforeach
        </ul>
    @else
        <div class="hc-v3-empty mt-5 py-9">
            <p class="font-bold">Konum özeti için henüz veri yok</p>
            <p class="hc-caption mt-1">Aktif üye profilinde tanımlı bir ülke olduğunda yalnız toplulaştırılmış sonuçlar burada görünür.</p>
        </div>
    @endif

    <p class="hc-caption mt-4" style="color:var(--hc-text-muted)">{{ $map['source'] === 'visitor_country_aggregate'
    ? 'Kaynak: sitenin kendi ziyaret sayacı. Yalnız gün ve ülke tutulur. Google Analytics raporu değildir; sayılar örtüşmeyebilir.'
    : 'Kaynak: uygulamadaki etkin üye profil ülkeleri. Google Analytics ziyaretçi raporu değildir.' }}</p>
</article>
