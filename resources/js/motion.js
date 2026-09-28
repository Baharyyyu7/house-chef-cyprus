/**
 * Hareket katmanı.
 *
 * Kinetik başlıklar, 3B savrulan kartlar, sayaçlar, enerji patlamaları,
 * okuma çizgisi, fareyi izleyen eğim ve kartın tarif sayfasına morf olması.
 *
 * Üç kural her şeyin üstünde:
 * - Açılışta ekranda olan öğe gizlenmez. Gizleyip açmak LCP'yi geciktirir,
 *   yerinden oynatıp geri getirmek ilk karede sıçrama yapar.
 * - Gizlenen yalnız ekranın dışındakiler ve yalnız IntersectionObserver
 *   varken. Betik hiç çalışmazsa sayfa efektsiz ama eksiksiz görünür.
 * - Hareket kısıtlıysa hiçbiri kurulmaz. Yalnız transform, opacity, filter
 *   ve clip-path canlandırılır; yerleşim hiç değişmez.
 *
 * Eski data-reveal düzeni ekranın altındakileri yüklemeden 1,5 sn sonra
 * topluca açıyordu: animasyon kimse bakmıyorken oynuyordu.
 */

const kisitli = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Aşımlı yay: hedefi geçip geri oturur. Oturma: aşımsız, yumuşak iniş.
const YAY = 'cubic-bezier(.16, 1.3, .3, 1)';
const OTUR = 'cubic-bezier(.2, .9, .25, 1)';

const KINETIK = '#main-content h2, #main-content .hc-display-lg';
const KINETIK_DISI = '.hc-v3-card, .hc-match, nav, form, [data-no-motion], [x-text], [x-html], .sr-only';
const KART = '#main-content .hc-v3-card--interactive';
const BLOK = '#main-content [data-reveal], #main-content [data-reveal-group] > *, #main-content .hc-v3-card:not(.hc-v3-card--interactive), #ingredients li, #nutrition dl > div, #faq details';
const USTYAZI = '#main-content .hc-kicker, #main-content .hc-overline';

const ekranda = (el) => {
    const r = el.getBoundingClientRect();

    return r.bottom > 0 && r.top < window.innerHeight;
};

/** Yalnız düz metinli başlık kelimelere bölünür; bağlı ya da yapılı başlığa dokunulmaz. */
function kelimele(baslik) {
    if (baslik.dataset.hcKw || baslik.closest(KINETIK_DISI)) {
        return [];
    }

    for (const dugum of baslik.childNodes) {
        if (dugum.nodeType === 1 && dugum.tagName !== 'BR') {
            return [];
        }
    }

    const metin = baslik.textContent.trim();

    if (metin === '' || metin.length > 120) {
        return [];
    }

    baslik.dataset.hcKw = '1';
    const kelimeler = [];

    for (const dugum of [...baslik.childNodes]) {
        if (dugum.nodeType !== 3) {
            continue;
        }

        const parca = document.createDocumentFragment();

        for (const p of dugum.textContent.split(/(\s+)/)) {
            if (p === '') {
                continue;
            }

            if (/^\s+$/.test(p)) {
                parca.append(p);
                continue;
            }

            const kelime = document.createElement('span');
            kelime.className = 'hc-kw';
            kelime.textContent = p;
            parca.append(kelime);
            kelimeler.push(kelime);
        }

        dugum.replaceWith(parca);
    }

    return kelimeler;
}

