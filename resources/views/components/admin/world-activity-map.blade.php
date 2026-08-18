@props(['map', 'periodLabel'])

<article class="hc-admin-world-map hc-v3-card p-5 sm:p-6" aria-labelledby="world-activity-heading">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="hc-kicker">GİZLİLİK KORUMALI ÜYE ETKİNLİĞİ</p>
            <h2 id="world-activity-heading" class="hc-heading-3 mt-1">Ülke bazlı hareketlilik</h2>
            <p class="hc-caption mt-2 max-w-2xl" style="color:var(--hc-text-muted)">Seçilen dönemde etkin olan üyelerin profil ülkeleri toplulaştırılır. Kişi, IP adresi ve şehir gösterilmez.</p>
        </div>
        <span class="hc-v3-badge hc-v3-badge--accent">{{ $periodLabel }}</span>
    </div>

    @if (($map['data_status'] ?? 'empty') === 'available')
        <div class="hc-world-map__canvas mt-5">
            <svg class="hc-world-map__svg" viewBox="0 0 960 430" role="img" aria-labelledby="world-activity-map-title world-activity-map-description">
                <title id="world-activity-map-title">Etkin üye ülkeleri için şematik dünya haritası</title>
                <desc id="world-activity-map-description">{{ $map['summary'] }}</desc>
                <defs>
                    <linearGradient id="worldMapSurface" x1="0" x2="1" y1="0" y2="1"><stop stop-color="var(--hc-background-subtle)"/><stop offset="1" stop-color="var(--hc-surface-raised)"/></linearGradient>
                    <filter id="worldMapGlow" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="6"/></filter>
                </defs>
                <rect width="960" height="430" rx="24" fill="url(#worldMapSurface)"/>
                <g class="hc-world-map__grid" aria-hidden="true">
                    <path d="M0 86H960M0 172H960M0 258H960M0 344H960M160 0V430M320 0V430M480 0V430M640 0V430M800 0V430"/>
                </g>
                <g class="hc-world-map__land" aria-hidden="true">
                    <path d="M96 114c34-35 104-43 155-11 28 18 39 45 25 67-17 27-54 18-78 43-24 25-17 56-49 57-31 1-46-28-40-52 7-28-35-57-13-104Z"/>
                    <path d="M279 260c20-20 53-22 70 1 12 16 3 37-4 57-10 28-13 78-39 86-25 7-42-27-36-55 5-23-11-47 9-89Z"/>
                    <path d="M437 116c45-32 109-37 157-7 35 22 48 50 77 60 34 12 82-4 106 23 26 29 4 62-33 66-34 3-53-22-84-13-31 9-32 56-70 67-49 15-70-26-105-43-37-18-81-4-91-34-10-28 17-50 43-69Z"/>
                    <path d="M523 270c32-10 66 0 81 26 11 19 4 48-13 66-20 22-54 25-73 5-19-20-22-48 5-97Z"/>
                    <path d="M742 297c48-29 112-17 132 20 18 34-7 76-49 82-43 6-77-23-90-60-7-21-11-30 7-42Z"/>
                </g>
                <g aria-hidden="true">
                    @foreach ($map['countries'] as $index => $country)
                        @php
                            $controlX = (int) (($country['x'] + 546) / 2);
                            $controlY = max(48, (int) (min($country['y'], 222) - 62));
                        @endphp
                        @if ($country['country_code'] !== 'CY')
                            <path class="hc-world-map__route" style="--route-delay: {{ $index * 0.42 }}s" d="M{{ $country['x'] }} {{ $country['y'] }} Q {{ $controlX }} {{ $controlY }} 546 222"/>
                        @endif
                    @endforeach
                </g>
                <g aria-hidden="true">
                    @foreach ($map['countries'] as $index => $country)
                        <circle class="hc-world-map__pulse" style="--pulse-delay: {{ $index * 0.42 }}s" cx="{{ $country['x'] }}" cy="{{ $country['y'] }}" r="10"/>
                        <circle class="hc-world-map__point" cx="{{ $country['x'] }}" cy="{{ $country['y'] }}" r="4.5"/>
                    @endforeach
                    <circle class="hc-world-map__hub-glow" cx="546" cy="222" r="16"/>
                    <circle class="hc-world-map__hub" cx="546" cy="222" r="6"/>
                </g>
            </svg>
        </div>

        <div class="hc-world-map__summary mt-5">
            <p><strong>{{ number_format($map['total_members'], 0, ',', '.') }}</strong> etkin üye · <strong>{{ count($map['countries']) }}</strong> ülke</p>
            <p>Harita şematiktir; yalnız ülke toplamlarını gösterir.</p>
        </div>
        <ul class="hc-world-map__countries mt-4" aria-label="Ülke bazlı etkin üye toplamları">
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

    <p class="hc-caption mt-4" style="color:var(--hc-text-muted)">Kaynak: uygulamadaki etkin üye profil ülkeleri. Google Analytics ziyaretçi raporu değildir.</p>
</article>
