# Görsel Tasarım Sistemi

House Chef Cyprus, Kıbrıs mutfağının sıcaklığını sade ve güven veren bir dijital deneyime dönüştürür.

## Temel dil

- **Forest:** `#153224` ana marka rengi; navigasyon, güçlü eylemler ve koyu yüzeyler.
- **Gold:** `#B98427` vurgu, ilerleme ve sıcak eylem rengi.
- **Cream/Paper:** `#FBF7EE` ve `#FFFDF8` okunaklı, doğal yüzeyler.
- Başlıklar **Playfair Display**, gövde metni **Inter** kullanır.

## Bileşen sözleşmesi

`resources/css/app.css` içindeki `hc-*` bileşenleri (container, card, panel, button, badge, alert, table, empty state) tekrar kullanılabilir tek kaynaktır. Controller veya Blade ekranlarında yeni rastgele tasarım token'ı tanımlanmaz.

## Logo

Özgün SVG işaret; aşçı bulutu, ev çatısı ve yapraktan oluşur. `x-brand-logo` açık/koyu yatay sürümü, `public/favicon.svg` uygulama simgesi için kullanılır. Referans siteden logo, kod veya telifli görsel kopyalanmamıştır.

## Durumlar

Başarı, uyarı ve hata mesajları merkezi alert stilleriyle; boş ekranlar ise eyleme yönlendiren `hc-empty` ile sunulur. Hareketler kısa tutulur ve `prefers-reduced-motion` tercihini gözetir.
