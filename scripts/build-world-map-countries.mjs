/**
 * Yönetim haritasının ülke sınırlarını, Türkçe adlarını, işaret noktalarını
 * ve yakınlaştırma kutularını üretir.
 *
 *   node scripts/build-world-map-countries.mjs
 *
 * Neden yeniden yazıldı (14 Eylül 2026):
 *
 * 1. Projeksiyon düz enlem-boylamdı (equirectangular) ve her koordinat tam
 *    sayıya yuvarlanıyordu. 960 piksel genişlikte bir piksel 0,375 derece;
 *    küçük ülkeler tırtıklı çıkıyor, yakınlaştırınca basamak basamak
 *    görünüyordu. Artık Equal Earth (eşit alanlı) projeksiyon ve bir ondalık
 *    hassasiyet kullanılıyor.
 *
 * 2. İşaret noktası ülkenin BÜTÜN sınır noktalarının ortalamasıydı. ABD'nin
 *    noktası Alaska yüzünden Kaliforniya'ya, Fransa'nınki Fransız Guyanası
 *    yüzünden Atlantik'e kayıyordu. Artık en büyük parçanın alan ağırlıklı
 *    merkezi kullanılıyor.
 *
 * 3. Ad eşleştirme kaldırılmış eski ISO kodlarını seçiyordu: Zimbabve RH
 *    (Rodezya), Yemen YD, Vanuatu NH olarak kaydedilmişti. Cloudflare ZW, YE,
 *    VU gönderdiği için o ülkelerden gelen ziyaretler hiç sayılmıyordu.
 *
 * Çıktı statik Blade + PHP: üretimde Node da veri dosyası da gerekmez.
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { feature } from 'topojson-client';

const COZUNURLUK = process.env.HARITA_COZUNURLUK ?? '50m';
const GENISLIK = 960;

const topo = JSON.parse(readFileSync(`node_modules/world-atlas/countries-${COZUNURLUK}.json`, 'utf8'));
const dunya = feature(topo, topo.objects.countries);

const ingilizce = new Intl.DisplayNames(['en'], { type: 'region' });
const turkce = new Intl.DisplayNames(['tr'], { type: 'region' });

// ISO 3166-3 ile kaldırılmış ve yeniden atanmamış kodlar. Intl bunlara hâlâ
// güncel ülkenin adını veriyor (RH=Zimbabwe, YD=Yemen) ve alfabetik sırada
// öne geçtiklerinde gerçek kodu eziyorlardı. BY, SK, AI, BQ gibi yeniden
// atanmış kodlar bilerek listede yok: onlar bugün geçerli.
const eskiKodlar = new Set([
    'BU', 'CS', 'CT', 'DD', 'DY', 'FQ', 'FX', 'HV', 'JT', 'MI', 'NH', 'NQ', 'NT',
    'PC', 'PU', 'PZ', 'RH', 'SU', 'TP', 'VD', 'WK', 'YD', 'YU', 'ZR',
    'UK', 'EU', 'EZ', 'UN', 'QO', 'QU', 'AN', 'SF', 'XA', 'XB',
]);

const adaKod = new Map();
for (let i = 65; i <= 90; i++) {
    for (let j = 65; j <= 90; j++) {
        const kod = String.fromCharCode(i) + String.fromCharCode(j);
        if (eskiKodlar.has(kod)) continue;
        let ad;
        try { ad = ingilizce.of(kod); } catch { continue; }
        if (! ad || ad === kod) continue;
        if (! adaKod.has(ad.toLowerCase())) adaKod.set(ad.toLowerCase(), kod);
    }
}

// Natural Earth bazı ülkeleri kısaltılmış ya da eski adla yazıyor.
const takma = {
    'united states of america': 'US', 'republic of serbia': 'RS', 'czechia': 'CZ',
    'macedonia': 'MK', 'north macedonia': 'MK', 'bosnia and herz.': 'BA',
    'dominican rep.': 'DO', 'central african rep.': 'CF', 's. sudan': 'SS',
    'dem. rep. congo': 'CD', 'eq. guinea': 'GQ', 'w. sahara': 'EH',
    'solomon is.': 'SB', 'falkland is.': 'FK', 'fr. s. antarctic lands': 'TF',
    "côte d'ivoire": 'CI', 'somaliland': 'SO', 'kosovo': 'XK', 'n. cyprus': 'CY',
    'turkey': 'TR', 'swaziland': 'SZ', 'eswatini': 'SZ', 'myanmar': 'MM',
    'laos': 'LA', 'vietnam': 'VN', 'south korea': 'KR', 'north korea': 'KP',
    'russia': 'RU', 'iran': 'IR', 'syria': 'SY', 'tanzania': 'TZ',
    'venezuela': 'VE', 'bolivia': 'BO', 'moldova': 'MD', 'brunei': 'BN',
    'palestine': 'PS', 'congo': 'CG', 'trinidad and tobago': 'TT',
    'zimbabwe': 'ZW', 'yemen': 'YE', 'vanuatu': 'VU',
    'cabo verde': 'CV', 'st. vin. and gren.': 'VC', 'st. kitts and nevis': 'KN',
    'antigua and barb.': 'AG', 'são tomé and principe': 'ST', 'marshall is.': 'MH',
    'fr. polynesia': 'PF', 'n. mariana is.': 'MP', 'u.s. virgin is.': 'VI',
    'british virgin is.': 'VG', 'cayman is.': 'KY', 'turks and caicos is.': 'TC',
    'faeroe is.': 'FO', 'heard i. and mcdonald is.': 'HM', 'st. pierre and miquelon': 'PM',
    'wallis and futuna is.': 'WF', 'cook is.': 'CK', 'pitcairn is.': 'PN',
    's. geo. and the is.': 'GS', 'br. indian ocean ter.': 'IO', 'åland': 'AX',
    'micronesia': 'FM', 'curaçao': 'CW', 'sint maarten': 'SX', 'st-martin': 'MF',
    'st-barthélemy': 'BL', 'saint helena': 'SH', 'macao': 'MO', 'hong kong': 'HK',
    'taiwan': 'TW', 'timor-leste': 'TL', 'guinea-bissau': 'GW', 'gambia': 'GM',
    'bahamas': 'BS', 'american samoa': 'AS', 'isle of man': 'IM', 'jersey': 'JE',
    'guernsey': 'GG', 'vatican': 'VA', 'aland': 'AX', 'saint lucia': 'LC',
};

// Miller silindirik projeksiyonu. Önce Equal Earth kullanıldı: alanlar doğruydu
// ama kenar meridyenleri büküldüğü için Kuzey Amerika ve Rusya sola yatık,
// basık görünüyordu ve harita "kayık" okundu (14 Eylül 2026, kullanıcı
// geri bildirimi). Google Analytics gibi alışılmış haritalar düz meridyenli
// silindirik projeksiyon kullanır; Miller, Mercator'un kutup şişmesini
// yumuşatır ve Grönland'ı Afrika büyüklüğüne çıkarmaz.
const derece = Math.PI / 180;
const projekte = ([lng, lat]) => [
    lng * derece,
    -1.25 * Math.log(Math.tan(Math.PI / 4 + 0.4 * lat * derece)),
];

// Ölçek: 360 derece boylam tuvali doldursun.
const OLCEK = GENISLIK / (2 * Math.PI);
const nokta = (lnglat) => {
    const [px, py] = projekte(lnglat);
    return [px * OLCEK + GENISLIK / 2, py * OLCEK];
};

// Dikey görüş: 83° kuzey (Grönland'ın ucu) ile 57° güney (Ateş Toprakları).
// Antarktika zaten çıkarılıyor; daha güneyde ziyaretçi ülkesi yok.
const KUZEY = 83;
const GUNEY = -57;

const alan = (halka) => {
    let a = 0;
    for (let i = 0, n = halka.length; i < n; i++) {
        const [x1, y1] = halka[i];
        const [x2, y2] = halka[(i + 1) % n];
        a += x1 * y2 - x2 * y1;
    }
    return a / 2;
};

const merkez = (halka) => {
    const a = alan(halka);
    if (Math.abs(a) < 1e-9) {
        const sx = halka.reduce((t, p) => t + p[0], 0), sy = halka.reduce((t, p) => t + p[1], 0);
        return [sx / halka.length, sy / halka.length];
    }
    let cx = 0, cy = 0;
    for (let i = 0, n = halka.length; i < n; i++) {
        const [x1, y1] = halka[i];
        const [x2, y2] = halka[(i + 1) % n];
        const f = x1 * y2 - x2 * y1;
        cx += (x1 + x2) * f;
        cy += (y1 + y2) * f;
    }
    return [cx / (6 * a), cy / (6 * a)];
};

// Douglas-Peucker sadeleştirmesi. 50m veri olduğu gibi 1 MB SVG ediyordu ve
// her panel açılışında satır içi yükleniyordu; 110m ise Malta, Bahreyn,
// Singapur gibi ziyaret gelebilecek küçük ülkeleri hiç içermiyor. Tolerans
// piksel altında: en yakın zoomda (12x) bile sapma 1,5 pikseli geçmez.
const TOLERANS = Number(process.env.HARITA_TOLERANS ?? 0.15);
const sadelestir = (noktalar) => {
    if (noktalar.length <= 4 || TOLERANS <= 0) return noktalar;
    const tut = new Uint8Array(noktalar.length);
    tut[0] = tut[noktalar.length - 1] = 1;
    const yigin = [[0, noktalar.length - 1]];
    while (yigin.length) {
        const [bas, son] = yigin.pop();
        const [x1, y1] = noktalar[bas], [x2, y2] = noktalar[son];
        const dx = x2 - x1, dy = y2 - y1, uzunluk = Math.hypot(dx, dy);
        let enUzak = 0, indis = -1;
        for (let i = bas + 1; i < son; i++) {
            const [px, py] = noktalar[i];
            // Kapalı halkada ilk ve son nokta aynıdır; doğru parçası sıfır
            // uzunlukta olur ve doğruya mesafe formülü her noktaya 0 verir -
            // bütün halka silinirdi. O durumda noktaya mesafe ölçülür.
            const mesafe = uzunluk === 0
                ? Math.hypot(px - x1, py - y1)
                : Math.abs(dy * px - dx * py + x2 * y1 - y2 * x1) / uzunluk;
            if (mesafe > enUzak) { enUzak = mesafe; indis = i; }
        }
        if (enUzak > TOLERANS && indis > 0) {
            tut[indis] = 1;
            yigin.push([bas, indis], [indis, son]);
        }
    }
    return noktalar.filter((_, i) => tut[i]);
};

const bir = (n) => Math.round(n * 10) / 10;
const yol = (halka) => {
    const noktalar = [];
    for (const [px, py] of sadelestir(halka)) {
        const p = [bir(px), bir(py)];
        const son = noktalar[noktalar.length - 1];
        if (! son || son[0] !== p[0] || son[1] !== p[1]) noktalar.push(p);
    }
    return noktalar.length < 3 ? '' : 'M' + noktalar.map(([x, y]) => `${x} ${y}`).join('L') + 'Z';
};

const yollar = [];
const ulkeler = new Map();
const ulkeParcalari = new Map();
const eslesmeyen = [];
let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

for (const f of dunya.features) {
    const ad = String(f.properties?.name ?? '');
    if (ad === 'Antarctica') continue; // Ziyaretçi ülkesi değil; haritanın beşte birini kaplıyordu.

    const kod = takma[ad.toLowerCase()] ?? adaKod.get(ad.toLowerCase());
    if (! kod) { eslesmeyen.push(ad); continue; }

    const g = f.geometry;
    if (! g) continue;
    const cokgenler = g.type === 'Polygon' ? [g.coordinates] : g.type === 'MultiPolygon' ? g.coordinates : [];

    let d = '';
    const parcalar = [];

    for (const cokgen of cokgenler) {
        const projeli = cokgen.map((halka) => {
            // Tarih çizgisini geçen halka (Rusya'nın doğu ucu, Fiji):
            // boylamlar -180/+180 arasında atladığında haritanın bir ucundan
            // öbürüne çizgi çekiliyordu. Negatif boylamlar 360 kaydırılır;
            // parça sağ kenardan taşar ve kırpılır, şekil bozulmaz.
            const lngler = halka.map(([lng]) => lng);
            const genis = Math.max(...lngler) - Math.min(...lngler) > 180;
            return halka.map(([lng, lat]) => nokta([genis && lng < 0 ? lng + 360 : lng, lat]));
        });

        for (const halka of projeli) d += yol(halka);

        const dis = projeli[0];
        const a = Math.abs(alan(dis));
        const xs = dis.map((p) => p[0]), ys = dis.map((p) => p[1]);
        parcalar.push({
            alan: a,
            merkez: merkez(dis),
            kutu: [Math.min(...xs), Math.min(...ys), Math.max(...xs), Math.max(...ys)],
        });

        for (const [px, py] of dis) {
            if (px > GENISLIK) continue;
            minX = Math.min(minX, px); maxX = Math.max(maxX, px);
            minY = Math.min(minY, py); maxY = Math.max(maxY, py);
        }
    }

    if (d === '' || parcalar.length === 0) continue;

    // Natural Earth bazı ülkeleri iki kayıt olarak veriyor (Kıbrıs + Kuzey
    // Kıbrıs, Somali + Somaliland). Önceden ikisi de aynı id ile ayrı <path>
    // basılıyordu; <use href="#ulke-CY"> yalnız İLK öğeyi boyadığı için
    // Kıbrıs'ın yarısı hiç boyanmıyordu. Artık parçalar tek yolda birleşir.
    const birikim = ulkeParcalari.get(kod) ?? { ad, d: '', parcalar: [] };
    birikim.d += d;
    birikim.parcalar.push(...parcalar);
    ulkeParcalari.set(kod, birikim);
}

for (const [kod, { ad, d, parcalar }] of ulkeParcalari) {
    // Çizgi kalınlığı yakınlaştırmayla büyümesin. Öznitelik yolun kendisinde
    // duruyor çünkü <use> kopyası belge stil kurallarını her tarayıcıda
    // aynı biçimde almıyor; öznitelik kopyayla birlikte taşınır.
    yollar.push(`<path id="ulke-${kod}" vector-effect="non-scaling-stroke" d="${d}"/>`);

    // İşaret ve yakınlaştırma ANAKARAYA göre. Kutuya yalnız ana parçaya
    // KOMŞU büyük parçalar girer: Japonya'da Hokkaido girer, ABD'de Alaska
    // girmez (ABD'ye yakınlaşan kişi 48 eyaleti görmek ister).
    //
    // Önce alan oranı kullanılıyordu (ana parçanın dörtte biri). Miller
    // projeksiyonu kuzeyi büyüttüğü için Alaska bu eşiği geçti ve ABD'ye
    // yakınlaşınca kutunun yarısı Pasifik'ti. Mesafe projeksiyondan
    // bağımsız: ana kutuya kendi boyutunun %15i kadar yakın parça. Yarısı
    // denendi; ABD kutusu (x 147-301) ile Alaska (x 32-96) yine birleşti.
    parcalar.sort((a, b) => b.alan - a.alan);
    const ana = parcalar[0];
    const [ax1, ay1, ax2, ay2] = ana.kutu;
    const payX = (ax2 - ax1) * 0.15, payY = (ay2 - ay1) * 0.15;
    const kutuParcalari = parcalar.filter((p) => p === ana || (
        p.alan >= ana.alan * 0.1
        && p.kutu[0] <= ax2 + payX && p.kutu[2] >= ax1 - payX
        && p.kutu[1] <= ay2 + payY && p.kutu[3] >= ay1 - payY
    ));
    const kutu = [
        Math.min(...kutuParcalari.map((p) => p.kutu[0])),
        Math.min(...kutuParcalari.map((p) => p.kutu[1])),
        Math.max(...kutuParcalari.map((p) => p.kutu[2])),
        Math.max(...kutuParcalari.map((p) => p.kutu[3])),
    ];

    ulkeler.set(kod, {
        kod,
        ad: turkce.of(kod) ?? ad,
        x: bir(ana.merkez[0]),
        y: bir(ana.merkez[1]),
        kutu: [bir(kutu[0]), bir(kutu[1]), bir(kutu[2] - kutu[0]), bir(kutu[3] - kutu[1])],
    });
}

// Görüş kutusu: yatayda gerçek geometri, dikeyde sabit enlemler. Miller'da
// kuzey adaları çok yükseğe çıkar; geometriden hesaplamak haritanın üstünde
// geniş bir buz bandı bırakırdı.
const PAY = 4;
const ust = nokta([0, KUZEY])[1];
const alt = nokta([0, GUNEY])[1];
const gorus = [bir(minX - PAY), bir(ust), bir(maxX - minX + PAY * 2), bir(alt - ust)];

for (const kod of ulkeler.keys()) {
    if (eskiKodlar.has(kod)) throw new Error(`Kaldırılmış ISO kodu üretildi: ${kod}`);
}

writeFileSync(
    'resources/views/components/admin/world-map-countries.blade.php',
    `{{-- Bu dosya üretilmiştir: scripts/build-world-map-countries.mjs\n     Kaynak: Natural Earth ${COZUNURLUK} (kamu malı), world-atlas paketi; Miller projeksiyonu.\n     Elle düzenlemeyin; değiştirmek için betiği çalıştırın. --}}\n<g class="hc-world-map__ulkeler" aria-hidden="true">${yollar.join('')}</g>\n`
);

const satirlar = [...ulkeler.values()]
    .sort((a, b) => a.kod.localeCompare(b.kod))
    .map((u) => `        '${u.kod}' => ['label' => '${String(u.ad).replace(/'/g, "\\'")}', 'x' => ${u.x}, 'y' => ${u.y}, 'box' => [${u.kutu.join(', ')}]],`)
    .join('\n');

writeFileSync(
    'app/Support/WorldMapCountries.php',
    `<?php

declare(strict_types=1);

namespace App\\Support;

/**
 * Yönetim haritasının ülke tablosu.
 *
 * Bu dosya üretilmiştir: scripts/build-world-map-countries.mjs
 * Elle düzenlemeyin. Koordinatlar Miller projeksiyonunda, VIEWBOX ile
 * aynı uzaydadır; x/y ana parçanın alan ağırlıklı merkezi, box yakınlaştırma
 * kutusudur [x, y, genişlik, yükseklik].
 */
final class WorldMapCountries
{
    /** @var array{0: float, 1: float, 2: float, 3: float} */
    public const VIEWBOX = [${gorus.join(', ')}];

    /** @var array<string, array{label: string, x: float, y: float, box: array{0: float, 1: float, 2: float, 3: float}}> */
    public const ALL = [
${satirlar}
    ];
}
`
);

const boyut = Buffer.byteLength(yollar.join(''));
console.log(`cozunurluk: ${COZUNURLUK} | ulke: ${ulkeler.size} | svg: ${(boyut / 1024).toFixed(0)} KB | viewBox: ${gorus.join(' ')}`);
if (eslesmeyen.length) console.log('eslesmeyen:', eslesmeyen.join(', '));
