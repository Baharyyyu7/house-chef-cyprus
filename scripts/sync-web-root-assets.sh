#!/usr/bin/env bash
set -euo pipefail

# Web kökünü yayındaki sürümün derlenmiş varlıklarıyla eşitler.
#
# public_html, release'in public klasörüne bağlı bir sembolik bağ DEĞİL; ayrı
# bir dizin. Deploy yeni CSS/JS'i releases/<sha>/public/build altına koyuyor,
# tarayıcı ise public_html/build altından okuyor. Bu adım atlanınca sayfa
# HTML'i yeni dosya adını isteyip 404 alıyor ve site stilsiz kalıyor - HTTP
# 200 dönen sayfalar sorunu gizlediği için fark edilmesi de zor.
#
# Bir kez yaşandı: public_html/build/assets içinde 20'den fazla eski app-*.css
# birikmişti, hepsi bu boşluğun elle kapatılmasından kalma.

APP_HOME="${APP_HOME:-$HOME/domains/housechefcyprus.com}"
CURRENT="$APP_HOME/current"
WEB_ROOT="$APP_HOME/public_html"

[ -d "$CURRENT/public/build" ] || { echo "HATA: $CURRENT/public/build yok" >&2; exit 1; }
[ -d "$WEB_ROOT" ] || { echo "HATA: $WEB_ROOT yok" >&2; exit 1; }

mkdir -p "$WEB_ROOT/build/assets"

# Eski dosyalar silinmez: bir önceki sürüme dönüldüğünde onun HTML'i hâlâ
# kendi varlıklarını istiyor olacak.
cp -a "$CURRENT/public/build/assets/." "$WEB_ROOT/build/assets/"
cp -f "$CURRENT/public/build/manifest.json" "$WEB_ROOT/build/manifest.json"

# Video gibi ayrı teslim edilen büyük varlıklar da web kökünde olmalı.
if [ -d "$CURRENT/public/videos" ]; then
    mkdir -p "$WEB_ROOT/videos"
    cp -a "$CURRENT/public/videos/." "$WEB_ROOT/videos/"
fi

# Doğrulama: HTML'in istediği dosyalar gerçekten servis ediliyor mu.
#
# Süreç ikamesi (< <(...)) kullanılmıyor: bu sunucunun kabuğunda /dev/fd
# yok ve komut "No such file or directory" ile düşüyor. Aynı tuzağa
# sha256sum -c ile de bir kez basıldı.
eksik=0
liste="$(mktemp)"

# Manifest metin olarak taranmıyor: desen biçimlendirmeye bağlı ve manifest
# boşluklu yazıldığında grep hiç eşleşme bulmadan sessizce boş liste
# üretiyordu - doğrulama da hiçbir şeyi doğrulamamış oluyordu. JSON'u
# ayrıştıran taraf okusun. Girişlerin yanında gelen css dosyaları da sayılır.
"${PHP_BIN:-/usr/bin/php}" -r '
    $m = json_decode(file_get_contents($argv[1]), true);
    foreach (is_array($m) ? $m : [] as $giris) {
        foreach (array_merge([$giris["file"] ?? null], $giris["css"] ?? []) as $yol) {
            if (is_string($yol) && $yol !== "") { echo "build/".$yol."\n"; }
        }
    }
' "$CURRENT/public/build/manifest.json" > "$liste"

if [ ! -s "$liste" ]; then
    echo "HATA: manifest okunamadı ya da boş; doğrulama yapılamıyor." >&2
    rm -f "$liste"
    exit 1
fi

while read -r dosya; do
    [ -n "$dosya" ] || continue
    if [ ! -f "$WEB_ROOT/$dosya" ]; then
        echo "EKSIK: $dosya" >&2
        eksik=1
    fi
done < "$liste"

rm -f "$liste"

if [ "$eksik" -ne 0 ]; then
    echo "Web kökü eksik dosyayla kaldı; sürüm değiştirilmemeli." >&2
    exit 1
fi

echo "[web-root-sync] Web kökü güncel sürümle eşitlendi."
