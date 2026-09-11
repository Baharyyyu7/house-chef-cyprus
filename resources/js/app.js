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
            if (copyStatus) copyStatus.textContent = 'Sayfa bağlantısı kopyalandı.';
        } catch {
            window.prompt('Sayfa bağlantısını kopyalayın:', url.toString());
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

// Tarif adımlarını pişirirken işaretlemek için. Giriş gerektirmez; ilerleme
// yalnız kullanıcının cihazında, tarif başına ayrı bir anahtarda saklanır.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('hcRecipeSteps', (slug, total) => ({
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

    window.Alpine.data('hcRecipeIngredients', (slug) => ({
        key: `hc-ing-${slug}`,
        checked: [],
        init() {
            this.checked = this.read();
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
        },
        reset() {
            this.checked = [];
            this.persist();
        },
        get count() {
            return this.checked.length;
        },
    }));

    window.Alpine.data('hcCookMode', () => ({
        wakeLock: null,
        active: false,
        supported: 'wakeLock' in navigator,
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
            document.addEventListener('visibilitychange', async () => {
                if (this.active && document.visibilityState === 'visible') {
                    await this.request();
                }
            });
        },
    }));
});

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

document.addEventListener('alpine:init', () => {
    window.Alpine.data('hcIngredientPicker', (options, preselected) => ({
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
});

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
document.addEventListener('alpine:init', () => {
    window.Alpine.data('hcBlogKaruseli', (adet) => ({
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
});

/**
 * Kahraman döngü videosu.
 *
 * Video kahraman görselin üzerine biner ve onun yerine geçmez: görsel
 * sayfanın LCP öğesi, video ise süs. Bu yüzden kaynak HTML'de durmuyor -
 * burada, sayfa yüklendikten sonra takılıyor. Böylece video baytları açılış
 * ölçümünün dışında kalıyor.
 *
 * Beş kapı var; biri bile kapalıysa video hiç indirilmez ve altındaki
 * görsel olduğu gibi kalır:
 *   - dar ekran (mobilde 60 KB'lik görsel için 2 MB video indirilmez)
 *   - hareket kısıtlı tercihi
 *   - Save-Data isteği
 *   - yavaş bağlantı
 *   - dosyanın oynatılamaması
 */
(() => {
    const video = document.querySelector('[data-hero-video]');

    if (! video) {
        return;
    }

    const genisEkran = window.matchMedia('(min-width: 1024px)');
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

        return genisEkran.matches
            && ! hareketKisitli.matches
            && baglanti.saveData !== true
            && ! ['slow-2g', '2g', '3g'].includes(baglanti.effectiveType);
    };

    let kaynakTakildi = false;

    const kaynaklariTak = () => {
        if (kaynakTakildi) {
            return;
        }

        kaynakTakildi = true;

        // Kaynak sırası önemli: tarayıcı ilk oynatabildiğini alır ve WebM
        // aynı kareyi MP4'ten küçük veriyor.
        for (const [tur, alan] of [['video/webm', 'webm'], ['video/mp4', 'mp4']]) {
            const adres = video.dataset[alan];

            if (adres) {
                const kaynak = document.createElement('source');
                kaynak.type = tur;
                kaynak.src = adres;
                video.append(kaynak);
            }
        }

        // Görünür olmak için gerçekten kare basmış olması gerekiyor. canplay
        // yetmez; dosya düştüğünde boş siyah bir kare açılıyordu.
        video.addEventListener('playing', () => video.setAttribute('data-hazir', ''), { once: true });

        // Kalıcı olarak vazgeçilen tek durum: dosya oynatılamıyor. Katman
        // görünmez kalır, altındaki görsel olduğu gibi durur.
        video.addEventListener('error', () => { video.dataset.kapali = 'medya-hatasi'; }, { once: true });

        video.load();
    };

    /**
     * Oynatma denemesi.
     *
     * Chrome sekme arka plandayken sessiz videoyu güç tasarrufu için
     * duraklatıyor ve play() AbortError ile reddediliyor. Bu geçici: reddi
     * kalıcı saymak, arka planda açılan sekmede videonun hiç görünmemesine
     * yol açıyordu. Reddedilirse hiçbir şey yapılmaz, sekme öne geldiğinde
     * yeniden denenir.
     */
    const oynat = () => {
        if (video.dataset.kapali || document.visibilityState !== 'visible' || ! kapilarAcik()) {
            return;
        }

        kaynaklariTak();

        const sozu = video.play();

        if (sozu && typeof sozu.catch === 'function') {
            sozu.catch(() => {});
        }
    };

    const durdur = () => {
        if (kaynakTakildi) {
            video.pause();
        }
    };

    const basla = () => {
        document.addEventListener('visibilitychange', () => {
            document.visibilityState === 'visible' ? oynat() : durdur();
        });

        // Pencere büyüyünce ya da hareket tercihi değişince yeniden denenir.
        genisEkran.addEventListener('change', () => (genisEkran.matches ? oynat() : durdur()));
        hareketKisitli.addEventListener('change', () => (hareketKisitli.matches ? durdur() : oynat()));

        // Hero ekrandan çıkınca oynatmanın sürmesi için sebep yok: görünmeyen
        // bir videoyu döndürmek pil ve işlemci harcar.
        if ('IntersectionObserver' in window) {
            new IntersectionObserver((kayitlar) => {
                for (const kayit of kayitlar) {
                    kayit.isIntersecting ? oynat() : durdur();
                }
            }, { threshold: 0.05 }).observe(video);

            return;
        }

        oynat();
    };

    // Sayfanın kendi yüklenmesi bitmeden başlamaz.
    if (document.readyState === 'complete') {
        basla();
    } else {
        window.addEventListener('load', basla, { once: true });
    }
})();

// Register every alpine:init listener before start dispatches that event.
if (document.body?.dataset.alpineOwner === 'app' && !window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}
