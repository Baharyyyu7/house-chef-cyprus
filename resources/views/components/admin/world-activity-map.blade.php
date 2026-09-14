@props(['map', 'periodLabel' => null, 'periods' => [], 'dashboardPeriod' => null])
@php
    $visits = $map['source'] === 'visitor_country_aggregate';
    $unit = $visits ? 'ziyaret' : 'etkin üye';
    $available = ($map['data_status'] ?? 'empty') === 'available';
    $countries = $available ? ($map['countries'] ?? []) : [];
    $enYuksek = max(1, (int) collect($countries)->max('members'));
    $gorus = \App\Support\WorldMapCountries::VIEWBOX;
    $aktifDonem = $map['period'] ?? null;
    $listeSiniri = 12;
    $listelenen = array_slice($countries, 0, $listeSiniri);
    $kalanUlke = max(0, count($countries) - $listeSiniri);
    $sonKayit = $map['last_recorded_day'] ?? null;
    // Sayaç durmuş sayılır: dünden eski son kayıt ya da sabah 06:00'yı geçmiş
    // olduğu hâlde bugün hiç kayıt yok. Yalnız "dünden eski" şartı yetmedi:
    // 14 Eylül 07:00'de son kayıt 13 Eylül'dü (dün), günde ~1.500 ziyaret
    // alan sitede bugün sıfır kayıt vardı ve kart hiçbir şey söylemiyordu.
    // 06:00 payı, gece yarısını yeni geçmiş sakin saatlerde yanlış alarmı önler.
    $sonKayitGunu = $sonKayit !== null ? \Illuminate\Support\Carbon::parse($sonKayit)->startOfDay() : null;
    $sayacDurdu = $sonKayitGunu !== null && (
        $sonKayitGunu->lt(now()->subDay()->startOfDay())
        || ($sonKayitGunu->lt(now()->startOfDay()) && now()->hour >= 6)
    );
    $kutular = collect($countries)
        ->filter(fn (array $country): bool => isset($country['box']))
        ->mapWithKeys(fn (array $country): array => [$country['country_code'] => $country['box']])
        ->all();
@endphp

