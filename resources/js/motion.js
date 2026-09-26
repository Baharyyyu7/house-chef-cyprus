// Ates hareket sistemi.
//
// Sitenin dili firin, ates ve sicak isik. Hareketler de oradan geliyor:
// basilan buton kivilcim sacar, hero'da korlar yukselir, imlecin gezdigi
// kartta sicak bir isik dolasir. Hepsi tek bir fixed katmanda, yalniz
// transform ve opacity ile, sayfa yerlesimine dokunmadan calisir.
//
// Hareket kisitliysa kivilcim yerine tek bir sonuk halka kalir (basin
// onaylandigi gorunsun diye), korlar hic baslamaz.

const azalt = window.matchMedia('(prefers-reduced-motion: reduce)');
const inceImlec = window.matchMedia('(hover: hover) and (pointer: fine)');
const veriTasarrufu = () => navigator.connection?.saveData === true;

// Tema gecisinin daireyi nereden acacagini bilmesi icin son basilan nokta.
window.hcSonIsaret = null;

/* ---------------------------------------------------------------- *
 * 1. Kivilcim katmani
 * ---------------------------------------------------------------- */

const TAVAN = 90;
const RENKLER = ['#ffe29a', '#ffc15e', '#ff9a2e', '#ff6b00', '#f2460d'];
let katman = null;
let canli = 0;

const katmanAl = () => {
    if (katman?.isConnected) return katman;
    katman = document.createElement('div');
    katman.className = 'hc-spark-layer';
    katman.setAttribute('aria-hidden', 'true');
    document.body.append(katman);

    return katman;
};

const halka = (x, y, guc) => {
    const el = document.createElement('span');
    el.className = 'hc-spark-ring';
    el.style.left = `${x}px`;
    el.style.top = `${y}px`;
    katmanAl().append(el);
    el.animate(
        [
            { transform: 'translate(-50%, -50%) scale(.2)', opacity: 0.85 },
            { transform: `translate(-50%, -50%) scale(${2.2 * guc})`, opacity: 0 },
        ],
        { duration: azalt.matches ? 240 : 520, easing: 'cubic-bezier(.16, 1, .3, 1)' },
    ).onfinish = () => el.remove();
};

// Basilan noktada bir anlik beyaz-sari cekirdek: patlamanin kaynagi.
const parla = (x, y, guc) => {
    if (azalt.matches) return;
    const el = document.createElement('span');
    el.className = 'hc-spark-flash';
    el.style.left = `${x}px`;
    el.style.top = `${y}px`;
    katmanAl().append(el);
    el.animate(
        [
            { transform: 'translate(-50%, -50%) scale(.3)', opacity: 1 },
            { transform: `translate(-50%, -50%) scale(${1.6 * guc})`, opacity: 0 },
        ],
        { duration: 300, easing: 'cubic-bezier(.16, 1, .3, 1)' },
    ).onfinish = () => el.remove();
};

export const kivilcim = (x, y, { adet = 18, guc = 1 } = {}) => {
    halka(x, y, guc);
    parla(x, y, guc);
    if (azalt.matches) return;

    const n = Math.max(0, Math.min(adet, TAVAN - canli));
    for (let i = 0; i < n; i += 1) {
        const el = document.createElement('span');
        el.className = 'hc-spark';
        const aci = (Math.PI * 2 * i) / n + (Math.random() - 0.5) * 0.7;
        const hiz = (80 + Math.random() * 120) * guc;
        const dx = Math.cos(aci) * hiz;
        const dy = Math.sin(aci) * hiz;
        const boy = 3 + Math.random() * 4;
        const derece = (aci * 180) / Math.PI;
        el.style.left = `${x}px`;
        el.style.top = `${y}px`;
        el.style.width = `${boy * 3}px`;
        el.style.height = `${boy}px`;
        el.style.background = RENKLER[i % RENKLER.length];
        katmanAl().append(el);
        canli += 1;

        // Son karede hafif dusus: kivilcim havada asili kalmaz, sonuyor.
        el.animate(
            [
                { transform: `translate(-50%, -50%) rotate(${derece}deg) scaleX(1.8)`, opacity: 1 },
                { transform: `translate(calc(-50% + ${dx * 0.72}px), calc(-50% + ${dy * 0.72}px)) rotate(${derece}deg) scaleX(1)`, opacity: 1, offset: 0.5 },
                { transform: `translate(calc(-50% + ${dx}px), calc(-50% + ${dy + 36 * guc}px)) rotate(${derece + 35}deg) scaleX(.25)`, opacity: 0 },
            ],
            { duration: 520 + Math.random() * 340, easing: 'cubic-bezier(.16, 1, .3, 1)' },
        ).onfinish = () => {
            el.remove();
            canli -= 1;
        };
    }
};

window.hcKivilcim = kivilcim;

