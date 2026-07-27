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

## QA & correction ilkeleri

- Tema tercihi `html.dark` üzerinde, ilk boyamadan önce uygulanır; böylece light/dark geçişinde kısa süreli yanlış tema parlaması oluşmaz.
- Public bileşenlerin zemin, yüzey, metin, sınır ve footer renkleri semantic CSS değişkenlerinden gelir. Koyu temada `--hc-text`, `--hc-muted` ve `--hc-line` ayrı kontrast değerleri kullanır.
- Tarif sonuçları yoksa ana sayfadaki Seçilmiş Tarifler alanı boş kalmaz; markalı fallback sunar. Veri varsa en fazla altı gerçek tarif kaydı gösterilir.
- `prefers-reduced-motion` kullanıcıları için reveal, hero ve hover hareketleri pratikte anlık hâle gelir.

## Durumlar

Başarı, uyarı ve hata mesajları merkezi alert stilleriyle; boş ekranlar ise eyleme yönlendiren `hc-empty` ile sunulur. Hareketler kısa tutulur ve `prefers-reduced-motion` tercihini gözetir.