<article id="ulke-haritasi" class="hc-admin-world-map hc-v3-card p-5 sm:p-6" aria-labelledby="world-activity-heading">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="hc-kicker">{{ $visits ? 'GİZLİLİK KORUMALI ZİYARET ÖZETİ' : 'GİZLİLİK KORUMALI ÜYE ETKİNLİĞİ' }}</p>
            <h2 id="world-activity-heading" class="hc-heading-3 mt-1">Ülke bazlı hareketlilik</h2>
            <p class="hc-caption mt-2 max-w-2xl" style="color:var(--hc-text-muted)">{{ $visits ? 'Seçilen dönemde gelen ziyaretlerin ülkeleri toplulaştırılır. Kişi, IP adresi, şehir ve oturum bilgisi tutulmaz.' : 'Seçilen dönemde etkin olan üyelerin ülkeleri toplulaştırılır. Kişi ve oturum bilgisi gösterilmez.' }}</p>
        </div>

        @if ($periods !== [])
            {{-- Harita dönemi paneldekinden bağımsız. Bağlantı sayfayı
                 haritaya geri kaydırır; yoksa dönem değiştiren kişi sayfanın
                 başına atılır ve haritayı yeniden aramak zorunda kalırdı. --}}
            <nav class="hc-admin-periods hc-world-map__donemler" aria-label="Harita dönemi">
                @foreach ($periods as $gun => $etiket)
                    <a
                        href="{{ route('admin.dashboard', array_filter(['period' => $dashboardPeriod, 'harita' => $gun], fn ($deger) => $deger !== null)) }}#ulke-haritasi"
                        @class(['hc-admin-period', 'hc-admin-period--active' => $aktifDonem === $gun])
                        @if ($aktifDonem === $gun) aria-current="true" @endif
                    >{{ $etiket }}</a>
                @endforeach
            </nav>
        @elseif ($periodLabel)
            <span class="hc-v3-badge hc-v3-badge--accent">{{ $periodLabel }}</span>
        @endif
    </div>

    @if ($sayacDurdu)
        {{-- 12-13 Eylül 2026'da site Cloudflare'den Hostinger CDN'e geçti.
             Sayaç ülkeyi CDN'in eklediği başlıktan okuyor ve yeni CDN bu
             başlığı göndermiyor; kayıt sessizce durdu. Haritanın "7 gün"
             rakamı günlerce eksik göründü ve bunu hiçbir şey söylemedi. --}}
        <div class="hc-world-map__empty mt-4" role="status">
            <x-ui.icon name="warning" size="18" />
            <div>
                <p class="font-semibold">Ülke sayacı {{ \Illuminate\Support\Carbon::parse($sonKayit)->translatedFormat('j F') }} tarihinden beri yeni kayıt almıyor</p>
                <p class="hc-caption mt-1">Rakamlar o güne kadarki ziyaretleri gösterir. Sayaç ülkeyi CDN'in gönderdiği başlıktan okur; site şu an bu başlığı göndermeyen bir CDN üzerinden yayınlanıyor.</p>
            </div>
        </div>
    @endif

    <div
        class="mt-5"
        x-data="{
            ulke: null, sonUlke: null, x: 0, y: 0,
            taban: @js($gorus),
            vb: @js($gorus),
            kutular: @js($kutular),
            animasyon: null,
            surukleme: null,
            surukledi: false,
            get olcek() { return this.vb[2] / this.taban[2]; },
            get yakin() { return this.olcek < 0.98; },
            {{-- Konum her zaman tuvale göre ölçülür. Odak olayında $el
                 odaklanan ülke öğesi olur; ipucu önceden o öğenin içine
                 göre konumlanıp yanlış yere düşüyordu. --}}
            konumla(olay) {
                const kutu = this.$refs.tuval.getBoundingClientRect();
                const hedef = olay.target.getBoundingClientRect();
                const sol = olay.type === 'focus' ? hedef.left + hedef.width / 2 : olay.clientX;
                const ust = olay.type === 'focus' ? hedef.top + hedef.height / 2 : olay.clientY;
                this.x = Math.max(8, Math.min(sol - kutu.left + 12, kutu.width - 184));
                this.y = Math.max(8, Math.min(ust - kutu.top + 12, kutu.height - 76));
            },
            svgNoktasi(cx, cy) {
                const r = this.$refs.svg.getBoundingClientRect();
                return [this.vb[0] + (cx - r.left) / r.width * this.vb[2], this.vb[1] + (cy - r.top) / r.height * this.vb[3]];
            },
            sinirla([x, y, w]) {
                const [tx, ty, tw, th] = this.taban;
                w = Math.min(tw, Math.max(tw / 16, w));
                const h = w * th / tw;
                return [Math.min(tx + tw - w, Math.max(tx, x)), Math.min(ty + th - h, Math.max(ty, y)), w, h];
            },
            {{-- Animasyon her zaman EKRANDAKİ değerden başlar; yarıda
                 yeni bir hedef gelirse sıçramadan oraya döner. --}}
            git(hedef) {
                cancelAnimationFrame(this.animasyon);
                const bas = [...this.vb];
                const son = this.sinirla(hedef);
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { this.vb = son; return; }
                const t0 = performance.now();
                const adim = (t) => {
                    const p = Math.min(1, (t - t0) / 460);
                    const e = 1 - Math.pow(1 - p, 3);
                    this.vb = bas.map((v, i) => v + (son[i] - v) * e);
                    if (p < 1) this.animasyon = requestAnimationFrame(adim);
                };
                this.animasyon = requestAnimationFrame(adim);
            },
            yakinlastir(kat) {
                const [x, y, w, h] = this.vb;
                const cx = x + w / 2, cy = y + h / 2;
                this.git([cx - w / kat / 2, cy - h / kat / 2, w / kat]);
            },
            ulkeyeGit(kod) {
                const b = this.kutular[kod];
                if (! b) return;
                const [, , tw, th] = this.taban;
                const oran = tw / th;
                const w = Math.max(b[2] * 1.9, b[3] * 1.9 * oran, 24);
                const h = w / oran;
                this.git([b[0] + b[2] / 2 - w / 2, b[1] + b[3] / 2 - h / 2, w]);
            },
            sifirla() { this.git([...this.taban]); },
            {{-- Tekerlek sayfayı kaydırmaya devam eder; yalnız Ctrl/Cmd ile
                 ya da dokunmatik yüzeyde iki parmak kıstırma ile
                 yakınlaştırır. Aksi hâlde haritanın üstünden geçen herkes
                 sayfada takılı kalırdı. --}}
            tekerlek(olay) {
                if (! (olay.ctrlKey || olay.metaKey)) return;
                olay.preventDefault();
                cancelAnimationFrame(this.animasyon);
                const kat = Math.exp(-olay.deltaY * 0.01);
                const [cx, cy] = this.svgNoktasi(olay.clientX, olay.clientY);
                const [x, y, w] = this.vb;
                this.vb = this.sinirla([cx - (cx - x) / kat, cy - (cy - y) / kat, w / kat]);
            },
            bas(olay) {
                if (olay.button !== 0) return;
                cancelAnimationFrame(this.animasyon);
                this.surukleme = { id: olay.pointerId, x: olay.clientX, y: olay.clientY, vb: [...this.vb] };
                this.surukledi = false;
            },
            {{-- Harita parmağa yapışık hareket eder: tutulan nokta imlecin
                 altında kalır. 4 piksellik eşik tıklamayı sürüklemeden ayırır. --}}
            kaydir(olay) {
                const s = this.surukleme;
                if (! s || s.id !== olay.pointerId) return;
                const dx = olay.clientX - s.x, dy = olay.clientY - s.y;
                if (! this.surukledi && Math.hypot(dx, dy) < 4) return;
                if (! this.surukledi) { this.surukledi = true; this.ulke = null; this.$refs.svg.setPointerCapture(olay.pointerId); }
                const r = this.$refs.svg.getBoundingClientRect();
                this.vb = this.sinirla([s.vb[0] - dx * s.vb[2] / r.width, s.vb[1] - dy * s.vb[3] / r.height, s.vb[2]]);
            },
            birak() { this.surukleme = null; },
            tikla(olay) {
                if (this.surukledi) { this.surukledi = false; return; }
                const h = olay.target.closest('[data-kod]');
                if (h) this.ulkeyeGit(h.dataset.kod);
            },
        }"
        x-effect="$refs.svg.setAttribute('viewBox', vb.join(' '))"
        x-init="$watch('ulke', (deger) => { if (deger) sonUlke = deger })"
    >
        <div
            x-ref="tuval"
            class="hc-world-map__canvas"
            :class="{ 'is-surukleniyor': surukledi && surukleme }"
            @mousemove="surukleme || konumla($event)"
            @mouseover="const h = $event.target.closest('[data-ad]'); ulke = h && ! surukleme ? { ad: h.dataset.ad, sayi: Number(h.dataset.sayi), pay: Number(h.dataset.pay) } : null"
            @mouseleave="ulke = null"
            @keydown.escape.stop="ulke = null; sifirla()"
        >
            <svg
                x-ref="svg"
                class="hc-world-map__svg"
                style="aspect-ratio: {{ $gorus[2] }} / {{ $gorus[3] }}"
                viewBox="{{ implode(' ', $gorus) }}"
                role="group"
                aria-label="{{ $visits ? 'Ziyaret gelen ülkeler için dünya haritası' : 'Etkin üyelerin ülkeleri için dünya haritası' }}"
                aria-describedby="world-activity-map-description"
                @wheel="tekerlek($event)"
                @pointerdown="bas($event)"
                @pointermove="kaydir($event)"
                @pointerup="birak()"
                @pointercancel="birak()"
                @click="tikla($event)"
                @dblclick.prevent="yakinlastir(2)"
            >
                {{-- <title> tarayıcının kendi gri ipucunu açıyor ve bizim
                     ipucumuzla çakışıyordu; erişilebilirlik aria-label ile
                     karşılanır, ekranda tek bir ipucu kalır. --}}
                <desc id="world-activity-map-description">{{ $map['summary'] }}</desc>

                <x-admin.world-map-countries />

                <g class="hc-world-map__veri">
                    @foreach ($countries as $country)
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
                            data-kod="{{ $country['country_code'] }}"
                            data-sayi="{{ (int) $country['members'] }}"
                            data-pay="{{ $country['share'] ?? '' }}"
                            tabindex="0"
                            role="img"
                            aria-label="{{ $country['label'] }}: {{ number_format((int) $country['members'], 0, ',', '.') }} {{ $unit }}"
                            @focus="ulke = { ad: @js($country['label']), sayi: @js((int) $country['members']), pay: @js($country['share'] ?? null) }; konumla($event)"
                            @blur="ulke = null"
                            @keydown.enter.prevent="ulkeyeGit(@js($country['country_code']))"
                        ></use>
                    @endforeach
                </g>

                <g aria-hidden="true">
                    @foreach (array_slice($countries, 0, $listeSiniri) as $index => $country)
                        <circle class="hc-world-map__pulse" style="--pulse-delay: {{ $index * 0.42 }}s" cx="{{ $country['x'] }}" cy="{{ $country['y'] }}" r="9" :r="9 * olcek"/>
                        <circle class="hc-world-map__point" cx="{{ $country['x'] }}" cy="{{ $country['y'] }}" r="3.5" :r="3.5 * olcek"/>
                    @endforeach
                </g>
            </svg>

            <div class="hc-world-map__kontroller" x-cloak>
                <button type="button" @click="yakinlastir(2)" :disabled="olcek <= 1 / 16 + 0.001" aria-label="Yakınlaştır" title="Yakınlaştır">+</button>
                <button type="button" @click="yakinlastir(0.5)" :disabled="! yakin" aria-label="Uzaklaştır" title="Uzaklaştır">&minus;</button>
                <button type="button" @click="sifirla()" :disabled="! yakin" aria-label="Bütün dünyayı göster" title="Bütün dünyayı göster">
                    <x-ui.icon name="refresh" size="15" />
                </button>
            </div>

            {{-- İpucu imlecin yanında durur; Google Analytics'te olduğu gibi
                 ülkeye yaklaşınca adı ve sayısı okunur.

                 Görünürlük ve içerik ayrı tutulur. Önceden ikisi de `ulke`
                 değişkenine bağlıydı: imleç ayrılınca `ulke` null oluyor,
                 metin anında siliniyor ama x-show/x-transition kutuyu bir
                 kare daha ekranda tutuyordu. Kullanıcı ekranında içi boş
                 beyaz bir kutu kaldı (iki ayrı ekran görüntüsünde). Artık
                 içerik son gösterilen ülkede kalır, yalnız opaklık değişir;
                 görünen kutunun içi hiçbir koşulda boş olamaz. --}}
            <div
                class="hc-world-map__ipucu"
                :class="{ 'is-acik': ulke }"
                :style="`left:${x}px; top:${y}px`"
                aria-hidden="true"
            >
                <strong x-text="sonUlke?.ad"></strong>
                <span x-text="sonUlke ? new Intl.NumberFormat('tr-TR').format(sonUlke.sayi) + @js(' '.$unit) + (sonUlke.pay ? ' · %' + new Intl.NumberFormat('tr-TR').format(sonUlke.pay) : '') : ''"></span>
            </div>
        </div>

        @if ($available)
            <p class="hc-world-map__ipucu-yardim hc-caption mt-2" x-cloak>Ülkeye tıklayın ya da listeden seçin; sürükleyerek kaydırın, Ctrl + tekerlek ile yakınlaşın. Esc bütün dünyaya döner.</p>

            <div class="hc-world-map__summary mt-4">
                <p><strong>{{ number_format($map['total_members'], 0, ',', '.') }}</strong> {{ $unit }} · <strong>{{ count($countries) }}</strong> ülke</p>
                <p>Miller projeksiyonu · sınırlar Natural Earth 1:50m</p>
            </div>
            <ul class="hc-world-map__countries mt-4" aria-label="Ülke bazlı toplamlar">
                @foreach ($listelenen as $country)
                    <li>
                        <button type="button" class="hc-world-map__ulke-dugme" @click="ulkeyeGit(@js($country['country_code'])); $refs.tuval.scrollIntoView({ block: 'nearest', behavior: 'smooth' })" aria-label="{{ $country['label'] }}: {{ number_format((int) $country['members'], 0, ',', '.') }} {{ $unit }}. Haritada göster">
                            <span class="hc-world-map__country-dot" aria-hidden="true"></span><span>{{ $country['label'] }}</span><strong>{{ number_format((int) $country['members'], 0, ',', '.') }}</strong>
                        </button>
                    </li>
                @endforeach
            </ul>
            @if ($kalanUlke > 0)
                <p class="hc-caption mt-2" style="color:var(--hc-text-muted)">+{{ $kalanUlke }} ülke daha haritada boyalı; üzerine gelince sayısı görünür.</p>
            @endif
        @else
            <div class="hc-world-map__empty" role="status">
                <x-ui.icon name="info" size="18" />
                <div><p class="font-semibold">Konum özeti için henüz veri yok</p>
                <p class="hc-caption mt-1">{{ $visits ? 'Seçilen dönemde ülke bilgisi olan ziyaret kaydı bulunmuyor.' : 'Seçilen dönemde ülke bilgisi olan etkin üye bulunmuyor.' }}</p></div>
            </div>
        @endif
    </div>

    <p class="hc-caption mt-4" style="color:var(--hc-text-muted)">{{ $visits
    ? 'Kaynak: sitenin kendi ziyaret sayacı. Yalnız gün ve ülke tutulur. Google Analytics raporu değildir; sayılar örtüşmeyebilir. Ülke verisi: DB-IP IP to Country Lite (CC BY 4.0).'
    : 'Kaynak: uygulamadaki etkin üye profil ülkeleri. Google Analytics ziyaretçi raporu değildir.' }}</p>
</article>
