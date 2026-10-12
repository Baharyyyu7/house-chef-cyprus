// Alpine bu uygulamada iki farkli yerden gelebiliyor.
//
// Eskiden her layout Livewire yukluyordu ve Alpine'i Livewire baslatiyordu.
// Livewire genel sayfalardan kaldirilinca (161 KB, hicbir genel sayfada
// bilesen yok) Alpine de onunla birlikte gitti: yapiskan baslik is-scrolled
// alamadi, acilir menuler ve tema dugmesi olduO ve x-cloak tasiyan ogeler
// kalici olarak gizli kaldi.
//
// Iki kopya calistirmak da olmaz - Livewire'in navigate sozlesmesini kirar.
// Bu yuzden sahibi sunucu soyluyor: layout, Livewire basip basmadigini
// data-alpine-owner ile bildiriyor ve Alpine yalniz sahipsiz sayfalarda
// buradan baslatiliyor.
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
window.Alpine = Alpine;
Alpine.plugin(collapse);
window.__HC_VERSION__ = '2026.09.11.3';
import './webmcp.js';
import { hareketiKur } from './motion.js';
let chartLibrary;

const chartInstances = new WeakMap();

const cssVariable = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

const palette = () => [
    cssVariable('--hc-chart-1'),
    cssVariable('--hc-chart-2'),
    cssVariable('--hc-chart-3'),
    cssVariable('--hc-chart-4'),
    cssVariable('--hc-chart-5'),
];

