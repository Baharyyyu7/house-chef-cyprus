@props(['map', 'periodLabel'])

<article class="hc-admin-world-map hc-v3-card p-5 sm:p-6" aria-labelledby="world-activity-heading">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="hc-kicker">{{ $map['source'] === 'visitor_country_aggregate' ? 'GİZLİLİK KORUMALI ZİYARET ÖZETİ' : 'GİZLİLİK KORUMALI ÜYE ETKİNLİĞİ' }}</p>
            <h2 id="world-activity-heading" class="hc-heading-3 mt-1">Ülke bazlı hareketlilik</h2>
            <p class="hc-caption mt-2 max-w-2xl" style="color:var(--hc-text-muted)">{{ $map['source'] === 'visitor_country_aggregate' ? 'Seçilen dönemde gelen ziyaretlerin ülkeleri toplulaştırılır. Kişi, IP adresi, şehir ve oturum bilgisi tutulmaz.' : 'Seçilen dönemde etkin olan üyelerin profil ülkeleri toplulaştırılır. Kişi, IP adresi ve şehir gösterilmez.' }}</p>
        </div>
        <span class="hc-v3-badge hc-v3-badge--accent">{{ $periodLabel }}</span>
    </div>

    @if (($map['data_status'] ?? 'empty') === 'available')
        @php
            // Boyama yoğunluğu en yüksek ülkeye göre ölçeklenir: mutlak sayıya
            // göre boyamak, 300 ziyaretli bir ülke varken 1 ziyaretli ülkeyi
            // görünmez yapıyordu.
            $enYuksek = max(1, (int) collect($map['countries'])->max('members'));
            $ulkeVerisi = collect($map['countries'])->keyBy('country_code');
        @endphp

        <div
            class="hc-world-map__canvas mt-5"
            x-data="{
                ulke: null, x: 0, y: 0,
                konumla(olay) {
                    const kutu = $el.getBoundingClientRect();
                    this.x = olay.clientX - kutu.left;
                    this.y = olay.clientY - kutu.top;
                },
            }"
            @mousemove="konumla($event)"
            @mouseleave="ulke = null"
        >
            <svg class="hc-world-map__svg" viewBox="0 18 960 344" role="img" aria-labelledby="world-activity-map-title world-activity-map-description">
                <title id="world-activity-map-title">Ziyaret gelen ülkeler için dünya haritası</title>
                <desc id="world-activity-map-description">{{ $map['summary'] }}</desc>
                <defs>
                    <linearGradient id="worldMapSurface" x1="0" x2="1" y1="0" y2="1"><stop stop-color="var(--hc-background-subtle)"/><stop offset="1" stop-color="var(--hc-surface-raised)"/></linearGradient>
                    {{-- Kutup bölgeleri kırpılır. Koordinatları sınıra sıkıştırmak
                         düz kenarlar üretip haritanın üstünde ve altında gri
                         bantlar bırakıyordu; kırpma burada yapılınca geometri
                         olduğu gibi kalır. --}}
                    <clipPath id="worldMapClip"><rect x="0" y="18" width="960" height="344" rx="24"/></clipPath>
                </defs>
                <rect x="0" y="18" width="960" height="344" rx="24" fill="url(#worldMapSurface)"/>

                {{-- Ülkeler kendi sınırlarıyla çizilir; ziyaret geleni yoğunluğa
                     göre boyanır. Önce yalnız birer nokta konuyordu, ülkenin
                     kendisi seçilemiyordu. --}}
                <g clip-path="url(#worldMapClip)">
                    <x-admin.world-map-countries />

                {{-- Dinleyici tek yerde: <use> öğelerinde mouseenter gölge ağaç
                     yüzünden güvenilir tetiklenmiyor, mouseover ise kabarıyor. --}}
                </g>

                <g clip-path="url(#worldMapClip)" class="hc-world-map__veri" @mouseover="const h = $event.target.closest('[data-ad]'); if (h) { konumla($event); ulke = { ad: h.dataset.ad, sayi: Number(h.dataset.sayi) } }">
                    @foreach ($map['countries'] as $country)
                        @php
                            $oran = min(1, (int) $country['members'] / $enYuksek);
                            // En küçük değer de görünsün diye taban opaklık var.
                            $opaklik = round(0.28 + ($oran * 0.62), 2);
                        @endphp
                        <use
                            class="hc-world-map__ulke-aktif"
                            href="#ulke-{{ $country['country_code'] }}"
                            style="opacity: {{ $opaklik }}"
                            data-ad="{{ $country['label'] }}"
                            data-sayi="{{ (int) $country['members'] }}"
                            tabindex="0"
                            role="img"
                            aria-label="{{ $country['label'] }}: {{ number_format((int) $country['members'], 0, ',', '.') }}"
                            @focus="ulke = { ad: @js($country['label']), sayi: @js((int) $country['members']) }"
                        ></use>
                    @endforeach
                </g>

                <g aria-hidden="true">
                    @foreach ($map['countries'] as $index => $country)
                        <circle class="hc-world-map__pulse" style="--pulse-delay: {{ $index * 0.42 }}s" cx="{{ $country['x'] }}" cy="{{ $country['y'] }}" r="9"/>
                        <circle class="hc-world-map__point" cx="{{ $country['x'] }}" cy="{{ $country['y'] }}" r="3.5"/>
                    @endforeach
                </g>
            </svg>

            {{-- İpucu imlecin yanında durur; Google Analytics'te olduğu gibi
                 ülkeye yaklaşınca adı ve sayısı okunur. --}}
            <div
                x-cloak
                x-show="ulke"
                class="hc-world-map__ipucu"
                :style="`left:${x}px; top:${y}px`"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                aria-hidden="true"
            >
                <strong x-text="ulke?.ad"></strong>
                <span x-text="ulke ? new Intl.NumberFormat('tr-TR').format(ulke.sayi) + ' ziyaret' : ''"></span>
            </div>
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
