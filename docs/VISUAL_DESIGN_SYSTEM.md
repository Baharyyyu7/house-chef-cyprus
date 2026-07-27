# Görsel Tasarım Sistemi

House Chef Cyprus, Kıbrıs mutfağının sıcaklığını sade ve güven veren bir dijital deneyime dönüştürür.

## Temel dil — Visual Redesign V2 preview

- **Charcoal:** `#141414` koyu yüzey, metin ve güçlü kontrast için kullanılır.
- **Orange/Terracotta:** `#F97316` ana vurgu ve eylem rengidir; erişilebilir koyu tonu `#C2410C` olarak tanımlıdır.
- **Ivory/Surface:** `#FFF8F0` ve `#FFFDFC` okunaklı, sıcak nötr yüzeylerdir.
- Başlıklar ve gövde metni **Manrope**; yardımcı metinler **Inter** kullanır.
- Yeşil/forest marka dili V2’de kullanılmaz. Geçiş sürecindeki eski utility adları yalnızca nötr semantic alias olarak kalır; yeni ekranlarda kullanılmaz.

## Bileşen sözleşmesi

`resources/css/app.css` içindeki `hc-*` bileşenleri (container, card, panel, button, badge, alert, table, empty state) tekrar kullanılabilir tek kaynaktır. Controller veya Blade ekranlarında yeni rastgele tasarım token'ı tanımlanmaz.

## Logo

Özgün SVG işaret; House Chef için soyut `H/C` monogramı, sıcak turuncu buhar eğrisi ve koyu yüzeyden oluşur. `x-brand-logo` açık/koyu yatay sürümü, `x-application-logo` ve `public/favicon.svg` aynı marka işaretini kullanır. Referans siteden logo, kod veya telifli görsel kopyalanmamıştır.

## V2 kapsamı

İlk V2 teslimi, yalnızca ana sayfa ile ortak public header/footer kapsamındadır. Tarif listesi, tarif detayı, eşleştirme, dolap, favoriler, cooking mode, kimlik doğrulama ve yönetim ekranlarının işlevsel/kavramsal akışları onaydan önce değiştirilmez.

## Durumlar

Başarı, uyarı ve hata mesajları merkezi alert stilleriyle; boş ekranlar ise eyleme yönlendiren `hc-empty` ile sunulur. Hareketler kısa tutulur ve `prefers-reduced-motion` tercihini gözetir.