const oynatici = {
    baslik(baslik) {
        baslik.querySelectorAll('.hc-kw').forEach((kelime, i) => {
            kelime.classList.remove('hc-m-gizli');
            kelime.animate([
                { opacity: 0, transform: 'translate3d(0, .85em, 0) rotate(8deg) scale(.86)', filter: 'blur(12px)' },
                { opacity: 1, transform: 'none', filter: 'blur(0)' },
            ], { duration: 950, delay: i * 55, easing: YAY, fill: 'backwards' });
        });
    },
    kart(kart, sira) {
        kart.classList.remove('hc-m-gizli');
        kart.animate([
            { opacity: 0, transform: 'perspective(1100px) translate3d(0, 90px, 0) rotateX(26deg) scale(.86)', filter: 'blur(10px)' },
            { opacity: 1, transform: 'perspective(1100px) translate3d(0, 0, 0) rotateX(0) scale(1)', filter: 'blur(0)' },
        ], { duration: 1050, delay: sira * 85, easing: YAY, fill: 'backwards' });
        const gorsel = kart.querySelector('img');

        if (gorsel) {
            gorsel.animate([
                { transform: 'scale(1.35)', filter: 'saturate(.2) brightness(1.2)' },
                { transform: 'scale(1)', filter: 'none' },
            ], { duration: 1400, delay: sira * 85 + 80, easing: OTUR, fill: 'backwards' });
        }
    },
    blok(blok, sira) {
        blok.classList.remove('hc-m-gizli');
        blok.animate([
            { opacity: 0, transform: 'translate3d(0, 44px, 0) scale(.96)', filter: 'blur(8px)' },
            { opacity: 1, transform: 'none', filter: 'blur(0)' },
        ], { duration: 850, delay: sira * 60, easing: YAY, fill: 'backwards' });
    },
    ustyazi(el, sira) {
        el.classList.remove('hc-m-gizli');
        el.animate([
            { opacity: 0, clipPath: 'inset(0 100% 0 0)', transform: 'translate3d(-14px, 0, 0)' },
            { opacity: 1, clipPath: 'inset(0 0 0 0)', transform: 'none' },
        ], { duration: 700, delay: sira * 40, easing: OTUR, fill: 'backwards' });
    },
    sayac(el) {
        const hedef = Number.parseInt(el.textContent.replace(/\D/g, ''), 10);
        const son = el.textContent;

        // Yalnız tam sayı sayılır: "12.5 g" rakamları birleştirilince 125 olurdu.
        if (!Number.isFinite(hedef) || hedef === 0 || /\d[.,]\d{1,2}(?!\d)/.test(son)) {
            return;
        }

        // Rakamlar büyüdükçe genişlik değişmesin: son hâlin genişliği sabitlenir.
        el.style.minWidth = `${el.getBoundingClientRect().width}px`;
        const ayirici = (son.match(/\d([.,\s ])\d{3}/) || [])[1] || '';
        const bicimle = (n) => (ayirici ? String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ayirici) : String(n));
        const sure = Math.min(1800, 700 + hedef * 4);
        const basla = performance.now();
        const adim = (simdi) => {
            const t = Math.min(1, (simdi - basla) / sure);
            const e = 1 - 2 ** (-10 * t);

            el.textContent = t < 1 ? son.replace(/\d[\d.,\s ]*\d|\d/, bicimle(Math.round(hedef * e))) : son;

            if (t < 1) {
                requestAnimationFrame(adim);
            }
        };

        requestAnimationFrame(adim);
    },
};