const chartConfig = (data) => {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const text = cssVariable('--hc-text-secondary');
    const grid = cssVariable('--hc-border-subtle');
    const colors = palette();
    const isDonut = data.type === 'doughnut';
    const fontFamily = getComputedStyle(document.body).fontFamily;

    return {
        type: data.type,
        data: {
            labels: data.labels,
            datasets: [{
                label: data.title,
                data: data.values,
                backgroundColor: isDonut ? colors : `${colors[0]}cc`,
                borderColor: isDonut ? cssVariable('--hc-chart-surface') : colors[0],
                borderWidth: isDonut ? 3 : 2,
                borderRadius: data.type === 'bar' ? 8 : 0,
                tension: data.type === 'line' ? 0.34 : 0,
                fill: data.type === 'line',
                pointRadius: data.type === 'line' ? 3 : 0,
                pointHoverRadius: data.type === 'line' ? 5 : 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: reducedMotion ? false : { duration: 420, easing: 'easeOutQuart' },
            plugins: {
                legend: { display: isDonut, position: 'bottom', labels: { color: text, boxWidth: 10, padding: 14, font: { family: fontFamily } } },
                tooltip: { backgroundColor: cssVariable('--hc-surface-overlay'), titleColor: cssVariable('--hc-text-primary'), bodyColor: cssVariable('--hc-text-secondary'), borderColor: cssVariable('--hc-border-default'), borderWidth: 1, padding: 11, cornerRadius: 10 },
            },
            scales: isDonut ? {} : {
                x: { grid: { display: false }, ticks: { color: text, maxRotation: 0, autoSkipPadding: 14, font: { family: fontFamily } } },
                y: { beginAtZero: true, grid: { color: grid }, border: { display: false }, ticks: { color: text, precision: 0, font: { family: fontFamily } } },
            },
        },
    };
};

const mountCharts = async () => {
    if (!document.querySelector('[data-admin-chart]')) return;
    chartLibrary ??= import('chart.js').then(({ Chart, registerables }) => {
        Chart.register(...registerables);
        return Chart;
    });
    const Chart = await chartLibrary;
    document.querySelectorAll('[data-admin-chart]').forEach((canvas) => {
        let data;
        try {
            data = JSON.parse(canvas.dataset.adminChartConfig || '{}');
        } catch {
            return;
        }

        if (!data.type || !Array.isArray(data.labels) || !Array.isArray(data.values)) return;
        chartInstances.get(canvas)?.destroy();
        chartInstances.set(canvas, new Chart(canvas, chartConfig(data)));
    });
};

const animateCounts = () => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    document.querySelectorAll('[data-hc-count]').forEach((element) => {
        const target = Number(element.dataset.hcCount);
        if (!Number.isFinite(target) || element.dataset.counted === 'true') return;
        element.dataset.counted = 'true';
        const started = performance.now();
        const duration = 420;
        const suffix = element.textContent.replace(/[\d.,\s]/g, '');
        const update = (now) => {
            const progress = Math.min(1, (now - started) / duration);
            const value = Math.round(target * (1 - Math.pow(1 - progress, 3)));
            element.textContent = `${new Intl.NumberFormat('tr-TR').format(value)}${suffix}`;
            if (progress < 1) requestAnimationFrame(update);
        };
        requestAnimationFrame(update);
    });
};

const mountAdminDashboard = () => {
    mountCharts();
    animateCounts();
};

const mountLegalInformationCenter = () => {
    const center = document.querySelector('[data-legal-center]');
    if (!center || center.dataset.initialized === 'true') return;

    center.dataset.initialized = 'true';
    const copyStatus = center.querySelector('[data-legal-copy-status]');
    center.querySelector('[data-legal-print]')?.addEventListener('click', () => window.print());
    center.querySelector('[data-copy-page]')?.addEventListener('click', async () => {
        const url = new URL(window.location.href);
        url.hash = '';
        try {
            await navigator.clipboard.writeText(url.toString());
            if (copyStatus) copyStatus.textContent = center.dataset.copiedStatus || 'Sayfa bağlantısı kopyalandı.';
        } catch {
            window.prompt(center.dataset.copyPrompt || 'Sayfa bağlantısını kopyalayın:', url.toString());
        }
    });
};

const mountPageFeatures = () => {
    mountAdminDashboard();
    mountLegalInformationCenter();
    // Ölçüm ve reklam onayı ayrı çerezlerde tutulur: ölçüme izin veren biri
    // reklama da izin vermiş sayılamaz. Reklam yuvası yalnız kendi çerezine
    // bakar, bu yüzden o çerezi verecek bir düğme olmadan hiçbir reklam çıkmaz.
    //
    // "Kabul et" düğmesi iki karar birden taşıyor: hem ölçüm hem reklam.
    // Eskiden her karar için ayrı bir dinleyici vardı ve her biri kendi
    // çerezini yazdıktan sonra sayfayı yeniliyordu. İlk dinleyici yenilemeyi
    // başlatınca ikincisi hiç çalışmıyordu: reklam çerezi yazılmıyor, bant
    // yenilenen sayfada geri geliyordu. Kullanıcı için düğme "çalışmıyor"du.
    //
    // Artık tek dinleyici var: düğmedeki bütün kararlar yazılır, yenileme
    // en sonda bir kez yapılır.
    const cerezYaz = (ad, deger) => {
        const guvenli = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `${ad}=${deger}; Path=/; Max-Age=31536000; SameSite=Lax${guvenli}`;
    };

    const kararlar = [
        ['consentChoice', 'hc_analytics_consent'],
        ['advertisingChoice', 'hc_advertising_consent'],
    ];

    document.querySelectorAll('[data-consent-choice], [data-advertising-choice]').forEach((button) => {
        if (button.dataset.consentBound === 'true') return;
        button.dataset.consentBound = 'true';

        button.addEventListener('click', () => {
            let yazildi = false;

            kararlar.forEach(([anahtar, cerez]) => {
                const secim = button.dataset[anahtar];
                if (secim === undefined) return;
                cerezYaz(cerez, secim === 'granted' ? 'granted' : 'denied');
                yazildi = true;
            });

            if (yazildi) {
                window.location.reload();
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', mountPageFeatures);
document.addEventListener('livewire:navigated', mountPageFeatures);
window.addEventListener('hc-theme-changed', () => window.setTimeout(mountCharts, 0));

// Gönderilen POST formunun düğmesi yanıt gelene kadar kilitlenir; 12 Ekim
// 2026 denetiminde sitede genel bir çift gönderim kilidi yoktu, yavaş ağda
// ikinci tık ikinci isteği gönderir. Kilit gönderimden sonra konur:
// önce konsa düğmenin adı ve değeri form verisinden düşerdi. Alpine ya da
// Livewire'ın işlediği (varsayılanı engellenmiş) formlara dokunulmaz.
document.addEventListener('submit', (olay) => {
    const dugme = olay.submitter;
    const form = olay.target;

    if (olay.defaultPrevented || !dugme || form.method.toLowerCase() !== 'post' || form.target === '_blank') {
        return;
    }

    window.setTimeout(() => {
        dugme.disabled = true;
        dugme.setAttribute('aria-busy', 'true');
    }, 0);
});

// Geri tuşuyla önbellekten dönen sayfada kilit açık gelir.
window.addEventListener('pageshow', (olay) => {
    if (!olay.persisted) {
        return;
    }

    document.querySelectorAll('[aria-busy="true"]:disabled').forEach((dugme) => {
        dugme.disabled = false;
        dugme.removeAttribute('aria-busy');
    });
});

// Ana sayfa hero'sundaki eşleştirme gösterisi.
//
// Dolaba gerçek malzemeler tek tek düşer, hedef tarifin eşleşme ölçeri
// dolar ve %100'de tarif "tam yapılabilir" olur. Veri sunucudan gelir
// (HomeMatchShowcaseService); burada yalnız zamanlama ve hareket var.
//
// Yüzde CSS geçişiyle ilerler (halka stroke-dashoffset, sayı @property
// tamsayı sayacı); eskiden her karede requestAnimationFrame ile yay
// hesaplanıyordu ve gösteri ekran dışındayken de dönüyordu. Artık yalnız
// ekrandayken başlar; ekran dışı, gizli sekme, fare üstü ya da odak içeride
// ise durur. Hareket istemeyen ziyaretçi döngü değil son hâli görür.
const HC_MATCH_GECIS = 900;

Alpine.data('hcMatchShowcase', (senaryolar, metin) => ({
    senaryolar,
    metin,
    sira: 0,
    adim: -1,
    evre: 'dolum',
    yuzde: 0,
    basladi: false,
    engel: { ekranDisi: true, gizli: false, fare: false, odak: false },
    _zaman: null,
    _gozlemci: null,
    _gorunurluk: null,

    get senaryo() { return this.senaryolar[this.sira] || null; },
    get adimlar() { return this.senaryo ? this.senaryo.steps : []; },
    get bitti() { return this.evre === 'tamam'; },
    get durdu() { return Object.values(this.engel).some(Boolean); },
    get pisirilebilir() {
        const a = this.adimlar[this.adim];
        return a ? a.can_cook : 0;
    },
    eklendi(i) { return i <= this.adim; },

    init() {
        if (!this.senaryo) return;

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            // Gösterinin söylediği şey sonuçtu, yolculuk değil.
            this.adim = this.adimlar.length - 1;
            this.yuzde = 100;
            this.evre = 'tamam';
            return;
        }

        this._gorunurluk = () => this.engelle('gizli', document.hidden);
        document.addEventListener('visibilitychange', this._gorunurluk);
        this.engel.gizli = document.hidden;

        if ('IntersectionObserver' in window) {
            this._gozlemci = new IntersectionObserver(([giris]) => this.engelle('ekranDisi', !giris.isIntersecting), { threshold: 0.35 });
            this._gozlemci.observe(this.$el);
        } else {
            this.engelle('ekranDisi', false);
        }
    },

    engelle(neden, durum) {
        this.engel[neden] = durum;
        window.clearTimeout(this._zaman);
        if (this.durdu) return;
        // İlk görünüşte kısa bir nefes; sonrasında kaldığı yerden.
        this._zaman = window.setTimeout(() => this.ilerle(), this.basladi ? 500 : 700);
        this.basladi = true;
    },

    ilerle() {
        if (this.durdu) return;

        if (this.evre === 'cikis') {
            this.sira = (this.sira + 1) % this.senaryolar.length;
            this.adim = -1;
            this.yuzde = 0;
            this.evre = 'dolum';
            this._zaman = window.setTimeout(() => this.ilerle(), 520);
            return;
        }

        if (this.evre === 'tamam') {
            this.evre = 'cikis';
            this._zaman = window.setTimeout(() => this.ilerle(), 460);
            return;
        }

        if (this.adim < this.adimlar.length - 1) {
            this.adim++;
            this.yuzde = this.adimlar[this.adim].percent;
            // Son malzemede tamamlanma, halkanın CSS geçişi bitince.
            const sonAdim = this.adim === this.adimlar.length - 1;
            this._zaman = window.setTimeout(() => (sonAdim ? this.tamamla() : this.ilerle()), sonAdim ? HC_MATCH_GECIS : 880);
            return;
        }

        this.tamamla();
    },

    tamamla() {
        if (this.evre !== 'dolum' || this.adim < this.adimlar.length - 1) return;
        window.clearTimeout(this._zaman);
        this.evre = 'tamam';
        this._zaman = window.setTimeout(() => this.ilerle(), 2800);
    },

    destroy() {
        window.clearTimeout(this._zaman);
        this._gozlemci?.disconnect();
        if (this._gorunurluk) document.removeEventListener('visibilitychange', this._gorunurluk);
    },
}));

// Tarif adımlarını pişirirken işaretlemek için. Giriş gerektirmez; ilerleme
// yalnız kullanıcının cihazında, tarif başına ayrı bir anahtarda saklanır.
Alpine.data('hcRecipeSteps', (slug, total) => ({
        key: `hc-steps-${slug}`,
        total,
        done: [],
        init() {
            this.done = this.read().filter((value) => Number.isInteger(value) && value >= 1 && value <= this.total);
        },
        read() {
            try {
                const stored = JSON.parse(window.localStorage.getItem(this.key) ?? '[]');

                return Array.isArray(stored) ? stored : [];
            } catch {
                return [];
            }
        },
        persist() {
            try {
                window.localStorage.setItem(this.key, JSON.stringify(this.done));
            } catch {
                // Depolama kapalıysa ilerleme yalnız bu sayfa görüntülemesi boyunca yaşar.
            }
        },
        isDone(position) {
            return this.done.includes(position);
        },
        toggle(position) {
            this.done = this.isDone(position)
                ? this.done.filter((value) => value !== position)
                : [...this.done, position];
            this.persist();
        },
        reset() {
            this.done = [];
            this.persist();
        },
        get completed() {
            return this.done.length;
        },
        get percent() {
            return this.total > 0 ? Math.round((this.completed / this.total) * 100) : 0;
        },
        get label() {
            return this.completed > 0 ? `${this.completed} / ${this.total} adım` : `${this.total} adım`;
        },
    }));

    // Yapışkan pişim zamanlayıcısı. Adım butonları `hc-start-timer`
    // olayıyla besler; sayfa kaydırılsa da sağ altta yaşar. Sesli uyarı
    // WebAudio ile üretilir, dosya gerektirmez.
    Alpine.data('hcCookTimer', () => ({
        acik: false,
        calisiyor: false,
        bitti: false,
        toplam: 0,
        kalan: 0,
        etiket: '',
        zamanlayici: null,
        bitis: 0,
        sesBaglami: null,
        get gosterim() {
            const saniye = Math.max(0, Math.ceil(this.kalan));
            const ss = String(saniye % 60).padStart(2, '0');

            // 6 saatlik dondurma adımı "360:00" basıyordu.
            if (saniye >= 3600) {
                return `${Math.floor(saniye / 3600)}:${String(Math.floor(saniye / 60) % 60).padStart(2, '0')}:${ss}`;
            }

            return `${String(Math.floor(saniye / 60)).padStart(2, '0')}:${ss}`;
        },
        get yuzde() {
            return this.toplam > 0 ? (this.kalan / this.toplam) * 100 : 0;
        },
        baslat(saniye, etiket) {
            this.durdur();
            this.toplam = saniye;
            this.kalan = saniye;
            this.etiket = etiket || '';
            this.acik = true;
            this.bitti = false;
            this.devamEt();
        },
        devamEt() {
            this.sesiHazirla();
            this.calisiyor = true;
            this.bitis = Date.now() + this.kalan * 1000;
            window.clearInterval(this.zamanlayici);
            this.zamanlayici = window.setInterval(() => {
                this.kalan = Math.max(0, (this.bitis - Date.now()) / 1000);
                if (this.kalan <= 0) {
                    this.bitir();
                }
            }, 250);
        },
        duraklatDevam() {
            if (!this.acik || this.bitti) {
                return;
            }
            if (this.calisiyor) {
                this.kalan = Math.max(0, (this.bitis - Date.now()) / 1000);
                if (this.kalan <= 0) {
                    this.bitir();
                } else {
                    this.durdur();
                }
            } else if (this.kalan > 0) {
                this.devamEt();
            }
        },
        sifirla() {
            this.durdur();
            this.kalan = this.toplam;
            this.bitti = false;
        },
        kapat() {
            this.durdur();
            this.acik = false;
        },
        durdur() {
            this.calisiyor = false;
            window.clearInterval(this.zamanlayici);
            this.zamanlayici = null;
        },
        destroy() {
            this.durdur();
            if (this.sesBaglami && this.sesBaglami.state !== 'closed') {
                this.sesBaglami.close().catch(() => {});
            }
            this.sesBaglami = null;
        },
        bitir() {
            this.durdur();
            this.kalan = 0;
            this.bitti = true;
            this.sesliUyari();
        },
        sesiHazirla() {
            try {
                this.sesBaglami ??= new (window.AudioContext || window.webkitAudioContext)();
                if (this.sesBaglami.state === 'suspended') {
                    // Başlat/devam et tıklamasının kullanıcı etkinliği içinde izin al.
                    this.sesBaglami.resume().catch(() => {});
                }
            } catch {
                // Ses desteklenmiyorsa görsel geri sayım çalışmaya devam eder.
            }
        },
        sesliUyari() {
            try {
                const ctx = this.sesBaglami;
                if (!ctx || ctx.state !== 'running') return;
                [0, 450, 900].forEach((gecikme, i) => {
                    const osc = ctx.createOscillator();
                    const kazanc = ctx.createGain();
                    osc.connect(kazanc);
                    kazanc.connect(ctx.destination);
                    osc.frequency.value = i === 2 ? 880 : 660;
                    osc.start(ctx.currentTime + gecikme / 1000);
                    osc.stop(ctx.currentTime + gecikme / 1000 + 0.35);
                });
            } catch {
                // Ses kapalıysa zamanlayıcı yine de görsel olarak biter.
            }
        },
    }));

    // Üyeliksiz 5 yıldız oylama. Sunucu `hc_voter` çereziyle tekilliği
    // korur; tarayıcı kopyası yalnız anlık arayüz içindir.
    Alpine.data('hcRecipeRating', (adres, kisa, adet, ortalama) => ({
        adres,
        kisa,
        adet,
        ortalama,
        vurgu: 0,
        secili: 0,
        gonderiliyor: false,
        tesekkur: false,
        init() {
            try {
                const sakli = window.localStorage.getItem(`hc-rated-${this.kisa}`);
                if (sakli) {
                    this.secili = Number.parseInt(sakli, 10) || 0;
                }
            } catch {
                // Depolama kapalıysa oy yine de sunucuya yazılır.
            }
        },
        get gorunen() {
            return this.secili;
        },
        oyla(yildiz) {
            if (this.gonderiliyor) {
                return;
            }
            this.gonderiliyor = true;
            const jeton = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            window.fetch(this.adres, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': jeton},
                body: JSON.stringify({rating: yildiz}),
            }).then((yanit) => {
                if (!yanit.ok) {
                    throw new Error('oy-gonderilemedi');
                }

                return yanit.json();
            }).then((veri) => {
                this.adet = veri.count;
                this.ortalama = veri.average;
                this.secili = veri.user_rating;
                this.tesekkur = true;
                try {
                    window.localStorage.setItem(`hc-rated-${this.kisa}`, String(yildiz));
                } catch {
                    // Sessiz geç.
                }
            }).catch(() => {
                this.tesekkur = false;
            }).finally(() => {
                this.gonderiliyor = false;
            });
        },
    }));

    // Porsiyon ölçekleme istemci tarafında: satır metinleri sunucuda 1-20
    // porsiyon için hazırlanıp sayfaya JSON olarak gömülür. Harita yoksa ya da
    // istenen porsiyon dışındaysa form normal gönderilir (#ingredients'e döner).
    Alpine.data('hcRecipeIngredients', (slug, porsiyon = null) => ({
        key: `hc-ing-${slug}`,
        checked: [],
        servings: porsiyon?.current ?? 1,
        harita: null,
        init() {
            this.checked = this.read();
            const kaynak = porsiyon ? document.getElementById(porsiyon.harita) : null;
            if (kaynak) {
                try {
                    this.harita = JSON.parse(kaynak.textContent);
                } catch {
                    this.harita = null;
                }
            }
        },
        get portionNote() {
            return porsiyon ? porsiyon.not.replace('__N__', String(this.servings)) : '';
        },
        setServings(deger) {
            const n = Number.parseInt(deger, 10);
            if (!this.harita || !Number.isFinite(n) || !this.harita[n]) {
                return false;
            }
            this.servings = n;
            try {
                const adres = new URL(window.location.href);
                if (n === porsiyon.base) {
                    adres.searchParams.delete('servings');
                } else {
                    adres.searchParams.set('servings', String(n));
                }
                adres.searchParams.delete('servings_step');
                window.history.replaceState(window.history.state, '', adres);
            } catch {
                // Adres güncellenemese de ölçekleme çalışır.
            }
            return true;
        },
        step(yon) {
            return this.setServings(Math.max(1, this.servings + yon));
        },
        line(id, alan) {
            return this.harita?.[this.servings]?.[id]?.[alan] ?? null;
        },
        read() {
            try {
                const stored = JSON.parse(window.localStorage.getItem(this.key) ?? '[]');
                return Array.isArray(stored) ? stored : [];
            } catch {
                return [];
            }
        },
        persist() {
            try {
                window.localStorage.setItem(this.key, JSON.stringify(this.checked));
            } catch {
                // Depolama kapalıysa oturum boyunca çalışır.
            }
        },
        isChecked(id) {
            return this.checked.includes(String(id));
        },
        toggle(id) {
            const sid = String(id);
            this.checked = this.isChecked(sid)
                ? this.checked.filter((v) => v !== sid)
                : [...this.checked, sid];
            this.persist();
            window.dispatchEvent(new CustomEvent('hc-ingredient-checked'));
        },
        reset() {
            this.checked = [];
            this.persist();
        },
        get count() {
            return this.checked.length;
        },
    }));

    Alpine.data('hcCookMode', () => ({
        wakeLock: null,
        active: false,
        supported: 'wakeLock' in navigator,
        autoTriggered: false,
        async toggle() {
            if (!this.supported) return;
            if (this.active) {
                await this.release();
            } else {
                await this.request();
            }
        },
        async request() {
            try {
                this.wakeLock = await navigator.wakeLock.request('screen');
                this.active = true;
                this.wakeLock.addEventListener('release', () => {
                    this.active = false;
                    this.wakeLock = null;
                });
            } catch (err) {
                this.active = false;
                this.wakeLock = null;
            }
        },
        async release() {
            if (this.wakeLock) {
                try {
                    await this.wakeLock.release();
                } catch {}
                this.wakeLock = null;
            }
            this.active = false;
        },
        init() {
            const autoStart = () => {
                if (!this.autoTriggered && this.supported && !this.active) {
                    this.autoTriggered = true;
                    this.request();
                }
            };

            // Kullanıcı sayfada adımlara/malzemelere kaydırdığında ekran açık tutma modu otomatik başlar
            window.addEventListener('scroll', () => {
                if (window.scrollY > 200) autoStart();
            }, { once: true, passive: true });

            window.addEventListener('hc-ingredient-checked', () => autoStart());

            document.addEventListener('visibilitychange', async () => {
                if (this.active && document.visibilityState === 'visible') {
                    await this.request();
                }
            });
        },
    }));

// Malzeme seçimi: "Cmd/Ctrl ile çoklu seçim" yerine aranabilir çip listesi.
// Türkçe karakterler normalize edilir; "sogan" da "Soğan" da eşleşir.
const hcNormalize = (value) => (value ?? '')
    .toLocaleLowerCase('tr')
    .replaceAll('ı', 'i').replaceAll('ğ', 'g').replaceAll('ü', 'u')
    .replaceAll('ş', 's').replaceAll('ö', 'o').replaceAll('ç', 'c')
    .replaceAll('â', 'a').replaceAll('î', 'i').replaceAll('û', 'u')
    .replace(/[^a-z0-9 ]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

Alpine.data('hcIngredientPicker', (options, preselected) => ({
        options,
        selected: Array.isArray(preselected) ? preselected.filter((id) => options.some((o) => o.id === id)) : [],
        query: '',
        highlighted: 0,
        get matches() {
            const needle = hcNormalize(this.query);

            if (needle === '') {
                return [];
            }

            return this.options
                .filter((option) => !this.selected.includes(option.id) && option.search.includes(needle))
                .slice(0, 8);
        },
        get chosen() {
            return this.selected
                .map((id) => this.options.find((option) => option.id === id))
                .filter(Boolean);
        },
        name(id) {
            return this.options.find((option) => option.id === id)?.name ?? '';
        },
        add(id) {
            if (id && !this.selected.includes(id)) {
                this.selected = [...this.selected, id];
            }
            this.query = '';
            this.highlighted = 0;
        },
        remove(id) {
            this.selected = this.selected.filter((value) => value !== id);
        },
        clear() {
            this.selected = [];
            this.query = '';
        },
        move(delta) {
            const total = this.matches.length;
            if (total === 0) {
                return;
            }
            this.highlighted = (this.highlighted + delta + total) % total;
        },
        commit() {
            const match = this.matches[this.highlighted];
            if (match) {
                this.add(match.id);
            }
        },
    }));

/**
 * Blog karuseli.
 *
 * Kartlar eliptik bir yörüngede durur, merkezdeki büyür. Konum hesabı
 * React sürümündekiyle aynı mantık: sıra farkı listenin başına/sonuna
 * sarılır, uzaklaştıkça ölçek ve saydamlık düşer.
 *
 * Hareket kısıtlıysa hiçbir şey dönmez ve otomatik ilerleme çalışmaz;
 * kartlar okunabilir bir sırada durur.
 */
Alpine.data('hcBlogKaruseli', (adet) => ({
        etkin: 0,
        duraklat: false,
        zamanlayici: null,
        kisitli: window.matchMedia('(prefers-reduced-motion: reduce)').matches,

        init() {
            if (this.kisitli) {
                return;
            }

            this.zamanlayici = setInterval(() => {
                if (! this.duraklat) {
                    this.ileri();
                }
            }, 5200);

            this.$watch('duraklat', () => {});
        },

        destroy() {
            if (this.zamanlayici) {
                clearInterval(this.zamanlayici);
            }
        },

        git(sira) {
            this.etkin = ((sira % adet) + adet) % adet;
        },

        ileri() {
            this.git(this.etkin + 1);
        },

        geri() {
            this.git(this.etkin - 1);
        },

        /** Sıra farkı listenin iki ucundan da sarılır: son karttan sonra ilk kart gelir. */
        fark(sira) {
            const yari = Math.floor(adet / 2);
            let f = sira - this.etkin;

            if (f > yari) f -= adet;
            if (f < -yari) f += adet;

            return f;
        },

        gorunur(sira) {
            return Math.abs(this.fark(sira)) <= 2;
        },

        konum(sira) {
            const f = this.fark(sira);

            if (this.kisitli) {
                return Math.abs(f) <= 2 ? '' : 'display:none';
            }

            if (Math.abs(f) > 2) {
                return 'opacity:0; pointer-events:none; transform:translate(-50%,-50%) scale(.7)';
            }

            // Yörünge yarıçapı sabit 240px'ti ve dar ekranda kartlar
            // görünür alanın dışına taşıyordu. Yarıçap kartın kendi
            // genişliğine göre daralır.
            const yaricap = Math.min(240, Math.max(120, window.innerWidth * 0.26));
            const aci = (f / 5) * Math.PI;
            const x = Math.sin(aci) * yaricap;
            const y = -Math.cos(aci) * 92 + 92;
            const uzaklik = Math.abs(f);
            const olcek = Math.max(0.7, 1 - uzaklik * 0.13);
            const saydam = Math.max(0.35, 1 - uzaklik * 0.3);

            return `transform: translate(calc(-50% + ${x}px), calc(-50% + ${y}px)) scale(${olcek}); opacity:${saydam}; z-index:${10 - uzaklik}`;
        },
    }));

/**
 * Kahraman videosu sahnenin kendisi: kaynak doğrudan markup'tadır ve her
 * ekranda oynar. Bu orkestratör kaynak takmaz; yalnız görünürlük, sekme
 * odağı ve erişilebilirlik kapılarını yönetir:
 *   - hareket kısıtlı tercihi (video durur ve gizlenir)
 *   - Save-Data isteği ve yavaş bağlantı (oynatma denenmez)
 *   - dosyanın oynatılamaması (katman gizli kalır, koyu zemin durur)
 */
(() => {
    const video = document.querySelector('[data-hero-video]');

    if (! video) {
        return;
    }

    const hareketKisitli = window.matchMedia('(prefers-reduced-motion: reduce)');

    /**
     * Koşullar her denemede yeniden ölçülür, bir kez değil.
     *
     * Önce bir kez ölçülüyordu ve arka planda açılan sekmede window.innerWidth
     * 0 dönüyordu: kapı kapanıyor, video o sayfada bir daha hiç açılmıyordu.
     * Kullanıcının linki yeni sekmede açması yeterliydi. Genişlik ve hareket
     * tercihi değişebilen şeyler; ekran döndürülebilir, pencere büyütülebilir.
     */
    const kapilarAcik = () => {
        const baglanti = navigator.connection || {};

        return ! hareketKisitli.matches
            && baglanti.saveData !== true
            && ! ['slow-2g', '2g'].includes(baglanti.effectiveType);
    };

    // Görünür olmak için gerçekten kare basmış olması gerekiyor. canplay
    // yetmez; dosya düşerse katman gizli kalır, koyu zemin durur.
    video.addEventListener('playing', () => video.setAttribute('data-hazir', ''), { once: true });

    // Kalıcı olarak vazgeçilen tek durum: dosya oynatılamıyor.
    video.addEventListener('error', () => { video.dataset.kapali = 'medya-hatasi'; }, { once: true });

    /**
     * Oynatma denemesi.
     */
    const oynat = () => {
        if (video.dataset.kapali || document.visibilityState !== 'visible' || ! kapilarAcik()) {
            return;
        }

        const sozu = video.play();

        if (sozu && typeof sozu.catch === 'function') {
            sozu.catch(() => {});
        }
    };

    const durdur = () => {
        video.pause();
    };

    const basla = () => {
        // Markup autoplay ile açılır; kapılar kapalıysa (hareket kısıtlı,
        // veri tasarrufu, yavaş hat) hemen durdurulur ve bir daha denenmez.
        if (! kapilarAcik()) {
            video.removeAttribute('autoplay');
            video.pause();
        }
        document.addEventListener('visibilitychange', () => {
            document.visibilityState === 'visible' ? oynat() : durdur();
        });

        // Video her ekranda oynar; genişlik değişimi oynatmayı kesmez.
        // Yalnız hareket tercihi değişimi izlenir.
        hareketKisitli.addEventListener('change', () => (hareketKisitli.matches ? durdur() : oynat()));

        // Hero ekrandan çıkınca oynatmanın sürmesi için sebep yok; kaydırmada GPU ve CPU boşalsın.
        if ('IntersectionObserver' in window) {
            new IntersectionObserver((kayitlar) => {
                for (const kayit of kayitlar) {
                    kayit.isIntersecting ? oynat() : durdur();
                }
            }, { threshold: 0.15 }).observe(video);

            return;
        }

        oynat();
    };

    if (document.readyState === 'complete') {
        basla();
    } else {
        window.addEventListener('load', basla, { once: true });
    }
})();

// Güvenli Alpine başlatıcı:
// DOM yüklenirken çalışırsa DOMContentLoaded'ı bekler; böylece document.body
// ve data-alpine-owner garantili olarak okunur, Alpine hiçbir sayfada ıskalanmaz.
const startAlpine = () => {
    if (window._alpineStarted) {
        return;
    }

    const owner = document.body?.dataset.alpineOwner;

    if (owner === 'app') {
        window._alpineStarted = true;
        Alpine.start();
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startAlpine, { once: true });
    document.addEventListener('DOMContentLoaded', hareketiKur, { once: true });
} else {
    startAlpine();
    hareketiKur();
}
