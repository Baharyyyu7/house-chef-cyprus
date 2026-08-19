/**
 * Yönetim haritasının ülke sınırlarını ve Türkçe adlarını üretir.
 *
 * Önceki hâl nokta ızgarasıydı: kıtalar doğruydu ama ülkeler seçilemiyordu,
 * yalnız üzerlerine birer nokta konuyordu. Google Analytics'teki gibi ülkenin
 * kendi alanının boyanması için her ülkenin sınır yolu gerekiyor.
 *
 * Ülke kodu eşleşmesi çevrimdışı yapılır: Natural Earth İngilizce adları ile
 * Intl.DisplayNames'in ürettiği adlar karşılaştırılır. Böylece elle tutulan
 * ve zamanla bayatlayan bir kod tablosu taşımıyoruz.
 *
 * Çıktı statik Blade + PHP: üretimde Node da veri dosyası da gerekmez.
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { feature } from 'topojson-client';

const GENISLIK = 960;
const YUKSEKLIK = 430;
const UST = 18;   // ~80 kuzey
const ALT = 362;  // ~-58 güney; Antarktika kırpılır

const topo = JSON.parse(readFileSync('node_modules/world-atlas/countries-110m.json', 'utf8'));
const dunya = feature(topo, topo.objects.countries);

const ingilizce = new Intl.DisplayNames(['en'], { type: 'region' });
const turkce = new Intl.DisplayNames(['tr'], { type: 'region' });

// Alfa-2 kodundan İngilizce ada bir sözlük kurup ters çeviriyoruz.
// Artık kullanılmayan kodlar. Intl bunlara hâlâ güncel ülkenin adını
// veriyor (DD=Germany, FX=France, UK=United Kingdom) ve alfabetik sırada
// öne geçtiklerinde gerçek kodu eziyorlar - ülke haritadan kayboluyor.
const eskiKodlar = new Set(['DD', 'FX', 'UK', 'SU', 'YU', 'CS', 'ZR', 'AN', 'TP', 'BU', 'NT', 'SF', 'AI', 'QU', 'EU', 'UN']);

const adaKod = new Map();
for (let i = 65; i <= 90; i++) {
    for (let j = 65; j <= 90; j++) {
        const kod = String.fromCharCode(i) + String.fromCharCode(j);
        if (eskiKodlar.has(kod)) continue;
        let ad;
        try { ad = ingilizce.of(kod); } catch { continue; }
        if (! ad || ad === kod) continue;
        // Bazı eski kodlar güncel olanla aynı adı taşıyor (FX=France,
        // UK=United Kingdom). Sonra gelen öncekini ezerse ülke kaybolur;
        // ilk gelen kazanır çünkü alfabetik olarak güncel kod önce geliyor.
        if (! adaKod.has(ad.toLowerCase())) {
            adaKod.set(ad.toLowerCase(), kod);
        }
    }
}

// Natural Earth bazı ülkeleri başka adla yazıyor.
const takma = {
    'united states of america': 'US',
    'republic of serbia': 'RS',
    'czechia': 'CZ',
    'macedonia': 'MK',
    'north macedonia': 'MK',
    'bosnia and herz.': 'BA',
    'dominican rep.': 'DO',
    'central african rep.': 'CF',
    's. sudan': 'SS',
    'dem. rep. congo': 'CD',
    'eq. guinea': 'GQ',
    'w. sahara': 'EH',
    'solomon is.': 'SB',
    'falkland is.': 'FK',
    'fr. s. antarctic lands': 'TF',
    "côte d'ivoire": 'CI',
    'somaliland': 'SO',
    'kosovo': 'XK',
    'n. cyprus': 'CY',
    'turkey': 'TR',
    'swaziland': 'SZ',
    'eswatini': 'SZ',
    'myanmar': 'MM',
    'laos': 'LA',
    'vietnam': 'VN',
    'south korea': 'KR',
    'north korea': 'KP',
    'russia': 'RU',
    'iran': 'IR',
    'syria': 'SY',
    'tanzania': 'TZ',
    'venezuela': 'VE',
    'bolivia': 'BO',
    'moldova': 'MD',
    'brunei': 'BN',
    'palestine': 'PS',
    'congo': 'CG',
    'trinidad and tobago': 'TT',
};

const x = (lng) => ((lng + 180) / 360) * GENISLIK;
const y = (lat) => ((90 - lat) / 180) * YUKSEKLIK;
// Tam sayıya yuvarlanır. 960 piksel genişlikte 176 ülkenin sınırında ondalık
// basamak gözle görülmüyor ama dosyanın beşte birini kaplıyordu.
const yuvarla = (n) => Math.round(n);

const yollar = [];
const etiketler = [];
const eslesmeyen = [];

for (const f of dunya.features) {
    const ad = String(f.properties?.name ?? '');
    const kod = takma[ad.toLowerCase()] ?? adaKod.get(ad.toLowerCase());

    if (! kod) { eslesmeyen.push(ad); continue; }

    const g = f.geometry;
    if (! g) continue;
    const cokgenler = g.type === 'Polygon' ? [g.coordinates] : g.type === 'MultiPolygon' ? g.coordinates : [];

    let d = '';
    let toplamX = 0, toplamY = 0, sayac = 0;

    for (const cokgen of cokgenler) {
        for (const halka of cokgen) {
            const noktalar = halka.map(([lng, lat]) => [yuvarla(x(lng)), yuvarla(y(lat))]);

            // Görüş alanının tamamen dışındaki halka atlanır.
            if (noktalar.every(([, py]) => py < UST || py > ALT)) continue;

            // Koordinatları sınıra sıkıştırmak (clamp) düz kenarlar üretiyordu:
            // haritanın üstünde ve altında gri bantlar oluşuyordu. Kırpma artık
            // SVG tarafında clipPath ile yapılır, geometri bozulmaz.
            //
            // Tarih çizgisini geçen ülkeler (Alaska, Rusya, Fiji) bir noktadan
            // diğerine haritanın bir ucundan öbür ucuna atlıyor ve arada dev bir
            // dikdörtgen bırakıyordu. Sıçrama görülünce yol kesilir, yeni bir
            // parça başlar.
            const esik = GENISLIK / 2;
            let parca = [];
            const parcalar = [];

            for (const nokta of noktalar) {
                if (parca.length > 0 && Math.abs(nokta[0] - parca[parca.length - 1][0]) > esik) {
                    parcalar.push(parca);
                    parca = [];
                }
                parca.push(nokta);
            }
            if (parca.length > 0) parcalar.push(parca);

            for (const p of parcalar) {
                if (p.length < 3) continue;
                d += 'M' + p.map(([px, py]) => `${px} ${py}`).join('L') + 'Z';
            }

            for (const [px, py] of noktalar) { toplamX += px; toplamY += py; sayac++; }
        }
    }

    if (d === '' || sayac === 0) continue;

    // Kimlik veriliyor ki boyanacak ülke aynı yolu <use> ile tekrar
    // kullanabilsin; sınırı iki kez yazmak dosyayı gereksiz büyütürdü.
    yollar.push(`<path id="ulke-${kod}" d="${d}"/>`);

    // Merkez, sınır noktalarının ortalaması. Kesin ağırlık merkezi değil ama
    // işaret koymak için yeterli ve tek bir uç ada onu kaçırmıyor.
    if (! etiketler.some((e) => e.kod === kod)) {
        etiketler.push({ kod, ad: turkce.of(kod) ?? ad, x: Math.round(toplamX / sayac), y: Math.round(toplamY / sayac) });
    }
}

writeFileSync(
    'resources/views/components/admin/world-map-countries.blade.php',
    `{{-- Bu dosya üretilmiştir: scripts/build-world-map-countries.mjs\n     Kaynak: Natural Earth 110m (kamu malı), world-atlas paketi.\n     Elle düzenlemeyin; değiştirmek için betiği çalıştırın. --}}\n<g class="hc-world-map__ulkeler" aria-hidden="true">${yollar.join('')}</g>\n`
);

etiketler.sort((a, b) => a.kod.localeCompare(b.kod));
const php = etiketler.map((e) => `        '${e.kod}' => ['label' => '${String(e.ad).replace(/'/g, "\\'")}', 'x' => ${e.x}, 'y' => ${e.y}],`).join('\n');

writeFileSync('scripts/.world-countries.php.txt', php);

console.log('ulke yolu:', yollar.length, '| etiket:', etiketler.length);
if (eslesmeyen.length) console.log('eslesmeyen:', eslesmeyen.join(', '));