function gozlemle() {
    const bekleyen = new Map();
    const gozlemci = new IntersectionObserver((girisler) => {
        const gorunen = girisler.filter((g) => g.isIntersecting)
            .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top || a.boundingClientRect.left - b.boundingClientRect.left);

        const siralar = {};

        for (const giris of gorunen) {
            const el = giris.target;
            const tur = bekleyen.get(el);

            gozlemci.unobserve(el);
            bekleyen.delete(el);
            siralar[tur] = (siralar[tur] ?? -1) + 1;
            oynatici[tur](el, Math.min(siralar[tur], 7));
        }
    }, { rootMargin: '0px 0px -6% 0px', threshold: 0.08 });

    // Önce bütün ölçümler, sonra bütün yazımlar: tek yerleşim hesabı.
    const adaylar = [];
    const ekle = (el, tur) => {
        if (!bekleyen.has(el) && !el.closest('[data-no-motion]')) {
            adaylar.push([el, tur]);
            bekleyen.set(el, tur);
        }
    };

    document.querySelectorAll(KART).forEach((el) => ekle(el, 'kart'));
    document.querySelectorAll(BLOK).forEach((el) => {
        if (!el.closest(KART) && !el.parentElement?.closest('.hc-v3-card:not(details)')) {
            ekle(el, 'blok');
        }
    });
    document.querySelectorAll(USTYAZI).forEach((el) => ekle(el, 'ustyazi'));
    document.querySelectorAll('[data-hc-say]').forEach((el) => ekle(el, 'sayac'));

    const basliklar = [...document.querySelectorAll(KINETIK)];
    const olcum = adaylar.map(([el]) => ekranda(el));
    const baslikOlcum = basliklar.map((b) => ekranda(b));

    adaylar.forEach(([el, tur], i) => {
        // Ekranda başlayan öğeye dokunulmaz; sayaç bile sayılmaz, yoksa
        // ilk karede görünen sayı sıfıra düşüp yeniden tırmanırdı.
        if (olcum[i]) {
            bekleyen.delete(el);

            return;
        }

        if (tur !== 'sayac') {
            el.classList.add('hc-m-gizli');
        }

        gozlemci.observe(el);
    });

    basliklar.forEach((baslik, i) => {
        if (baslikOlcum[i]) {
            return;
        }

        const kelimeler = kelimele(baslik);

        if (kelimeler.length === 0) {
            return;
        }

        kelimeler.forEach((k) => k.classList.add('hc-m-gizli'));
        bekleyen.set(baslik, 'baslik');
        gozlemci.observe(baslik);
    });
}

/** Tıklama noktasından saçılan parçacıklar ve bir şok halkası. */
export function patlat(x, y, secenek = {}) {
    if (kisitli()) {
        return;
    }

    const {
        renkler = ['#fb923c', '#f97316', '#fde047', '#fdba74', '#ffffff'],
        adet = 16,
        menzil = 78,
        konfeti = false,
    } = secenek;

    const kap = document.createElement('div');
    kap.className = 'hc-burst';
    kap.setAttribute('aria-hidden', 'true');
    kap.style.left = `${x}px`;
    kap.style.top = `${y}px`;
    document.body.append(kap);

    const bitenler = [];
    const halka = document.createElement('span');
    halka.className = 'hc-burst__ring';
    halka.style.borderColor = renkler[0];
    kap.append(halka);
    bitenler.push(halka.animate([
        { transform: 'translate(-50%, -50%) scale(.15)', opacity: 0.95, borderWidth: '6px' },
        { transform: `translate(-50%, -50%) scale(${konfeti ? 2.4 : 1})`, opacity: 0, borderWidth: '1px' },
    ], { duration: konfeti ? 900 : 560, easing: 'cubic-bezier(.08, .8, .3, 1)' }).finished);

    for (let i = 0; i < adet; i++) {
        const p = document.createElement('span');
        const cizgi = !konfeti && i % 3 === 0;
        p.className = `hc-burst__p${cizgi ? ' hc-burst__p--cizgi' : ''}${konfeti ? ' hc-burst__p--konfeti' : ''}`;
        p.style.background = renkler[i % renkler.length];
        kap.append(p);

        const aci = (i / adet) * Math.PI * 2 + Math.random() * 0.6;
        const r = menzil * (0.5 + Math.random() * 0.7);
        const dx = Math.cos(aci) * r;
        const dy = Math.sin(aci) * r;
        const don = (aci * 180) / Math.PI;

        const kareler = konfeti
            ? [
                { transform: 'translate(-50%, -50%) rotate(0) scale(1)', opacity: 1 },
                { transform: `translate(calc(-50% + ${dx}px), calc(-50% + ${dy}px)) rotate(${don * 2}deg) scale(1)`, opacity: 1, offset: 0.4 },
                { transform: `translate(calc(-50% + ${dx * 1.25}px), calc(-50% + ${dy + 170}px)) rotate(${don * 4}deg) scale(.6)`, opacity: 0 },
            ]
            : [
                { transform: `translate(-50%, -50%) rotate(${don}deg) translateX(0) scale(1)`, opacity: 1 },
                { transform: `translate(-50%, -50%) rotate(${don}deg) translateX(${r}px) scale(0)`, opacity: 0.2 },
            ];

        bitenler.push(p.animate(kareler, {
            duration: (konfeti ? 1500 : 620) + Math.random() * 320,
            easing: konfeti ? 'cubic-bezier(.2, .7, .4, 1)' : 'cubic-bezier(.05, .75, .3, 1)',
        }).finished);
    }

    Promise.allSettled(bitenler).then(() => kap.remove());
}