/* ---------------------------------------------------------------- *
 * 2. Basinca kivilcim
 * ---------------------------------------------------------------- */

const TETIK = [
    '.hc-btn-primary', '.hc-btn-gold', '.hc-btn-danger',
    '.hc-v3-btn--primary', '.hc-v3-btn--danger',
    '.hc-future-btn', '.hc-hero__cta', '.hc-home-command__submit',
    '[data-theme-switch]', '[data-hc-burst]',
].join(',');

const secenek = (hedef) => (hedef.dataset.hcBurst === 'big' || hedef.matches('.hc-btn-danger, .hc-v3-btn--danger')
    ? { adet: 32, guc: 1.8 }
    : { adet: 18, guc: 1 });

const kapali = (hedef) => hedef.disabled || hedef.getAttribute('aria-disabled') === 'true';

document.addEventListener('pointerdown', (olay) => {
    window.hcSonIsaret = { x: olay.clientX, y: olay.clientY };
    if (olay.button !== 0) return;
    const hedef = olay.target.closest(TETIK);
    if (!hedef || kapali(hedef)) return;
    kivilcim(olay.clientX, olay.clientY, secenek(hedef));
}, { passive: true });

// Klavyeyle basilan butonda da onay gorunsun; kivilcim merkezden cikar.
document.addEventListener('click', (olay) => {
    if (olay.detail !== 0) return;
    const hedef = olay.target.closest(TETIK);
    if (!hedef || kapali(hedef)) return;
    const r = hedef.getBoundingClientRect();
    window.hcSonIsaret = { x: r.left + r.width / 2, y: r.top + r.height / 2 };
    kivilcim(window.hcSonIsaret.x, window.hcSonIsaret.y, secenek(hedef));
});

/* ---------------------------------------------------------------- *
 * 3. Kartlarda gezen sicak isik
 * ---------------------------------------------------------------- */

const ISIK = '.hc-v3-card--interactive, .hc-collection-card, .hc-card-hover, .hc-tool-card, .hc-testimonial-card, .hc-admin-metric, [data-hc-glow]';
let isikKare = 0;

const isikKur = (kart) => {
    if (kart.dataset.hcIsik) return;
    kart.dataset.hcIsik = '1';
    if (getComputedStyle(kart).position === 'static') kart.style.position = 'relative';
    const parilti = document.createElement('span');
    parilti.className = 'hc-heat-glow';
    parilti.setAttribute('aria-hidden', 'true');
    kart.append(parilti);
};

document.addEventListener('pointermove', (olay) => {
    if (!inceImlec.matches || azalt.matches) return;
    const kart = olay.target.closest?.(ISIK);
    if (!kart) return;
    isikKur(kart);
    cancelAnimationFrame(isikKare);
    isikKare = requestAnimationFrame(() => {
        const r = kart.getBoundingClientRect();
        kart.style.setProperty('--hc-mx', `${olay.clientX - r.left}px`);
        kart.style.setProperty('--hc-my', `${olay.clientY - r.top}px`);
    });
}, { passive: true });

/* ---------------------------------------------------------------- *
 * 4. Hero korlari ve atesleme
 * ---------------------------------------------------------------- */

