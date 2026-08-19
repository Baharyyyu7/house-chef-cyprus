/**
 * Yönetim panelindeki dünya haritasının nokta ızgarasını üretir.
 *
 * Harita elle çizilmiş beş serbest şekilden ibaretti ve hiçbir kıtaya
 * benzemiyordu. Bu betik gerçek sınır verisinden (Natural Earth, kamu malı,
 * world-atlas paketi) eş dikdörtgen izdüşümde bir nokta ızgarası çıkarır.
 *
 * Çıktı statik bir Blade parçasıdır: üretimde ne Node ne de veri dosyası
 * gerekir, sunucuda hesap yapılmaz.
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { feature } from 'topojson-client';

const GENISLIK = 960;
const YUKSEKLIK = 430;
const ADIM = 4.6; // piksel cinsinden nokta aralığı

const topo = JSON.parse(readFileSync('node_modules/world-atlas/countries-110m.json', 'utf8'));
const kara = feature(topo, topo.objects.countries);

// Eş dikdörtgen izdüşüm. Ülke noktaları da aynı formülü kullanacak, yoksa
// nokta ile kara parçası birbirini tutmaz.
const xTo = (lng) => ((lng + 180) / 360) * GENISLIK;
const yTo = (lat) => ((90 - lat) / 180) * YUKSEKLIK;

const halkalar = [];
for (const f of kara.features) {
    const g = f.geometry;
    if (! g) continue;
    const cokgenler = g.type === 'Polygon' ? [g.coordinates] : g.type === 'MultiPolygon' ? g.coordinates : [];
    for (const cokgen of cokgenler) {
        for (const halka of cokgen) {
            halkalar.push(halka.map(([lng, lat]) => [xTo(lng), yTo(lat)]));
        }
    }
}

const icinde = (x, y, halka) => {
    let sonuc = false;
    for (let i = 0, j = halka.length - 1; i < halka.length; j = i++) {
        const [xi, yi] = halka[i];
        const [xj, yj] = halka[j];
        if ((yi > y) !== (yj > y) && x < ((xj - xi) * (y - yi)) / (yj - yi) + xi) {
            sonuc = ! sonuc;
        }
    }
    return sonuc;
};

// Kutupları kırpıyoruz: Antarktika ve uç kuzey haritanın altını ve üstünü
// doldurup okunurluğu düşürüyor, ziyaretçi de oralardan gelmiyor.
const ustSinir = yTo(80);
const altSinir = yTo(-58);

const noktalar = [];
for (let y = ustSinir; y <= altSinir; y += ADIM) {
    for (let x = 0; x <= GENISLIK; x += ADIM) {
        if (halkalar.some((halka) => icinde(x, y, halka))) {
            noktalar.push([Math.round(x * 10) / 10, Math.round(y * 10) / 10]);
        }
    }
}

const daireler = noktalar.map(([x, y]) => `<circle cx="${x}" cy="${y}" r="1.05"/>`).join('');

writeFileSync(
    'resources/views/components/admin/world-map-dots.blade.php',
    `{{-- Bu dosya üretilmiştir: scripts/build-world-map-dots.mjs\n     Kaynak: Natural Earth 110m (kamu malı), world-atlas paketi.\n     Elle düzenlemeyin; haritayı değiştirmek için betiği çalıştırın. --}}\n<g class="hc-world-map__kara" aria-hidden="true">${daireler}</g>\n`
);

console.log('nokta:', noktalar.length);