const merkez = (el) => {
    const r = el.getBoundingClientRect();

    return [r.left + r.width / 2, r.top + r.height / 2];
};

function patlamalar() {
    const tamamMi = () => {
        const cubuk = document.querySelector('.hc-step-progress');

        return cubuk !== null && cubuk.getAttribute('aria-valuenow') === cubuk.getAttribute('aria-valuemax');
    };

    // Yakalama evresi: Alpine durumu değiştirmeden önceki hâli okunur.
    document.addEventListener('click', (olay) => {
        const adim = olay.target.closest?.('.hc-step__toggle');

        if (adim) {
            if (adim.getAttribute('aria-pressed') === 'true') {
                return;
            }

            const onceTamam = tamamMi();
            const [x, y] = merkez(adim);
            patlat(x, y, { renkler: ['#22c55e', '#86efac', '#fde047', '#ffffff'], adet: 18, menzil: 90 });
            adim.animate([{ transform: 'scale(.7) rotate(-12deg)' }, { transform: 'scale(1.18) rotate(4deg)', offset: 0.55 }, { transform: 'none' }], { duration: 520, easing: YAY });

            // Alpine DOM'u mikro görevde günceller; zamanlayıcı ondan sonra
            // koşar. requestAnimationFrame arka plandaki sekmede hiç koşmuyordu.
            setTimeout(() => {
                if (!onceTamam && tamamMi()) {
                    const [cx, cy] = merkez(document.querySelector('.hc-step-progress'));
                    patlat(cx, cy, { konfeti: true, adet: 46, menzil: 240, renkler: ['#f97316', '#22c55e', '#fde047', '#38bdf8', '#f472b6', '#ffffff'] });
                }
            }, 30);

            return;
        }

        const dugme = olay.target.closest?.('.hc-v3-btn--primary, .hc-btn-primary, .hc-hero__cta, .hc-match__open, [data-hc-patla]');

        if (dugme) {
            const [x, y] = olay.detail === 0 ? merkez(dugme) : [olay.clientX, olay.clientY];
            patlat(x, y);
        }
    }, true);

    document.addEventListener('change', (olay) => {
        const kutu = olay.target;

        if (kutu.matches?.('#ingredients input[type="checkbox"]') && kutu.checked) {
            const [x, y] = merkez(kutu);
            patlat(x, y, { renkler: ['#22c55e', '#86efac', '#fde047'], adet: 10, menzil: 46 });
        }
    });
}