const korlar = (tuval) => {
    const ctx = tuval.getContext('2d');
    if (!ctx) return;

    const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
    let g = 0;
    let y = 0;
    let parcalar = [];
    let calisiyor = false;
    let gorunur = true;
    let kare = 0;
    let onceki = 0;
    const imlec = { x: -9999, y: -9999 };

    const yeni = (baslangic) => {
        // Isik kaynagi sag ustte, korlar sag alttan ve ortadan yukselir.
        const sagda = Math.random() < 0.68;
        return {
            x: sagda ? g * (0.48 + Math.random() * 0.52) : Math.random() * g,
            y: baslangic ? Math.random() * y : y + Math.random() * 40,
            vx: (Math.random() - 0.5) * 0.25,
            vy: -(0.35 + Math.random() * 0.95),
            r: 0.8 + Math.random() * 2.1,
            faz: Math.random() * Math.PI * 2,
            omur: 0,
            azami: 260 + Math.random() * 420,
        };
    };

    const olc = () => {
        const r = tuval.getBoundingClientRect();
        g = r.width;
        y = r.height;
        tuval.width = Math.round(g * dpr);
        tuval.height = Math.round(y * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        const adet = g < 640 ? 22 : 46;
        parcalar = Array.from({ length: adet }, () => yeni(true));
    };

    const ciz = (simdi) => {
        const adim = Math.min(3, (simdi - onceki) / 16.7 || 1);
        onceki = simdi;
        const karanlik = document.documentElement.classList.contains('dark');
        ctx.clearRect(0, 0, g, y);
        ctx.globalCompositeOperation = karanlik ? 'lighter' : 'source-over';

        for (let i = 0; i < parcalar.length; i += 1) {
            const p = parcalar[i];
            p.omur += adim;
            p.faz += 0.03 * adim;
            const itX = p.x - imlec.x;
            const itY = p.y - imlec.y;
            const mesafe = itX * itX + itY * itY;
            if (mesafe < 14400) {
                const kuvvet = (1 - mesafe / 14400) * 0.6;
                p.vx += (itX / 120) * kuvvet * adim;
                p.vy += (itY / 120) * kuvvet * 0.5 * adim;
            }
            p.vx *= 0.985;
            p.x += (p.vx + Math.sin(p.faz) * 0.35) * adim;
            p.y += p.vy * adim;

            const oran = p.omur / p.azami;
            if (oran >= 1 || p.y < -20 || p.x < -20 || p.x > g + 20) {
                parcalar[i] = yeni(false);
                continue;
            }

            // Dogar, parlar, soner. Titreme gercek atesteki gibi duzensiz.
            const parlak = Math.sin(Math.PI * oran) * (0.75 + Math.sin(p.faz * 3.1) * 0.25);
            const sicak = karanlik ? (oran < 0.4 ? '255, 214, 140' : '255, 132, 38') : (oran < 0.4 ? '255, 140, 20' : '232, 76, 0');
            ctx.fillStyle = karanlik ? `rgba(255, 110, 20, ${0.16 * parlak})` : `rgba(255, 96, 0, ${0.07 * parlak})`;
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r * (karanlik ? 3.6 : 2.2), 0, Math.PI * 2);
            ctx.fill();
            ctx.fillStyle = `rgba(${sicak}, ${(karanlik ? 0.95 : 0.8) * parlak})`;
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fill();
        }

        kare = requestAnimationFrame(ciz);
    };

    const basla = () => {
        if (calisiyor || !gorunur || document.hidden || azalt.matches || veriTasarrufu()) return;
        calisiyor = true;
        onceki = performance.now();
        kare = requestAnimationFrame(ciz);
    };

    const dur = () => {
        calisiyor = false;
        cancelAnimationFrame(kare);
    };

    olc();
    new ResizeObserver(() => olc()).observe(tuval);
    new IntersectionObserver(([giris]) => {
        gorunur = giris.isIntersecting;
        gorunur ? basla() : dur();
    }).observe(tuval);
    document.addEventListener('visibilitychange', () => (document.hidden ? dur() : basla()));
    azalt.addEventListener('change', () => {
        if (azalt.matches) {
            dur();
            ctx.clearRect(0, 0, g, y);
        } else {
            basla();
        }
    });

    const kahraman = tuval.parentElement;
    kahraman.addEventListener('pointermove', (olay) => {
        const r = tuval.getBoundingClientRect();
        imlec.x = olay.clientX - r.left;
        imlec.y = olay.clientY - r.top;
    }, { passive: true });
    kahraman.addEventListener('pointerleave', () => {
        imlec.x = -9999;
        imlec.y = -9999;
    });

    basla();
};

// Basligin altindaki turuncu cizgi tutusur ve ucundan kivilcim sacar.
// Sayfanin tek sahnelenmis ani bu; digerleri geri bildirim.
const atesle = () => {
    const baslik = document.querySelector('.hc-hero__title');
    if (!baslik || azalt.matches) return;
    window.setTimeout(() => {
        const r = baslik.getBoundingClientRect();
        if (r.bottom < 0 || r.top > window.innerHeight) return;
        kivilcim(r.left + 52, r.bottom - 3, { adet: 22, guc: 1.35 });
    }, 1020);
};

/* ---------------------------------------------------------------- *
 * 5. Eslestirme vitrini tamamlaninca
 * ---------------------------------------------------------------- */

const vitrin = (kok) => {
    let onceki = kok.classList.contains('is-done');
    new MutationObserver(() => {
        const simdi = kok.classList.contains('is-done');
        if (simdi && !onceki && !document.hidden) {
            const halkaEl = kok.querySelector('.hc-match__ring') || kok;
            const r = halkaEl.getBoundingClientRect();
            if (r.bottom > 0 && r.top < window.innerHeight) {
                kivilcim(r.left + r.width / 2, r.top + r.height / 2, { adet: 20, guc: 1.4 });
            }
        }
        onceki = simdi;
    }).observe(kok, { attributes: true, attributeFilter: ['class'] });
};

const kur = () => {
    document.querySelectorAll('[data-hc-embers]:not([data-hc-hazir])').forEach((tuval) => {
        tuval.setAttribute('data-hc-hazir', '');
        korlar(tuval);
        atesle();
    });
    document.querySelectorAll('.hc-match:not([data-hc-hazir])').forEach((kok) => {
        kok.setAttribute('data-hc-hazir', '');
        vitrin(kok);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', kur, { once: true });
} else {
    kur();
}
document.addEventListener('livewire:navigated', kur);