/** Kart fareyi izleyerek eğilir, üstünden ışık geçer. Yalnız fare ile. */
function egim() {
    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        return;
    }

    let aktif = null;
    let bekliyor = false;
    let son = null;

    const birak = (kart) => {
        kart.classList.remove('is-egiliyor');
        kart.style.removeProperty('--hc-rx');
        kart.style.removeProperty('--hc-ry');
    };

    document.addEventListener('pointermove', (olay) => {
        if (olay.pointerType !== 'mouse') {
            return;
        }

        son = olay;

        if (bekliyor) {
            return;
        }

        bekliyor = true;
        requestAnimationFrame(() => {
            bekliyor = false;
            const kart = son.target.closest?.('.hc-v3-card--interactive');

            if (aktif && aktif !== kart) {
                birak(aktif);
            }

            aktif = kart;

            if (!kart) {
                return;
            }

            const r = kart.getBoundingClientRect();
            const px = (son.clientX - r.left) / r.width - 0.5;
            const py = (son.clientY - r.top) / r.height - 0.5;

            kart.classList.add('is-egiliyor');
            kart.style.setProperty('--hc-rx', `${(-py * 9).toFixed(2)}deg`);
            kart.style.setProperty('--hc-ry', `${(px * 11).toFixed(2)}deg`);
            kart.style.setProperty('--hc-gx', `${((px + 0.5) * 100).toFixed(1)}%`);
            kart.style.setProperty('--hc-gy', `${((py + 0.5) * 100).toFixed(1)}%`);
        });
    }, { passive: true });

    // Yakalamalı pointerleave her alt öğeden çıkışta tetiklenir; yalnız
    // pencereden çıkış dinlenir, kart içi çıkışı pointermove zaten görüyor.
    document.documentElement.addEventListener('pointerleave', () => {
        if (aktif) {
            birak(aktif);
            aktif = null;
        }
    });

    // Mıknatıs: büyük çağrı düğmesi imlece doğru çekilir.
    document.querySelectorAll('.hc-hero__cta, [data-hc-miknatis]').forEach((dugme) => {
        dugme.addEventListener('pointermove', (olay) => {
            const r = dugme.getBoundingClientRect();
            dugme.style.setProperty('--hc-mx', `${((olay.clientX - r.left - r.width / 2) * 0.28).toFixed(1)}px`);
            dugme.style.setProperty('--hc-my', `${((olay.clientY - r.top - r.height / 2) * 0.4).toFixed(1)}px`);
        });
        dugme.addEventListener('pointerleave', () => {
            dugme.style.removeProperty('--hc-mx');
            dugme.style.removeProperty('--hc-my');
        });
    });
}

/** Uzun sayfada okuma çizgisi. */
function okumaCizgisi() {
    if (!document.querySelector('[data-hc-okuma]')) {
        return;
    }

    const cizgi = document.createElement('div');
    cizgi.className = 'hc-okuma';
    cizgi.setAttribute('aria-hidden', 'true');
    cizgi.append(document.createElement('span'));
    document.body.append(cizgi);

    let bekliyor = false;
    const guncelle = () => {
        bekliyor = false;
        const kok = document.documentElement;
        const oran = kok.scrollHeight > window.innerHeight ? window.scrollY / (kok.scrollHeight - window.innerHeight) : 0;
        cizgi.style.setProperty('--hc-okuma', Math.min(1, Math.max(0, oran)).toFixed(4));
    };

    window.addEventListener('scroll', () => {
        if (!bekliyor) {
            bekliyor = true;
            requestAnimationFrame(guncelle);
        }
    }, { passive: true });
    guncelle();
}

/**
 * Tıklanan kartın görseli tarif sayfasının kahraman görseline dönüşür.
 * Ad yalnız tıklanan karta verilir; aynı ad iki öğede olursa geçiş iptal olur.
 */
function morf() {
    document.addEventListener('click', (olay) => {
        const baglanti = olay.target.closest?.('a[href]');
        const kart = baglanti?.closest('.hc-v3-card--interactive');

        if (!kart || olay.defaultPrevented || olay.metaKey || olay.ctrlKey || olay.shiftKey || olay.button !== 0) {
            return;
        }

        document.querySelectorAll('[style*="view-transition-name"]').forEach((el) => el.style.removeProperty('view-transition-name'));
        const gorsel = kart.querySelector('img');

        if (gorsel) {
            gorsel.style.viewTransitionName = 'hc-kahraman';
        }
    });

    // Geri tuşuyla önbellekten dönülünce ad kalmasın.
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('.hc-v3-card img[style*="view-transition-name"]').forEach((el) => el.style.removeProperty('view-transition-name'));
    });
}

export function hareketiKur() {
    if (kisitli() || !('IntersectionObserver' in window) || !('animate' in Element.prototype)) {
        return;
    }

    document.documentElement.classList.add('hc-motion');
    gozlemle();
    patlamalar();
    egim();
    okumaCizgisi();
    morf();
}
